<?php
class RelatorioModel extends Model
{
    public function relatoriosBuscarLotes()
    {
        return $this->db()->query("
    SELECT l.id AS lote_id, p.nome AS produto_nome, p.fabricante,
           l.numero_lote, l.data_validade, l.qtd_atual,
           DATEDIFF(l.data_validade, CURDATE()) AS dias_para_vencer
    FROM lotes l
    INNER JOIN produtos p ON l.produto_id = p.id
    WHERE l.data_validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
      AND l.qtd_atual > 0
    ORDER BY l.data_validade ASC
");
    }

    public function relatoriosBuscarVendas()
    {
        return $this->db()->query("
    SELECT v.id, v.data_venda, v.total, v.supervisor_liberacao,
           u.nome AS vendedor, u.cargo AS cargo_vendedor
    FROM vendas v
    INNER JOIN usuarios u ON v.usuario_id = u.id
    ORDER BY v.data_venda DESC
    LIMIT 50
");
    }
}
