<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../Models/DashboardModel.php';

class DashboardController extends Controller {

    public function index(): void {
        $this->requireAuth();
        $model          = new DashboardModel();
        $banners        = $model->banners();
        $lotes_vencendo = $model->lotesVencendo();
        $vendas_recentes = $model->vendasRecentes();
        $total_lotes_vencendo = count($lotes_vencendo);

        $page_title = 'Dashboard';
        $this->view('layouts/header', compact('page_title'));
        $this->view('dashboard/index', compact('banners', 'lotes_vencendo', 'vendas_recentes', 'total_lotes_vencendo'));
        $this->view('layouts/footer');
    }

    public function bannerCriar(): void {
        $this->requireGerente();
        header('Content-Type: application/json');
        $titulo = trim($_POST['titulo'] ?? '');
        if (!$titulo) { echo json_encode(['success' => false, 'message' => 'Título obrigatório.']); exit; }
        $model  = new DashboardModel();
        $imagem = $model->salvarImagemBanner();
        $model->criarBanner($titulo, trim($_POST['descricao'] ?? ''), $imagem, $_POST['cor_fundo'] ?? '#1976D2', $_POST['data_inicio'] ?: null, $_POST['data_fim'] ?: null);
        echo json_encode(['success' => true]);
        exit;
    }

    public function bannerDeletar(): void {
        $this->requireGerente();
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = intval($data['id'] ?? 0);
        (new DashboardModel())->deletarBanner($id);
        echo json_encode(['success' => true]);
        exit;
    }
}
