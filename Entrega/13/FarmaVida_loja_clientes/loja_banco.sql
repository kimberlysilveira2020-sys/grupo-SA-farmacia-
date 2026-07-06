-- ============================================================
-- FarmaVida LOJA — Execute no phpMyAdmin
-- ============================================================
USE `farmavida`;

-- Tabela de clientes da loja (separada dos funcionários)
CREATE TABLE IF NOT EXISTS `clientes_loja` (
  `id`           int(11)      NOT NULL AUTO_INCREMENT,
  `nome`         varchar(150) NOT NULL,
  `email`        varchar(150) NOT NULL,
  `senha_hash`   varchar(255) NOT NULL,
  `cpf`          varchar(14)  DEFAULT NULL,
  `telefone`     varchar(20)  DEFAULT NULL,
  `criado_em`    datetime     DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf`   (`cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Pedidos feitos pela loja
CREATE TABLE IF NOT EXISTS `pedidos` (
  `id`           int(11)        NOT NULL AUTO_INCREMENT,
  `cliente_id`   int(11)        NOT NULL,
  `status`       enum('pendente','confirmado','cancelado') DEFAULT 'pendente',
  `total`        decimal(10,2)  NOT NULL DEFAULT 0.00,
  `criado_em`    datetime       DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes_loja` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Itens dos pedidos
CREATE TABLE IF NOT EXISTS `pedido_itens` (
  `id`          int(11)       NOT NULL AUTO_INCREMENT,
  `pedido_id`   int(11)       NOT NULL,
  `produto_id`  int(11)       NOT NULL,
  `quantidade`  int(11)       NOT NULL,
  `preco`       decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_id`  (`pedido_id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `pi_ibfk_1` FOREIGN KEY (`pedido_id`)  REFERENCES `pedidos`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `pi_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
