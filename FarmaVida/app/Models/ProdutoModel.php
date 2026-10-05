<?php
class ProdutoModel extends Model
{
    public function produtosCriarTabelaCategorias()
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

    public function produtosBuscarCategorias()
    {
        return $this->db()->query("SELECT COUNT(*) FROM categorias");
    }

    public function produtosInserirCategorias()
    {
        return $this->db()->exec("
        INSERT IGNORE INTO `categorias` (`nome`, `icone`, `ordem`) VALUES
        ('Comum', 'bi-capsule', 1),
        ('Genérico', 'bi-capsule-pill', 2),
        ('Controlado', 'bi-shield-lock', 3),
        ('Antibiótico', 'bi-bacteria', 4),
        ('Vitaminas', 'bi-heart', 5),
        ('Suplementos', 'bi-activity', 6),
        ('Dermocosméticos', 'bi-stars', 7),
        ('Higiene', 'bi-droplet', 8),
        ('Beleza', 'bi-flower1', 9),
        ('Infantil', 'bi-emoji-smile', 10),
        ('Ortopédico', 'bi-bandaid', 11),
        ('Hospitalar', 'bi-hospital', 12)
    ");
    }

    public function produtosBuscarCategorias2()
    {
        return $this->db()->query("SELECT * FROM categorias WHERE ativo=1 ORDER BY ordem ASC, nome ASC");
    }

    public function produtosBuscarProdutos2($whereSQL)
    {
        return $this->db()->prepare("SELECT COUNT(*) FROM produtos p $whereSQL");
    }

    public function produtosBuscarProdutos3($whereSQL, $por_pag, $offset)
    {
        return $this->db()->prepare("
    SELECT p.id, p.nome, p.fabricante, p.categoria, p.preco_venda, p.receita_obrigatoria, p.foto,
           COALESCE(SUM(l.qtd_atual),0) AS estoque_total,
           COUNT(l.id) AS num_lotes
    FROM produtos p
    LEFT JOIN lotes l ON l.produto_id = p.id AND l.qtd_atual > 0
    $whereSQL
    GROUP BY p.id
    ORDER BY p.nome ASC
    LIMIT $por_pag OFFSET $offset
");
    }
}
