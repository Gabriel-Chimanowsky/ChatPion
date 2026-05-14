-- =====================================================
-- Broadcast Pro - Adicionar Item ao Menu
-- =====================================================
-- Execute este script após as tabelas terem sido criadas
-- =====================================================

-- Obtém o maior serial atual
SET @max_serial = (SELECT COALESCE(MAX(serial), 0) + 1 FROM menu);

-- Insere o menu do Broadcast Pro
INSERT INTO menu (
    name,
    icon,
    color,
    url,
    serial,
    module_access,
    have_child,
    only_admin,
    only_member,
    add_ons_id,
    is_external,
    header_text,
    is_menu_manager,
    custom_page_id
) VALUES (
    'Broadcast Pro',
    'fas fa-satellite-dish',
    '#667eea',
    'broadcast_pro/index',
    @max_serial,
    '275',
    '0',
    '0',
    '0',
    0,
    '0',
    'FACEBOOK TOOLS',
    '0',
    0
);

-- =====================================================
-- Verificação
-- =====================================================
-- Execute para confirmar:
-- SELECT * FROM menu WHERE name = 'Broadcast Pro';
-- =====================================================
