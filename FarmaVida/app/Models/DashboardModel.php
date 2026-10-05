<?php
class DashboardModel extends Model
{
    public function dashboardBuscarBanners()
    {
        return $this->db()->query("
    SELECT * FROM banners 
    WHERE ativo = 1 
      AND (data_inicio IS NULL OR data_inicio <= CURDATE())
      AND (data_fim IS NULL OR data_fim >= CURDATE())
    ORDER BY ordem ASC
");
    }

    public function dashboardBuscarLotes()
    {
        return $this->db()->query("
    SELECT el.id AS lote_id, p.nome AS produto_nome, p.fabricante, el.numero_lote, el.data_validade, el.qtd_atual, DATEDIFF(el.data_validade, CURDATE()) AS dias_para_vencer 
    FROM lotes el 
    INNER JOIN produtos p ON el.produto_id = p.id 
    WHERE el.data_validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND el.qtd_atual > 0 
    ORDER BY el.data_validade ASC
");
    }

    public function dashboardBuscarVendas()
    {
        return $this->db()->query("
    SELECT v.id, v.data_venda, v.total, u.nome AS vendedor, u.cargo AS cargo_vendedor, v.supervisor_liberacao 
    FROM vendas v 
    INNER JOIN usuarios u ON v.usuario_id = u.id 
    ORDER BY v.data_venda DESC LIMIT 10
");
    }
}
