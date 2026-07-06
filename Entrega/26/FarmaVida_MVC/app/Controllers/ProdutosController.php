<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../Models/ProdutoModel.php';

class ProdutosController extends Controller {

    private function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function index(): void {
        $this->requireAuth();
        $model    = new ProdutoModel();
        $produtos = $model->listarTodos();
        $page_title = 'Estoque & Produtos';
        $this->view('layouts/header', compact('page_title'));
        $this->view('produtos/index', compact('produtos'));
        $this->view('layouts/footer');
    }

    public function criar(): void {
        $this->requireAuth();
        $model  = new ProdutoModel();
        $nome   = trim($_POST['nome'] ?? '');
        $fabric = trim($_POST['fabricante'] ?? '');
        $cat    = trim($_POST['categoria'] ?? '');
        $preco  = $_POST['preco_venda'] ?? 0;
        $desc   = trim($_POST['descricao'] ?? '');

        if (!$nome || !$fabric || !$cat || !$preco) {
            $this->json(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
        }

        $existente = $model->existePorNomeFabricante($nome, $fabric);
        if ($existente) {
            $this->json(['success' => true, 'produto_id' => $existente]);
        }

        try {
            $foto      = $model->salvarImagem('foto');
            $produtoId = $model->criar($nome, $fabric, $cat, (float)$preco, $desc, $foto);

            $loteNum = trim($_POST['lote_numero'] ?? '');
            $loteVal = trim($_POST['lote_validade'] ?? '');
            $loteQtd = intval($_POST['lote_quantidade'] ?? 0);
            if ($loteNum && $loteVal && $loteQtd > 0) {
                $model->criarLote($produtoId, $loteNum, $loteVal, $loteQtd);
            }
            $this->json(['success' => true, 'produto_id' => $produtoId]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function editar(): void {
        $this->requireAuth();
        $model  = new ProdutoModel();
        $id     = intval($_POST['id'] ?? 0);
        $nome   = trim($_POST['nome'] ?? '');
        $fab    = trim($_POST['fabricante'] ?? '');
        $cat    = trim($_POST['categoria'] ?? '');
        $preco  = $_POST['preco_venda'] ?? 0;
        $desc   = trim($_POST['descricao'] ?? '');

        if (!$id || !$nome || !$fab || !$cat || !$preco) {
            $this->json(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
        }

        try {
            $foto = $model->salvarImagem('foto');
            $model->editar($id, $nome, $fab, $cat, (float)$preco, $desc, $foto);
            $this->json(['success' => true]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deletar(): void {
        $this->requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = intval($data['id'] ?? 0);
        if (!$id) $this->json(['success' => false, 'message' => 'ID inválido.']);
        $result = (new ProdutoModel())->deletar($id);
        $this->json(array_merge(['success' => true], $result));
    }

    public function lotes(): void {
        $this->requireAuth();
        $produtoId = intval($_GET['produto_id'] ?? 0);
        $lotes     = (new ProdutoModel())->listarLotes($produtoId);
        $this->json(['success' => true, 'lotes' => $lotes]);
    }

    public function loteCriar(): void {
        $this->requireAuth();
        $model = new ProdutoModel();
        $qtd   = intval($_POST['qtd_atual'] ?? 0);
        $model->criarLote(intval($_POST['produto_id']), trim($_POST['numero_lote']), $_POST['data_validade'], $qtd);
        $this->json(['success' => true]);
    }
}
