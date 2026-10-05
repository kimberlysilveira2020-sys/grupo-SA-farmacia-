<?php
class RelatorioController extends Controller
{
    public function index(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: login.php");
            exit;
        }

        $model = new RelatorioModel();

        // Lotes vencendo nos próximos 30 dias
        $stmtLotes = $model->relatoriosBuscarLotes();
        $lotes_vencendo = $stmtLotes->fetchAll();

        // Últimas 50 vendas
        $stmtVendas = $model->relatoriosBuscarVendas();
        $vendas = $stmtVendas->fetchAll();

        $total_vendas_count  = count($vendas);
        $valor_total_vendas  = array_sum(array_column($vendas, 'total'));

        $page_title = "Relatórios";
        $this->render('relatorios/index', get_defined_vars());
    }
}
