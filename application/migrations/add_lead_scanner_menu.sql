-- =====================================================
-- Lead Scanner - Script de Migração para Menu
-- =====================================================
-- Execute este script no banco de dados do Chatpeao
-- para adicionar o item "Lead Scanner" ao menu
-- =====================================================

-- O Lead Scanner será adicionado como um novo item de menu
-- próximo ao Broadcasting (ID 42) na seção Facebook Tools

-- Encontra o próximo ID disponível na tabela menu
SET @next_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM menu);

-- Encontra o próximo serial disponível (após o Broadcasting)
SET @next_serial = (SELECT serial + 1 FROM menu WHERE id = 42);

-- Insere o item de menu Lead Scanner
INSERT INTO `menu` (
    `id`,
    `name`,
    `icon`,
    `color`,
    `url`,
    `serial`,
    `module_access`,
    `have_child`,
    `only_admin`,
    `only_member`,
    `add_ons_id`,
    `is_external`,
    `header_text`,
    `is_menu_manager`,
    `custom_page_id`
) VALUES (
    @next_id,
    'Lead Scanner',
    'fas fa-search-plus',
    '#667eea',
    'lead_scanner',
    @next_serial,
    '275',          -- Mesmo módulo do Broadcasting (275)
    '0',
    '0',
    '0',
    0,
    '0',
    '',             -- Não precisa de header separado
    '0',
    0
);

-- Atualiza os serials dos itens após para manter ordem
UPDATE menu SET serial = serial + 1 WHERE serial >= @next_serial AND id != @next_id;

-- =====================================================
-- VERIFICAÇÃO
-- =====================================================
-- Após executar, verifique se o item foi adicionado:
-- SELECT * FROM menu WHERE name = 'Lead Scanner';

-- =====================================================
-- INSTRUÇÃO ALTERNATIVA (Manual via Admin Panel)
-- =====================================================
-- Se preferir adicionar manualmente:
-- 1. Acesse o Admin Panel do Chatpeao como Administrador
-- 2. Vá em "System" > "Menu Manager"
-- 3. Clique em "Add Menu" ou similar
-- 4. Preencha:
--    - Name: Lead Scanner
--    - URL: lead_scanner
--    - Icon: fas fa-search-plus
--    - Color: #667eea
--    - Module Access: 275
--    - Header Text: Facebook Tools (para agrupar)
-- 5. Salve e ajuste a ordem do menu conforme necessário
-- =====================================================

