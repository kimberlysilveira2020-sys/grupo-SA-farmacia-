<?php
class DashboardModel {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Config::getDbConnection();
    }

    public function getBanners(): array {
        return $this->pdo->query("
            SELECT * FROM banners
            WHERE ativo = 1
              AND (data_inicio IS NULL OR data_inicio <= CURDATE())
              AND (data_fim IS NULL OR data_fim >= CURDATE())
            ORDER BY ordem ASC
        ")->fetchAll();
    }

    public function getLotesVencendo(): array {
        return $this->pdo->query("
            SELECT el.id AS lote_id, p.nome AS produto_nome, p.fabricante,
                   el.numero_lote, el.data_validade, el.qtd_atual,
                   DATEDIFF(el.data_validade, CURDATE()) AS dias_para_vencer
            FROM lotes el INNER JOIN produtos p ON el.produto_id = p.id
            WHERE el.data_validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
              AND el.qtd_atual > 0
            ORDER BY el.data_validade ASC
        ")->fetchAll();
    }

    public function getVendasRecentes(): array {
        return $this->pdo->query("
            SELECT v.id, v.data_venda, v.total, u.nome AS vendedor,
                   u.cargo AS cargo_vendedor, v.supervisor_liberacao
            FROM vendas v INNER JOIN usuarios u ON v.usuario_id = u.id
            ORDER BY v.data_venda DESC LIMIT 10
        ")->fetchAll();
    }

    public function criarBanner(array $d, ?string $imagem): bool {
        $stmt = $this->pdo->prepare("INSERT INTO banners (titulo, descricao, imagem, cor_fundo, data_inicio, data_fim, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$d['titulo'], $d['descricao'] ?? '', $imagem, $d['cor_fundo'] ?? '#1976D2', $d['data_inicio'] ?: null, $d['data_fim'] ?: null]);
        return true;
    }

    public function deletarBanner(int $id): bool {
        $old = $this->pdo->prepare("SELECT imagem FROM banners WHERE id = ?"); $old->execute([$id]);
        $img = $old->fetchColumn();
        if ($img && file_exists(__DIR__ . '/../../uploads/banners/' . $img)) {
            @unlink(__DIR__ . '/../../uploads/banners/' . $img);
        }
        $this->pdo->prepare("DELETE FROM banners WHERE id=?")->execute([$id]);
        return true;
    }
}
