<?php
class VendaModel extends Model
{
    public function vendasBuscarCaixa()
    {
        return $this->db()->prepare("SELECT * FROM caixa WHERE usuario_id = ? AND status = 'aberto' ORDER BY aberto_em DESC LIMIT 1");
    }

    public function vendasBuscarVendas()
    {
        return $this->db()->prepare("SELECT COUNT(*) AS qtd, COALESCE(SUM(total),0) AS soma FROM vendas WHERE caixa_id = ?");
    }

    public function vendasBuscarVendas2()
    {
        return $this->db()->prepare("
    SELECT v.id, v.data_venda, v.total, v.supervisor_liberacao,
           u.nome AS vendedor, u.cargo AS cargo_vendedor,
           c.nome AS cliente_nome,
           cx.id AS caixa_id
    FROM vendas v
    INNER JOIN usuarios u ON v.usuario_id = u.id
    LEFT JOIN clientes c ON v.cliente_id = c.id
    LEFT JOIN caixa cx   ON v.caixa_id   = cx.id
    WHERE DATE(v.data_venda) BETWEEN ? AND ?
    ORDER BY v.data_venda DESC
");
    }

    public function vendasBuscarPedidos()
    {
        return $this->db()->prepare("
    SELECT p.id, p.criado_em, p.total, p.status, p.forma_pagamento, p.pix_pago,
           p.boleto_codigo, p.paypal_order_id,
           cl.nome AS cliente_nome, cl.email AS cliente_email, cl.telefone AS cliente_tel,
           COUNT(pi.id) AS qtd_itens
    FROM pedidos p
    INNER JOIN clientes_loja cl ON cl.id = p.cliente_id
    LEFT JOIN pedido_itens pi ON pi.pedido_id = p.id
    WHERE p.status = 'confirmado' AND DATE(p.criado_em) BETWEEN ? AND ?
    GROUP BY p.id
    ORDER BY p.criado_em DESC
");
    }

    public function vendasBuscarCaixa2()
    {
        return $this->db()->prepare("
    SELECT cx.*, u.nome AS operador,
           COUNT(v.id) AS qtd_vendas,
           COALESCE(SUM(v.total),0) AS total_vendas
    FROM caixa cx
    INNER JOIN usuarios u ON cx.usuario_id = u.id
    LEFT JOIN vendas v ON v.caixa_id = cx.id
    GROUP BY cx.id
    ORDER BY cx.aberto_em DESC
    LIMIT 20
");
    }
}
