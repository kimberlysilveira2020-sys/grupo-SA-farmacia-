<?php
require_once __DIR__ . '/../../core/Model.php';

class ProdutoModel extends Model {

    public function __construct() {
        parent::__construct();
        $this->garantirColunaAtivo();
    }

    private function garantirColunaAtivo(): void {
        $col = $this->db->query("SHOW COLUMNS FROM produtos LIKE 'ativo'")->fetchAll();
        if (empty($col)) {
            $this->db->exec("ALTER TABLE produtos ADD COLUMN `ativo` TINYINT(1) NOT NULL DEFAULT 1");
            $this->db->exec("UPDATE produtos SET ativo = 1 WHERE ativo IS NULL");
        }
    }

    public function listarTodos(): array {
        return $this->db->query("
            SELECT p.id, p.nome, p.fabricante, p.categoria, p.foto,
                   p.preco_venda, p.descricao,
                   COALESCE(est.estoque_total, 0) AS estoque_total,
                   est.validade_mais_proxima,
                   DATEDIFF(est.validade_mais_proxima, CURDATE()) AS dias_para_vencer
            FROM produtos p
            LEFT JOIN (
                SELECT produto_id,
                       SUM(qtd_atual) AS estoque_total,
                       MIN(data_validade) AS validade_mais_proxima
                FROM lotes WHERE qtd_atual > 0 GROUP BY produto_id
            ) est ON est.produto_id = p.id
            WHERE COALESCE(p.ativo, 1) = 1
            ORDER BY p.nome ASC
        ")->fetchAll();
    }

    public function buscarPorId(int $id): array|false {
        $s = $this->db->prepare("SELECT * FROM produtos WHERE id = ?");
        $s->execute([$id]);
        return $s->fetch();
    }

    public function existePorNomeFabricante(string $nome, string $fabricante): int|false {
        $s = $this->db->prepare("SELECT id FROM produtos WHERE nome = ? AND fabricante = ? AND COALESCE(ativo,1) = 1 LIMIT 1");
        $s->execute([$nome, $fabricante]);
        return $s->fetchColumn();
    }

    public function criar(string $nome, string $fabricante, string $categoria, float $preco, string $desc, ?string $foto): int {
        $s = $this->db->prepare("INSERT INTO produtos (nome, fabricante, categoria, preco_venda, descricao, foto, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $s->execute([$nome, $fabricante, $categoria, $preco, $desc, $foto]);
        return (int)$this->db->lastInsertId();
    }

    public function editar(int $id, string $nome, string $fabricante, string $categoria, float $preco, string $desc, ?string $foto = null): void {
        if ($foto) {
            $old = $this->db->prepare("SELECT foto FROM produtos WHERE id = ?");
            $old->execute([$id]);
            $oldFoto = $old->fetchColumn();
            if ($oldFoto && file_exists(UPLOAD_PATH . 'produtos/' . $oldFoto)) {
                @unlink(UPLOAD_PATH . 'produtos/' . $oldFoto);
            }
            $s = $this->db->prepare("UPDATE produtos SET nome=?, fabricante=?, categoria=?, preco_venda=?, descricao=?, foto=? WHERE id=?");
            $s->execute([$nome, $fabricante, $categoria, $preco, $desc, $foto, $id]);
        } else {
            $s = $this->db->prepare("UPDATE produtos SET nome=?, fabricante=?, categoria=?, preco_venda=?, descricao=? WHERE id=?");
            $s->execute([$nome, $fabricante, $categoria, $preco, $desc, $id]);
        }
    }

    public function deletar(int $id): array {
        $chk = $this->db->prepare("SELECT COUNT(*) FROM itens_venda WHERE produto_id = ?");
        $chk->execute([$id]);
        if ((int)$chk->fetchColumn() > 0) {
            $this->db->prepare("UPDATE produtos SET ativo = 0 WHERE id = ?")->execute([$id]);
            return ['aviso' => 'Produto desativado pois possui vendas vinculadas.'];
        }
        $this->db->prepare("DELETE FROM lotes WHERE produto_id = ?")->execute([$id]);
        $old = $this->db->prepare("SELECT foto FROM produtos WHERE id = ?");
        $old->execute([$id]);
        $foto = $old->fetchColumn();
        if ($foto && file_exists(UPLOAD_PATH . 'produtos/' . $foto)) @unlink(UPLOAD_PATH . 'produtos/' . $foto);
        $this->db->prepare("DELETE FROM produtos WHERE id = ?")->execute([$id]);
        return [];
    }

    public function listarLotes(int $produtoId): array {
        $s = $this->db->prepare("
            SELECT id, numero_lote, data_validade, qtd_atual,
                   DATEDIFF(data_validade, CURDATE()) AS dias_para_vencer,
                   CASE WHEN data_validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END AS vencendo
            FROM lotes WHERE produto_id = ? ORDER BY data_validade ASC
        ");
        $s->execute([$produtoId]);
        return $s->fetchAll();
    }

    public function criarLote(int $produtoId, string $numero, string $validade, int $qtd): void {
        $s = $this->db->prepare("INSERT INTO lotes (produto_id, numero_lote, data_validade, qtd_atual, qtd_inicial) VALUES (?, ?, ?, ?, ?)");
        $s->execute([$produtoId, $numero, $validade, $qtd, $qtd]);
    }

    public function salvarImagem(string $campo): ?string {
        if (empty($_FILES[$campo]['tmp_name'])) return null;
        $file = $_FILES[$campo];
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024) return null;
        $mime    = mime_content_type($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) throw new Exception('Tipo de imagem não permitido.');
        $nome = uniqid('img_', true) . '.' . $allowed[$mime];
        $dir  = UPLOAD_PATH . 'produtos/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (!move_uploaded_file($file['tmp_name'], $dir . $nome)) throw new Exception('Falha ao salvar imagem.');
        return $nome;
    }
}
