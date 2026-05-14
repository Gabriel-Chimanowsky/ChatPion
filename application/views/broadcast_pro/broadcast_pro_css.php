<!-- CSS específico do Broadcast Pro -->
<style>
    /* =====================================================
   Broadcast Pro - Estilos
   ===================================================== */

    /* Header da página */
    .broadcast-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        padding: 25px 30px;
        margin-bottom: 25px;
        color: white;
    }

    .broadcast-header h2 {
        margin: 0;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .broadcast-header h2 i {
        font-size: 1.5em;
    }

    .broadcast-header p {
        margin: 8px 0 0 0;
        opacity: 0.9;
        font-size: 0.95em;
    }

    /* Stats cards */
    .stats-row {
        display: flex;
        gap: 20px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 20px 25px;
        flex: 1;
        min-width: 200px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .stat-card .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4em;
        color: white;
    }

    .stat-card .stat-icon.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-card .stat-icon.success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .stat-card .stat-icon.warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stat-card .stat-icon.info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .stat-card .stat-content h3 {
        margin: 0;
        font-size: 1.8em;
        font-weight: 700;
        color: #333;
    }

    .stat-card .stat-content p {
        margin: 5px 0 0 0;
        color: #6c757d;
        font-size: 0.9em;
    }

    /* Action buttons */
    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .action-buttons .btn {
        padding: 10px 20px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Campaigns table */
    .campaigns-table {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .campaigns-table .table-header {
        background: #f8f9fa;
        padding: 18px 25px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .campaigns-table .table-header h5 {
        margin: 0;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .campaigns-table table {
        margin: 0;
    }

    .campaigns-table th {
        background: #f8f9fa;
        font-weight: 600;
        font-size: 0.85em;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        padding: 15px 20px;
        border: none;
    }

    .campaigns-table td {
        padding: 18px 20px;
        vertical-align: middle;
        border-color: #f0f0f0;
    }

    /* Status badges */
    .status-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8em;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-processing {
        background: #cce5ff;
        color: #004085;
    }

    .status-completed {
        background: #d4edda;
        color: #155724;
    }

    .status-error {
        background: #f8d7da;
        color: #721c24;
    }

    .status-cancelled {
        background: #e2e3e5;
        color: #383d41;
    }

    .status-draft {
        background: #e9ecef;
        color: #495057;
    }

    /* Progress bar */
    .campaign-progress {
        height: 8px;
        border-radius: 4px;
        background: #e9ecef;
        overflow: hidden;
        min-width: 100px;
    }

    .campaign-progress .progress-bar {
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transition: width 0.5s ease;
    }

    /* Copys cards */
    .copy-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #667eea;
    }

    .copy-card.used {
        border-left-color: #6c757d;
        opacity: 0.7;
    }

    .copy-card .copy-name {
        font-weight: 600;
        font-size: 1.1em;
        margin-bottom: 8px;
    }

    .copy-card .copy-meta {
        font-size: 0.85em;
        color: #6c757d;
    }

    /* Schedules */
    .schedule-item {
        background: white;
        border-radius: 10px;
        padding: 15px 20px;
        margin-bottom: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .schedule-item .schedule-time {
        font-size: 1.3em;
        font-weight: 600;
        color: #333;
    }

    .schedule-item .schedule-status {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    /* Logs page */
    .log-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .log-stat-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    .log-stat-card .value {
        font-size: 2em;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .log-stat-card .label {
        font-size: 0.85em;
        color: #6c757d;
    }

    .log-stat-card.success .value {
        color: #28a745;
    }

    .log-stat-card.error .value {
        color: #dc3545;
    }

    .log-stat-card.info .value {
        color: #17a2b8;
    }

    /* Pending campaigns highlight */
    .pending-section {
        background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .pending-section h5 {
        color: #ff6f00;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Modal styles */
    .modal-header.broadcast-modal {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .modal-header.broadcast-modal .close {
        color: white;
        opacity: 1;
    }

    /* Form styles */
    .form-section {
        background: white;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    .form-section h5 {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-row {
            flex-direction: column;
        }

        .stat-card {
            min-width: 100%;
        }

        .action-buttons {
            flex-direction: column;
        }

        .action-buttons .btn {
            width: 100%;
            justify-content: center;
        }
    }

    /* Animations */
    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }

        100% {
            opacity: 1;
        }
    }

    .processing-indicator {
        animation: pulse 1.5s infinite;
    }

    /* Empty state */
    .empty-state-container {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .empty-state-container i {
        font-size: 4em;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state-container h5 {
        margin-bottom: 10px;
    }
</style>