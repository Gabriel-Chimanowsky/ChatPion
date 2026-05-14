<?php
/**
 * Lead Scanner - View Principal
 * 
 * Exibe grid de páginas do Facebook com contagem de leads
 */
?>

<!-- CSS do Lead Scanner -->
<?php $this->load->view('lead_scanner/lead_scanner_css'); ?>

<div class="lead-scanner-container">

    <!-- Header com estatísticas -->
    <div class="lead-scanner-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4><i class="fas fa-search-plus"></i>
                    <?= $this->lang->line("Lead Scanner") ?: "Lead Scanner" ?>
                </h4>
                <p class="subtitle">
                    <?= $this->lang->line("Collect and manage leads from all your Facebook Pages") ?: "Colete e gerencie leads de todas as suas Páginas do Facebook" ?>
                </p>
            </div>
            <div class="col-md-6">
                <div class="stats-row justify-content-md-end">
                    <div class="stat-card">
                        <span class="number">
                            <?= number_format($total_pages, 0, ',', '.') ?>
                        </span>
                        <span class="label">
                            <?= $this->lang->line("Pages") ?: "Páginas" ?>
                        </span>
                    </div>
                    <div class="stat-card">
                        <span class="number">
                            <?= number_format($total_leads, 0, ',', '.') ?>
                        </span>
                        <span class="label">
                            <?= $this->lang->line("Total Leads") ?: "Total Leads" ?>
                        </span>
                    </div>
                    <div class="stat-card">
                        <span class="number">
                            <?= number_format($total_active, 0, ',', '.') ?>
                        </span>
                        <span class="label">
                            <?= $this->lang->line("Active") ?: "Ativos" ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões de ação -->
        <div class="header-actions">
            <button type="button" id="btn-collect-all" class="btn btn-collect-all">
                <i class="fas fa-sync-alt"></i>
                <?= $this->lang->line("Collect All Pages") ?: "Coletar Todas as Páginas" ?>
            </button>
            <a href="<?= site_url('lead_scanner/export_leads?all=1') ?>" id="btn-export-all" class="btn btn-export-all">
                <i class="fas fa-download"></i>
                <?= $this->lang->line("Export All Leads") ?: "Exportar Todos os Leads" ?>
            </a>
        </div>

        <!-- Progress bar para coleta -->
        <div class="collect-progress">
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
            </div>
            <div class="progress-text">
                <?= $this->lang->line("Collecting leads...") ?: "Coletando leads..." ?>
            </div>
        </div>
    </div>

    <!-- Filtros e Busca -->
    <div class="filters-section">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="filter-tabs">
                    <button class="filter-tab active" data-filter="all">
                        <i class="fas fa-th"></i>
                        <?= $this->lang->line("All") ?: "Todas" ?>
                    </button>
                    <button class="filter-tab" data-filter="with-leads">
                        <i class="fas fa-users"></i>
                        <?= $this->lang->line("With Leads") ?: "Com Leads" ?>
                    </button>
                    <button class="filter-tab" data-filter="no-leads">
                        <i class="fas fa-user-slash"></i>
                        <?= $this->lang->line("No Leads") ?: "Sem Leads" ?>
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" id="search-pages" class="form-control"
                        placeholder="<?= $this->lang->line("Search pages...") ?: "Buscar páginas..." ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de Páginas -->
    <?php if (empty($page_list)): ?>
        <div class="empty-state">
            <i class="fab fa-facebook-square"></i>
            <h5>
                <?= $this->lang->line("No pages found") ?: "Nenhuma página encontrada" ?>
            </h5>
            <p>
                <?= $this->lang->line("Connect your Facebook pages to start collecting leads.") ?: "Conecte suas páginas do Facebook para começar a coletar leads." ?>
            </p>
            <a href="<?= site_url('social_accounts/index') ?>" class="btn btn-primary btn-lg">
                <i class="fab fa-facebook"></i>
                <?= $this->lang->line("Login with Facebook") ?: "Login com Facebook" ?>
            </a>
        </div>
    <?php else: ?>
        <div class="pages-grid">
            <?php foreach ($page_list as $page): ?>
                <div class="page-card" data-page-id="<?= $page['id'] ?>" data-total-leads="<?= $page['total_leads'] ?>">

                    <!-- Ícone/Imagem da página -->
                    <div class="page-card-icon">
                        <?php if (!empty($page['page_image'])): ?>
                            <img src="<?= $page['page_image'] ?>" alt="<?= htmlspecialchars($page['page_name']) ?>">
                        <?php else: ?>
                            <i class="fas fa-chart-bar"></i>
                        <?php endif; ?>
                    </div>

                    <!-- Título e ID -->
                    <h5 class="page-card-title" title="<?= htmlspecialchars($page['page_name']) ?>">
                        <?= htmlspecialchars($page['page_name']) ?>
                    </h5>
                    <div class="page-card-id">
                        <a href="https://facebook.com/<?= $page['page_id'] ?>" target="_blank" class="text-muted">
                            <i class="fab fa-facebook"></i>
                            <?= $page['page_id'] ?>
                        </a>
                    </div>

                    <!-- Badges -->
                    <div class="page-card-badges">
                        <span class="badge-leads">
                            <i class="fas fa-users"></i>
                            <?= number_format($page['total_leads'], 0, ',', '.') ?> Leads
                        </span>
                        <?php if ($page['active_leads'] > 0): ?>
                            <span class="badge-active">
                                <?= number_format($page['active_leads'], 0, ',', '.') ?>
                                <?= $this->lang->line("Active") ?: "Ativos" ?>
                            </span>
                        <?php endif; ?>
                        <?php
                        $inactive = $page['total_leads'] - $page['active_leads'];
                        if ($inactive > 0):
                            ?>
                            <span class="badge-inactive">
                                <?= number_format($inactive, 0, ',', '.') ?>
                                <?= $this->lang->line("Inactive") ?: "Inativos" ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Estatísticas -->
                    <div class="page-card-stats">
                        <div class="page-stat">
                            <span class="value">
                                <?= number_format($page['total_leads'], 0, ',', '.') ?>
                            </span>
                            <span class="label">
                                <?= $this->lang->line("Total") ?: "Total" ?>
                            </span>
                        </div>
                        <div class="page-stat">
                            <span class="value">
                                <?= number_format($page['active_leads'], 0, ',', '.') ?>
                            </span>
                            <span class="label">
                                <?= $this->lang->line("Active") ?: "Ativos" ?>
                            </span>
                        </div>
                        <div class="page-stat">
                            <span class="value">
                                <?= number_format($page['total_leads'] - $page['active_leads'], 0, ',', '.') ?>
                            </span>
                            <span class="label">
                                <?= $this->lang->line("Inactive") ?: "Inativos" ?>
                            </span>
                        </div>
                    </div>

                    <!-- Botões de ação -->
                    <div class="page-card-actions">
                        <button type="button" class="btn btn-view-leads" data-page-id="<?= $page['id'] ?>"
                            data-page-name="<?= htmlspecialchars($page['page_name']) ?>">
                            <i class="fas fa-eye"></i>
                            <?= $this->lang->line("View Leads") ?: "Ver Leads" ?>
                        </button>
                        <button type="button" class="btn btn-collect" data-page-id="<?= $page['id'] ?>">
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Modal para visualizar leads -->
<div class="modal fade leads-modal" id="leadsModal" tabindex="-1" role="dialog" aria-labelledby="leadsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leadsModalLabel">
                    <i class="fas fa-users"></i>
                    <?= $this->lang->line("Leads") ?: "Leads" ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="current-page-id" value="">

                <!-- Ações do modal -->
                <div class="mb-3">
                    <button type="button" id="btn-export-page" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download"></i>
                        <?= $this->lang->line("Export CSV") ?: "Exportar CSV" ?>
                    </button>
                </div>

                <!-- Tabela de leads -->
                <div class="table-responsive">
                    <table id="leads-datatable" class="table table-striped table-hover leads-table" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><input type="checkbox" id="select-all-leads"></th>
                                <th>
                                    <?= $this->lang->line("First Name") ?: "Nome" ?>
                                </th>
                                <th>
                                    <?= $this->lang->line("Last Name") ?: "Sobrenome" ?>
                                </th>
                                <th>
                                    <?= $this->lang->line("Gender") ?: "Gênero" ?>
                                </th>
                                <th>
                                    <?= $this->lang->line("Locale") ?: "Idioma" ?>
                                </th>
                                <th>
                                    <?= $this->lang->line("Email") ?: "Email" ?>
                                </th>
                                <th>
                                    <?= $this->lang->line("Phone") ?: "Telefone" ?>
                                </th>
                                <th>
                                    <?= $this->lang->line("Subscribed At") ?: "Data Inscrição" ?>
                                </th>
                                <th>
                                    <?= $this->lang->line("Status") ?: "Status" ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <?= $this->lang->line("Close") ?: "Fechar" ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JS do Lead Scanner -->
<?php $this->load->view('lead_scanner/lead_scanner_js'); ?>