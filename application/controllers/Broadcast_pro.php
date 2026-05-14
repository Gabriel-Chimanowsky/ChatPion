<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once("Home.php"); // Carrega o controller base

/**
 * Broadcast Pro Controller
 * 
 * Sistema de broadcast otimizado para alto volume de mensagens.
 * Utiliza Redis para filas e workers paralelos para disparo.
 * 
 * @author Chatpeao Team
 * @version 1.0.0
 */
class Broadcast_pro extends Home
{
    /**
     * Conexão com Redis
     * @var Redis
     */
    private $redis;

    /**
     * ID do módulo para verificação de permissão
     * Usa o mesmo ID do Broadcasting (275)
     */
    private $module_id = 275;

    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct();

        // Verifica se usuário está logado
        if (!$this->session->userdata('user_id')) {
            redirect('home/login_page', 'location');
        }

        // Verifica permissão do módulo
        if ($this->session->userdata('user_type') != 'Admin' && !in_array($this->module_id, $this->module_access)) {
            redirect('home/login_page', 'location');
        }

        // Inicializa conexão com Redis
        $this->_init_redis();
    }

    /**
     * Inicializa conexão com Redis
     */
    private function _init_redis()
    {
        // Verifica se a extensão Redis está carregada
        if (!extension_loaded('redis')) {
            log_message('info', 'Broadcast Pro - Redis extension not loaded, using MySQL fallback');
            $this->redis = null;
            return;
        }

        try {
            $this->redis = new Redis();
            $this->redis->connect('127.0.0.1', 6379);

            // Autenticação Redis (se configurada)
            $redis_password = $this->config->item('redis_password');
            if (!empty($redis_password)) {
                $this->redis->auth($redis_password);
            }

            $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_JSON);
        } catch (Exception $e) {
            log_message('error', 'Broadcast Pro - Redis connection failed: ' . $e->getMessage());
            $this->redis = null;
        }
    }

    // =========================================================================
    // PÁGINAS PRINCIPAIS
    // =========================================================================

    /**
     * Dashboard principal - Lista de campanhas
     */
    public function index()
    {
        $data['body'] = 'broadcast_pro/index';
        $data['page_title'] = $this->lang->line("Broadcast Pro") ?: "Broadcast Pro";

        // Busca campanhas do usuário
        $data['campaigns'] = $this->_get_campaigns();
        $data['stats'] = $this->_get_dashboard_stats();

        $this->_viewcontroller($data);
    }

    /**
     * Página de criar nova campanha
     */
    public function create()
    {
        $data['body'] = 'broadcast_pro/create';
        $data['page_title'] = $this->lang->line("Create Campaign") ?: "Nova Campanha";

        // Busca páginas do usuário
        $data['pages'] = $this->_get_user_pages();

        // Busca copys disponíveis
        $data['copies'] = $this->_get_available_copies();

        $this->_viewcontroller($data);
    }

    /**
     * Gerenciador de Copys (Templates)
     */
    public function copies()
    {
        $data['body'] = 'broadcast_pro/copies';
        $data['page_title'] = $this->lang->line("Copy Manager") ?: "Banco de Copys";

        $data['copies'] = $this->_get_all_copies();
        $data['stats'] = $this->_get_copies_stats();

        $this->_viewcontroller($data);
    }

    /**
     * Agendador de horários automáticos
     */
    public function schedules()
    {
        $data['body'] = 'broadcast_pro/schedules';
        $data['page_title'] = $this->lang->line("Auto Scheduler") ?: "Agendador Automático";

        $data['schedules'] = $this->_get_schedules();
        $data['timezone'] = $this->_get_user_timezone();

        $this->_viewcontroller($data);
    }

    /**
     * Monitoramento de campanhas em tempo real
     */
    public function monitoring()
    {
        $data['body'] = 'broadcast_pro/monitoring';
        $data['page_title'] = $this->lang->line("Campaign Monitoring") ?: "Monitoramento de Campanhas";

        $data['pending_campaigns'] = $this->_get_pending_campaigns();
        $data['processing_campaigns'] = $this->_get_processing_campaigns();

        $this->_viewcontroller($data);
    }

    /**
     * Logs detalhados de uma campanha
     */
    public function logs($campaign_id = null)
    {
        if (!$campaign_id) {
            redirect('broadcast_pro/monitoring');
        }

        // Verifica se a campanha pertence ao usuário
        $campaign = $this->_get_campaign_by_id($campaign_id);
        if (!$campaign) {
            redirect('broadcast_pro/monitoring');
        }

        $data['body'] = 'broadcast_pro/logs';
        $data['page_title'] = $this->lang->line("Campaign Logs") ?: "Logs: " . $campaign['campaign_name'];

        $data['campaign'] = $campaign;
        $data['page_stats'] = $this->_get_campaign_page_stats($campaign_id);
        $data['errors'] = $this->_get_campaign_errors($campaign_id);

        $this->_viewcontroller($data);
    }

    // =========================================================================
    // AÇÕES AJAX
    // =========================================================================

    /**
     * Cria uma nova campanha
     * AJAX POST
     */
    public function create_campaign_action()
    {
        $this->csrf_token_check();

        $campaign_name = $this->input->post('campaign_name', true);
        $momento_dia = $this->input->post('momento_dia', true);
        $template_type = $this->input->post('template_type', true);
        $message_content = $this->input->post('message_content', true);

        // Validações
        if (empty($campaign_name)) {
            echo json_encode(['status' => 'error', 'message' => 'Nome da campanha é obrigatório']);
            return;
        }

        if (empty($momento_dia) || !in_array($momento_dia, ['manha', 'tarde', 'noite'])) {
            echo json_encode(['status' => 'error', 'message' => 'Selecione o momento do dia']);
            return;
        }

        // Busca horário aleatório do período
        $schedule = $this->_get_random_schedule_time($momento_dia);
        if (!$schedule) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Nenhum horário cadastrado para o período "' . $momento_dia . '". Cadastre horários primeiro.'
            ]);
            return;
        }

        // Calcula o próximo datetime para o horário
        $scheduled_at = $this->_get_next_datetime_for_time($schedule['time']);

        // Monta dados da campanha
        $campaign_data = [
            'user_id' => $this->user_id,
            'campaign_name' => $campaign_name,
            'template_type' => $template_type ?: 'text_with_buttons',
            'momento_dia' => $momento_dia,
            'message_content' => json_encode($message_content),
            'status' => 'scheduled',
            'schedule_type' => 'auto',
            'scheduled_at' => $scheduled_at,
            'timezone' => $this->_get_user_timezone(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Insere campanha
        $this->db->insert('broadcast_pro_campaigns', $campaign_data);
        $campaign_id = $this->db->insert_id();

        if ($campaign_id) {
            // Popula a fila com os leads de todas as páginas
            $leads_count = $this->_populate_queue($campaign_id);

            // Atualiza total de leads na campanha
            $this->db->where('id', $campaign_id);
            $this->db->update('broadcast_pro_campaigns', [
                'total_leads' => $leads_count,
                'pending_count' => $leads_count
            ]);

            // Formata horário para exibição
            $scheduled_display = date('d/m/Y H:i', strtotime($scheduled_at));

            echo json_encode([
                'status' => 'success',
                'message' => "Campanha agendada para {$scheduled_display}",
                'campaign_id' => $campaign_id,
                'leads_count' => $leads_count,
                'scheduled_at' => $scheduled_at
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao criar campanha']);
        }
    }

    /**
     * Popula a fila Redis/MySQL com leads de todas as páginas
     * 
     * @param int $campaign_id
     * @return int Total de leads adicionados
     */
    private function _populate_queue($campaign_id)
    {
        $total_leads = 0;
        $pages = $this->_get_user_pages();

        foreach ($pages as $page) {
            // Busca leads ativos da página
            $leads = $this->db->select('id, subscribe_id')
                ->from('messenger_bot_subscriber')
                ->where('page_table_id', $page['id'])
                ->where('user_id', $this->user_id)
                ->where('unavailable', '0')
                ->get()
                ->result_array();

            $page_leads_count = 0;
            $batch_data = [];

            foreach ($leads as $lead) {
                $queue_item = [
                    'campaign_id' => $campaign_id,
                    'page_id' => $page['id'],
                    'subscriber_id' => $lead['id'],
                    'fb_page_id' => $page['page_id'],
                    'psid' => $lead['subscribe_id'],
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $batch_data[] = $queue_item;
                $page_leads_count++;

                // Insere em lotes de 1000 para performance
                if (count($batch_data) >= 1000) {
                    $this->db->insert_batch('broadcast_pro_queue', $batch_data);
                    $batch_data = [];
                }
            }

            // Insere o restante
            if (!empty($batch_data)) {
                $this->db->insert_batch('broadcast_pro_queue', $batch_data);
            }

            // Registra estatísticas por página
            if ($page_leads_count > 0) {
                $this->db->insert('broadcast_pro_campaign_pages', [
                    'campaign_id' => $campaign_id,
                    'page_id' => $page['id'],
                    'total_leads' => $page_leads_count
                ]);
            }

            $total_leads += $page_leads_count;
        }

        return $total_leads;
    }

    /**
     * Remove uma campanha
     * AJAX POST
     */
    public function remove_campaign()
    {
        $this->csrf_token_check();

        $campaign_id = $this->input->post('campaign_id', true);

        // Verifica se a campanha pertence ao usuário
        $campaign = $this->_get_campaign_by_id($campaign_id);
        if (!$campaign) {
            echo json_encode(['status' => 'error', 'message' => 'Campanha não encontrada']);
            return;
        }

        // Só permite remover se estiver em draft, scheduled ou cancelled
        if (!in_array($campaign['status'], ['draft', 'scheduled', 'cancelled', 'completed', 'error'])) {
            echo json_encode(['status' => 'error', 'message' => 'Não é possível remover campanha em processamento']);
            return;
        }

        // Remove fila, logs e campanha
        $this->db->where('campaign_id', $campaign_id)->delete('broadcast_pro_queue');
        $this->db->where('campaign_id', $campaign_id)->delete('broadcast_pro_logs');
        $this->db->where('campaign_id', $campaign_id)->delete('broadcast_pro_errors');
        $this->db->where('campaign_id', $campaign_id)->delete('broadcast_pro_campaign_pages');
        $this->db->where('id', $campaign_id)->delete('broadcast_pro_campaigns');

        echo json_encode(['status' => 'success', 'message' => 'Campanha removida com sucesso']);
    }

    /**
     * Retorna status da campanha em tempo real
     * AJAX GET
     */
    public function get_campaign_status($campaign_id)
    {
        $campaign = $this->_get_campaign_by_id($campaign_id);
        if (!$campaign) {
            echo json_encode(['status' => 'error', 'message' => 'Campanha não encontrada']);
            return;
        }

        $progress = 0;
        if ($campaign['total_leads'] > 0) {
            $progress = round(($campaign['sent_count'] / $campaign['total_leads']) * 100, 1);
        }

        echo json_encode([
            'status' => 'success',
            'campaign' => [
                'id' => $campaign['id'],
                'status' => $campaign['status'],
                'total_leads' => $campaign['total_leads'],
                'sent_count' => $campaign['sent_count'],
                'success_count' => $campaign['success_count'],
                'failed_count' => $campaign['failed_count'],
                'pending_count' => $campaign['pending_count'],
                'progress' => $progress
            ]
        ]);
    }

    // =========================================================================
    // COPYS (TEMPLATES)
    // =========================================================================

    /**
     * Salva uma nova copy
     * AJAX POST
     */
    public function save_copy()
    {
        $this->csrf_token_check();

        $name = $this->input->post('name', true);
        $template_type = $this->input->post('template_type', true);
        $content = $this->input->post('content', true);

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Nome é obrigatório']);
            return;
        }

        $data = [
            'user_id' => $this->user_id,
            'name' => $name,
            'template_type' => $template_type ?: 'text',
            'content' => json_encode($content),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('broadcast_pro_copies', $data);
        $copy_id = $this->db->insert_id();

        if ($copy_id) {
            echo json_encode(['status' => 'success', 'message' => 'Copy salva com sucesso', 'id' => $copy_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar copy']);
        }
    }

    /**
     * Reativa copys usadas
     * AJAX POST
     */
    public function reactivate_copies()
    {
        $this->csrf_token_check();

        $this->db->where('user_id', $this->user_id);
        $this->db->where('is_used', 1);
        $this->db->update('broadcast_pro_copies', ['is_used' => 0]);

        $affected = $this->db->affected_rows();

        echo json_encode([
            'status' => 'success',
            'message' => $affected . ' copys reativadas com sucesso'
        ]);
    }

    // =========================================================================
    // SCHEDULES (HORÁRIOS)
    // =========================================================================

    /**
     * Salva um novo horário
     * AJAX POST
     */
    public function save_schedule()
    {
        $this->csrf_token_check();

        $time = $this->input->post('time', true);
        $is_active = $this->input->post('is_active', true);

        if (empty($time)) {
            echo json_encode(['status' => 'error', 'message' => 'Horário é obrigatório']);
            return;
        }

        // Calcula o período baseado no horário
        $periodo = $this->_calculate_periodo($time);

        // Verifica se já existe
        $exists = $this->db->where('user_id', $this->user_id)
            ->where('time', $time)
            ->get('broadcast_pro_schedules')
            ->row_array();

        if ($exists) {
            // Atualiza
            $this->db->where('id', $exists['id']);
            $this->db->update('broadcast_pro_schedules', [
                'is_active' => $is_active,
                'periodo' => $periodo
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Horário atualizado']);
        } else {
            // Insere
            $data = [
                'user_id' => $this->user_id,
                'time' => $time,
                'periodo' => $periodo,
                'is_active' => $is_active ?: 1,
                'timezone' => $this->_get_user_timezone()
            ];
            $this->db->insert('broadcast_pro_schedules', $data);
            echo json_encode(['status' => 'success', 'message' => 'Horário adicionado']);
        }
    }

    /**
     * Remove um horário
     * AJAX POST
     */
    public function delete_schedule()
    {
        $this->csrf_token_check();

        $schedule_id = $this->input->post('schedule_id', true);

        $this->db->where('id', $schedule_id);
        $this->db->where('user_id', $this->user_id);
        $this->db->delete('broadcast_pro_schedules');

        echo json_encode(['status' => 'success', 'message' => 'Horário removido']);
    }

    // =========================================================================
    // MÉTODOS AUXILIARES
    // =========================================================================

    /**
     * Busca campanhas do usuário
     */
    private function _get_campaigns($limit = 50)
    {
        return $this->db->select('*')
            ->from('broadcast_pro_campaigns')
            ->where('user_id', $this->user_id)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
    }

    /**
     * Busca campanha por ID (verificando usuário)
     */
    private function _get_campaign_by_id($campaign_id)
    {
        return $this->db->select('*')
            ->from('broadcast_pro_campaigns')
            ->where('id', $campaign_id)
            ->where('user_id', $this->user_id)
            ->get()
            ->row_array();
    }

    /**
     * Busca estatísticas do dashboard
     */
    private function _get_dashboard_stats()
    {
        $stats = [];

        // Total de campanhas
        $stats['total_campaigns'] = $this->db->where('user_id', $this->user_id)
            ->count_all_results('broadcast_pro_campaigns');

        // Campanhas completadas
        $stats['completed_campaigns'] = $this->db->where('user_id', $this->user_id)
            ->where('status', 'completed')
            ->count_all_results('broadcast_pro_campaigns');

        // Total de mensagens enviadas
        $result = $this->db->select_sum('success_count')
            ->where('user_id', $this->user_id)
            ->get('broadcast_pro_campaigns')
            ->row_array();
        $stats['total_sent'] = $result['success_count'] ?: 0;

        return $stats;
    }

    /**
     * Busca páginas do usuário
     */
    private function _get_user_pages()
    {
        return $this->db->select('id, page_id, page_name')
            ->from('facebook_rx_fb_page_info')
            ->where('user_id', $this->user_id)
            ->where('facebook_rx_fb_user_info_id', $this->session->userdata('facebook_rx_fb_user_info'))
            ->where('bot_enabled', '1')
            ->get()
            ->result_array();
    }

    /**
     * Busca copys disponíveis
     */
    private function _get_available_copies()
    {
        return $this->db->select('*')
            ->from('broadcast_pro_copies')
            ->where('user_id', $this->user_id)
            ->where('is_used', 0)
            ->order_by('created_at', 'DESC')
            ->get()
            ->result_array();
    }

    /**
     * Busca todas as copys
     */
    private function _get_all_copies()
    {
        return $this->db->select('*')
            ->from('broadcast_pro_copies')
            ->where('user_id', $this->user_id)
            ->order_by('created_at', 'DESC')
            ->get()
            ->result_array();
    }

    /**
     * Busca estatísticas das copys
     */
    private function _get_copies_stats()
    {
        $stats = [];

        $stats['total'] = $this->db->where('user_id', $this->user_id)
            ->count_all_results('broadcast_pro_copies');

        $stats['used'] = $this->db->where('user_id', $this->user_id)
            ->where('is_used', 1)
            ->count_all_results('broadcast_pro_copies');

        $stats['available'] = $stats['total'] - $stats['used'];

        return $stats;
    }

    /**
     * Busca horários agendados
     */
    private function _get_schedules()
    {
        return $this->db->select('*')
            ->from('broadcast_pro_schedules')
            ->where('user_id', $this->user_id)
            ->order_by('time', 'ASC')
            ->get()
            ->result_array();
    }

    /**
     * Busca campanhas pendentes
     */
    private function _get_pending_campaigns()
    {
        return $this->db->select('*')
            ->from('broadcast_pro_campaigns')
            ->where('user_id', $this->user_id)
            ->where('status', 'scheduled')
            ->order_by('scheduled_at', 'ASC')
            ->get()
            ->result_array();
    }

    /**
     * Busca campanhas em processamento
     */
    private function _get_processing_campaigns()
    {
        return $this->db->select('*')
            ->from('broadcast_pro_campaigns')
            ->where('user_id', $this->user_id)
            ->where('status', 'processing')
            ->order_by('started_at', 'ASC')
            ->get()
            ->result_array();
    }

    /**
     * Busca estatísticas por página de uma campanha
     */
    private function _get_campaign_page_stats($campaign_id)
    {
        return $this->db->select('bcp.*, fp.page_name')
            ->from('broadcast_pro_campaign_pages bcp')
            ->join('facebook_rx_fb_page_info fp', 'fp.id = bcp.page_id', 'left')
            ->where('bcp.campaign_id', $campaign_id)
            ->get()
            ->result_array();
    }

    /**
     * Busca erros de uma campanha
     */
    private function _get_campaign_errors($campaign_id, $limit = 10)
    {
        return $this->db->select('*')
            ->from('broadcast_pro_errors')
            ->where('campaign_id', $campaign_id)
            ->order_by('count', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
    }

    /**
     * Retorna timezone do usuário
     */
    private function _get_user_timezone()
    {
        return $this->session->userdata('time_zone') ?: 'America/Sao_Paulo';
    }

    /**
     * Calcula o período baseado no horário
     * @param string $time Horário no formato HH:MM ou HH:MM:SS
     * @return string manha|tarde|noite
     */
    private function _calculate_periodo($time)
    {
        $hour = (int) substr($time, 0, 2);

        if ($hour >= 6 && $hour < 12) {
            return 'manha';
        } elseif ($hour >= 12 && $hour < 18) {
            return 'tarde';
        } else {
            return 'noite';
        }
    }

    /**
     * Busca um horário aleatório para o período especificado
     * @param string $periodo manha|tarde|noite
     * @return array|null Dados do horário ou null se não existir
     */
    private function _get_random_schedule_time($periodo)
    {
        // Busca todos os horários ativos do período
        $schedules = $this->db->where('user_id', $this->user_id)
            ->where('periodo', $periodo)
            ->where('is_active', 1)
            ->get('broadcast_pro_schedules')
            ->result_array();

        if (empty($schedules)) {
            return null;
        }

        // Escolhe aleatoriamente
        return $schedules[array_rand($schedules)];
    }

    /**
     * Calcula o próximo datetime para um horário
     * Se o horário já passou hoje, retorna amanhã
     * @param string $time Horário no formato HH:MM
     * @return string DateTime no formato Y-m-d H:i:s
     */
    private function _get_next_datetime_for_time($time)
    {
        $timezone = new DateTimeZone($this->_get_user_timezone());
        $now = new DateTime('now', $timezone);

        // Cria datetime com o horário especificado para hoje
        $scheduled = new DateTime('today ' . $time, $timezone);

        // Se já passou, agenda para amanhã
        if ($scheduled <= $now) {
            $scheduled->modify('+1 day');
        }

        return $scheduled->format('Y-m-d H:i:s');
    }
}
