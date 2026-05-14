<?php

require_once("Home.php"); // loading home controller

/**
 * Lead Scanner Controller
 * 
 * Controller para coletar e visualizar leads de todas as páginas do Facebook.
 * Preparação para o novo sistema de Broadcast.
 * 
 * @category Controller
 * @author Chatpeao Team
 */
class Lead_scanner extends Home
{
    /**
     * ID do módulo para verificação de permissões
     * Usando o mesmo ID do módulo de Broadcasting (275)
     */
    private $module_id = 275;

    public function __construct()
    {
        parent::__construct();

        // Verifica se o usuário está logado
        if ($this->session->userdata('logged_in') != 1) {
            redirect('home/login_page', 'location');
        }

        // Verifica permissão de acesso ao módulo
        if ($this->session->userdata('user_type') != 'Admin' && !in_array($this->module_id, $this->module_access)) {
            redirect('home/login_page', 'location');
        }

        // Verifica se o usuário tem conta do Facebook conectada
        $function_name = $this->uri->segment(2);
        if ($function_name != "" && $function_name != "index") {
            if ($this->session->userdata("facebook_rx_fb_user_info") == 0) {
                redirect('social_accounts/index', 'refresh');
            }
        }

        // Carrega funções importantes
        $this->important_feature();
        $this->member_validity();
    }

    /**
     * Página principal do Lead Scanner
     * Exibe grid de páginas com contagem de leads
     */
    public function index()
    {
        // Obtém lista de páginas do usuário
        $where = array(
            "where" => array(
                "user_id" => $this->user_id,
                "bot_enabled" => "1"
            )
        );

        // Filtra por páginas permitidas para team members
        if (!empty($this->team_allowed_pages)) {
            $where['where_in'] = array("facebook_rx_fb_page_info.id" => $this->team_allowed_pages);
        }

        $data['page_list'] = $this->basic->get_data(
            "facebook_rx_fb_page_info",
            $where,
            '',
            '',
            '',
            NULL,
            'page_name ASC'
        );

        // Calcula estatísticas gerais
        $total_leads = 0;
        $total_active = 0;
        $total_pages = count($data['page_list']);

        foreach ($data['page_list'] as $key => $page) {
            // Conta leads da página
            $lead_count = $this->_get_page_lead_count($page['id']);
            $data['page_list'][$key]['total_leads'] = $lead_count['total'];
            $data['page_list'][$key]['active_leads'] = $lead_count['active'];

            $total_leads += $lead_count['total'];
            $total_active += $lead_count['active'];
        }

        $data['total_leads'] = $total_leads;
        $data['total_active'] = $total_active;
        $data['total_pages'] = $total_pages;

        $data['body'] = 'lead_scanner/index';
        $data['page_title'] = $this->lang->line('Lead Scanner') ?: 'Lead Scanner';
        $this->_viewcontroller($data);
    }

    /**
     * AJAX: Retorna dados das páginas com contagem de leads
     */
    public function get_pages_data()
    {
        $this->ajax_check();

        $where = array(
            "where" => array(
                "user_id" => $this->user_id,
                "bot_enabled" => "1"
            )
        );

        if (!empty($this->team_allowed_pages)) {
            $where['where_in'] = array("facebook_rx_fb_page_info.id" => $this->team_allowed_pages);
        }

        $pages = $this->basic->get_data(
            "facebook_rx_fb_page_info",
            $where,
            array('id', 'page_id', 'page_name', 'page_image', 'current_subscribed_lead_count', 'current_unsubscribed_lead_count'),
            '',
            '',
            NULL,
            'page_name ASC'
        );

        $result = array();
        foreach ($pages as $page) {
            $lead_count = $this->_get_page_lead_count($page['id']);
            $result[] = array(
                'id' => $page['id'],
                'page_id' => $page['page_id'],
                'page_name' => $page['page_name'],
                'page_image' => !empty($page['page_image']) ? $page['page_image'] : base_url('assets/images/default_page.png'),
                'total_leads' => $lead_count['total'],
                'active_leads' => $lead_count['active'],
                'inactive_leads' => $lead_count['total'] - $lead_count['active']
            );
        }

        echo json_encode(array('status' => 'success', 'data' => $result));
    }

    /**
     * AJAX: Retorna leads de uma página específica (para DataTables)
     */
    public function get_leads_by_page()
    {
        $this->ajax_check();

        $page_id = $this->input->post("page_id", TRUE);

        if (empty($page_id)) {
            echo json_encode(array('status' => 'error', 'message' => 'ID da página não informado'));
            return;
        }

        // Verifica se a página pertence ao usuário
        $page_info = $this->basic->get_data("facebook_rx_fb_page_info", array(
            "where" => array("id" => $page_id, "user_id" => $this->user_id)
        ));

        if (empty($page_info)) {
            echo json_encode(array('status' => 'error', 'message' => 'Página não encontrada'));
            return;
        }

        // Configuração do DataTables
        $search_value = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

        $display_columns = array(
            "#",
            "CHECKBOX",
            'first_name',
            'last_name',
            'gender',
            'locale',
            'subscribed_at',
            'unavailable'
        );

        $search_columns = array('first_name', 'last_name', 'full_name');

        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 8;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'subscribed_at';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by = $sort . " " . $order;

        // Monta WHERE
        $where_custom = "messenger_bot_subscriber.user_id = " . $this->user_id . " AND page_table_id = " . $page_id . " AND is_bot_subscriber = '1'";

        if ($search_value != '') {
            $temp = array();
            foreach ($search_columns as $value) {
                $temp[] = $value . " LIKE " . "'%$search_value%'";
            }
            $imp = implode(" OR ", $temp);
            $where_custom .= " AND (" . $imp . ") ";
        }

        $this->db->where($where_custom);

        $table = "messenger_bot_subscriber";
        $info = $this->basic->get_data($table, '', '', '', $limit, $start, $order_by, '');

        $this->db->where($where_custom);
        $total_rows_array = $this->basic->count_row($table, '', $table . ".id", '', '');
        $total_result = $total_rows_array[0]['total_rows'];

        // Formata dados para exibição
        foreach ($info as $key => $value) {
            // Status de disponibilidade
            if ($value['unavailable'] == '0') {
                $info[$key]['unavailable'] = '<span class="badge badge-success">' . ($this->lang->line("Active") ?: "Ativo") . '</span>';
            } else {
                $info[$key]['unavailable'] = '<span class="badge badge-danger">' . ($this->lang->line("Inactive") ?: "Inativo") . '</span>';
            }

            // Data de inscrição
            if ($value['subscribed_at'] != "0000-00-00 00:00:00") {
                $info[$key]['subscribed_at'] = date("d/m/Y H:i", strtotime($value['subscribed_at']));
            }


        }

        $data['draw'] = isset($_POST['draw']) ? (int) $_POST['draw'] + 1 : 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns, $start, "id");

        echo json_encode($data);
    }

    /**
     * AJAX: Coleta leads de todas as páginas
     */
    public function collect_all_leads()
    {
        $this->ajax_check();
        $this->csrf_token_check();

        $where = array(
            "where" => array(
                "user_id" => $this->user_id,
                "bot_enabled" => "1"
            )
        );

        if (!empty($this->team_allowed_pages)) {
            $where['where_in'] = array("facebook_rx_fb_page_info.id" => $this->team_allowed_pages);
        }

        $pages = $this->basic->get_data("facebook_rx_fb_page_info", $where, array('id', 'page_name'));

        $results = array();
        $success_count = 0;
        $error_count = 0;

        foreach ($pages as $page) {
            $result = $this->_collect_page_leads($page['id']);
            $results[] = array(
                'page_id' => $page['id'],
                'page_name' => $page['page_name'],
                'status' => $result['status'],
                'message' => $result['message'],
                'count' => isset($result['count']) ? $result['count'] : 0
            );

            if ($result['status'] == 'success') {
                $success_count++;
            } else {
                $error_count++;
            }
        }

        echo json_encode(array(
            'status' => 'success',
            'message' => sprintf(
                $this->lang->line("Collected leads from %d pages. Errors: %d") ?: "Coletados leads de %d páginas. Erros: %d",
                $success_count,
                $error_count
            ),
            'results' => $results,
            'summary' => array(
                'total_pages' => count($pages),
                'success' => $success_count,
                'errors' => $error_count
            )
        ));
    }

    /**
     * AJAX: Coleta leads de uma página específica
     */
    public function collect_page_leads()
    {
        $this->ajax_check();
        $this->csrf_token_check();

        $page_id = $this->input->post("page_id", TRUE);

        if (empty($page_id)) {
            echo json_encode(array('status' => 'error', 'message' => 'ID da página não informado'));
            return;
        }

        $result = $this->_collect_page_leads($page_id);
        echo json_encode($result);
    }

    /**
     * AJAX: Exporta leads para CSV
     */
    public function export_leads()
    {
        $page_id = $this->input->get("page_id", TRUE);
        $export_all = $this->input->get("all", TRUE);

        $where = array(
            "where" => array(
                "messenger_bot_subscriber.user_id" => $this->user_id,
                "is_bot_subscriber" => "1"
            )
        );

        if (!empty($page_id) && $export_all != '1') {
            $where["where"]["page_table_id"] = $page_id;
        }

        $leads = $this->basic->get_data(
            "messenger_bot_subscriber",
            $where,
            array('subscribe_id', 'first_name', 'last_name', 'full_name', 'gender', 'locale', 'timezone', 'subscribed_at', 'unavailable'),
            '',
            '',
            NULL,
            'subscribed_at DESC'
        );

        // Gera CSV
        $filename = 'leads_export_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // BOM para UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Cabeçalho
        fputcsv($output, array(
            'ID do Subscriber',
            'Nome',
            'Sobrenome',
            'Nome Completo',
            'Gênero',
            'Locale',
            'Timezone',
            'Data de Inscrição',
            'Status'
        ), ';');

        // Dados
        foreach ($leads as $lead) {
            fputcsv($output, array(
                $lead['subscribe_id'],
                $lead['first_name'],
                $lead['last_name'],
                $lead['full_name'],
                $lead['gender'],
                $lead['locale'],
                $lead['timezone'],
                $lead['subscribed_at'],
                $lead['unavailable'] == '0' ? 'Ativo' : 'Inativo'
            ), ';');
        }

        fclose($output);
        exit();
    }

    /**
     * Conta leads de uma página
     * 
     * @param int $page_table_id ID da página na tabela
     * @return array Array com 'total' e 'active'
     */
    private function _get_page_lead_count($page_table_id)
    {
        // Total de leads
        $total_result = $this->basic->get_data(
            'messenger_bot_subscriber',
            array('where' => array('page_table_id' => $page_table_id, 'user_id' => $this->user_id, 'is_bot_subscriber' => '1')),
            array('count(id) as total')
        );
        $total = isset($total_result[0]['total']) ? $total_result[0]['total'] : 0;

        // Leads ativos (não indisponíveis)
        $active_result = $this->basic->get_data(
            'messenger_bot_subscriber',
            array('where' => array('page_table_id' => $page_table_id, 'user_id' => $this->user_id, 'is_bot_subscriber' => '1', 'unavailable' => '0')),
            array('count(id) as total')
        );
        $active = isset($active_result[0]['total']) ? $active_result[0]['total'] : 0;

        return array(
            'total' => $total,
            'active' => $active
        );
    }

    /**
     * Coleta leads de uma página específica
     * Atualiza contadores na tabela de páginas
     * 
     * @param int $page_table_id ID da página na tabela
     * @return array Resultado da operação
     */
    private function _collect_page_leads($page_table_id)
    {
        // Verifica se a página pertence ao usuário
        $page_info = $this->basic->get_data("facebook_rx_fb_page_info", array(
            "where" => array("id" => $page_table_id, "user_id" => $this->user_id)
        ));

        if (empty($page_info)) {
            return array('status' => 'error', 'message' => 'Página não encontrada');
        }

        // Conta leads atuais
        $lead_count = $this->_get_page_lead_count($page_table_id);

        // Atualiza contadores na tabela de páginas
        $update_data = array(
            'current_subscribed_lead_count' => $lead_count['active'],
            'current_unsubscribed_lead_count' => $lead_count['total'] - $lead_count['active']
        );

        $this->basic->update_data(
            "facebook_rx_fb_page_info",
            array("id" => $page_table_id),
            $update_data
        );

        return array(
            'status' => 'success',
            'message' => sprintf(
                $this->lang->line("Found %d leads (%d active)") ?: "Encontrados %d leads (%d ativos)",
                $lead_count['total'],
                $lead_count['active']
            ),
            'count' => $lead_count['total'],
            'active' => $lead_count['active']
        );
    }
}
