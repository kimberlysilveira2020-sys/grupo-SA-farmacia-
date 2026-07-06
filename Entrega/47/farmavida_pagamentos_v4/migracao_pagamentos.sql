-- ============================================================
-- MIGRAÇÃO: Cartão de Crédito, PayPal e Boleto
-- Execute APÓS migracao_pix.sql
-- ============================================================

-- Colunas extras em pedidos (se ainda não existirem)
ALTER TABLE `pedidos`
    ADD COLUMN IF NOT EXISTS `forma_pagamento` VARCHAR(20)  NOT NULL DEFAULT 'pix'   AFTER `status`,
    ADD COLUMN IF NOT EXISTS `pix_txid`        VARCHAR(50)  NULL                     AFTER `forma_pagamento`,
    ADD COLUMN IF NOT EXISTS `pix_pago`        TINYINT(1)   NOT NULL DEFAULT 0       AFTER `pix_txid`,
    ADD COLUMN IF NOT EXISTS `boleto_codigo`   VARCHAR(60)  NULL                     AFTER `pix_pago`,
    ADD COLUMN IF NOT EXISTS `boleto_vencimento` DATE       NULL                     AFTER `boleto_codigo`,
    ADD COLUMN IF NOT EXISTS `paypal_order_id` VARCHAR(80)  NULL                     AFTER `boleto_vencimento`;

ALTER TABLE `pedidos`
    ADD INDEX IF NOT EXISTS `idx_pix_txid`      (`pix_txid`),
    ADD INDEX IF NOT EXISTS `idx_paypal_order`  (`paypal_order_id`),
    ADD INDEX IF NOT EXISTS `idx_boleto_codigo` (`boleto_codigo`);

-- Tabela de cartões salvos do cliente (apenas últimos 4 dígitos + token)
CREATE TABLE IF NOT EXISTS `cartoes_cliente` (
    `id`           INT(11)      NOT NULL AUTO_INCREMENT,
    `cliente_id`   INT(11)      NOT NULL,
    `apelido`      VARCHAR(40)  NOT NULL DEFAULT '',
    `bandeira`     VARCHAR(20)  NOT NULL DEFAULT 'visa',
    `ultimos4`     CHAR(4)      NOT NULL,
    `nome_titular` VARCHAR(60)  NOT NULL,
    `mes_validade` CHAR(2)      NOT NULL,
    `ano_validade` CHAR(4)      NOT NULL,
    `token_hash`   VARCHAR(255) NOT NULL COMMENT 'hash seguro — NUNCA armazene o número completo',
    `padrao`       TINYINT(1)   NOT NULL DEFAULT 0,
    `criado_em`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cartao_cliente` (`cliente_id`),
    CONSTRAINT `fk_cartao_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes_loja` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
