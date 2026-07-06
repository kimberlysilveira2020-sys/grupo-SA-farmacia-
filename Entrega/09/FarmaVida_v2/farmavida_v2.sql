-- ============================================================
-- FarmaVida v2 — Execute no phpMyAdmin
-- ============================================================
USE `farmavida`;

-- Tabela de clientes
CREATE TABLE IF NOT EXISTS `clientes` (
  `id`        int(11)      NOT NULL AUTO_INCREMENT,
  `nome`      varchar(150) NOT NULL,
  `cpf`       varchar(14)  DEFAULT NULL,
  `telefone`  varchar(20)  DEFAULT NULL,
  `criado_em` datetime     DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de controle de caixa
CREATE TABLE IF NOT EXISTS `caixa` (
  `id`               int(11)        NOT NULL AUTO_INCREMENT,
  `usuario_id`       int(11)        NOT NULL,
  `valor_abertura`   decimal(10,2)  NOT NULL DEFAULT 0.00,
  `valor_fechamento` decimal(10,2)  DEFAULT NULL,
  `aberto_em`        datetime       DEFAULT current_timestamp(),
  `fechado_em`       datetime       DEFAULT NULL,
  `status`           enum('aberto','fechado') DEFAULT 'aberto',
  `observacao`       text           DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `caixa_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Coluna cliente_id na tabela vendas (opcional, pode ser NULL)
ALTER TABLE `vendas`
  ADD COLUMN IF NOT EXISTS `cliente_id` int(11) DEFAULT NULL AFTER `usuario_id`,
  ADD COLUMN IF NOT EXISTS `caixa_id`   int(11) DEFAULT NULL AFTER `cliente_id`;
