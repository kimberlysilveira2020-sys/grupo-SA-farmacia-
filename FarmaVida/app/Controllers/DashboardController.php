<?php
class DashboardController extends Controller
{
    public function index(): void
    {
        if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

        $model = new DashboardModel();

        // Banners ativos e dentro do período
        $stmtBanners = $model->dashboardBuscarBanners();
        $banners = $stmtBanners->fetchAll();

        // Lotes Vencendo (30 dias)
        $stmtLotes = $model->dashboardBuscarLotes();
        $lotes_vencendo = $stmtLotes->fetchAll();
        $total_lotes_vencendo = count($lotes_vencendo);

        // Vendas Recentes
        $stmtVendas = $model->dashboardBuscarVendas();
        $vendas_recentes = $stmtVendas->fetchAll();

        $page_title = "Dashboard";
        $this->render('dashboard/index', get_defined_vars());
    }
}
