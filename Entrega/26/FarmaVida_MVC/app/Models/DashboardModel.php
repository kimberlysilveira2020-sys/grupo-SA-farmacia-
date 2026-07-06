<?php
require_once __DIR__ . '/../../core/Model.php';

class DashboardModel extends Model {

    public function lotesVencendo(): array {
        return $this->db->query("
            SELECT el.id AS lote_id, p.nome AS produto_nome, p.fabricante,
                   el.numero_lote, el.data_validade, el.qtd_atual,
                   DATEDIFF(el.data_validade, CURDATE()) AS dias_para_vencer
            FROM lotes el
            INNER JOIN produtos p ON el.produto_id = p.id
            WHERE el.data_validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
              AND el.qtd_atual > 0
            ORDER BY el.data_validade ASC
        ")->fetchAll();
    }

    public function banners(): array {
        return $this->db->query("
            SELECT * FROM banners
            WHERE ativo = 1
              AND (data_inicio IS NULL OR data_inicio <= CURDATE())
              AND (data_fim IS NULL OR data_fim >= CURDATE())
            ORDER BY ordem ASC
        ")->fetchAll();
    }

    public function vendasRecentes(int $limite = 10): array {
        return $this->db->query("
            SELECT v.id, v.data_venda, v.total, u.nome AS vendedor,
                   u.cargo AS cargo_vendedor, v.supervisor_liberacao
            FROM vendas v
            INNER JOIN usuarios u ON v.usuario_id = u.id
            ORDER BY v.data_venda DESC LIMIT $limite
        ")->fetchAll();
    }

    public function criarBanner(string $titulo, string $desc, ?string $imagem, string $cor, ?string $dtInicio, ?string $dtFim): void {
        $s = $this->db->prepare("INSERT INTO banners (titulo, descricao, imagem, cor_fundo, data_inicio, data_fim, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $s->execute([$titulo, $desc, $imagem, $cor, $dtInicio, $dtFim]);
    }

    public function deletarBanner(int $id): void {
        $old = $this->db->prepare("SELECT imagem FROM banners WHERE id = ?");
        $old->execute([$id]);
        $img = $old->fetchColumn();
        if ($img && file_exists(UPLOAD_PATH . 'banners/' . $img)) @unlink(UPLOAD_PATH . 'banners/' . $img);
        $this->db->prepare("DELETE FROM banners WHERE id=?")->execute([$id]);
    }

    public function salvarImagemBanner(): ?string {
        if (empty($_FILES['imagem']['tmp_name'])) return null;
        $file = $_FILES['imagem'];
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024) return null;
        $mime    = mime_content_type($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) return null;
        $nome = uniqid('img_', true) . '.' . $allowed[$mime];
        $dir  = UPLOAD_PATH . 'banners/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        move_uploaded_file($file['tmp_name'], $dir . $nome);
        return $nome;
    }
}
