<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/RelatorioModel.php';

class RelatorioController extends BaseController {

    public function index(): void {
        $this->requireAuth();
        $model               = new RelatorioModel();
        $lotes_vencendo      = $model->getLotesVencendo();
        $vendas              = $model->getUltimasVendas(50);
        $total_vendas_count  = count($vendas);
        $valor_total_vendas  = array_sum(array_column($vendas, 'total'));
        $page_title          = "Relatórios";
        include __DIR__ . '/../Views/layouts/header.php';
        include __DIR__ . '/../Views/relatorios/index.php';
        include __DIR__ . '/../Views/layouts/footer.php';
    }
}
