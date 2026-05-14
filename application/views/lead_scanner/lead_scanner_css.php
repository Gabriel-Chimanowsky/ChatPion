<style>
    /* =====================================================
   Lead Scanner - Estilos Customizados
   ===================================================== */

    /* Container principal */
    .lead-scanner-container {
        padding: 0;
    }

    /* Header com estatísticas */
    .lead-scanner-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
        color: white;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
    }

    .lead-scanner-header h4 {
        margin: 0;
        font-weight: 600;
        font-size: 24px;
    }

    .lead-scanner-header .subtitle {
        opacity: 0.9;
        font-size: 14px;
        margin-top: 5px;
    }

    /* Cards de estatísticas */
    .stats-row {
        display: flex;
        gap: 15px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 15px 25px;
        min-width: 150px;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .stat-card .number {
        font-size: 28px;
        font-weight: 700;
        display: block;
    }

    .stat-card .label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.9;
    }

    /* Botões de ação no header */
    .header-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .header-actions .btn {
        border-radius: 25px;
        padding: 10px 25px;
        font-weight: 500;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-collect-all {
        background: white;
        color: #667eea;
    }

    .btn-collect-all:hover {
        background: #f8f9fa;
        color: #764ba2;
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    }

    .btn-export-all {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .btn-export-all:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
    }

    /* Filtros */
    .filters-section {
        margin-bottom: 20px;
    }

    .filter-tabs {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 8px 20px;
        border-radius: 20px;
        background: #f0f0f0;
        color: #666;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        font-size: 14px;
    }

    .filter-tab:hover,
    .filter-tab.active {
        background: #667eea;
        color: white;
    }

    /* Grid de páginas */
    .pages-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-top: 20px;
    }

    @media (max-width: 1200px) {
        .pages-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .pages-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Card de página */
    .page-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 1px solid #eee;
        position: relative;
        overflow: hidden;
    }

    .page-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
    }

    .page-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2);
    }

    /* Ícone da página */
    .page-card-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
    }

    .page-card-icon i {
        font-size: 28px;
        color: white;
    }

    .page-card-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 12px;
    }

    /* Título e info */
    .page-card-title {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .page-card-id {
        font-size: 12px;
        color: #999;
        margin-bottom: 15px;
    }

    /* Badges */
    .page-card-badges {
        display: flex;
        gap: 8px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }

    .badge-leads {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-active {
        background: #28a745;
        color: white;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-inactive {
        background: #dc3545;
        color: white;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 500;
    }

    /* Estatísticas do card */
    .page-card-stats {
        display: flex;
        gap: 20px;
        padding: 15px 0;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
        margin-bottom: 15px;
    }

    .page-stat {
        text-align: center;
        flex: 1;
    }

    .page-stat .value {
        font-size: 20px;
        font-weight: 700;
        color: #333;
    }

    .page-stat .label {
        font-size: 11px;
        color: #999;
        text-transform: uppercase;
    }

    /* Botões do card */
    .page-card-actions {
        display: flex;
        gap: 10px;
    }

    .page-card-actions .btn {
        flex: 1;
        border-radius: 8px;
        padding: 10px;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-view-leads {
        background: #667eea;
        color: white;
        border: none;
    }

    .btn-view-leads:hover {
        background: #5a6fd6;
        color: white;
    }

    .btn-collect {
        background: #f8f9fa;
        color: #667eea;
        border: 1px solid #667eea;
    }

    .btn-collect:hover {
        background: #667eea;
        color: white;
    }

    /* Modal de leads */
    .leads-modal .modal-content {
        border-radius: 15px;
        border: none;
    }

    .leads-modal .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        border: none;
    }

    .leads-modal .modal-header .close {
        color: white;
        opacity: 1;
    }

    .leads-modal .modal-body {
        padding: 25px;
    }

    /* DataTable customizado */
    .leads-table {
        width: 100% !important;
    }

    .leads-table thead th {
        background: #f8f9fa;
        border-bottom: 2px solid #667eea;
        color: #333;
        font-weight: 600;
        font-size: 13px;
    }

    .leads-table tbody tr:hover {
        background: #f8f9fa;
    }

    /* Loading overlay */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 15px;
        z-index: 10;
    }

    .loading-overlay .spinner-border {
        width: 3rem;
        height: 3rem;
        color: #667eea;
    }

    /* Progress bar para coleta */
    .collect-progress {
        margin-top: 20px;
        display: none;
    }

    .collect-progress .progress {
        height: 10px;
        border-radius: 5px;
        background: rgba(255, 255, 255, 0.3);
    }

    .collect-progress .progress-bar {
        background: white;
        border-radius: 5px;
    }

    .collect-progress .progress-text {
        font-size: 13px;
        margin-top: 8px;
        opacity: 0.9;
    }

    /* Estado vazio */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .empty-state i {
        font-size: 60px;
        margin-bottom: 20px;
        color: #ddd;
    }

    .empty-state h5 {
        color: #666;
        margin-bottom: 10px;
    }

    /* Animações */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .page-card {
        animation: fadeIn 0.5s ease forwards;
    }

    .page-card:nth-child(1) {
        animation-delay: 0.1s;
    }

    .page-card:nth-child(2) {
        animation-delay: 0.2s;
    }

    .page-card:nth-child(3) {
        animation-delay: 0.3s;
    }

    .page-card:nth-child(4) {
        animation-delay: 0.4s;
    }

    .page-card:nth-child(5) {
        animation-delay: 0.5s;
    }

    .page-card:nth-child(6) {
        animation-delay: 0.6s;
    }

    /* Botão conectar página */
    .btn-connect-page {
        background: linear-gradient(135deg, #1877f2 0%, #42b72a 100%);
        color: white;
        border: none;
        padding: 15px 35px;
        border-radius: 30px;
        font-size: 16px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 5px 20px rgba(24, 119, 242, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
    }

    .btn-connect-page:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(24, 119, 242, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-connect-page i {
        font-size: 18px;
    }
</style>