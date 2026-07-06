-- ============================================================
-- FarmaVida - Atualização do Banco de Dados
-- Execute este script no phpMyAdmin ou via linha de comando
-- ============================================================

USE `farmavida`;

-- Adiciona coluna de imagem na tabela produtos (se não existir)
ALTER TABLE `produtos`
  ADD COLUMN IF NOT EXISTS `foto` varchar(255) DEFAULT NULL AFTER `descricao`;

-- Cria tabela de banners/promoções
CREATE TABLE IF NOT EXISTS `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `cor_fundo` varchar(20) DEFAULT '#1976D2',
  `ativo` tinyint(1) DEFAULT 1,
  `ordem` int(11) DEFAULT 0,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insere alguns banners de exemplo
INSERT INTO `banners` (`titulo`, `descricao`, `cor_fundo`, `ativo`, `ordem`) VALUES
('🌿 Semana da Saúde', 'Vitaminas e suplementos com até 30% de desconto!', '#2E7D32', 1, 1),
('💊 Genéricos em Oferta', 'Medicamentos genéricos com preços especiais para você.', '#1565C0', 1, 2),
('🌸 Linha Dermatológica', 'Cuide da sua pele: produtos dermocosméticos em promoção.', '#AD1457', 1, 3);
