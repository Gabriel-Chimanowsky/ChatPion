-- =====================================================
-- Broadcast Pro - Migração de Banco de Dados
-- =====================================================
-- Execute este script no banco de dados do Chatpeao
-- =====================================================

-- =====================================================
-- 1. Tabela de Campanhas
-- =====================================================
CREATE TABLE IF NOT EXISTS `broadcast_pro_campaigns` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `campaign_name` VARCHAR(255) NOT NULL,
    `copy_id` INT(11) UNSIGNED DEFAULT NULL COMMENT 'FK para broadcast_pro_copies',
    `template_type` ENUM('text','image','text_with_buttons','carousel','generic_template') NOT NULL DEFAULT 'text',
    `message_content` LONGTEXT NOT NULL COMMENT 'JSON com conteúdo da mensagem',
    `status` ENUM('draft','scheduled','processing','completed','cancelled','error') NOT NULL DEFAULT 'draft',
    `schedule_type` ENUM('now','scheduled') NOT NULL DEFAULT 'now',
    `scheduled_at` DATETIME DEFAULT NULL,
    `started_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `timezone` VARCHAR(100) NOT NULL DEFAULT 'America/Sao_Paulo',
    `total_leads` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `sent_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `success_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `failed_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `pending_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `priority` TINYINT(3) UNSIGNED NOT NULL DEFAULT 5 COMMENT '1=alta, 10=baixa',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_scheduled_at` (`scheduled_at`),
    KEY `idx_status_scheduled` (`status`, `scheduled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. Tabela de Copys (Templates de Mensagem)
-- =====================================================
CREATE TABLE IF NOT EXISTS `broadcast_pro_copies` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `template_type` ENUM('text','image','text_with_buttons','carousel','generic_template') NOT NULL DEFAULT 'text',
    `content` LONGTEXT NOT NULL COMMENT 'JSON com conteúdo do template',
    `is_used` TINYINT(1) NOT NULL DEFAULT 0,
    `last_used_at` DATETIME DEFAULT NULL,
    `total_pages` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Páginas vinculadas',
    `campaigns_count` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Campanhas que usaram',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_is_used` (`is_used`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. Tabela de Agendamentos Automáticos (Horários)
-- =====================================================
CREATE TABLE IF NOT EXISTS `broadcast_pro_schedules` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `time` TIME NOT NULL COMMENT 'Horário no formato HH:MM:SS',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `timezone` VARCHAR(100) NOT NULL DEFAULT 'America/Sao_Paulo',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_is_active` (`is_active`),
    UNIQUE KEY `uk_user_time` (`user_id`, `time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. Tabela de Fila de Mensagens (Queue)
-- =====================================================
CREATE TABLE IF NOT EXISTS `broadcast_pro_queue` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_id` INT(11) UNSIGNED NOT NULL,
    `page_id` INT(11) NOT NULL COMMENT 'FK para facebook_rx_fb_page_info.id',
    `subscriber_id` INT(11) NOT NULL COMMENT 'FK para messenger_bot_subscriber.id',
    `fb_page_id` VARCHAR(50) NOT NULL COMMENT 'ID da página no Facebook',
    `psid` VARCHAR(50) NOT NULL COMMENT 'Page-Scoped User ID',
    `status` ENUM('pending','processing','sent','failed','cancelled') NOT NULL DEFAULT 'pending',
    `attempts` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
    `last_attempt_at` DATETIME DEFAULT NULL,
    `sent_at` DATETIME DEFAULT NULL,
    `error_code` VARCHAR(50) DEFAULT NULL,
    `error_message` TEXT DEFAULT NULL,
    `priority` TINYINT(3) UNSIGNED NOT NULL DEFAULT 5,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_campaign_id` (`campaign_id`),
    KEY `idx_status` (`status`),
    KEY `idx_page_id` (`page_id`),
    KEY `idx_status_priority` (`status`, `priority`, `id`),
    KEY `idx_campaign_status` (`campaign_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. Tabela de Logs Detalhados
-- =====================================================
CREATE TABLE IF NOT EXISTS `broadcast_pro_logs` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_id` INT(11) UNSIGNED NOT NULL,
    `queue_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `page_id` INT(11) NOT NULL,
    `subscriber_id` INT(11) NOT NULL,
    `psid` VARCHAR(50) NOT NULL,
    `status` ENUM('sent','delivered','read','failed') NOT NULL,
    `fb_message_id` VARCHAR(255) DEFAULT NULL COMMENT 'ID da mensagem no Facebook',
    `error_code` VARCHAR(50) DEFAULT NULL,
    `error_message` TEXT DEFAULT NULL,
    `sent_at` DATETIME DEFAULT NULL,
    `delivered_at` DATETIME DEFAULT NULL,
    `read_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_campaign_id` (`campaign_id`),
    KEY `idx_page_id` (`page_id`),
    KEY `idx_status` (`status`),
    KEY `idx_sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. Tabela de Erros Agregados
-- =====================================================
CREATE TABLE IF NOT EXISTS `broadcast_pro_errors` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_id` INT(11) UNSIGNED NOT NULL,
    `error_code` VARCHAR(50) NOT NULL,
    `error_message` VARCHAR(500) NOT NULL,
    `count` INT(11) UNSIGNED NOT NULL DEFAULT 1,
    `first_occurrence` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_occurrence` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_campaign_id` (`campaign_id`),
    UNIQUE KEY `uk_campaign_error` (`campaign_id`, `error_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. Tabela de Páginas por Campanha (Relacionamento)
-- =====================================================
CREATE TABLE IF NOT EXISTS `broadcast_pro_campaign_pages` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_id` INT(11) UNSIGNED NOT NULL,
    `page_id` INT(11) NOT NULL COMMENT 'FK para facebook_rx_fb_page_info.id',
    `total_leads` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `sent_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `success_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `failed_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `active_percent` DECIMAL(5,2) DEFAULT 0.00,
    `inactive_percent` DECIMAL(5,2) DEFAULT 0.00,
    PRIMARY KEY (`id`),
    KEY `idx_campaign_id` (`campaign_id`),
    KEY `idx_page_id` (`page_id`),
    UNIQUE KEY `uk_campaign_page` (`campaign_id`, `page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Índices adicionais para performance
-- =====================================================

-- Índice para buscar mensagens pendentes ordenadas por prioridade
-- (Usado pelo worker para pegar próximas mensagens)
ALTER TABLE `broadcast_pro_queue` 
ADD INDEX `idx_pending_fetch` (`status`, `priority`, `created_at`);

-- =====================================================
-- FIM DA MIGRAÇÃO
-- =====================================================
-- Após executar, verifique se as tabelas foram criadas:
-- SHOW TABLES LIKE 'broadcast_pro_%';
-- =====================================================
