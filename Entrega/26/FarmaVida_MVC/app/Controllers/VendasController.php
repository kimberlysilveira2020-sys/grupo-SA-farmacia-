<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../Models/VendaModel.php';

class VendasController extends Controller {

    private function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function index(): void {
        $this->requireAuth();
        $model       = new VendaModel();
        $filtroData  = $_GET['data']  ?? date('Y-m-d');
        $filtroData2 = $_GET['data2'] ?? date('Y-m-d');
        $caixaAberto = $model->caixaAberto($_SESSION['usuario_id']);

        $totalCaixa     = 0;
        $qtdVendasCaixa = 0;
        if ($caixaAberto) {
            $tot = $model->totaisCaixa($caixaAberto['id']);
            $totalCaixa     = $tot['soma'];
            $qtdVendasCaixa = $tot['qtd'];
        }

        $vendas          = $model->listarPorPeriodo($filtroData, $filtroData2);
        $totalPeriodo    = array_sum(array_column($vendas, 'total'));
        $qtdPeriodo      = count($vendas);
        $historicoCaixas = $model->historicoCaixas();
        $produtos        = $model->listarProdutosAtivos();

        $page_title = 'Vendas & Caixa';
        $this->view('layouts/header', compact('page_title'));
        $this->view('vendas/index', compact(
            'caixaAberto', 'totalCaixa', 'qtdVendasCaixa',
            'vendas', 'totalPeriodo', 'qtdPeriodo',
            'filtroData', 'filtroData2', 'historicoCaixas', 'produtos'
        ));
        $this->view('layouts/footer');
    }

    public function finalizar(): void {
        $this->requireAuth();
        $itens      = json_decode($_POST['itens'] ?? '[]', true);
        $supervisor = $_POST['supervisor'] ?? null;

        if ($supervisor && $supervisor !== Config::SENHA_SUPERVISOR_MESTRA) {
            $this->json(['success' => false, 'message' => 'Senha do supervisor incorreta!'], 403);
        }

        $caixaAberto = (new VendaModel())->caixaAberto($_SESSION['usuario_id']);
        $caixaId     = $caixaAberto ? $caixaAberto['id'] : null;

        try {
            $vendaId = (new VendaModel())->finalizar($itens, $_SESSION['usuario_id'], $supervisor, $caixaId);
            $this->json(['success' => true, 'venda_id' => $vendaId]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function itens(): void {
        $this->requireAuth();
        $id    = intval($_GET['id'] ?? 0);
        $itens = (new VendaModel())->buscarItens($id);
        $total = array_sum(array_map(fn($i) => $i['quantidade'] * $i['preco'], $itens));
        $this->json(['success' => true, 'itens' => $itens, 'total' => $total]);
    }

    public function abrirCaixa(): void {
        $this->requireAuth();
        $model = new VendaModel();
        $chk   = $model->caixaAberto($_SESSION['usuario_id']);
        if ($chk) $this->json(['success' => false, 'message' => 'Você já possui um caixa aberto.']);
        $valor = floatval($_POST['valor_abertura'] ?? 0);
        $obs   = trim($_POST['observacao'] ?? '') ?: null;
        $id    = $model->abrirCaixa($_SESSION['usuario_id'], $valor, $obs);
        $this->json(['success' => true, 'caixa_id' => $id]);
    }

    public function fecharCaixa(): void {
        $this->requireAuth();
        $id    = intval($_POST['id'] ?? 0);
        $valor = floatval($_POST['valor_fechamento'] ?? 0);
        $obs   = trim($_POST['observacao'] ?? '') ?: null;
        if (!$id) $this->json(['success' => false, 'message' => 'ID inválido.']);
        (new VendaModel())->fecharCaixa($id, $valor, $obs, $_SESSION['usuario_id']);
        $this->json(['success' => true]);
    }
}
