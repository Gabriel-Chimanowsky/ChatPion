-- =====================================================
-- Broadcast Pro v2 - Migration
-- =====================================================
-- Adicionar campos para momento do dia e período
-- =====================================================

-- 1. Adicionar campo periodo na tabela de horários
ALTER TABLE broadcast_pro_schedules 
ADD COLUMN IF NOT EXISTS periodo ENUM('manha', 'tarde', 'noite') NULL AFTER time;

-- 2. Atualizar períodos baseado nos horários existentes
UPDATE broadcast_pro_schedules 
SET periodo = CASE
    WHEN time >= '06:00:00' AND time < '12:00:00' THEN 'manha'
    WHEN time >= '12:00:00' AND time < '18:00:00' THEN 'tarde'
    WHEN time >= '18:00:00' OR time < '06:00:00' THEN 'noite'
END
WHERE periodo IS NULL;

-- 3. Adicionar campo momento_dia na tabela de campanhas
ALTER TABLE broadcast_pro_campaigns 
ADD COLUMN IF NOT EXISTS momento_dia ENUM('manha', 'tarde', 'noite') NULL AFTER template_type;

-- =====================================================
-- Verificação
-- =====================================================
-- SELECT id, time, periodo FROM broadcast_pro_schedules;
-- SELECT id, campaign_name, momento_dia FROM broadcast_pro_campaigns;
