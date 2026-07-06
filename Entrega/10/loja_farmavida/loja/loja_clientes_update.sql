-- ============================================================
-- FarmaVida - Atualização para Portal do Cliente
-- Execute este script no phpMyAdmin ANTES de usar o portal
-- ============================================================

-- Adiciona campos de acesso ao portal na tabela clientes
ALTER TABLE `clientes`
  ADD COLUMN `email` varchar(150) DEFAULT NULL AFTER `telefone`,
  ADD COLUMN `senha_hash` varchar(255) DEFAULT NULL AFTER `email`,
  ADD COLUMN `endereco` varchar(255) DEFAULT NULL AFTER `senha_hash`,
  ADD COLUMN `data_nascimento` date DEFAULT NULL AFTER `endereco`,
  ADD UNIQUE KEY `email` (`email`);

-- Tabela de sessões do portal (opcional, para segurança extra)
CREATE TABLE IF NOT EXISTS `pedidos_loja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `status` enum('pendente','confirmado','pronto','entregue','cancelado') DEFAULT 'pendente',
  `total` decimal(10,2) NOT NULL,
  `observacao` text DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  CONSTRAINT `pedidos_loja_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `itens_pedido_loja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_id` (`pedido_id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `itens_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos_loja` (`id`) ON DELETE CASCADE,
  CONSTRAINT `itens_pedido_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
