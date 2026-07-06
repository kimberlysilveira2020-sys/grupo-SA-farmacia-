<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../Models/DashboardModel.php';
require_once __DIR__ . '/../Models/VendaModel.php';

class RelatoriosController extends Controller {

    public function index(): void {
        $this->requireAuth();
        $dash           = new DashboardModel();
        $venda          = new VendaModel();
        $lotes_vencendo = $dash->lotesVencendo();
        $vendas         = $venda->listarRecentes(50);
        $total_vendas_count = count($vendas);
        $valor_total_vendas = array_sum(array_column($vendas, 'total'));

        $page_title = 'Relatórios';
        $this->view('layouts/header', compact('page_title'));
        $this->view('relatorios/index', compact('lotes_vencendo', 'vendas', 'total_vendas_count', 'valor_total_vendas'));
        $this->view('layouts/footer');
    }
}
