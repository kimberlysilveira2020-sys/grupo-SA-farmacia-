-- ============================================================
-- MIGRAÇÃO: Adiciona suporte a PIX na tabela `pedidos`
-- Execute este script UMA VEZ no banco de dados farmavida
-- ============================================================

ALTER TABLE `pedidos`
    ADD COLUMN IF NOT EXISTS `forma_pagamento` VARCHAR(20)  NOT NULL DEFAULT 'pix'  AFTER `status`,
    ADD COLUMN IF NOT EXISTS `pix_txid`        VARCHAR(50)  NULL                    AFTER `forma_pagamento`,
    ADD COLUMN IF NOT EXISTS `pix_pago`        TINYINT(1)   NOT NULL DEFAULT 0      AFTER `pix_txid`;

-- Índice para consulta rápida por txid (útil para confirmação futura via webhook)
ALTER TABLE `pedidos`
    ADD INDEX IF NOT EXISTS `idx_pix_txid` (`pix_txid`);

-- ============================================================
-- NOTA: Após aplicar a migração, configure as constantes PIX
-- no arquivo config.php:
--   PIX_CHAVE  → sua chave PIX (CPF, CNPJ, e-mail, tel. ou aleatória)
--   PIX_NOME   → nome do beneficiário (máx 25 caracteres)
--   PIX_CIDADE → cidade do beneficiário (máx 15 caracteres)
-- ============================================================
