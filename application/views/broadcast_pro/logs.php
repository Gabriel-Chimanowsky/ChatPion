<?php $this->load->view('broadcast_pro/broadcast_pro_css'); ?>

<!-- Header -->
<div class="broadcast-header">
    <h2>
        <i class="fas fa-envelope-open-text"></i>
        <?= $this->lang->line("Logs") ?: "Logs" ?>:
        <?= htmlspecialchars($campaign['campaign_name']) ?>
    </h2>
    <p>
        <?= $this->lang->line("Detailed execution logs for this campaign") ?: "Logs detalhados de execução desta campanha." ?>
    </p>
</div>

<!-- Back Button -->
<div class="action-buttons mb-4">
    <a href="<?= site_url('broadcast_pro/monitoring') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i>
        <?= $this->lang->line("Back to Monitoring") ?: "Voltar ao Monitoramento" ?>
    </a>
</div>

<!-- Campaign Info -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="form-section text-center">
            <small class="text-muted">
                <?= $this->lang->line("Scheduled For") ?: "Agendado para" ?>
            </small>
            <h5 class="mt-2">
                <?php if ($campaign['scheduled_at']): ?>
                    <?= date('d/m/Y H:i:s', strtotime($campaign['scheduled_at'])) ?> BRT
                <?php else: ?>
                    -
                <?php endif; ?>
            </h5>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-section text-center">
            <small class="text-muted">
                <?= $this->lang->line("Completed At") ?: "Concluído em" ?>
            </small>
            <h5 class="mt-2">
                <?php if ($campaign['completed_at']): ?>
                    <?= date('d/m/Y H:i:s', strtotime($campaign['completed_at'])) ?> BRT
                <?php else: ?>
                    <span class="text-warning">
                        <?= $this->lang->line("In Progress") ?: "Em andamento" ?>
                    </span>
                <?php endif; ?>
            </h5>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-section text-center">
            <small class="text-muted">
                <?= $this->lang->line("Duration") ?: "Duração Real" ?>
            </small>
            <h5 class="mt-2">
                <?php
                if ($campaign['started_at'] && $campaign['completed_at']) {
                    $start = strtotime($campaign['started_at']);
                    $end = strtotime($campaign['completed_at']);
                    $diff = $end - $start;
                    $hours = floor($diff / 3600);
                    $minutes = floor(($diff % 3600) / 60);
                    $seconds = $diff % 60;
                    echo sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                } else {
                    echo '00:00:00';
                }
                ?>
            </h5>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="log-stats">
    <div class="log-stat-card info">
        <div class="value">
            <?= number_format($campaign['total_leads']) ?>
        </div>
        <div class="label">
            <?= $this->lang->line("Total Leads") ?: "Total Leads" ?>
        </div>
    </div>

    <div class="log-stat-card success">
        <div class="value">
            <?= number_format($campaign['success_count']) ?>
        </div>
        <div class="label">
            <?= $this->lang->line("Success") ?: "Sucesso" ?>
        </div>
    </div>

    <div class="log-stat-card error">
        <div class="value">
            <?= number_format($campaign['failed_count']) ?>
        </div>
        <div class="label">
            <?= $this->lang->line("Failures") ?: "Falhas / Inválidos" ?>
        </div>
    </div>
</div>

<!-- Resume by Page -->
<div class="form-section">
    <h5>
        <i class="fas fa-file-alt"></i>
        <?= $this->lang->line("Summary by Page") ?: "Resumo por Página" ?>
    </h5>

    <?php if (!empty($page_stats)): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>
                            <?= $this->lang->line("Page") ?: "Página" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Total") ?: "Total" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Success") ?: "Sucesso" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Failures") ?: "Falhas" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Pending") ?: "Pendentes" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Active") ?: "Ativos (%)" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Inactive") ?: "Inativos (%)" ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($page_stats as $stat): ?>
                        <tr>
                            <td><strong>
                                    <?= htmlspecialchars($stat['page_name'] ?? 'Página #' . $stat['page_id']) ?>
                                </strong></td>
                            <td>
                                <?= number_format($stat['total_leads']) ?>
                            </td>
                            <td class="text-success">
                                <?= number_format($stat['success_count']) ?>
                            </td>
                            <td class="text-danger">
                                <?= number_format($stat['failed_count']) ?>
                            </td>
                            <td class="text-warning">
                                <?= number_format($stat['total_leads'] - $stat['sent_count']) ?>
                            </td>
                            <td><span class="text-success">
                                    <?= number_format($stat['active_percent'], 1) ?>%
                                </span></td>
                            <td><span class="text-danger">
                                    <?= number_format($stat['inactive_percent'], 1) ?>%
                                </span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted text-center py-3">
            <?= $this->lang->line("No data available") ?: "Nenhum dado disponível" ?>
        </p>
    <?php endif; ?>
</div>

<!-- Errors Section -->
<div class="form-section">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            <i class="fas fa-exclamation-triangle text-danger"></i>
            <?= $this->lang->line("Main Errors (Sample)") ?: "Principais Erros (Amostra)" ?>
        </h5>
        <span class="badge badge-secondary">
            <?= $this->lang->line("Types") ?: "Tipos" ?>:
            <?= count($errors ?? []) ?>
        </span>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>
                            <?= $this->lang->line("Error Code") ?: "Código" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Message") ?: "Mensagem" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Occurrences") ?: "Ocorrências" ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($errors as $error): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($error['error_code']) ?></code></td>
                            <td><small>
                                    <?= htmlspecialchars($error['error_message']) ?>
                                </small></td>
                            <td><span class="badge badge-danger">
                                    <?= number_format($error['count']) ?>
                                </span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted text-center py-3">
            <i class="fas fa-check-circle text-success"></i>
            <?= $this->lang->line("No errors recorded") ?: "Nenhum erro registrado." ?>
        </p>
    <?php endif; ?>
</div>

<?php $this->load->view('broadcast_pro/broadcast_pro_js'); ?>