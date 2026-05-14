<?php $this->load->view('broadcast_pro/broadcast_pro_css'); ?>

<!-- Header -->
<div class="broadcast-header">
    <h2>
        <i class="fas fa-plus-circle"></i>
        <?= $this->lang->line("Create Campaign") ?: "Nova Campanha" ?>
    </h2>
    <p>
        <?= $this->lang->line("Create a new broadcast campaign") ?: "Configure e agende uma nova campanha de broadcast" ?>
    </p>
</div>

<!-- Back Button -->
<div class="action-buttons mb-4">
    <a href="<?= site_url('broadcast_pro') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i>
        <?= $this->lang->line("Back") ?: "Voltar" ?>
    </a>
</div>

<form id="form-create-campaign">
    <!-- Campaign Name -->
    <div class="form-section">
        <h5>
            <i class="fas fa-tag"></i>
            <?= $this->lang->line("Campaign Details") ?: "Detalhes da Campanha" ?>
        </h5>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>
                        <?= $this->lang->line("Campaign Name") ?: "Nome da Campanha" ?> <span
                            class="text-danger">*</span>
                    </label>
                    <input type="text" name="campaign_name" class="form-control" required
                        placeholder="<?= $this->lang->line("Ex: Campaign January 2026") ?: "Ex: Campanha Janeiro 2026" ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Momento do Dia -->
    <div class="form-section">
        <h5>
            <i class="fas fa-clock"></i>
            <?= $this->lang->line("Time of Day") ?: "Momento do Dia" ?> <span class="text-danger">*</span>
        </h5>
        <p class="text-muted mb-3">Selecione o período do dia para envio. Um horário será escolhido aleatoriamente.</p>

        <div class="row">
            <div class="col-md-4">
                <label class="momento-card" for="momento-manha">
                    <input type="radio" name="momento_dia" id="momento-manha" value="manha" required>
                    <div class="momento-card-content">
                        <i class="fas fa-sun fa-2x text-warning"></i>
                        <h6>Manhã</h6>
                        <small>06:00 - 11:59</small>
                    </div>
                </label>
            </div>
            <div class="col-md-4">
                <label class="momento-card" for="momento-tarde">
                    <input type="radio" name="momento_dia" id="momento-tarde" value="tarde">
                    <div class="momento-card-content">
                        <i class="fas fa-cloud-sun fa-2x text-info"></i>
                        <h6>Tarde</h6>
                        <small>12:00 - 17:59</small>
                    </div>
                </label>
            </div>
            <div class="col-md-4">
                <label class="momento-card" for="momento-noite">
                    <input type="radio" name="momento_dia" id="momento-noite" value="noite">
                    <div class="momento-card-content">
                        <i class="fas fa-moon fa-2x text-primary"></i>
                        <h6>Noite</h6>
                        <small>18:00 - 23:59</small>
                    </div>
                </label>
            </div>
        </div>
    </div>

    <!-- Message Content -->
    <div class="form-section">
        <h5>
            <i class="fas fa-comment-alt"></i>
            <?= $this->lang->line("Message Content") ?: "Conteúdo da Mensagem" ?>
        </h5>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>
                        <?= $this->lang->line("Template Type") ?: "Tipo de Template" ?>
                    </label>
                    <select name="template_type" class="form-control" id="template-type">
                        <option value="text_with_buttons">Texto com botões</option>
                        <option value="generic_template">Texto + imagem + botões</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Text Message -->
        <div class="form-group" id="content-text">
            <label>
                <?= $this->lang->line("Message Text") ?: "Texto da Mensagem" ?>
            </label>
            <textarea name="message_content[text]" class="form-control" rows="5"
                placeholder="<?= $this->lang->line("Write your message here...") ?: "Escreva sua mensagem aqui..." ?>"></textarea>
            <small class="text-muted">
                <?= $this->lang->line("Use variables") ?: "Use variáveis" ?>: {first_name}, {last_name}, {full_name}
            </small>
        </div>

        <!-- Image URL (only for generic_template) -->
        <div class="form-group" id="content-image" style="display: none;">
            <label>
                <?= $this->lang->line("Image URL") ?: "URL da Imagem" ?>
            </label>
            <input type="url" name="message_content[image_url]" class="form-control"
                placeholder="https://example.com/image.jpg">
            <small class="text-muted">Use uma URL pública HTTPS</small>
        </div>

        <!-- Buttons -->
        <div id="content-buttons">
            <label>
                <?= $this->lang->line("Buttons") ?: "Botões" ?> (<?= $this->lang->line("max 3") ?: "máx. 3" ?>)
            </label>

            <div class="row mb-2">
                <div class="col-md-6">
                    <input type="text" name="message_content[buttons][0][title]" class="form-control"
                        placeholder="<?= $this->lang->line("Button 1 text") ?: "Texto do Botão 1" ?>">
                </div>
                <div class="col-md-6">
                    <input type="url" name="message_content[buttons][0][url]" class="form-control"
                        placeholder="https://...">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-6">
                    <input type="text" name="message_content[buttons][1][title]" class="form-control"
                        placeholder="<?= $this->lang->line("Button 2 text") ?: "Texto do Botão 2" ?>">
                </div>
                <div class="col-md-6">
                    <input type="url" name="message_content[buttons][1][url]" class="form-control"
                        placeholder="https://...">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-6">
                    <input type="text" name="message_content[buttons][2][title]" class="form-control"
                        placeholder="<?= $this->lang->line("Button 3 text") ?: "Texto do Botão 3" ?>">
                </div>
                <div class="col-md-6">
                    <input type="url" name="message_content[buttons][2][url]" class="form-control"
                        placeholder="https://...">
                </div>
            </div>
        </div>
    </div>

    <!-- Pages Info -->
    <div class="form-section">
        <h5>
            <i class="fas fa-users"></i>
            <?= $this->lang->line("Target Audience") ?: "Público Alvo" ?>
        </h5>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <?= $this->lang->line("The broadcast will be sent to all active leads from all your connected pages.")
                ?: "O broadcast será enviado para todos os leads ativos de todas as suas páginas conectadas." ?>
        </div>

        <p>
            <strong>
                <?= $this->lang->line("Connected Pages") ?: "Páginas Conectadas" ?>:
            </strong>
            <?= count($pages) ?>
        </p>

        <?php if (!empty($pages)): ?>
            <ul class="list-inline">
                <?php foreach ($pages as $page): ?>
                    <li class="list-inline-item">
                        <span class="badge badge-primary">
                            <?= htmlspecialchars($page['page_name']) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Submit -->
    <div class="form-section text-center">
        <button type="submit" class="btn btn-success btn-lg">
            <i class="fas fa-paper-plane"></i>
            <?= $this->lang->line("Create Campaign") ?: "Criar Campanha" ?>
        </button>
    </div>
</form>

<style>
    /* Momento do Dia Cards */
    .momento-card {
        display: block;
        cursor: pointer;
        margin-bottom: 15px;
    }

    .momento-card input[type="radio"] {
        display: none;
    }

    .momento-card-content {
        padding: 20px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        text-align: center;
        transition: all 0.3s ease;
        background: #fff;
    }

    .momento-card-content:hover {
        border-color: #667eea;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
    }

    .momento-card input[type="radio"]:checked+.momento-card-content {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .momento-card-content h6 {
        margin: 10px 0 5px;
        font-weight: 600;
    }

    .momento-card-content small {
        color: #888;
    }
</style>

<script>
    $(document).ready(function () {
        // Toggle template type fields
        $('#template-type').on('change', function () {
            var type = $(this).val();
            $('#content-image').toggle(type === 'generic_template');
        });
    });
</script>

<?php $this->load->view('broadcast_pro/broadcast_pro_js'); ?>