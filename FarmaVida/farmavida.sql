-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 09/06/2026 às 03:11
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

--
-- Despejando dados para a tabela `itens_pedido_loja`
--

INSERT INTO `itens_pedido_loja` (`id`, `pedido_id`, `produto_id`, `quantidade`, `preco`) VALUES
(1, 1, 2, 1, 8.00);

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

--
-- Despejando dados para a tabela `itens_venda`
--

INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `lote_id`, `quantidade`, `preco`) VALUES
(1, 1, 2, 2, 1, 8.00);

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
(2, 2, '2', '2026-06-04', 9, 10);

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `status` enum('pendente','confirmado','cancelado') DEFAULT 'pendente',
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pedidos`
--

INSERT INTO `pedidos` (`id`, `cliente_id`, `status`, `total`, `criado_em`) VALUES
(1, 1, 'pendente', 8.00, '2026-06-08 19:51:33');

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

--
-- Despejando dados para a tabela `pedidos_loja`
--

INSERT INTO `pedidos_loja` (`id`, `cliente_id`, `status`, `total`, `observacao`, `criado_em`) VALUES
(1, 2, 'pendente', 8.00, NULL, '2026-06-03 22:12:18');

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
(1, 1, 2, 1, 8.00);

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
(2, 'caio', 'FarmaVida', 'Comum', 8.00, 'Gostoso', 'img_6a20a59e63fd71.74401529.png', 0, 0),
(25, 'Benzetacil', 'FarmaVida', 'Controlado', 10.00, '', 'img_6a2765d04248c5.37720560.webp', 0, 1),
(26, 'Dipirona', 'FarmaVida', 'Comum', 10.00, '', 'img_6a2765db48cd59.55974094.webp', 0, 1);

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
-- Despejando dados para a tabela `vendas`
--

INSERT INTO `vendas` (`id`, `usuario_id`, `cliente_id`, `caixa_id`, `total`, `data_venda`, `supervisor_liberacao`) VALUES
(1, 4, NULL, NULL, 8.00, '2026-06-03 19:08:19', NULL);

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
  ADD KEY `cliente_id` (`cliente_id`);

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
  ADD UNIQUE KEY `uq_nome_fabricante` (`nome`,`fabricante`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `pedidos_loja`
--
ALTER TABLE `pedidos_loja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `pedido_itens`
--
ALTER TABLE `pedido_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
