<?php
/**
 * Broadcast Pro - Worker de Processamento
 * 
 * Este script processa mensagens da fila e envia para a API do Facebook.
 * Deve ser executado via CLI como processo em background.
 * 
 * Uso: php worker_broadcast.php [worker_id]
 * 
 * Exemplo de execução paralela:
 *   php worker_broadcast.php 1 &
 *   php worker_broadcast.php 2 &
 *   php worker_broadcast.php 3 &
 * 
 * @author Chatpeao Team
 * @version 1.0.0
 */

// Verifica se está rodando via CLI
if (php_sapi_name() !== 'cli') {
    die('Este script deve ser executado via linha de comando.');
}

// Configurações
define('BASEPATH', __DIR__ . '/../');
define('WORKER_VERSION', '1.0.0');
define('BATCH_SIZE', 100);              // Mensagens por lote
define('RATE_LIMIT_PER_PAGE', 200);     // Mensagens por segundo por página
define('RETRY_MAX_ATTEMPTS', 3);        // Tentativas máximas
define('SLEEP_BETWEEN_BATCHES', 100);   // Milissegundos entre lotes
define('FACEBOOK_API_VERSION', 'v18.0');

// Worker ID (para logs e identificação)
$worker_id = isset($argv[1]) ? (int) $argv[1] : 1;

// Carrega configuração do banco de dados
$db_content = file_get_contents(__DIR__ . '/../config/database.php');

// Parser para formato CodeIgniter: $db['default']['hostname'] = 'value';
preg_match("/\\['hostname'\\]\\s*=\\s*['\"]([^'\"]+)['\"]/", $db_content, $hostname);
preg_match("/\\['username'\\]\\s*=\\s*['\"]([^'\"]+)['\"]/", $db_content, $username);
preg_match("/\\['password'\\]\\s*=\\s*['\"]([^'\"]*)['\"];/", $db_content, $password);
preg_match("/\\['database'\\]\\s*=\\s*['\"]([^'\"]+)['\"]/", $db_content, $database);

$db_config = [
    'hostname' => $hostname[1] ?? 'localhost',
    'username' => $username[1] ?? 'root',
    'password' => $password[1] ?? '',
    'database' => $database[1] ?? 'chatpeao_db'
];

/**
 * Classe Worker
 */
class BroadcastWorker
{
    private $worker_id;
    private $db;
    private $redis;
    private $running = true;
    private $stats = [
        'processed' => 0,
        'success' => 0,
        'failed' => 0,
        'start_time' => 0
    ];
    private $page_tokens = [];

    public function __construct($worker_id, $db_config)
    {
        $this->worker_id = $worker_id;
        $this->stats['start_time'] = time();

        // Conecta ao MySQL
        $this->db = new mysqli(
            $db_config['hostname'],
            $db_config['username'],
            $db_config['password'],
            $db_config['database']
        );

        if ($this->db->connect_error) {
            $this->log('ERRO: Falha na conexão MySQL: ' . $this->db->connect_error, 'error');
            exit(1);
        }

        $this->db->set_charset('utf8mb4');

        // Conecta ao Redis
        try {
            $this->redis = new Redis();
            $this->redis->connect('127.0.0.1', 6379);

            // Carrega senha do Redis do config
            $redis_password = $this->getRedisPassword();
            if (!empty($redis_password)) {
                $this->redis->auth($redis_password);
            }

            $this->log('Conectado ao Redis');
        } catch (Exception $e) {
            $this->log('AVISO: Redis não disponível, usando apenas MySQL', 'warning');
            $this->redis = null;
        }

        // Configura handler de sinais para shutdown gracioso
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'shutdown']);
            pcntl_signal(SIGINT, [$this, 'shutdown']);
        }

        $this->log('Worker iniciado - Versão ' . WORKER_VERSION);
    }

    /**
     * Handler de shutdown
     */
    public function shutdown()
    {
        $this->log('Recebido sinal de shutdown...');
        $this->running = false;
    }

    /**
     * Loop principal do worker
     */
    public function run()
    {
        while ($this->running) {
            // Processa sinais se disponível
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }

            // Busca campanhas para processar
            $campaign = $this->getNextCampaign();

            if (!$campaign) {
                // Nenhuma campanha, espera 5 segundos
                sleep(5);
                continue;
            }

            $this->log("Processando campanha #{$campaign['id']}: {$campaign['campaign_name']}");

            // Marca campanha como em processamento
            $this->updateCampaignStatus($campaign['id'], 'processing');

            // Processa mensagens da campanha
            $this->processCampaign($campaign);

            // Verifica se a campanha foi concluída
            $this->checkCampaignCompletion($campaign['id']);
        }

        $this->log("Worker finalizado. Estatísticas: " . json_encode($this->stats));
    }

    /**
     * Busca próxima campanha para processar
     * Considera o timezone do usuário ao comparar scheduled_at
     */
    private function getNextCampaign()
    {
        $now_utc = gmdate('Y-m-d H:i:s');

        // A query converte scheduled_at do timezone do usuário para UTC antes de comparar
        // Se timezone for America/Sao_Paulo (UTC-3), adiciona 3 horas ao scheduled_at
        // Exemplo: scheduled_at 16:25 (Brazil) -> 19:25 (UTC)
        $sql = "SELECT * FROM broadcast_pro_campaigns 
                WHERE status IN ('scheduled', 'processing') 
                AND pending_count > 0
                AND (
                    scheduled_at IS NULL 
                    OR CONVERT_TZ(scheduled_at, COALESCE(timezone, 'America/Sao_Paulo'), 'UTC') <= ?
                )
                ORDER BY priority ASC, scheduled_at ASC 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $now_utc);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Processa uma campanha
     */
    private function processCampaign($campaign)
    {
        $campaign_id = $campaign['id'];
        $message_content = json_decode($campaign['message_content'], true);

        // Busca lote de mensagens pendentes
        while ($this->running) {
            $messages = $this->getPendingMessages($campaign_id, BATCH_SIZE);

            if (empty($messages)) {
                $this->log("Campanha #{$campaign_id}: Sem mais mensagens pendentes");
                break;
            }

            $this->log("Campanha #{$campaign_id}: Processando lote de " . count($messages) . " mensagens");

            foreach ($messages as $message) {
                if (!$this->running)
                    break;

                $this->processMessage($campaign, $message, $message_content);

                usleep(SLEEP_BETWEEN_BATCHES * 1000);
            }
        }
    }

    /**
     * Busca mensagens pendentes (com lock real para evitar duplicação)
     * Usa transação + SELECT FOR UPDATE SKIP LOCKED para garantir atomicidade
     */
    private function getPendingMessages($campaign_id, $limit)
    {
        // Inicia transação para garantir atomicidade
        $this->db->begin_transaction();

        try {
            // 1. Seleciona e trava os registros pendentes (SKIP LOCKED ignora registros já travados)
            // FOR UPDATE garante lock exclusivo, SKIP LOCKED evita deadlock
            $sql = "SELECT id FROM broadcast_pro_queue 
                    WHERE campaign_id = ? 
                    AND status = 'pending'
                    ORDER BY priority ASC, id ASC
                    LIMIT ?
                    FOR UPDATE SKIP LOCKED";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ii', $campaign_id, $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $ids = [];
            while ($row = $result->fetch_assoc()) {
                $ids[] = $row['id'];
            }

            if (empty($ids)) {
                $this->db->rollback();
                return [];
            }

            // 2. Marca como processing os IDs que conseguimos travar
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "UPDATE broadcast_pro_queue SET status = 'processing' WHERE id IN ($placeholders)";
            $stmt = $this->db->prepare($sql);

            // Bind dinâmico para os IDs
            $types = str_repeat('i', count($ids));
            $stmt->bind_param($types, ...$ids);
            $stmt->execute();

            // 3. Commit da transação
            $this->db->commit();

            // 4. Agora busca os dados completos (fora da transação)
            $sql = "SELECT q.*, p.page_access_token 
                    FROM broadcast_pro_queue q
                    JOIN facebook_rx_fb_page_info p ON p.id = q.page_id
                    WHERE q.id IN ($placeholders)
                    ORDER BY q.priority ASC, q.id ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$ids);
            $stmt->execute();
            $result = $stmt->get_result();

            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }

            return $messages;

        } catch (Exception $e) {
            $this->db->rollback();
            $this->log("Erro ao buscar mensagens: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Processa uma mensagem individual
     */
    private function processMessage($campaign, $queue_item, $message_content)
    {
        $queue_id = $queue_item['id'];
        $psid = $queue_item['psid'];
        $access_token = $queue_item['page_access_token'];
        $page_id = $queue_item['fb_page_id'];

        try {
            // Monta payload da mensagem
            $payload = $this->buildMessagePayload($message_content, $psid, $campaign['template_type']);

            // Envia para a API do Facebook
            $response = $this->sendToFacebook($page_id, $access_token, $payload);

            if (isset($response['message_id'])) {
                // Sucesso
                $this->markMessageSent($queue_id, $campaign['id'], $queue_item['page_id'], $queue_item['subscriber_id'], $psid, $response['message_id']);
                $this->stats['success']++;
            } else {
                // Erro
                $error_code = $response['error']['code'] ?? 'unknown';
                $error_message = $response['error']['message'] ?? 'Unknown error';
                $this->markMessageFailed($queue_id, $campaign['id'], $queue_item['page_id'], $queue_item['subscriber_id'], $psid, $error_code, $error_message, $queue_item['attempts']);
                $this->stats['failed']++;
            }
        } catch (Exception $e) {
            $this->markMessageFailed($queue_id, $campaign['id'], $queue_item['page_id'], $queue_item['subscriber_id'], $psid, 'exception', $e->getMessage(), $queue_item['attempts']);
            $this->stats['failed']++;
        }

        $this->stats['processed']++;
    }

    /**
     * Constrói payload da mensagem para a API do Facebook
     */
    private function buildMessagePayload($content, $psid, $template_type)
    {
        $payload = [
            'recipient' => ['id' => $psid],
            'messaging_type' => 'MESSAGE_TAG',
            'tag' => 'ACCOUNT_UPDATE'
        ];

        switch ($template_type) {
            case 'text':
                $payload['message'] = ['text' => $content['text'] ?? ''];
                break;

            case 'image':
                $payload['message'] = [
                    'attachment' => [
                        'type' => 'image',
                        'payload' => ['url' => $content['image_url'] ?? '']
                    ]
                ];
                break;

            case 'text_with_buttons':
                $buttons = [];
                foreach (($content['buttons'] ?? []) as $btn) {
                    if (!empty($btn['title']) && !empty($btn['url'])) {
                        $buttons[] = [
                            'type' => 'web_url',
                            'url' => $btn['url'],
                            'title' => $btn['title']
                        ];
                    }
                }

                $payload['message'] = [
                    'attachment' => [
                        'type' => 'template',
                        'payload' => [
                            'template_type' => 'button',
                            'text' => $content['text'] ?? '',
                            'buttons' => $buttons
                        ]
                    ]
                ];
                break;

            case 'generic_template':
                // Template genérico (Card) com imagem, título e botões
                $buttons = [];
                foreach (($content['buttons'] ?? []) as $btn) {
                    if (!empty($btn['title']) && !empty($btn['url'])) {
                        $buttons[] = [
                            'type' => 'web_url',
                            'url' => $btn['url'],
                            'title' => $btn['title']
                        ];
                    }
                }

                $element = [
                    'title' => substr($content['text'] ?? 'Mensagem', 0, 80), // Facebook limita a 80 chars
                    'image_url' => $content['image_url'] ?? ''
                ];

                // Adiciona botões se existirem
                if (!empty($buttons)) {
                    $element['buttons'] = $buttons;
                }

                $payload['message'] = [
                    'attachment' => [
                        'type' => 'template',
                        'payload' => [
                            'template_type' => 'generic',
                            'elements' => [$element]
                        ]
                    ]
                ];
                break;

            default:
                $payload['message'] = ['text' => $content['text'] ?? ''];
        }

        return $payload;
    }

    /**
     * Envia mensagem para a API do Facebook
     */
    private function sendToFacebook($page_id, $access_token, $payload)
    {
        $url = "https://graph.facebook.com/" . FACEBOOK_API_VERSION . "/me/messages?access_token=" . $access_token;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => ['code' => 'curl_error', 'message' => $error]];
        }

        return json_decode($response, true) ?: ['error' => ['code' => 'parse_error', 'message' => 'Could not parse response']];
    }

    /**
     * Marca mensagem como enviada
     */
    private function markMessageSent($queue_id, $campaign_id, $page_id, $subscriber_id, $psid, $fb_message_id)
    {
        $now = date('Y-m-d H:i:s');

        // Atualiza queue
        $sql = "UPDATE broadcast_pro_queue SET status = 'sent', sent_at = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $now, $queue_id);
        $stmt->execute();

        // Registra log
        $sql = "INSERT INTO broadcast_pro_logs (campaign_id, queue_id, page_id, subscriber_id, psid, status, fb_message_id, sent_at) 
                VALUES (?, ?, ?, ?, ?, 'sent', ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iiissss', $campaign_id, $queue_id, $page_id, $subscriber_id, $psid, $fb_message_id, $now);
        $stmt->execute();

        // Atualiza contadores da campanha (com proteção contra underflow)
        $sql = "UPDATE broadcast_pro_campaigns SET sent_count = sent_count + 1, success_count = success_count + 1, pending_count = GREATEST(0, CAST(pending_count AS SIGNED) - 1) WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $campaign_id);
        $stmt->execute();

        // Atualiza contadores por página
        $sql = "UPDATE broadcast_pro_campaign_pages SET sent_count = sent_count + 1, success_count = success_count + 1 WHERE campaign_id = ? AND page_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $campaign_id, $page_id);
        $stmt->execute();
    }

    /**
     * Marca mensagem como falha
     */
    private function markMessageFailed($queue_id, $campaign_id, $page_id, $subscriber_id, $psid, $error_code, $error_message, $attempts)
    {
        $now = date('Y-m-d H:i:s');
        $attempts++;

        if ($attempts >= RETRY_MAX_ATTEMPTS) {
            // Falha definitiva
            $sql = "UPDATE broadcast_pro_queue SET status = 'failed', attempts = ?, last_attempt_at = ?, error_code = ?, error_message = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('isssi', $attempts, $now, $error_code, $error_message, $queue_id);
            $stmt->execute();

            // Atualiza contadores (com proteção contra underflow)
            $sql = "UPDATE broadcast_pro_campaigns SET sent_count = sent_count + 1, failed_count = failed_count + 1, pending_count = GREATEST(0, CAST(pending_count AS SIGNED) - 1) WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $campaign_id);
            $stmt->execute();

            $sql = "UPDATE broadcast_pro_campaign_pages SET sent_count = sent_count + 1, failed_count = failed_count + 1 WHERE campaign_id = ? AND page_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ii', $campaign_id, $page_id);
            $stmt->execute();

            // Registra erro agregado
            $sql = "INSERT INTO broadcast_pro_errors (campaign_id, error_code, error_message, count) 
                    VALUES (?, ?, ?, 1)
                    ON DUPLICATE KEY UPDATE count = count + 1, last_occurrence = NOW()";
            $stmt = $this->db->prepare($sql);
            $error_message_short = substr($error_message, 0, 500);
            $stmt->bind_param('iss', $campaign_id, $error_code, $error_message_short);
            $stmt->execute();
        } else {
            // Volta para fila para retry
            $sql = "UPDATE broadcast_pro_queue SET status = 'pending', attempts = ?, last_attempt_at = ?, error_code = ?, error_message = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('isssi', $attempts, $now, $error_code, $error_message, $queue_id);
            $stmt->execute();
        }

        // Registra log
        $sql = "INSERT INTO broadcast_pro_logs (campaign_id, queue_id, page_id, subscriber_id, psid, status, error_code, error_message) 
                VALUES (?, ?, ?, ?, ?, 'failed', ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iiissss', $campaign_id, $queue_id, $page_id, $subscriber_id, $psid, $error_code, $error_message);
        $stmt->execute();
    }

    /**
     * Atualiza status da fila
     */
    private function updateQueueStatus($queue_id, $status)
    {
        $sql = "UPDATE broadcast_pro_queue SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $status, $queue_id);
        $stmt->execute();
    }

    /**
     * Atualiza status da campanha
     */
    private function updateCampaignStatus($campaign_id, $status)
    {
        $now = date('Y-m-d H:i:s');

        $sql = "UPDATE broadcast_pro_campaigns SET status = ?";
        if ($status === 'processing') {
            $sql .= ", started_at = ?";
        } elseif ($status === 'completed') {
            $sql .= ", completed_at = ?";
        }
        $sql .= " WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        if ($status === 'processing' || $status === 'completed') {
            $stmt->bind_param('ssi', $status, $now, $campaign_id);
        } else {
            $stmt->bind_param('si', $status, $campaign_id);
        }
        $stmt->execute();
    }

    /**
     * Verifica se campanha foi concluída
     */
    private function checkCampaignCompletion($campaign_id)
    {
        $sql = "SELECT COUNT(*) as pending FROM broadcast_pro_queue WHERE campaign_id = ? AND status IN ('pending', 'processing')";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $campaign_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result['pending'] == 0) {
            $this->updateCampaignStatus($campaign_id, 'completed');
            $this->log("Campanha #{$campaign_id}: Concluída!");

            // Calcula percentuais por página
            $this->calculatePagePercentages($campaign_id);
        }
    }

    /**
     * Calcula percentuais de ativos/inativos por página
     */
    private function calculatePagePercentages($campaign_id)
    {
        $sql = "UPDATE broadcast_pro_campaign_pages 
                SET active_percent = (success_count / total_leads) * 100,
                    inactive_percent = (failed_count / total_leads) * 100
                WHERE campaign_id = ? AND total_leads > 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $campaign_id);
        $stmt->execute();
    }

    /**
     * Obtém senha do Redis do arquivo de configuração
     */
    private function getRedisPassword()
    {
        $config_path = __DIR__ . '/../config/my_config.php';
        if (file_exists($config_path)) {
            $content = file_get_contents($config_path);
            if (preg_match("/'redis_password'\\s*=>\\s*'([^']*)'/", $content, $matches)) {
                return $matches[1];
            }
        }
        return '';
    }

    /**
     * Log com timestamp e worker ID
     */
    private function log($message, $level = 'info')
    {
        $timestamp = date('Y-m-d H:i:s');
        $level = strtoupper($level);
        echo "[{$timestamp}] [WORKER-{$this->worker_id}] [{$level}] {$message}\n";
    }

    /**
     * Destrutor
     */
    public function __destruct()
    {
        if ($this->db) {
            $this->db->close();
        }
        if ($this->redis) {
            $this->redis->close();
        }
    }
}

// =============================================================================
// MAIN
// =============================================================================

$worker = new BroadcastWorker($worker_id, $db_config);
$worker->run();
