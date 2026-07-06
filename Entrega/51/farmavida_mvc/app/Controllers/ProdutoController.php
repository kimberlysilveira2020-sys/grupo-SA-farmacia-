<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/ProdutoModel.php';

class ProdutoController extends BaseController {

    public function index(): void {
        $this->requireAuth();
        $model      = new ProdutoModel();
        $categorias = $model->getCategorias();
        $busca      = trim($_GET['q'] ?? '');
        $catFiltro  = trim($_GET['cat'] ?? '');
        $pagina     = max(1, (int)($_GET['p'] ?? 1));
        $resultado  = $model->getPaginado($busca, $catFiltro, $pagina, Config::PRODUTOS_POR_PAGINA);
        $produtos   = $resultado['lista'];
        $total      = $resultado['total'];
        $totalPags  = $resultado['totalPags'];
        $page_title = "Estoque / Produtos";
        include __DIR__ . '/../Views/layouts/header.php';
        include __DIR__ . '/../Views/produtos/index.php';
        include __DIR__ . '/../Views/layouts/footer.php';
    }

    // ─── API actions (chamadas pelo api.php) ────────────────────────────────

    public function apiCriar(): void {
        $this->requireAuthJson();
        $model = new ProdutoModel();
        $nome  = trim($_POST['nome'] ?? '');
        $fab   = trim($_POST['fabricante'] ?? '');
        $cat   = trim($_POST['categoria'] ?? '');
        $preco = $_POST['preco_venda'] ?? 0;
        if (!$nome || !$fab || !$cat || !$preco) {
            $this->jsonResponse(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
        }
        try {
            $foto = $this->salvarImagem('foto', 'produtos');
            $res  = $model->criar($_POST, $foto);
            $this->jsonResponse($res);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiEditarForm(): void {
        $this->requireAuthJson();
        $id    = intval($_POST['id'] ?? 0);
        $nome  = trim($_POST['nome'] ?? '');
        $fab   = trim($_POST['fabricante'] ?? '');
        $cat   = trim($_POST['categoria'] ?? '');
        $preco = $_POST['preco_venda'] ?? 0;
        if (!$id || !$nome || !$fab || !$cat || !$preco) {
            $this->jsonResponse(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
        }
        try {
            $model = new ProdutoModel();
            $foto  = $this->salvarImagem('foto', 'produtos');
            $model->editar(['id' => $id, 'nome' => $nome, 'fabricante' => $fab, 'categoria' => $cat, 'preco_venda' => $preco, 'descricao' => trim($_POST['descricao'] ?? '')], $foto);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiEditar(): void {
        $this->requireAuthJson();
        $data = $this->getJsonInput();
        $model = new ProdutoModel();
        $model->editar($data, null);
        $this->jsonResponse(['success' => true]);
    }

    public function apiDeletar(): void {
        $this->requireAuthJson();
        $data = $this->getJsonInput();
        $id   = intval($data['id'] ?? 0);
        if (!$id) $this->jsonResponse(['success' => false, 'message' => 'ID inválido.']);
        $model = new ProdutoModel();
        $this->jsonResponse($model->deletar($id));
    }

    public function apiLotesListar(): void {
        $this->requireAuthJson();
        $model = new ProdutoModel();
        $lotes = $model->getLotes((int)$_GET['produto_id']);
        $this->jsonResponse(['success' => true, 'lotes' => $lotes]);
    }

    public function apiLotesCriar(): void {
        $this->requireAuthJson();
        $model = new ProdutoModel();
        $model->criarLote($_POST);
        $this->jsonResponse(['success' => true]);
    }

    public function apiCategoriaCriar(): void {
        $this->requireAuthJson();
        $nome  = trim($_POST['nome'] ?? '');
        $icone = trim($_POST['icone'] ?? 'bi-tag');
        if (!$nome) $this->jsonResponse(['success' => false, 'message' => 'Nome da categoria obrigatório.']);
        try {
            $model = new ProdutoModel();
            $id    = $model->criarCategoria($nome, $icone);
            $this->jsonResponse(['success' => true, 'id' => $id]);
        } catch (\PDOException $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Categoria já existe com este nome.']);
        }
    }

    public function apiCategoriaRemover(): void {
        $this->requireAuthJson();
        $data = $this->getJsonInput();
        $id   = intval($data['id'] ?? 0);
        if (!$id) $this->jsonResponse(['success' => false, 'message' => 'ID inválido.']);
        $model = new ProdutoModel();
        $model->removerCategoria($id);
        $this->jsonResponse(['success' => true]);
    }

    public function apiCategoriasListar(): void {
        $this->requireAuthJson();
        $model = new ProdutoModel();
        $this->jsonResponse(['success' => true, 'categorias' => $model->getCategorias()]);
    }
}
