<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/DashboardModel.php';

class DashboardController extends BaseController {

    public function index(): void {
        $this->requireAuth();
        $model              = new DashboardModel();
        $banners            = $model->getBanners();
        $lotes_vencendo     = $model->getLotesVencendo();
        $total_lotes_vencendo = count($lotes_vencendo);
        $vendas_recentes    = $model->getVendasRecentes();
        $page_title         = "Dashboard";
        include __DIR__ . '/../Views/layouts/header.php';
        include __DIR__ . '/../Views/dashboard/index.php';
        include __DIR__ . '/../Views/layouts/footer.php';
    }
}
