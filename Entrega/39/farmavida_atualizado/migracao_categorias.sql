-- Migração: Tabela de categorias
-- Execute este script para adicionar suporte a categorias dinâmicas

CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `icone` varchar(50) DEFAULT 'bi-tag',
  `ativo` tinyint(1) DEFAULT 1,
  `ordem` int(11) DEFAULT 0,
  `criado_em` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Categorias padrão do sistema
INSERT IGNORE INTO `categorias` (`nome`, `icone`, `ordem`) VALUES
('Comum', 'bi-capsule', 1),
('Genérico', 'bi-capsule-pill', 2),
('Controlado', 'bi-shield-lock', 3),
('Antibiótico', 'bi-bacteria', 4),
('Vitaminas', 'bi-heart', 5),
('Suplementos', 'bi-activity', 6),
('Dermocosméticos', 'bi-stars', 7),
('Higiene', 'bi-droplet', 8),
('Beleza', 'bi-flower1', 9),
('Infantil', 'bi-emoji-smile', 10),
('Ortopédico', 'bi-bandaid', 11),
('Hospitalar', 'bi-hospital', 12);
