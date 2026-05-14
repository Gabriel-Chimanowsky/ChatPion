<script>
    $(document).ready(function () {
        // =====================================================
        // Broadcast Pro - JavaScript
        // =====================================================

        var csrfToken = '<?= $this->session->userdata("csrf_token_session") ?>';
        var baseUrl = '<?= base_url() ?>';

        // =====================================================
        // Remover Campanha
        // =====================================================
        $(document).on('click', '.btn-remove-campaign', function () {
            var campaignId = $(this).data('id');
            var $btn = $(this);

            swal({
                title: '<?= $this->lang->line("Are you sure") ?: "Tem certeza?" ?>',
                text: '<?= $this->lang->line("This action cannot be undone") ?: "Esta ação não pode ser desfeita" ?>',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                    $.ajax({
                        url: baseUrl + 'broadcast_pro/remove_campaign',
                        type: 'POST',
                        data: {
                            campaign_id: campaignId,
                            csrf_token: csrfToken
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'success') {
                                swal('<?= $this->lang->line("Success") ?: "Sucesso" ?>', response.message, 'success')
                                    .then(() => location.reload());
                            } else {
                                swal('<?= $this->lang->line("Error") ?: "Erro" ?>', response.message, 'error');
                                $btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                            }
                        },
                        error: function () {
                            swal('<?= $this->lang->line("Error") ?: "Erro" ?>', '<?= $this->lang->line("Connection error") ?: "Erro de conexão" ?>', 'error');
                            $btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                        }
                    });
                }
            });
        });

        // =====================================================
        // Atualização automática de status (polling)
        // =====================================================
        function updateProcessingCampaigns() {
            $('.status-processing').each(function () {
                var $row = $(this).closest('tr');
                var campaignId = $row.find('td:first').text();

                $.ajax({
                    url: baseUrl + 'broadcast_pro/get_campaign_status/' + campaignId,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            var campaign = response.campaign;
                            var $progressBar = $row.find('.campaign-progress .progress-bar');
                            var $progressText = $row.find('.campaign-progress').siblings('small');

                            $progressBar.css('width', campaign.progress + '%');
                            $progressText.text(campaign.sent_count + '/' + campaign.total_leads);

                            // Atualiza status se mudou
                            if (campaign.status !== 'processing') {
                                location.reload();
                            }
                        }
                    }
                });
            });
        }

        // Atualiza a cada 5 segundos se houver campanhas em processamento
        if ($('.status-processing').length > 0) {
            setInterval(updateProcessingCampaigns, 5000);
        }

        // =====================================================
        // Copys - Reativar
        // =====================================================
        $(document).on('click', '#btn-reactivate-copies', function () {
            var $btn = $(this);

            swal({
                title: '<?= $this->lang->line("Reactivate copies") ?: "Reativar copys?" ?>',
                text: '<?= $this->lang->line("All used copies will be available again") ?: "Todas as copys usadas ficarão disponíveis novamente" ?>',
                icon: 'info',
                buttons: true,
            }).then((willReactivate) => {
                if (willReactivate) {
                    $btn.prop('disabled', true);

                    $.ajax({
                        url: baseUrl + 'broadcast_pro/reactivate_copies',
                        type: 'POST',
                        data: { csrf_token: csrfToken },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'success') {
                                swal('<?= $this->lang->line("Success") ?: "Sucesso" ?>', response.message, 'success')
                                    .then(() => location.reload());
                            } else {
                                swal('<?= $this->lang->line("Error") ?: "Erro" ?>', response.message, 'error');
                            }
                            $btn.prop('disabled', false);
                        },
                        error: function () {
                            swal('<?= $this->lang->line("Error") ?: "Erro" ?>', '<?= $this->lang->line("Connection error") ?: "Erro de conexão" ?>', 'error');
                            $btn.prop('disabled', false);
                        }
                    });
                }
            });
        });

        // =====================================================
        // Schedules - Salvar horário
        // =====================================================
        $(document).on('submit', '#form-schedule', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var originalText = $btn.html();

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');

            $.ajax({
                url: baseUrl + 'broadcast_pro/save_schedule',
                type: 'POST',
                data: $form.serialize() + '&csrf_token=' + csrfToken,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        swal('<?= $this->lang->line("Success") ?: "Sucesso" ?>', response.message, 'success')
                            .then(() => location.reload());
                    } else {
                        swal('<?= $this->lang->line("Error") ?: "Erro" ?>', response.message, 'error');
                    }
                    $btn.prop('disabled', false).html(originalText);
                },
                error: function () {
                    swal('<?= $this->lang->line("Error") ?: "Erro" ?>', '<?= $this->lang->line("Connection error") ?: "Erro de conexão" ?>', 'error');
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });

        // =====================================================
        // Schedules - Remover horário
        // =====================================================
        $(document).on('click', '.btn-delete-schedule', function () {
            var scheduleId = $(this).data('id');
            var $btn = $(this);

            swal({
                title: '<?= $this->lang->line("Remove schedule") ?: "Remover horário?" ?>',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $btn.prop('disabled', true);

                    $.ajax({
                        url: baseUrl + 'broadcast_pro/delete_schedule',
                        type: 'POST',
                        data: {
                            schedule_id: scheduleId,
                            csrf_token: csrfToken
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'success') {
                                $btn.closest('.schedule-item').fadeOut(300, function () {
                                    $(this).remove();
                                });
                            } else {
                                swal('<?= $this->lang->line("Error") ?: "Erro" ?>', response.message, 'error');
                            }
                            $btn.prop('disabled', false);
                        },
                        error: function () {
                            swal('<?= $this->lang->line("Error") ?: "Erro" ?>', '<?= $this->lang->line("Connection error") ?: "Erro de conexão" ?>', 'error');
                            $btn.prop('disabled', false);
                        }
                    });
                }
            });
        });

        // =====================================================
        // Create Campaign Form
        // =====================================================
        $(document).on('submit', '#form-create-campaign', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var originalText = $btn.html();

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Criando...');

            $.ajax({
                url: baseUrl + 'broadcast_pro/create_campaign_action',
                type: 'POST',
                data: $form.serialize() + '&csrf_token=' + csrfToken,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        swal('<?= $this->lang->line("Success") ?: "Sucesso" ?>',
                            response.message + ' (' + response.leads_count + ' leads)',
                            'success')
                            .then(() => window.location.href = baseUrl + 'broadcast_pro');
                    } else {
                        swal('<?= $this->lang->line("Error") ?: "Erro" ?>', response.message, 'error');
                    }
                    $btn.prop('disabled', false).html(originalText);
                },
                error: function () {
                    swal('<?= $this->lang->line("Error") ?: "Erro" ?>', '<?= $this->lang->line("Connection error") ?: "Erro de conexão" ?>', 'error');
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });

    });
</script>