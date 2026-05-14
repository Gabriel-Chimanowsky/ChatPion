<?php $this->load->view('broadcast_pro/broadcast_pro_css'); ?>

<!-- Header -->
<div class="broadcast-header">
    <h2>
        <i class="fas fa-clock"></i>
        <?= $this->lang->line("Auto Scheduler") ?: "Agendador Automático (Horários Diários)" ?>
    </h2>
    <p>
        <?= $this->lang->line("Schedule times for automatic broadcasts") ?: "Configure os horários de envio. O sistema detecta automaticamente o período (Manhã/Tarde/Noite)." ?>
    </p>
</div>

<!-- Back Button -->
<div class="action-buttons mb-4">
    <a href="<?= site_url('broadcast_pro') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i>
        <?= $this->lang->line("Back") ?: "Voltar" ?>
    </a>
</div>

<!-- Resumo por Período -->
<div class="row mb-4">
    <?php
    $count_manha = 0;
    $count_tarde = 0;
    $count_noite = 0;
    foreach ($schedules as $s) {
        if ($s['is_active']) {
            $hour = (int) date('H', strtotime($s['time']));
            if ($hour >= 6 && $hour < 12)
                $count_manha++;
            elseif ($hour >= 12 && $hour < 18)
                $count_tarde++;
            else
                $count_noite++;
        }
    }
    ?>
    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #ffecd2, #fcb69f);">
            <div class="stat-icon"><i class="fas fa-sun text-warning"></i></div>
            <div class="stat-value"><?= $count_manha ?></div>
            <div class="stat-label">Manhã (06h-12h)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #a1c4fd, #c2e9fb);">
            <div class="stat-icon"><i class="fas fa-cloud-sun text-info"></i></div>
            <div class="stat-value"><?= $count_tarde ?></div>
            <div class="stat-label">Tarde (12h-18h)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea, #764ba2);">
            <div class="stat-icon"><i class="fas fa-moon text-white"></i></div>
            <div class="stat-value" style="color:#fff;"><?= $count_noite ?></div>
            <div class="stat-label" style="color:#fff;">Noite (18h-00h)</div>
        </div>
    </div>
</div>

<!-- Form Section -->
<div class="form-section">
    <h5>
        <i class="fas fa-plus-circle"></i>
        <?= $this->lang->line("Add Schedule") ?: "Adicionar Horário" ?>
    </h5>

    <form id="form-schedule">
        <div class="row align-items-end">
            <div class="col-md-4">
                <div class="form-group">
                    <label>
                        <i class="fas fa-clock"></i>
                        <?= $this->lang->line("Time") ?: "Horário" ?>
                    </label>
                    <input type="time" name="time" class="form-control form-control-lg" required value="09:00">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label id="periodo-preview" class="periodo-badge periodo-manha">
                        <i class="fas fa-sun"></i> Manhã
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <button type="submit" class="btn btn-success btn-lg btn-block">
                        <i class="fas fa-plus"></i>
                        <?= $this->lang->line("Add") ?: "Adicionar" ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Schedules List -->
<div class="form-section">
    <h5>
        <i class="fas fa-list"></i>
        <?= $this->lang->line("Registered Schedules") ?: "Horários Cadastrados" ?>
        <small class="text-muted">(<?= count($schedules) ?> horários)</small>
    </h5>

    <?php if (!empty($schedules)): ?>
        <div class="row">
            <?php foreach ($schedules as $schedule):
                $hour = (int) date('H', strtotime($schedule['time']));
                if ($hour >= 6 && $hour < 12) {
                    $periodo = 'manha';
                    $periodo_label = 'Manhã';
                    $periodo_icon = 'fa-sun text-warning';
                } elseif ($hour >= 12 && $hour < 18) {
                    $periodo = 'tarde';
                    $periodo_label = 'Tarde';
                    $periodo_icon = 'fa-cloud-sun text-info';
                } else {
                    $periodo = 'noite';
                    $periodo_label = 'Noite';
                    $periodo_icon = 'fa-moon text-primary';
                }
                ?>
                <div class="col-md-4 col-lg-3 mb-3">
                    <div class="schedule-card <?= $schedule['is_active'] ? 'active' : 'inactive' ?>">
                        <div class="schedule-time">
                            <?= date('H:i', strtotime($schedule['time'])) ?>
                        </div>
                        <div class="schedule-periodo">
                            <i class="fas <?= $periodo_icon ?>"></i>
                            <?= $periodo_label ?>
                        </div>
                        <div class="schedule-actions">
                            <?php if ($schedule['is_active']): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Ativo</span>
                            <?php else: ?>
                                <span class="badge badge-secondary"><i class="fas fa-pause"></i> Inativo</span>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-outline-danger btn-delete-schedule" data-id="<?= $schedule['id'] ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-calendar-plus fa-3x mb-3"></i>
            <p class="lead">Nenhum horário cadastrado</p>
            <p>Adicione horários para ativar o agendamento automático de broadcasts.</p>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Stat Cards */
    .stat-card {
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        font-size: 2rem;
        margin-bottom: 10px;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
    }

    .stat-label {
        font-size: 0.9rem;
        opacity: 0.8;
    }

    /* Período Badge */
    .periodo-badge {
        display: inline-block;
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 600;
        font-size: 1rem;
    }

    .periodo-manha {
        background: linear-gradient(135deg, #ffecd2, #fcb69f);
        color: #b44d12;
    }

    .periodo-tarde {
        background: linear-gradient(135deg, #a1c4fd, #c2e9fb);
        color: #1565c0;
    }

    .periodo-noite {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
    }

    /* Schedule Cards */
    .schedule-card {
        background: #fff;
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .schedule-card.active {
        border-color: #28a745;
    }

    .schedule-card.inactive {
        opacity: 0.6;
        border-color: #ccc;
    }

    .schedule-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }

    .schedule-time {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
    }

    .schedule-periodo {
        margin: 10px 0;
        font-size: 0.9rem;
    }

    .schedule-actions {
        margin-top: 15px;
        display: flex;
        justify-content: center;
        gap: 10px;
        align-items: center;
    }
</style>

<script>
    $(document).ready(function () {
        // Atualiza preview do período ao mudar o horário
        $('input[name="time"]').on('change input', function () {
            var time = $(this).val();
            var hour = parseInt(time.split(':')[0]);
            var $preview = $('#periodo-preview');

            if (hour >= 6 && hour < 12) {
                $preview.removeClass('periodo-tarde periodo-noite').addClass('periodo-manha');
                $preview.html('<i class="fas fa-sun"></i> Manhã');
            } else if (hour >= 12 && hour < 18) {
                $preview.removeClass('periodo-manha periodo-noite').addClass('periodo-tarde');
                $preview.html('<i class="fas fa-cloud-sun"></i> Tarde');
            } else {
                $preview.removeClass('periodo-manha periodo-tarde').addClass('periodo-noite');
                $preview.html('<i class="fas fa-moon"></i> Noite');
            }
        });

        // Trigger inicial
        $('input[name="time"]').trigger('change');
    });
</script>

<?php $this->load->view('broadcast_pro/broadcast_pro_js'); ?>