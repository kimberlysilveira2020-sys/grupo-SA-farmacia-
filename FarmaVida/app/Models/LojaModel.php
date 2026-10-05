<?php
class LojaModel extends Model
{
    public function registrarInserirClientesLoja()
    {
        return $this->db()->prepare("INSERT INTO clientes_loja (nome,email,senha_hash,cpf,telefone) VALUES (?,?,?,?,?)");
    }

    public function loginBuscarClientesLoja()
    {
        return $this->db()->prepare("SELECT id,nome,email,senha_hash FROM clientes_loja WHERE email=?");
    }

    public function produtosBuscarProdutos($whereSQL)
    {
        return $this->db()->prepare("
                SELECT COUNT(*) FROM produtos p
                LEFT JOIN (SELECT produto_id, SUM(qtd_atual) AS total FROM lotes WHERE qtd_atual>0 GROUP BY produto_id) est ON est.produto_id=p.id
                $whereSQL
            ");
    }

    public function produtosBuscarProdutos2($whereSQL, $porPagina, $offset)
    {
        return $this->db()->prepare("
                SELECT p.id, p.nome, p.fabricante, p.categoria, p.preco_venda, p.descricao, p.foto,
                       COALESCE(est.total,0) AS estoque,
                       DATEDIFF(MIN(l.data_validade), CURDATE()) AS dias_vencer
                FROM produtos p
                LEFT JOIN (SELECT produto_id, SUM(qtd_atual) AS total FROM lotes WHERE qtd_atual>0 GROUP BY produto_id) est ON est.produto_id=p.id
                LEFT JOIN lotes l ON l.produto_id=p.id AND l.qtd_atual>0
                $whereSQL
                GROUP BY p.id
                ORDER BY p.nome ASC
                LIMIT $porPagina OFFSET $offset
            ");
    }

    public function categoriasBuscarCategorias()
    {
        return $this->db()->query("
                    SELECT nome, icone FROM categorias WHERE ativo=1 ORDER BY ordem ASC, nome ASC
                ");
    }

    public function bannersBuscarBanners()
    {
        return $this->db()->query("
                SELECT id, titulo, descricao, imagem, cor_fundo FROM banners
                WHERE ativo=1
                  AND (data_inicio IS NULL OR data_inicio <= CURDATE())
                  AND (data_fim   IS NULL OR data_fim   >= CURDATE())
                ORDER BY ordem ASC
            ");
    }

    public function gerarPixInspecionarPedidos()
    {
        return $this->db()->query("SHOW COLUMNS FROM pedidos LIKE 'pix_txid'");
    }

    public function gerarPixAlterarTabelaPedidos()
    {
        return $this->db()->exec("ALTER TABLE pedidos
                        ADD COLUMN `forma_pagamento` VARCHAR(20) NOT NULL DEFAULT 'pix' AFTER `status`,
                        ADD COLUMN `pix_txid`        VARCHAR(50) NULL AFTER `forma_pagamento`,
                        ADD COLUMN `pix_pago`        TINYINT(1)  NOT NULL DEFAULT 0 AFTER `pix_txid`
                    ");
    }

    public function gerarPixInserirPedidos()
    {
        return $this->db()->prepare("INSERT INTO pedidos (cliente_id,total,status,forma_pagamento,pix_txid,pix_pago) VALUES (?,?,'pendente','pix',?,0)");
    }

    public function gerarPixInserirPedidoItens()
    {
        return $this->db()->prepare("INSERT INTO pedido_itens (pedido_id,produto_id,quantidade,preco) VALUES (?,?,?,?)");
    }

    public function confirmarPixBuscarPedidos()
    {
        return $this->db()->prepare("
                SELECT id, total, pix_txid
                FROM pedidos
                WHERE id = ? AND cliente_id = ? AND forma_pagamento = 'pix' AND pix_pago = 0
            ");
    }

    public function confirmarPixBuscarPedidos2()
    {
        return $this->db()->prepare("SELECT status FROM pedidos WHERE id=? AND cliente_id=?");
    }

    public function confirmarPixAtualizarPedidos()
    {
        return $this->db()->prepare("
                UPDATE pedidos
                SET pix_pago = 1, status = 'aguardando_confirmacao'
                WHERE id = ? AND cliente_id = ? AND pix_pago = 0
            ");
    }

    public function cancelarPixBuscarPedidos()
    {
        return $this->db()->prepare("SELECT id FROM pedidos WHERE id=? AND cliente_id=? AND pix_pago=0");
    }

    public function cancelarPixExcluirPedidoItens()
    {
        return $this->db()->prepare("DELETE FROM pedido_itens WHERE pedido_id=?");
    }

    public function cancelarPixExcluirPedidos()
    {
        return $this->db()->prepare("DELETE FROM pedidos WHERE id=? AND pix_pago=0");
    }

    public function cartoesListarBuscarCartoesCliente()
    {
        return $this->db()->prepare("SELECT id,apelido,bandeira,ultimos4,nome_titular,mes_validade,ano_validade,padrao FROM cartoes_cliente WHERE cliente_id=? ORDER BY padrao DESC, criado_em DESC");
    }

    public function cartaoSalvarBuscarCartoesCliente()
    {
        return $this->db()->prepare("SELECT id FROM cartoes_cliente WHERE cliente_id=? AND ultimos4=? AND bandeira=? AND mes_validade=? AND ano_validade=?");
    }

    public function cartaoSalvarAtualizarCartoesCliente()
    {
        return $this->db()->prepare("UPDATE cartoes_cliente SET padrao=0 WHERE cliente_id=?");
    }

    public function cartaoSalvarInserirCartoesCliente()
    {
        return $this->db()->prepare("INSERT INTO cartoes_cliente (cliente_id,apelido,bandeira,ultimos4,nome_titular,mes_validade,ano_validade,token_hash,padrao) VALUES (?,?,?,?,?,?,?,?,?)");
    }

    public function cartaoExcluirExcluirCartoesCliente()
    {
        return $this->db()->prepare("DELETE FROM cartoes_cliente WHERE id=? AND cliente_id=?");
    }

    public function pagarCartaoBuscarCartoesCliente()
    {
        return $this->db()->prepare("SELECT id,bandeira,ultimos4 FROM cartoes_cliente WHERE id=? AND cliente_id=?");
    }

    public function pagarCartaoInspecionarPedidos()
    {
        return $this->db()->query("SHOW COLUMNS FROM pedidos LIKE 'forma_pagamento'");
    }

    public function pagarCartaoAlterarTabelaPedidos()
    {
        return $this->db()->exec("ALTER TABLE pedidos
                        ADD COLUMN `forma_pagamento` VARCHAR(20) NOT NULL DEFAULT 'pix' AFTER `status`,
                        ADD COLUMN `pix_txid`        VARCHAR(50) NULL AFTER `forma_pagamento`,
                        ADD COLUMN `pix_pago`        TINYINT(1)  NOT NULL DEFAULT 0 AFTER `pix_txid`,
                        ADD COLUMN `boleto_codigo`   VARCHAR(60) NULL AFTER `pix_pago`,
                        ADD COLUMN `boleto_vencimento` DATE NULL AFTER `boleto_codigo`,
                        ADD COLUMN `paypal_order_id` VARCHAR(80) NULL AFTER `boleto_vencimento`
                    ");
    }

    public function pagarCartaoInserirPedidos()
    {
        return $this->db()->prepare("INSERT INTO pedidos (cliente_id,total,status,forma_pagamento) VALUES (?,?,'confirmado','cartao')");
    }

    public function pagarCartaoInserirPedidoItens()
    {
        return $this->db()->prepare("INSERT INTO pedido_itens (pedido_id,produto_id,quantidade,preco) VALUES (?,?,?,?)");
    }

    public function pagarCartaoAtualizarPedidos($pedidoId)
    {
        return $this->db()->exec("UPDATE pedidos SET status='cancelado' WHERE id=$pedidoId");
    }

    public function gerarBoletoInspecionarPedidos()
    {
        return $this->db()->query("SHOW COLUMNS FROM pedidos LIKE 'boleto_codigo'");
    }

    public function gerarBoletoAlterarTabelaPedidos()
    {
        return $this->db()->exec("ALTER TABLE pedidos
                        ADD COLUMN IF NOT EXISTS `forma_pagamento` VARCHAR(20) NOT NULL DEFAULT 'pix' AFTER `status`,
                        ADD COLUMN IF NOT EXISTS `pix_txid`        VARCHAR(50) NULL AFTER `forma_pagamento`,
                        ADD COLUMN IF NOT EXISTS `pix_pago`        TINYINT(1)  NOT NULL DEFAULT 0 AFTER `pix_txid`,
                        ADD COLUMN IF NOT EXISTS `boleto_codigo`   VARCHAR(60) NULL AFTER `pix_pago`,
                        ADD COLUMN IF NOT EXISTS `boleto_vencimento` DATE NULL AFTER `boleto_codigo`,
                        ADD COLUMN IF NOT EXISTS `paypal_order_id` VARCHAR(80) NULL AFTER `boleto_vencimento`
                    ");
    }

    public function gerarBoletoInserirPedidos()
    {
        return $this->db()->prepare("INSERT INTO pedidos (cliente_id,total,status,forma_pagamento,boleto_codigo,boleto_vencimento) VALUES (?,?,'pendente','boleto',?,?)");
    }

    public function gerarBoletoInserirPedidoItens()
    {
        return $this->db()->prepare("INSERT INTO pedido_itens (pedido_id,produto_id,quantidade,preco) VALUES (?,?,?,?)");
    }

    public function paypalCriarOrderInspecionarPedidos()
    {
        return $this->db()->query("SHOW COLUMNS FROM pedidos LIKE 'paypal_order_id'");
    }

    public function paypalCriarOrderAlterarTabelaPedidos()
    {
        return $this->db()->exec("ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS `paypal_order_id` VARCHAR(80) NULL");
    }

    public function paypalCriarOrderInserirPedidos()
    {
        return $this->db()->prepare("INSERT INTO pedidos (cliente_id,total,status,forma_pagamento,paypal_order_id) VALUES (?,?,'pendente','paypal',?)");
    }

    public function paypalCriarOrderInserirPedidoItens()
    {
        return $this->db()->prepare("INSERT INTO pedido_itens (pedido_id,produto_id,quantidade,preco) VALUES (?,?,?,?)");
    }

    public function paypalCapturarAtualizarPedidos()
    {
        return $this->db()->prepare("UPDATE pedidos SET status='confirmado' WHERE id=? AND cliente_id=?");
    }

    public function paypalCapturarAtualizarPedidos2()
    {
        return $this->db()->prepare("UPDATE pedidos SET status='cancelado' WHERE id=?");
    }

    public function pedidoCriarInserirPedidos()
    {
        return $this->db()->prepare("INSERT INTO pedidos (cliente_id,total,status) VALUES (?,?,'pendente')");
    }

    public function pedidoCriarInserirPedidoItens()
    {
        return $this->db()->prepare("INSERT INTO pedido_itens (pedido_id,produto_id,quantidade,preco) VALUES (?,?,?,?)");
    }

    public function pedidoItensBuscarPedidos()
    {
        return $this->db()->prepare("SELECT id FROM pedidos WHERE id=? AND cliente_id=?");
    }

    public function pedidoItensBuscarPedidoItens()
    {
        return $this->db()->prepare("
                SELECT pi.quantidade, pi.preco,
                       pr.nome AS produto_nome, pr.foto
                FROM pedido_itens pi
                INNER JOIN produtos pr ON pr.id = pi.produto_id
                WHERE pi.pedido_id = ?
            ");
    }

    public function meusPedidosBuscarPedidos()
    {
        return $this->db()->prepare("
                SELECT p.id, p.status, p.total, p.criado_em,
                       GROUP_CONCAT(pr.nome SEPARATOR ', ') AS produtos_nomes
                FROM pedidos p
                INNER JOIN pedido_itens itv ON itv.pedido_id = p.id
                INNER JOIN produtos pr ON pr.id = itv.produto_id
                WHERE p.cliente_id = ?
                GROUP BY p.id ORDER BY p.criado_em DESC
            ");
    }
}
