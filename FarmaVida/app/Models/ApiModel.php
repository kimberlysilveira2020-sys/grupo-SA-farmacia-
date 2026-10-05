<?php
class ApiModel extends Model
{
    public function apiInspecionarProdutos()
    {
        return $this->db()->query("SHOW COLUMNS FROM produtos LIKE 'ativo'");
    }

    public function apiAlterarTabelaProdutos()
    {
        return $this->db()->exec("ALTER TABLE produtos ADD COLUMN `ativo` TINYINT(1) NOT NULL DEFAULT 1");
    }

    public function apiAtualizarProdutos()
    {
        return $this->db()->exec("UPDATE produtos SET ativo = 1 WHERE ativo IS NULL");
    }

    public function apiExcluirProdutos()
    {
        return $this->db()->exec("
        DELETE FROM produtos
        WHERE COALESCE(ativo,1) = 0
          AND id NOT IN (SELECT DISTINCT produto_id FROM itens_venda)
          AND id NOT IN (SELECT DISTINCT produto_id FROM pedido_itens)
    ");
    }

    public function apiBuscarInformationSchema()
    {
        return $this->db()->query("
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_NAME='produtos' AND COLUMN_NAME='nome' AND SEQ_IN_INDEX=1
        AND INDEX_NAME LIKE 'idx_%' AND NON_UNIQUE=0
    ");
    }

    public function apiAlterarTabelaProdutos2()
    {
        return $this->db()->exec("ALTER TABLE produtos ADD UNIQUE INDEX idx_nome_fabricante_ativo (nome, fabricante, ativo)");
    }

    public function produtosCriarBuscarProdutos()
    {
        return $this->db()->prepare("SELECT id, ativo FROM produtos WHERE nome = ? AND fabricante = ? LIMIT 1 FOR UPDATE");
    }

    public function produtosCriarAtualizarProdutos($foto)
    {
        return $this->db()->prepare(
                        "UPDATE produtos SET categoria=?, preco_venda=?, descricao=?, ativo=1" .
                        ($foto ? ", foto=?" : "") .
                        " WHERE id=?"
                    );
    }

    public function produtosCriarInserirProdutos()
    {
        return $this->db()->prepare("INSERT INTO produtos (nome, fabricante, categoria, preco_venda, descricao, foto, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)");
    }

    public function produtosCriarInserirLotes()
    {
        return $this->db()->prepare("INSERT INTO lotes (produto_id, numero_lote, data_validade, qtd_atual, qtd_inicial) VALUES (?, ?, ?, ?, ?)");
    }

    public function produtosEditarFormBuscarProdutos()
    {
        return $this->db()->prepare("SELECT foto FROM produtos WHERE id = ?");
    }

    public function produtosEditarFormAtualizarProdutos()
    {
        return $this->db()->prepare("UPDATE produtos SET nome=?, fabricante=?, categoria=?, preco_venda=?, descricao=?, foto=? WHERE id=?");
    }

    public function produtosEditarFormAtualizarProdutos2()
    {
        return $this->db()->prepare("UPDATE produtos SET nome=?, fabricante=?, categoria=?, preco_venda=?, descricao=? WHERE id=?");
    }

    public function produtosEditarAtualizarProdutos()
    {
        return $this->db()->prepare("UPDATE produtos SET nome=?, fabricante=?, categoria=?, preco_venda=?, descricao=? WHERE id=?");
    }

    public function produtosDeletarBuscarItensVenda()
    {
        return $this->db()->prepare("SELECT COUNT(*) FROM itens_venda WHERE produto_id = ?");
    }

    public function produtosDeletarBuscarPedidoItens()
    {
        return $this->db()->prepare("SELECT COUNT(*) FROM pedido_itens WHERE produto_id = ?");
    }

    public function produtosDeletarAtualizarProdutos()
    {
        return $this->db()->prepare("UPDATE produtos SET ativo = 0 WHERE id = ?");
    }

    public function produtosDeletarExcluirLotes()
    {
        return $this->db()->prepare("DELETE FROM lotes WHERE produto_id = ?");
    }

    public function produtosDeletarBuscarProdutos()
    {
        return $this->db()->prepare("SELECT foto FROM produtos WHERE id = ?");
    }

    public function produtosDeletarExcluirProdutos()
    {
        return $this->db()->prepare("DELETE FROM produtos WHERE id = ?");
    }

    public function lotesListarBuscarLotes()
    {
        return $this->db()->prepare("SELECT id, numero_lote, data_validade, qtd_atual, DATEDIFF(data_validade, CURDATE()) AS dias_para_vencer, CASE WHEN data_validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END AS vencendo FROM lotes WHERE produto_id = ? ORDER BY data_validade ASC");
    }

    public function lotesCriarInserirLotes()
    {
        return $this->db()->prepare("INSERT INTO lotes (produto_id, numero_lote, data_validade, qtd_atual, qtd_inicial) VALUES (?, ?, ?, ?, ?)");
    }

    public function vendaFinalizarInserirVendas()
    {
        return $this->db()->prepare("INSERT INTO vendas (total, usuario_id, supervisor_liberacao) VALUES (?, ?, ?)");
    }

    public function vendaFinalizarInserirItensVenda()
    {
        return $this->db()->prepare("INSERT INTO itens_venda (venda_id, produto_id, lote_id, quantidade, preco) VALUES (?, ?, ?, ?, ?)");
    }

    public function vendaFinalizarAtualizarLotes()
    {
        return $this->db()->prepare("UPDATE lotes SET qtd_atual = qtd_atual - ? WHERE id = ?");
    }

    public function vendaFinalizarBuscarLotes()
    {
        return $this->db()->prepare("SELECT id, qtd_atual FROM lotes WHERE produto_id = ? AND qtd_atual >= ? ORDER BY data_validade ASC LIMIT 1");
    }

    public function bannerCriarInserirBanners()
    {
        return $this->db()->prepare("INSERT INTO banners (titulo, descricao, imagem, cor_fundo, data_inicio, data_fim, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)");
    }

    public function bannerDeletarBuscarBanners()
    {
        return $this->db()->prepare("SELECT imagem FROM banners WHERE id = ?");
    }

    public function bannerDeletarExcluirBanners()
    {
        return $this->db()->prepare("DELETE FROM banners WHERE id=?");
    }

    public function clienteCriarInserirClientes()
    {
        return $this->db()->prepare("INSERT INTO clientes (nome,cpf,telefone) VALUES (?,?,?)");
    }

    public function clienteEditarAtualizarClientes()
    {
        return $this->db()->prepare("UPDATE clientes SET nome=?,cpf=?,telefone=? WHERE id=?");
    }

    public function clienteDeletarExcluirClientes()
    {
        return $this->db()->prepare("DELETE FROM clientes WHERE id=?");
    }

    public function caixaAbrirBuscarCaixa()
    {
        return $this->db()->prepare("SELECT id FROM caixa WHERE usuario_id=? AND status='aberto'");
    }

    public function caixaAbrirInserirCaixa()
    {
        return $this->db()->prepare("INSERT INTO caixa (usuario_id,valor_abertura,observacao,status) VALUES (?,?,?,'aberto')");
    }

    public function caixaFecharAtualizarCaixa()
    {
        return $this->db()->prepare("UPDATE caixa SET status='fechado', valor_fechamento=?, fechado_em=NOW(), observacao=CONCAT(COALESCE(observacao,''),' | Fechamento: ',?) WHERE id=? AND usuario_id=?");
    }

    public function vendaItensBuscarItensVenda()
    {
        return $this->db()->prepare("
                SELECT iv.quantidade, iv.preco, p.nome AS produto_nome
                FROM itens_venda iv
                INNER JOIN produtos p ON iv.produto_id = p.id
                WHERE iv.venda_id = ?
            ");
    }

    public function categoriaCriarCriarTabelaCategorias()
    {
        return $this->db()->exec("
                CREATE TABLE IF NOT EXISTS `categorias` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `nome` varchar(100) NOT NULL,
                  `icone` varchar(50) DEFAULT 'bi-tag',
                  `ativo` tinyint(1) DEFAULT 1,
                  `ordem` int(11) DEFAULT 0,
                  `criado_em` datetime DEFAULT current_timestamp(),
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `nome` (`nome`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ");
    }

    public function categoriaCriarInserirCategorias()
    {
        return $this->db()->prepare("INSERT INTO categorias (nome, icone) VALUES (?, ?)");
    }

    public function categoriaRemoverExcluirCategorias()
    {
        return $this->db()->prepare("DELETE FROM categorias WHERE id=?");
    }

    public function categoriasListarCriarTabelaCategorias()
    {
        return $this->db()->exec("
                CREATE TABLE IF NOT EXISTS `categorias` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `nome` varchar(100) NOT NULL,
                  `icone` varchar(50) DEFAULT 'bi-tag',
                  `ativo` tinyint(1) DEFAULT 1,
                  `ordem` int(11) DEFAULT 0,
                  `criado_em` datetime DEFAULT current_timestamp(),
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `nome` (`nome`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ");
    }

    public function categoriasListarBuscarCategorias()
    {
        return $this->db()->query("SELECT * FROM categorias WHERE ativo=1 ORDER BY ordem ASC, nome ASC");
    }

    public function pedidosLojaListarBuscarPedidos($whereSQL)
    {
        return $this->db()->prepare("
                SELECT p.id, p.status, p.total, p.forma_pagamento, p.pix_pago,
                       p.boleto_codigo, p.boleto_vencimento, p.pix_txid,
                       p.criado_em,
                       cl.nome AS cliente_nome, cl.email AS cliente_email, cl.telefone AS cliente_tel,
                       COUNT(pi.id) AS qtd_itens
                FROM pedidos p
                INNER JOIN clientes_loja cl ON cl.id = p.cliente_id
                LEFT  JOIN pedido_itens pi  ON pi.pedido_id = p.id
                WHERE $whereSQL
                GROUP BY p.id
                ORDER BY p.criado_em DESC
            ");
    }

    public function pedidosLojaListarBuscarPedidos2()
    {
        return $this->db()->query("
                SELECT status,
                       COUNT(*) AS qtd,
                       COALESCE(SUM(total),0) AS soma
                FROM pedidos
                GROUP BY status
                UNION ALL
                SELECT 'total_geral' AS status,
                       COUNT(*) AS qtd,
                       COALESCE(SUM(total),0) AS soma
                FROM pedidos
            ");
    }

    public function pedidoLojaDetalhesBuscarPedidos()
    {
        return $this->db()->prepare("
                SELECT p.*, cl.nome AS cliente_nome, cl.email AS cliente_email,
                       cl.telefone AS cliente_tel, cl.cpf AS cliente_cpf
                FROM pedidos p
                INNER JOIN clientes_loja cl ON cl.id = p.cliente_id
                WHERE p.id = ?
            ");
    }

    public function pedidoLojaDetalhesBuscarPedidoItens()
    {
        return $this->db()->prepare("
                SELECT pi.quantidade, pi.preco,
                       pr.nome AS produto_nome, pr.fabricante, pr.categoria
                FROM pedido_itens pi
                INNER JOIN produtos pr ON pr.id = pi.produto_id
                WHERE pi.pedido_id = ?
            ");
    }

    public function pedidoLojaStatusAtualizarPedidos()
    {
        return $this->db()->prepare("UPDATE pedidos SET status=? WHERE id=?");
    }

    public function pedidoLojaPixConfirmarAtualizarPedidos()
    {
        return $this->db()->prepare("UPDATE pedidos SET pix_pago=1, status='confirmado' WHERE id=?");
    }

    public function pedidoLojaDeletarExcluirPedidoItens()
    {
        return $this->db()->prepare("DELETE FROM pedido_itens WHERE pedido_id=?");
    }

    public function pedidoLojaDeletarExcluirPedidos()
    {
        return $this->db()->prepare("DELETE FROM pedidos WHERE id=?");
    }
}
