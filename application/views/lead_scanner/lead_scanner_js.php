<script>
    $(document).ready(function () {
        // =====================================================
        // Lead Scanner - JavaScript
        // =====================================================

        // Token CSRF para requisições AJAX
        var csrfToken = '<?= $this->session->userdata("csrf_token_session") ?>';
        var baseUrl = '<?= base_url() ?>';

        // DataTable para modal de leads
        var leadsTable = null;

        // =====================================================
        // Funções de Utilidade
        // =====================================================

        /**
         * Exibe notificação usando SweetAlert (disponível no projeto)
         */
        function showToast(message, type) {
            type = type || 'info';
            var iconMap = {
                'success': 'success',
                'error': 'error',
                'warning': 'warning',
                'info': 'info'
            };
            var titleMap = {
                'success': '<?= $this->lang->line("Success") ?: "Sucesso" ?>',
                'error': '<?= $this->lang->line("Error") ?: "Erro" ?>',
                'warning': '<?= $this->lang->line("Warning") ?: "Atenção" ?>',
                'info': 'Lead Scanner'
            };

            swal(titleMap[type] || 'Lead Scanner', message, iconMap[type] || 'info');
        }

        /**
         * Formata números com separador de milhares
         */
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // =====================================================
        // Coleta de Leads
        // =====================================================

        /**
         * Coleta leads de uma página específica
         */
        $(document).on('click', '.btn-collect', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var pageId = $btn.data('page-id');
            var $card = $btn.closest('.page-card');
            var originalText = $btn.html();

            // Desabilita botão e mostra loading
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            $card.addClass('collecting');

            $.ajax({
                url: baseUrl + 'lead_scanner/collect_page_leads',
                type: 'POST',
                data: {
                    page_id: pageId,
                    csrf_token: csrfToken
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        showToast(response.message, 'success');

                        // Atualiza contadores no card
                        if (response.count !== undefined) {
                            $card.find('.page-stat .value').first().text(formatNumber(response.count));
                        }
                        if (response.active !== undefined) {
                            $card.find('.badge-active').text(formatNumber(response.active) + ' <?= $this->lang->line("Active") ?: "Ativos" ?>');
                        }
                    } else {
                        showToast(response.message || '<?= $this->lang->line("Error collecting leads") ?: "Erro ao coletar leads" ?>', 'error');
                    }
                },
                error: function () {
                    showToast('<?= $this->lang->line("Connection error") ?: "Erro de conexão" ?>', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).html(originalText);
                    $card.removeClass('collecting');
                }
            });
        });

        /**
         * Coleta leads de todas as páginas
         */
        $(document).on('click', '#btn-collect-all', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var originalText = $btn.html();
            var $progress = $('.collect-progress');

            // Confirma ação
            if (!confirm('<?= $this->lang->line("This will collect leads from all pages. Continue?") ?: "Isso irá coletar leads de todas as páginas. Continuar?" ?>')) {
                return;
            }

            // Desabilita botão e mostra loading
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> <?= $this->lang->line("Collecting...") ?: "Coletando..." ?>');
            $progress.show();

            $.ajax({
                url: baseUrl + 'lead_scanner/collect_all_leads',
                type: 'POST',
                data: {
                    csrf_token: csrfToken
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        showToast(response.message, 'success');

                        // Recarrega a página para atualizar contadores
                        setTimeout(function () {
                            location.reload();
                        }, 1500);
                    } else {
                        showToast(response.message || '<?= $this->lang->line("Error collecting leads") ?: "Erro ao coletar leads" ?>', 'error');
                    }
                },
                error: function () {
                    showToast('<?= $this->lang->line("Connection error") ?: "Erro de conexão" ?>', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).html(originalText);
                    $progress.hide();
                }
            });
        });

        // =====================================================
        // Visualização de Leads
        // =====================================================

        /**
         * Abre modal com lista de leads da página
         */
        $(document).on('click', '.btn-view-leads', function (e) {
            e.preventDefault();
            var pageId = $(this).data('page-id');
            var pageName = $(this).data('page-name');

            // Atualiza título do modal
            $('#leadsModalLabel').text('<?= $this->lang->line("Leads from:") ?: "Leads de:" ?> ' + pageName);
            $('#current-page-id').val(pageId);

            // Destrói DataTable anterior se existir
            if (leadsTable !== null) {
                leadsTable.destroy();
            }

            // Inicializa DataTable
            leadsTable = $('#leads-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: baseUrl + 'lead_scanner/get_leads_by_page',
                    type: 'POST',
                    data: function (d) {
                        d.page_id = pageId;
                        d.csrf_token = csrfToken;
                    }
                },
                columns: [
                    { data: 0, title: '#', orderable: false },
                    { data: 1, title: '<input type="checkbox" id="select-all-leads">', orderable: false },
                    { data: 2, title: '<?= $this->lang->line("First Name") ?: "Nome" ?>' },
                    { data: 3, title: '<?= $this->lang->line("Last Name") ?: "Sobrenome" ?>' },
                    { data: 4, title: '<?= $this->lang->line("Gender") ?: "Gênero" ?>' },
                    { data: 5, title: '<?= $this->lang->line("Locale") ?: "Idioma" ?>' },
                    { data: 6, title: '<?= $this->lang->line("Email") ?: "Email" ?>' },
                    { data: 7, title: '<?= $this->lang->line("Phone") ?: "Telefone" ?>' },
                    { data: 8, title: '<?= $this->lang->line("Subscribed At") ?: "Data Inscrição" ?>' },
                    { data: 9, title: '<?= $this->lang->line("Status") ?: "Status" ?>' }
                ],
                order: [[8, 'desc']],
                pageLength: 10,
                language: {
                    url: '<?= base_url("assets/datatables/pt-BR.json") ?>',
                    processing: '<i class="fas fa-spinner fa-spin fa-2x"></i>',
                    emptyTable: '<?= $this->lang->line("No leads found") ?: "Nenhum lead encontrado" ?>'
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
            });

            // Abre o modal
            $('#leadsModal').modal('show');
        });

        /**
         * Seleciona/deseleciona todos os leads
         */
        $(document).on('change', '#select-all-leads', function () {
            var isChecked = $(this).prop('checked');
            $('#leads-datatable tbody input[type="checkbox"]').prop('checked', isChecked);
        });

        // =====================================================
        // Exportação
        // =====================================================

        /**
         * Exporta leads da página atual
         */
        $(document).on('click', '#btn-export-page', function (e) {
            e.preventDefault();
            var pageId = $('#current-page-id').val();
            window.location.href = baseUrl + 'lead_scanner/export_leads?page_id=' + pageId;
        });

        /**
         * Exporta todos os leads
         */
        $(document).on('click', '#btn-export-all', function (e) {
            e.preventDefault();
            window.location.href = baseUrl + 'lead_scanner/export_leads?all=1';
        });

        // =====================================================
        // Filtros
        // =====================================================

        /**
         * Filtra cards por tipo
         */
        $(document).on('click', '.filter-tab', function () {
            var filter = $(this).data('filter');

            $('.filter-tab').removeClass('active');
            $(this).addClass('active');

            if (filter === 'all') {
                $('.page-card').show();
            } else if (filter === 'with-leads') {
                $('.page-card').each(function () {
                    var totalLeads = parseInt($(this).data('total-leads')) || 0;
                    $(this).toggle(totalLeads > 0);
                });
            } else if (filter === 'no-leads') {
                $('.page-card').each(function () {
                    var totalLeads = parseInt($(this).data('total-leads')) || 0;
                    $(this).toggle(totalLeads === 0);
                });
            }
        });

        // =====================================================
        // Busca
        // =====================================================

        /**
         * Busca em tempo real
         */
        $('#search-pages').on('keyup', function () {
            var searchTerm = $(this).val().toLowerCase();

            $('.page-card').each(function () {
                var pageName = $(this).find('.page-card-title').text().toLowerCase();
                $(this).toggle(pageName.indexOf(searchTerm) > -1);
            });
        });

    });
</script>