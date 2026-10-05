<?php
class ProdutoController extends Controller
{
    public function index(): void
    {
        if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

        $model = new ProdutoModel();

        // Garante tabela de categorias com dados padrão
        $model->produtosCriarTabelaCategorias();

        // Insere categorias padrão se a tabela estiver vazia
        $qtdCat = (int)$model->produtosBuscarCategorias()->fetchColumn();
        if ($qtdCat === 0) {
            $model->produtosInserirCategorias();
        }

        // Busca categorias
        $categorias = $model->produtosBuscarCategorias2()->fetchAll();

        // Busca produtos com estoque
        $busca    = trim($_GET['q'] ?? '');
        $catFiltro= trim($_GET['cat'] ?? '');
        $pagina   = max(1, (int)($_GET['p'] ?? 1));
        $por_pag  = Config::PRODUTOS_POR_PAGINA;
        $offset   = ($pagina - 1) * $por_pag;

        $where  = ["p.ativo = 1"];
        $params = [];
        if ($busca)    { $where[] = "(p.nome LIKE ? OR p.fabricante LIKE ?)"; $params[] = "%$busca%"; $params[] = "%$busca%"; }
        if ($catFiltro){ $where[] = "p.categoria = ?"; $params[] = $catFiltro; }
        $whereSQL = 'WHERE ' . implode(' AND ', $where);

        $stC = $model->produtosBuscarProdutos2($whereSQL);
        $stC->execute($params);
        $total = (int)$stC->fetchColumn();
        $totalPags = max(1, ceil($total / $por_pag));

        $stP = $model->produtosBuscarProdutos3($whereSQL, $por_pag, $offset);
        $stP->execute($params);
        $produtos = $stP->fetchAll();

        $page_title = "Estoque / Produtos";
        $this->render('produtos/index', get_defined_vars());
    }
}
