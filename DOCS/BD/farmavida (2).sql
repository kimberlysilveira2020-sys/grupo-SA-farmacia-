-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 23/06/2026 às 03:15
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `farmavida`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `cor_fundo` varchar(20) DEFAULT '#1976D2',
  `ativo` tinyint(1) DEFAULT 1,
  `ordem` int(11) DEFAULT 0,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `banners`
--

INSERT INTO `banners` (`id`, `titulo`, `descricao`, `imagem`, `cor_fundo`, `ativo`, `ordem`, `data_inicio`, `data_fim`, `criado_em`) VALUES
(1, '🌿 Semana da Saúde', 'Vitaminas e suplementos com até 30% de desconto!', NULL, '#2E7D32', 1, 1, NULL, NULL, '2026-06-02 21:45:14'),
(2, '💊 Genéricos em Oferta', 'Medicamentos genéricos com preços especiais para você.', NULL, '#1565C0', 1, 2, NULL, NULL, '2026-06-02 21:45:14'),
(3, '🌸 Linha Dermatológica', 'Cuide da sua pele: produtos dermocosméticos em promoção.', NULL, '#AD1457', 1, 3, NULL, NULL, '2026-06-02 21:45:14');

-- --------------------------------------------------------

--
-- Estrutura para tabela `caixa`
--

CREATE TABLE `caixa` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `valor_abertura` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_fechamento` decimal(10,2) DEFAULT NULL,
  `aberto_em` datetime DEFAULT current_timestamp(),
  `fechado_em` datetime DEFAULT NULL,
  `status` enum('aberto','fechado') DEFAULT 'aberto',
  `observacao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `icone` varchar(50) DEFAULT 'bi-tag',
  `ativo` tinyint(1) DEFAULT 1,
  `ordem` int(11) DEFAULT 0,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`, `icone`, `ativo`, `ordem`, `criado_em`) VALUES
(1, 'Comum', 'bi-capsule', 1, 1, '2026-06-22 20:06:08'),
(2, 'Genérico', 'bi-capsule-pill', 1, 2, '2026-06-22 20:06:08'),
(3, 'Controlado', 'bi-shield-lock', 1, 3, '2026-06-22 20:06:08'),
(4, 'Antibiótico', 'bi-bacteria', 1, 4, '2026-06-22 20:06:08'),
(5, 'Vitaminas', 'bi-heart', 1, 5, '2026-06-22 20:06:08'),
(6, 'Suplementos', 'bi-activity', 1, 6, '2026-06-22 20:06:08'),
(7, 'Dermocosméticos', 'bi-stars', 1, 7, '2026-06-22 20:06:08'),
(8, 'Higiene', 'bi-droplet', 1, 8, '2026-06-22 20:06:08'),
(9, 'Beleza', 'bi-flower1', 1, 9, '2026-06-22 20:06:08'),
(10, 'Infantil', 'bi-emoji-smile', 1, 10, '2026-06-22 20:06:08'),
(11, 'Ortopédico', 'bi-bandaid', 1, 11, '2026-06-22 20:06:08'),
(12, 'Hospitalar', 'bi-hospital', 1, 12, '2026-06-22 20:06:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `senha_hash` varchar(255) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome`, `cpf`, `telefone`, `email`, `senha_hash`, `endereco`, `data_nascimento`, `criado_em`) VALUES
(2, 'gustavo', '111.222.222-22', '(33) 3333-3333', 'ciueslacio@gmail.com', '$2y$10$cjZXhWpFeB8b0U5xmjQ0YOybatnYyTvvGPeuDDCjX9LIA1BP44wZG', NULL, NULL, '2026-06-03 21:07:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes_loja`
--

CREATE TABLE `clientes_loja` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `clientes_loja`
--

INSERT INTO `clientes_loja` (`id`, `nome`, `email`, `senha_hash`, `cpf`, `telefone`, `criado_em`) VALUES
(1, 'gusta', 'gusta@gmail.com', '$2y$10$VffnJ0Ckbwr662V0Q25Egu2nu5a8vxWpsddphgCevRrTaLj7bvY6q', '123.131.121-23', '(13) 12312-3221', '2026-06-08 19:50:30'),
(2, 'Caio', 'caio@gmail.com', '$2y$10$isAfZ6lC/kxIw0lfgxSyHOwKDrswDde7s5wkkjkmW1gAPmCGOoiLS', '123.300.021-20', '(02) 12939-9012', '2026-06-08 20:02:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_pedido_loja`
--

CREATE TABLE `itens_pedido_loja` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_venda`
--

CREATE TABLE `itens_venda` (
  `id` int(11) NOT NULL,
  `venda_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `lote_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `lotes`
--

CREATE TABLE `lotes` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `numero_lote` varchar(50) NOT NULL,
  `data_validade` date NOT NULL,
  `qtd_atual` int(11) NOT NULL,
  `qtd_inicial` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `lotes`
--

INSERT INTO `lotes` (`id`, `produto_id`, `numero_lote`, `data_validade`, `qtd_atual`, `qtd_inicial`) VALUES
(29, 61, '10', '2026-07-10', 10, 10),
(30, 62, 'INF-001', '2027-06-30', 50, 50),
(31, 63, 'INF-002', '2027-08-31', 40, 40),
(32, 64, 'INF-003', '2027-12-31', 60, 60),
(33, 65, 'INF-004', '2027-03-31', 80, 80),
(34, 66, 'INF-005', '2027-09-30', 45, 45),
(35, 67, 'INF-006', '2027-11-30', 55, 55),
(36, 68, 'INF-007', '2027-07-31', 70, 70),
(37, 69, 'INF-008', '2028-01-31', 35, 35),
(38, 70, 'INF-009', '2027-05-31', 30, 30),
(39, 71, 'INF-010', '2027-10-31', 50, 50),
(40, 72, 'ORT-001', '2027-06-30', 40, 40),
(41, 73, 'ORT-002', '2027-08-31', 35, 35),
(42, 74, 'ORT-003', '2027-12-31', 60, 60),
(43, 75, 'ORT-004', '2028-06-30', 10, 10),
(44, 76, 'ORT-005', '2028-12-31', 20, 20),
(45, 77, 'ORT-006', '2028-12-31', 15, 15),
(46, 78, 'ORT-007', '2028-12-31', 12, 12),
(47, 79, 'ORT-008', '2028-12-31', 18, 18),
(48, 80, 'ORT-009', '2028-06-30', 8, 8),
(49, 81, 'ORT-010', '2027-10-31', 30, 30),
(50, 82, 'HOS-001', '2027-06-30', 20, 20),
(51, 83, 'HOS-002', '2027-12-31', 100, 100),
(52, 84, 'HOS-003', '2027-12-31', 50, 50),
(53, 85, 'HOS-004', '2027-09-30', 25, 25),
(54, 86, 'HOS-005', '2027-08-31', 15, 15),
(55, 87, 'HOS-006', '2027-06-30', 30, 30),
(56, 88, 'HOS-007', '2027-12-31', 100, 100),
(57, 89, 'HOS-008', '2028-06-30', 20, 20),
(58, 90, 'HOS-009', '2027-12-31', 40, 40),
(59, 91, 'HOS-010', '2028-06-30', 15, 15);

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `status` enum('pendente','confirmado','cancelado') DEFAULT 'pendente',
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `forma_pagamento` enum('credito','pix','paypal','boleto') DEFAULT 'pix',
  `pix_txid` varchar(50) DEFAULT NULL,
  `pix_pago` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pedidos`
--

INSERT INTO `pedidos` (`id`, `cliente_id`, `status`, `total`, `forma_pagamento`, `pix_txid`, `pix_pago`, `criado_em`) VALUES
(8, 1, 'pendente', 29.90, 'pix', NULL, 0, '2026-06-22 21:38:49'),
(9, 1, 'pendente', 74.90, 'pix', NULL, 0, '2026-06-22 21:49:11'),
(10, 1, 'pendente', 18.90, 'pix', 'FV9DB502F21984', 0, '2026-06-22 22:03:12'),
(11, 1, 'pendente', 8.00, 'pix', 'FV9DC11164D757', 0, '2026-06-22 22:06:25'),
(12, 1, 'pendente', 159.90, 'pix', 'FV9DC266927657', 0, '2026-06-22 22:06:46'),
(14, 1, 'pendente', 159.90, 'pix', 'FV9DC9A9B4E893', 0, '2026-06-22 22:08:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos_loja`
--

CREATE TABLE `pedidos_loja` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `status` enum('pendente','confirmado','pronto','entregue','cancelado') DEFAULT 'pendente',
  `total` decimal(10,2) NOT NULL,
  `observacao` text DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedido_itens`
--

CREATE TABLE `pedido_itens` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pedido_itens`
--

INSERT INTO `pedido_itens` (`id`, `pedido_id`, `produto_id`, `quantidade`, `preco`) VALUES
(8, 8, 66, 1, 29.90),
(9, 9, 79, 1, 74.90),
(10, 10, 85, 1, 18.90),
(11, 11, 61, 1, 8.00),
(12, 12, 75, 1, 159.90),
(14, 14, 75, 1, 159.90);

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `fabricante` varchar(100) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `preco_venda` decimal(10,2) NOT NULL,
  `descricao` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `receita_obrigatoria` tinyint(1) DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `fabricante`, `categoria`, `preco_venda`, `descricao`, `foto`, `receita_obrigatoria`, `ativo`) VALUES
(61, 'Dipirona', 'FarmaVida', 'Comum', 10.00, '', 'img_6a39bbbd6c4c08.10849010.webp', 0, 1),
(62, 'Paracetamol Infantil Gotas 100mg/mL', 'EMS', 'Infantil', 12.90, 'Analgésico e antitérmico em gotas para crianças. Frasco 15mL.', NULL, 0, 1),
(63, 'Ibuprofeno Infantil Suspensão 50mg/mL', 'Medley', 'Infantil', 18.50, 'Anti-inflamatório infantil em suspensão oral. Frasco 120mL.', NULL, 0, 1),
(64, 'Vitassay C Mastigável 100mg', 'Legrand', 'Infantil', 22.90, 'Vitamina C mastigável sabor laranja para crianças. Caixa com 30 comp.', NULL, 0, 1),
(65, 'Soro Fisiológico Nasal Infantil', 'Novafito', 'Infantil', 8.90, 'Solução salina isotônica para higiene nasal. Kit com 30 ampolas 5mL.', NULL, 0, 1),
(66, 'Bepantol Baby Pomada', 'Bayer', 'Infantil', 29.90, 'Pomada preventiva e tratadora de assaduras. 30g.', NULL, 0, 1),
(67, 'Calcitran D3 Gotas', 'Sanavita', 'Infantil', 34.90, 'Suplemento de cálcio e vitamina D3 em gotas para crianças. 30mL.', NULL, 0, 1),
(68, 'Espasmo Bebê Gotas', 'União Química', 'Infantil', 14.50, 'Antiespasmódico para cólicas infantis. Frasco 20mL.', NULL, 0, 1),
(69, 'Addera D3 400UI Gotas', 'Sanofi', 'Infantil', 27.90, 'Suplemento de vitamina D3 para bebês e crianças. Frasco 10mL.', NULL, 0, 1),
(70, 'Floratil Baby 200mg Sachê', 'Merck', 'Infantil', 38.90, 'Probiótico para reequilíbrio da flora intestinal. Caixa com 10 sachês.', NULL, 0, 1),
(71, 'Histadin Pediátrico Xarope', 'Marjan', 'Infantil', 19.90, 'Xarope antialérgico para crianças. Frasco 120mL.', NULL, 0, 1),
(72, 'Voltaren Emulgel 1% 60g', 'Novartis', 'Ortopédico', 42.90, 'Anti-inflamatório tópico para dores musculares e articulares. Bisnaga 60g.', NULL, 0, 1),
(73, 'Profenid Gel 2,5% 60g', 'Sanofi', 'Ortopédico', 38.50, 'Cetoprofeno gel para alívio de dores e inflamações locais. Bisnaga 60g.', NULL, 0, 1),
(74, 'Cataflan 50mg', 'Novartis', 'Ortopédico', 24.90, 'Diclofenaco potássico anti-inflamatório. Caixa com 20 comprimidos.', NULL, 0, 1),
(75, 'Bengala Regulável Alumínio', 'Carci', 'Ortopédico', 159.90, 'Bengala dobrável em alumínio com 4 pés antiderrapantes. Regulagem de altura.', NULL, 0, 1),
(76, 'Tornozeleira Elástica Neoprene M', 'Ortho Pauher', 'Ortopédico', 49.90, 'Suporte compressivo para tornozelo em neoprene. Tamanho M.', NULL, 0, 1),
(77, 'Joelheira com Abertura Patelar M', 'Corflex', 'Ortopédico', 59.90, 'Joelheira elástica com abertura patelar para suporte e compressão. Tamanho M.', NULL, 0, 1),
(78, 'Órtese Imobilizadora de Punho', 'Dyna', 'Ortopédico', 89.90, 'Imobilizador de punho com talas removíveis. Tamanho único ajustável.', NULL, 0, 1),
(79, 'Cinto Lombar Elástico G', 'Saúde Life', 'Ortopédico', 74.90, 'Cinto lombar com barbatanas para suporte vertebral. Tamanho G.', NULL, 0, 1),
(80, 'Muleta Axilar Alumínio Par', 'Carci', 'Ortopédico', 189.90, 'Par de muletas axilares em alumínio com regulagem de altura. Capacidade 120kg.', NULL, 0, 1),
(81, 'Gel para Ultrassom 1kg', 'Carbogel', 'Ortopédico', 32.90, 'Gel condutor para aparelhos de ultrassom fisioterapêutico. Pote 1kg.', NULL, 0, 1),
(82, 'Luva Procedimento Látex M Caixa 100', '3M', 'Hospitalar', 49.90, 'Luvas de látex sem pó para procedimentos. Caixa com 100 unidades. Tamanho M.', NULL, 0, 1),
(83, 'Seringa 5mL Bico Luer Lock', 'BD', 'Hospitalar', 2.90, 'Seringa descartável 5mL com bico Luer Lock. Unidade.', NULL, 0, 1),
(84, 'Agulha 40x12 Caixa 100', 'Descarpack', 'Hospitalar', 19.90, 'Agulhas hipodérmicas 40x12mm descartáveis. Caixa com 100 unidades.', NULL, 0, 1),
(85, 'Curativo Tegaderm 10x12cm', '3M', 'Hospitalar', 18.90, 'Filme transparente para curativo com borda adesiva. Caixa com 5 unidades.', NULL, 0, 1),
(86, 'Esparadrapo Impermeável 10mx5cm', 'Missner', 'Hospitalar', 14.90, 'Esparadrapo impermeável bege. Rolo 10mx5cm.', NULL, 0, 1),
(87, 'Álcool 70% Líquido 1L', 'Rioquímica', 'Hospitalar', 19.90, 'Álcool etílico hidratado 70% INPM para antissepsia. Frasco 1L.', NULL, 0, 1),
(88, 'Atadura de Crepe 10cm x 1,8m', 'Neve', 'Hospitalar', 3.90, 'Atadura de crepe 10cm de largura. Rolo de 1,8m. Unitário.', NULL, 0, 1),
(89, 'Termômetro Digital Axilar', 'G-Tech', 'Hospitalar', 29.90, 'Termômetro digital com alarme sonoro. Resultado em 60 segundos.', NULL, 0, 1),
(90, 'Máscara Cirúrgica Tripla Cx 50', 'Descarpack', 'Hospitalar', 24.90, 'Máscara descartável tripla camada com elástico. Caixa com 50 unidades.', NULL, 0, 1),
(91, 'Oxímetro de Pulso Digital', 'G-Tech', 'Hospitalar', 89.90, 'Oxímetro portátil para medição de SpO2 e frequência cardíaca. Pilha inclusa.', NULL, 0, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `login` varchar(50) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `cargo` varchar(50) DEFAULT 'Atendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `login`, `senha_hash`, `cargo`) VALUES
(1, 'Administrador', 'admin', '$2y$10$eE0m1W8uX0s.B3v4F7m/0.b.p0P1Qe/c/O/vQ/vQ/vQ/vQ/vQ/vQ', 'Gerente'),
(3, 'Caio', 'Caio', '$2y$10$xs9YDqUXXIMdPvA1Tb2k7.SDJ.XOrJOd28JajwJPwB.snNn6AjcLG', 'Atendente'),
(4, '123', '123', '$2y$10$C.YdblO4MAk2gLzRtyTTPuXCrneiGodaSCJ9ElBCeLJZiWahGlA8u', 'Atendente'),
(5, 'Gustavo', 'Gustavo', '$2y$10$GQABeAWPQx2i28Qk6rVODeCaBn7UeQLxccEO7hVCMgQgwCA83wdU2', 'Atendente'),
(6, 'Kim', 'Kim', '$2y$10$sT9n6ySoYgP997ImZe.8i.8D4pD0UtmkjiYsCzhELO4dpS7a6O8nK', 'Gerente');

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--

CREATE TABLE `vendas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `caixa_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `data_venda` datetime DEFAULT current_timestamp(),
  `supervisor_liberacao` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `caixa`
--
ALTER TABLE `caixa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `clientes_loja`
--
ALTER TABLE `clientes_loja`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cpf` (`cpf`);

--
-- Índices de tabela `itens_pedido_loja`
--
ALTER TABLE `itens_pedido_loja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices de tabela `itens_venda`
--
ALTER TABLE `itens_venda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venda_id` (`venda_id`),
  ADD KEY `produto_id` (`produto_id`),
  ADD KEY `lote_id` (`lote_id`);

--
-- Índices de tabela `lotes`
--
ALTER TABLE `lotes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices de tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `idx_pix_txid` (`pix_txid`);

--
-- Índices de tabela `pedidos_loja`
--
ALTER TABLE `pedidos_loja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices de tabela `pedido_itens`
--
ALTER TABLE `pedido_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_produto_ativo` (`nome`,`fabricante`,`ativo`),
  ADD UNIQUE KEY `idx_nome_fabricante_ativo` (`nome`,`fabricante`,`ativo`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- Índices de tabela `vendas`
--
ALTER TABLE `vendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `caixa`
--
ALTER TABLE `caixa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `clientes_loja`
--
ALTER TABLE `clientes_loja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `itens_pedido_loja`
--
ALTER TABLE `itens_pedido_loja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `itens_venda`
--
ALTER TABLE `itens_venda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `lotes`
--
ALTER TABLE `lotes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `pedidos_loja`
--
ALTER TABLE `pedidos_loja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `pedido_itens`
--
ALTER TABLE `pedido_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `caixa`
--
ALTER TABLE `caixa`
  ADD CONSTRAINT `caixa_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `itens_pedido_loja`
--
ALTER TABLE `itens_pedido_loja`
  ADD CONSTRAINT `itens_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos_loja` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `itens_pedido_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);

--
-- Restrições para tabelas `itens_venda`
--
ALTER TABLE `itens_venda`
  ADD CONSTRAINT `itens_venda_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `itens_venda_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`),
  ADD CONSTRAINT `itens_venda_ibfk_3` FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`);

--
-- Restrições para tabelas `lotes`
--
ALTER TABLE `lotes`
  ADD CONSTRAINT `lotes_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes_loja` (`id`);

--
-- Restrições para tabelas `pedidos_loja`
--
ALTER TABLE `pedidos_loja`
  ADD CONSTRAINT `pedidos_loja_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Restrições para tabelas `pedido_itens`
--
ALTER TABLE `pedido_itens`
  ADD CONSTRAINT `pi_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pi_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);

--
-- Restrições para tabelas `vendas`
--
ALTER TABLE `vendas`
  ADD CONSTRAINT `vendas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
