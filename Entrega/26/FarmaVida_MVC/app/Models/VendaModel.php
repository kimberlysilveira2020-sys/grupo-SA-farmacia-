<?php
require_once __DIR__ . '/../../core/Model.php';

class VendaModel extends Model {

    public function listarPorPeriodo(string $data1, string $data2): array {
        $s = $this->db->prepare("
            SELECT v.id, v.data_venda, v.total, v.supervisor_liberacao,
                   u.nome AS vendedor, u.cargo AS cargo_vendedor,
                   c.nome AS cliente_nome, cx.id AS caixa_id
            FROM vendas v
            INNER JOIN usuarios u ON v.usuario_id = u.id
            LEFT JOIN clientes c ON v.cliente_id = c.id
            LEFT JOIN caixa cx ON v.caixa_id = cx.id
            WHERE DATE(v.data_venda) BETWEEN ? AND ?
            ORDER BY v.data_venda DESC
        ");
        $s->execute([$data1, $data2]);
        return $s->fetchAll();
    }

    public function listarRecentes(int $limite = 10): array {
        return $this->db->query("
            SELECT v.id, v.data_venda, v.total, u.nome AS vendedor,
                   u.cargo AS cargo_vendedor, v.supervisor_liberacao
            FROM vendas v
            INNER JOIN usuarios u ON v.usuario_id = u.id
            ORDER BY v.data_venda DESC LIMIT $limite
        ")->fetchAll();
    }

    public function buscarItens(int $vendaId): array {
        $s = $this->db->prepare("
            SELECT iv.quantidade, iv.preco, p.nome AS produto_nome
            FROM itens_venda iv
            INNER JOIN produtos p ON iv.produto_id = p.id
            WHERE iv.venda_id = ?
        ");
        $s->execute([$vendaId]);
        return $s->fetchAll();
    }

    public function finalizar(array $itens, int $usuarioId, ?string $supervisor, ?int $caixaId): int {
        $total = array_sum(array_map(fn($i) => $i['quantidade'] * $i['preco'], $itens));
        $this->db->beginTransaction();

        $s = $this->db->prepare("INSERT INTO vendas (total, usuario_id, supervisor_liberacao, caixa_id) VALUES (?, ?, ?, ?)");
        $s->execute([$total, $usuarioId, $supervisor, $caixaId]);
        $vendaId = (int)$this->db->lastInsertId();

        $sItem  = $this->db->prepare("INSERT INTO itens_venda (venda_id, produto_id, lote_id, quantidade, preco) VALUES (?, ?, ?, ?, ?)");
        $sEst   = $this->db->prepare("UPDATE lotes SET qtd_atual = qtd_atual - ? WHERE id = ?");
        $sLote  = $this->db->prepare("SELECT id, qtd_atual FROM lotes WHERE produto_id = ? AND qtd_atual >= ? ORDER BY data_validade ASC LIMIT 1");

        foreach ($itens as $item) {
            $sLote->execute([$item['produto_id'], $item['quantidade']]);
            $lote = $sLote->fetch();
            if (!$lote) throw new Exception("Estoque insuficiente para {$item['nome']}.");
            $sItem->execute([$vendaId, $item['produto_id'], $lote['id'], $item['quantidade'], $item['preco']]);
            $sEst->execute([$item['quantidade'], $lote['id']]);
        }
        $this->db->commit();
        return $vendaId;
    }

    public function caixaAberto(int $usuarioId): array|false {
        $s = $this->db->prepare("SELECT * FROM caixa WHERE usuario_id = ? AND status = 'aberto' ORDER BY aberto_em DESC LIMIT 1");
        $s->execute([$usuarioId]);
        return $s->fetch();
    }

    public function totaisCaixa(int $caixaId): array {
        $s = $this->db->prepare("SELECT COUNT(*) AS qtd, COALESCE(SUM(total),0) AS soma FROM vendas WHERE caixa_id = ?");
        $s->execute([$caixaId]);
        return $s->fetch();
    }

    public function abrirCaixa(int $usuarioId, float $valor, ?string $obs): int {
        $s = $this->db->prepare("INSERT INTO caixa (usuario_id, valor_abertura, observacao, status) VALUES (?, ?, ?, 'aberto')");
        $s->execute([$usuarioId, $valor, $obs]);
        return (int)$this->db->lastInsertId();
    }

    public function fecharCaixa(int $id, float $valor, ?string $obs, int $usuarioId): void {
        $s = $this->db->prepare("UPDATE caixa SET status='fechado', valor_fechamento=?, fechado_em=NOW(), observacao=CONCAT(COALESCE(observacao,''),' | Fechamento: ',?) WHERE id=? AND usuario_id=?");
        $s->execute([$valor, $obs ?? '', $id, $usuarioId]);
    }

    public function historicoCaixas(int $limite = 20): array {
        return $this->db->query("
            SELECT cx.*, u.nome AS operador,
                   COUNT(v.id) AS qtd_vendas,
                   COALESCE(SUM(v.total),0) AS total_vendas
            FROM caixa cx
            INNER JOIN usuarios u ON cx.usuario_id = u.id
            LEFT JOIN vendas v ON v.caixa_id = cx.id
            GROUP BY cx.id ORDER BY cx.aberto_em DESC LIMIT $limite
        ")->fetchAll();
    }

    public function listarProdutosAtivos(): array {
        return $this->db->query("
            SELECT p.id, p.nome, p.fabricante, p.categoria, p.preco_venda, p.foto,
                   COALESCE(SUM(l.qtd_atual),0) AS estoque_total
            FROM produtos p
            LEFT JOIN lotes l ON l.produto_id = p.id
            WHERE COALESCE(p.ativo,1) = 1
            GROUP BY p.id
            HAVING estoque_total > 0
            ORDER BY p.nome ASC
        ")->fetchAll();
    }
}
