<?php $this->load->view('broadcast_pro/broadcast_pro_css'); ?>

<!-- Header -->
<div class="broadcast-header">
    <h2>
        <i class="fas fa-desktop"></i>
        <?= $this->lang->line("Campaign Monitoring") ?: "Monitoramento de Campanhas" ?>
    </h2>
    <p>
        <?= $this->lang->line("Track campaign status and progress in real time") ?: "Acompanhe o status e o progresso das campanhas em tempo real." ?>
    </p>
</div>

<!-- Back Button -->
<div class="action-buttons mb-4">
    <a href="<?= site_url('broadcast_pro') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i>
        <?= $this->lang->line("Back") ?: "Voltar para Banco de Copys" ?>
    </a>
</div>

<!-- Pending Campaigns -->
<?php if (!empty($pending_campaigns)): ?>
    <div class="pending-section">
        <h5>
            <i class="fas fa-hourglass-half"></i>
            <?= $this->lang->line("Pending Campaigns") ?: "Campanhas Pendentes" ?>
            (
            <?= count($pending_campaigns) ?>)
        </h5>

        <div class="table-responsive">
            <table class="table table-hover mb-0 bg-white rounded">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>
                            <?= $this->lang->line("Campaign") ?: "Campanha" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Type") ?: "Tipo" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Scheduled") ?: "Agendada (BRT)" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Status") ?: "Status" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Progress") ?: "Progresso" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Actions") ?: "Ações" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Logs") ?: "Logs" ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_campaigns as $campaign): ?>
                        <tr>
                            <td>
                                <?= $campaign['id'] ?>
                            </td>
                            <td><strong>
                                    <?= htmlspecialchars($campaign['campaign_name']) ?>
                                </strong></td>
                            <td>
                                <span class="text-muted text-capitalize">
                                    <?= str_replace('_', ' ', $campaign['template_type']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($campaign['scheduled_at']): ?>
                                    <?= date('d/m/Y H:i:s', strtotime($campaign['scheduled_at'])) ?> BR
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge status-pending">Pendente</span>
                            </td>
                            <td>
                                <span class="text-muted">
                                    <?= $campaign['sent_count'] ?>/
                                    <?= $campaign['total_leads'] ?>
                                </span>
                                <div class="campaign-progress mt-1">
                                    <div class="progress-bar" style="width: 0%;"></div>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger btn-remove-campaign"
                                    data-id="<?= $campaign['id'] ?>">
                                    <i class="fas fa-trash"></i> Remover
                                </button>
                            </td>
                            <td>
                                <a href="<?= site_url('broadcast_pro/logs/' . $campaign['id']) ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-chart-line"></i> Ver Logs
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Processing Campaigns -->
<?php if (!empty($processing_campaigns)): ?>
    <div class="form-section">
        <h5 class="text-primary">
            <i class="fas fa-cog fa-spin"></i>
            <?= $this->lang->line("Processing Campaigns") ?: "Campanhas em Processamento" ?>
            (
            <?= count($processing_campaigns) ?>)
        </h5>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>
                            <?= $this->lang->line("Campaign") ?: "Campanha" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Started") ?: "Iniciada em" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Progress") ?: "Progresso" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Success") ?: "Sucesso" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Failed") ?: "Falhas" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Logs") ?: "Logs" ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($processing_campaigns as $campaign): ?>
                        <?php
                        $progress = 0;
                        if ($campaign['total_leads'] > 0) {
                            $progress = round(($campaign['sent_count'] / $campaign['total_leads']) * 100, 1);
                        }
                        ?>
                        <tr>
                            <td>
                                <?= $campaign['id'] ?>
                            </td>
                            <td><strong>
                                    <?= htmlspecialchars($campaign['campaign_name']) ?>
                                </strong></td>
                            <td>
                                <?php if ($campaign['started_at']): ?>
                                    <?= date('d/m/Y H:i:s', strtotime($campaign['started_at'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="campaign-progress" style="flex: 1; min-width: 150px;">
                                        <div class="progress-bar" style="width: <?= $progress ?>%;"></div>
                                    </div>
                                    <strong class="text-primary processing-indicator">
                                        <?= $progress ?>%
                                    </strong>
                                </div>
                                <small class="text-muted">
                                    <?= $campaign['sent_count'] ?> /
                                    <?= $campaign['total_leads'] ?>
                                </small>
                            </td>
                            <td>
                                <span class="text-success font-weight-bold">
                                    <?= number_format($campaign['success_count']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-danger font-weight-bold">
                                    <?= number_format($campaign['failed_count']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= site_url('broadcast_pro/logs/' . $campaign['id']) ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-chart-line"></i> Ver Logs
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Empty State -->
<?php if (empty($pending_campaigns) && empty($processing_campaigns)): ?>
    <div class="form-section">
        <div class="empty-state-container">
            <i class="fas fa-check-circle text-success"></i>
            <h5>
                <?= $this->lang->line("No campaigns to monitor") ?: "Nenhuma campanha para monitorar" ?>
            </h5>
            <p>
                <?= $this->lang->line("All campaigns have been processed") ?: "Todas as campanhas foram processadas" ?>
            </p>
            <a href="<?= site_url('broadcast_pro') ?>" class="btn btn-primary mt-3">
                <i class="fas fa-arrow-left"></i>
                <?= $this->lang->line("Back to Dashboard") ?: "Voltar ao Painel Principal" ?>
            </a>
        </div>
    </div>
<?php endif; ?>

<?php $this->load->view('broadcast_pro/broadcast_pro_js'); ?>