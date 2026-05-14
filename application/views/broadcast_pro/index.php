<?php $this->load->view('broadcast_pro/broadcast_pro_css'); ?>

<!-- Header -->
<div class="broadcast-header">
    <h2>
        <i class="fas fa-satellite-dish"></i>
        <?= $this->lang->line("Broadcast Pro") ?: "Broadcast Pro" ?>
    </h2>
    <p>
        <?= $this->lang->line("Broadcast scheduling and reporting optimized for high volume") ?: "Agendamentos e relatórios de broadcasts otimizado para alto volume" ?>
    </p>
</div>

<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-paper-plane"></i>
        </div>
        <div class="stat-content">
            <h3>
                <?= number_format($stats['total_campaigns'] ?? 0) ?>
            </h3>
            <p>
                <?= $this->lang->line("Total Campaigns") ?: "Total de Campanhas" ?>
            </p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3>
                <?= number_format($stats['completed_campaigns'] ?? 0) ?>
            </h3>
            <p>
                <?= $this->lang->line("Completed") ?: "Concluídas" ?>
            </p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-envelope"></i>
        </div>
        <div class="stat-content">
            <h3>
                <?= number_format($stats['total_sent'] ?? 0) ?>
            </h3>
            <p>
                <?= $this->lang->line("Messages Sent") ?: "Mensagens Enviadas" ?>
            </p>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="action-buttons mb-4">
    <a href="<?= site_url('broadcast_pro/schedules') ?>" class="btn btn-outline-warning">
        <i class="fas fa-clock"></i>
        <?= $this->lang->line("Scheduler") ?: "Agendador" ?>
    </a>

    <a href="<?= site_url('broadcast_pro/copies') ?>" class="btn btn-outline-primary">
        <i class="fas fa-file-alt"></i>
        <?= $this->lang->line("Copy Manager") ?: "Banco de Copys" ?>
    </a>

    <a href="<?= site_url('broadcast_pro/create') ?>" class="btn btn-success">
        <i class="fas fa-plus"></i>
        <?= $this->lang->line("Create Campaign") ?: "Nova Campanha" ?>
    </a>

    <a href="<?= site_url('broadcast_pro/monitoring') ?>" class="btn btn-info">
        <i class="fas fa-desktop"></i>
        <?= $this->lang->line("Monitoring") ?: "Monitoramento" ?>
    </a>
</div>

<!-- Campaigns Table -->
<div class="campaigns-table">
    <div class="table-header">
        <h5>
            <i class="fas fa-list"></i>
            <?= $this->lang->line("Scheduled and Sent Campaigns") ?: "Campanhas Agendadas e Enviadas" ?>
        </h5>
        <span class="badge badge-secondary">
            <?= $this->lang->line("Total") ?>:
            <?= count($campaigns ?? []) ?>
        </span>
    </div>

    <?php if (!empty($campaigns)): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>
                            <?= $this->lang->line("Campaign Name") ?: "Nome da Campanha" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Type") ?: "Tipo" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Status") ?: "Status" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Scheduled For") ?: "Agendado para" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Progress") ?: "Progresso" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Created") ?: "Criado em" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Actions") ?: "Ações" ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campaigns as $index => $campaign): ?>
                        <tr>
                            <td>
                                <?= $campaign['id'] ?>
                            </td>
                            <td>
                                <strong>
                                    <?= htmlspecialchars($campaign['campaign_name']) ?>
                                </strong>
                            </td>
                            <td>
                                <span class="text-muted text-capitalize">
                                    <?= str_replace('_', ' ', $campaign['template_type']) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $status_class = [
                                    'draft' => 'status-draft',
                                    'scheduled' => 'status-pending',
                                    'processing' => 'status-processing',
                                    'completed' => 'status-completed',
                                    'error' => 'status-error',
                                    'cancelled' => 'status-cancelled'
                                ];
                                $status_label = [
                                    'draft' => 'Rascunho',
                                    'scheduled' => 'Pendente',
                                    'processing' => 'Processando',
                                    'completed' => 'Concluída',
                                    'error' => 'Erro',
                                    'cancelled' => 'Cancelada'
                                ];
                                ?>
                                <span class="status-badge <?= $status_class[$campaign['status']] ?? '' ?>">
                                    <?= $status_label[$campaign['status']] ?? $campaign['status'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($campaign['scheduled_at']): ?>
                                    <?= date('d/m/Y H:i', strtotime($campaign['scheduled_at'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $progress = 0;
                                if ($campaign['total_leads'] > 0) {
                                    $progress = round(($campaign['sent_count'] / $campaign['total_leads']) * 100, 1);
                                }
                                ?>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="campaign-progress" style="flex: 1;">
                                        <div class="progress-bar" style="width: <?= $progress ?>%;"></div>
                                    </div>
                                    <small class="text-muted">
                                        <?= $campaign['sent_count'] ?>/
                                        <?= $campaign['total_leads'] ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('d/m/Y', strtotime($campaign['created_at'])) ?><br>
                                    <?= date('H:i', strtotime($campaign['created_at'])) ?>
                                </small>
                            </td>
                            <td>
                                <a href="<?= site_url('broadcast_pro/logs/' . $campaign['id']) ?>"
                                    class="btn btn-sm btn-outline-info" title="Ver Logs">
                                    <i class="fas fa-chart-line"></i> Ver Logs
                                </a>

                                <?php if (in_array($campaign['status'], ['draft', 'scheduled', 'cancelled', 'completed', 'error'])): ?>
                                    <button class="btn btn-sm btn-outline-danger btn-remove-campaign"
                                        data-id="<?= $campaign['id'] ?>" title="Remover">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state-container">
            <i class="fas fa-inbox"></i>
            <h5>
                <?= $this->lang->line("No campaigns found") ?: "Nenhuma campanha encontrada" ?>
            </h5>
            <p>
                <?= $this->lang->line("Create your first campaign to get started") ?: "Crie sua primeira campanha para começar" ?>
            </p>
            <a href="<?= site_url('broadcast_pro/create') ?>" class="btn btn-primary mt-3">
                <i class="fas fa-plus"></i>
                <?= $this->lang->line("Create Campaign") ?: "Nova Campanha" ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php $this->load->view('broadcast_pro/broadcast_pro_js'); ?>