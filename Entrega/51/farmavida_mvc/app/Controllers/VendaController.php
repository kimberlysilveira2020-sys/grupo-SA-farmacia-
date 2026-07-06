<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/VendaModel.php';

class VendaController extends BaseController {

    public function index(): void {
        $this->requireAuth();
        $model          = new VendaModel();
        $caixaAberto    = $model->getCaixaAberto($_SESSION['usuario_id']);
        $totalCaixa     = 0;
        $qtdVendasCaixa = 0;
        if ($caixaAberto) {
            $tots           = $model->getTotaisCaixa($caixaAberto['id']);
            $totalCaixa     = $tots['soma'];
            $qtdVendasCaixa = $tots['qtd'];
        }
        $filtroData  = $_GET['data']  ?? date('Y-m-d');
        $filtroData2 = $_GET['data2'] ?? date('Y-m-d');
        $vendas         = $model->listar($filtroData, $filtroData2);
        $totalPeriodo   = array_sum(array_column($vendas, 'total'));
        $qtdPeriodo     = count($vendas);
        $historicoCaixas = $model->getHistoricoCaixas();
        $page_title     = "Vendas & Caixa";
        include __DIR__ . '/../Views/layouts/header.php';
        include __DIR__ . '/../Views/vendas/index.php';
        include __DIR__ . '/../Views/layouts/footer.php';
    }

    public function apiFinalizar(): void {
        $this->requireAuthJson();
        $itens      = json_decode($_POST['itens'] ?? '[]', true);
        $supervisor = $_POST['supervisor'] ?? null;
        $clienteId  = isset($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
        $caixaId    = isset($_POST['caixa_id'])   ? (int)$_POST['caixa_id']   : null;
        try {
            $model = new VendaModel();
            $res   = $model->finalizar($itens, $_SESSION['usuario_id'], $supervisor, $clienteId, $caixaId);
            $this->jsonResponse($res);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiItens(): void {
        $this->requireAuthJson();
        $id    = intval($_GET['id'] ?? 0);
        $model = new VendaModel();
        $itens = $model->getItens($id);
        $total = array_sum(array_map(fn($i) => $i['quantidade'] * $i['preco'], $itens));
        $this->jsonResponse(['success' => true, 'itens' => $itens, 'total' => $total]);
    }

    public function apiAbrirCaixa(): void {
        $this->requireAuthJson();
        $valor = floatval($_POST['valor_abertura'] ?? 0);
        $obs   = trim($_POST['observacao'] ?? '') ?: null;
        $model = new VendaModel();
        $this->jsonResponse($model->abrirCaixa($_SESSION['usuario_id'], $valor, $obs));
    }

    public function apiFecharCaixa(): void {
        $this->requireAuthJson();
        $id    = intval($_POST['id'] ?? 0);
        $valor = floatval($_POST['valor_fechamento'] ?? 0);
        $obs   = trim($_POST['observacao'] ?? '') ?: null;
        if (!$id) $this->jsonResponse(['success' => false, 'message' => 'ID inválido.']);
        $model = new VendaModel();
        $this->jsonResponse($model->fecharCaixa($id, $_SESSION['usuario_id'], $valor, $obs));
    }
}
