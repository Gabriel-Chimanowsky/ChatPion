<?php $this->load->view('broadcast_pro/broadcast_pro_css'); ?>

<!-- Header -->
<div class="broadcast-header">
    <h2>
        <i class="fas fa-file-alt"></i>
        <?= $this->lang->line("Copy Manager") ?: "Status das Copys" ?>
    </h2>
    <p>
        <?= $this->lang->line("Manage your message templates") ?: "Acompanhe suas copys cadastradas, usadas e disponíveis para novas campanhas." ?>
    </p>
</div>

<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-file"></i>
        </div>
        <div class="stat-content">
            <h3>
                <?= number_format($stats['total'] ?? 0) ?>
            </h3>
            <p>
                <?= $this->lang->line("Total Copies") ?: "Total de Copys" ?>
            </p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3>
                <?= number_format($stats['used'] ?? 0) ?>
            </h3>
            <p>
                <?= $this->lang->line("Used Copies") ?: "Copys Usadas" ?>
            </p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-folder-open"></i>
        </div>
        <div class="stat-content">
            <h3>
                <?= number_format($stats['available'] ?? 0) ?>
            </h3>
            <p>
                <?= $this->lang->line("Available Copies") ?: "Copys Disponíveis" ?>
            </p>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="action-buttons mb-4">
    <a href="<?= site_url('broadcast_pro') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i>
        <?= $this->lang->line("Back") ?: "Voltar para Banco de Copys" ?>
    </a>

    <button id="btn-reactivate-copies" class="btn btn-primary">
        <i class="fas fa-redo"></i>
        <?= $this->lang->line("Reactivate Used Copies") ?: "Reativar Copys Usadas" ?>
    </button>
</div>

<!-- Copies Table -->
<div class="campaigns-table">
    <div class="table-header">
        <h5>
            <i class="fas fa-list"></i>
            <?= $this->lang->line("Registered Copies") ?: "Copys Cadastradas" ?>
        </h5>
    </div>

    <?php if (!empty($copies)): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>
                            <?= $this->lang->line("Name") ?: "Nome" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Type") ?: "Tipo" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Used") ?: "Usada?" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Last Used") ?: "Último uso" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Total Pages") ?: "Total de páginas" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Linked Campaigns") ?: "Campanhas vinculadas" ?>
                        </th>
                        <th>
                            <?= $this->lang->line("Actions") ?: "Ações" ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($copies as $copy): ?>
                        <tr>
                            <td>
                                <?= $copy['id'] ?>
                            </td>
                            <td><strong>
                                    <?= htmlspecialchars($copy['name']) ?>
                                </strong></td>
                            <td>
                                <span class="text-muted text-capitalize">
                                    <?= str_replace('_', ' ', $copy['template_type']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($copy['is_used']): ?>
                                    <span class="badge badge-warning">Sim</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Não</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($copy['last_used_at']): ?>
                                    <?= date('d/m/Y H:i', strtotime($copy['last_used_at'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $copy['total_pages'] ?>
                            </td>
                            <td>
                                <?= $copy['campaigns_count'] ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary btn-view-copy" data-id="<?= $copy['id'] ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-delete-copy" data-id="<?= $copy['id'] ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state-container">
            <i class="fas fa-file-alt"></i>
            <h5>
                <?= $this->lang->line("No copies found") ?: "Nenhuma copy encontrada." ?>
            </h5>
            <p>
                <?= $this->lang->line("Create copies when creating campaigns") ?: "As copys são criadas ao criar novas campanhas" ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php $this->load->view('broadcast_pro/broadcast_pro_js'); ?>