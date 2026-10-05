<?php
class VendaController extends Controller
{
    public function index(): void
    {
        if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

        $model = new VendaModel();

        // ── Caixa atual do usuário ──────────────────────────────────────────
        $stmtCaixa = $model->vendasBuscarCaixa();
        $stmtCaixa->execute([$_SESSION['usuario_id']]);
        $caixaAberto = $stmtCaixa->fetch();

        // ── Totais do caixa atual ───────────────────────────────────────────
        $totalCaixa = 0;
        $qtdVendasCaixa = 0;
        if ($caixaAberto) {
            $stmtTot = $model->vendasBuscarVendas();
            $stmtTot->execute([$caixaAberto['id']]);
            $totRow = $stmtTot->fetch();
            $totalCaixa    = $totRow['soma'];
            $qtdVendasCaixa = $totRow['qtd'];
        }

        // ── Filtros da listagem ─────────────────────────────────────────────
        $filtroData  = $_GET['data']  ?? date('Y-m-d');
        $filtroData2 = $_GET['data2'] ?? date('Y-m-d');

        $stmtVendas = $model->vendasBuscarVendas2();
        $stmtVendas->execute([$filtroData, $filtroData2]);
        $vendas = $stmtVendas->fetchAll();

        $totalPeriodo = array_sum(array_column($vendas, 'total'));
        $qtdPeriodo   = count($vendas);

        // ── Vendas online (Pedidos da Loja) no período ──────────────────────
        // Considera apenas pedidos confirmados como venda efetivada
        $stmtOnline = $model->vendasBuscarPedidos();
        $stmtOnline->execute([$filtroData, $filtroData2]);
        $vendasOnline = $stmtOnline->fetchAll();

        $totalOnlinePeriodo = array_sum(array_column($vendasOnline, 'total'));
        $qtdOnlinePeriodo   = count($vendasOnline);

        // ── Totais gerais (PDV + Loja Online) ───────────────────────────────
        $totalGeralPeriodo = $totalPeriodo + $totalOnlinePeriodo;
        $qtdGeralPeriodo   = $qtdPeriodo + $qtdOnlinePeriodo;

        // ── Histórico de caixas ─────────────────────────────────────────────
        $stmtHist = $model->vendasBuscarCaixa2();
        $stmtHist->execute();
        $historicoCaixas = $stmtHist->fetchAll();

        $page_title = "Vendas & Caixa";
        $this->render('vendas/index', get_defined_vars());
    }
}
