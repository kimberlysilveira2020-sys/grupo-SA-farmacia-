<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/ClienteModel.php';

class ClienteController extends BaseController {

    public function index(): void {
        $this->requireAuth();
        $model          = new ClienteModel();
        $busca          = trim($_GET['busca'] ?? '');
        $clientes       = $model->listar($busca);
        $total_clientes = count($clientes);
        $total_loja     = count(array_filter($clientes, fn($c) => $c['origem'] === 'loja'));
        $total_interno  = $total_clientes - $total_loja;
        $page_title     = "Clientes";
        include __DIR__ . '/../Views/layouts/header.php';
        include __DIR__ . '/../Views/clientes/index.php';
        include __DIR__ . '/../Views/layouts/footer.php';
    }

    public function apiCriar(): void {
        $this->requireAuthJson();
        $nome     = trim($_POST['nome'] ?? '');
        $cpf      = preg_replace('/\D/', '', $_POST['cpf'] ?? '') ?: null;
        $telefone = trim($_POST['telefone'] ?? '') ?: null;
        if (!$nome) $this->jsonResponse(['success' => false, 'message' => 'Nome obrigatório.']);
        $model = new ClienteModel();
        $this->jsonResponse($model->criar($nome, $cpf, $telefone));
    }

    public function apiEditar(): void {
        $this->requireAuthJson();
        $id       = intval($_POST['id'] ?? 0);
        $nome     = trim($_POST['nome'] ?? '');
        $cpf      = preg_replace('/\D/', '', $_POST['cpf'] ?? '') ?: null;
        $telefone = trim($_POST['telefone'] ?? '') ?: null;
        if (!$id || !$nome) $this->jsonResponse(['success' => false, 'message' => 'Dados inválidos.']);
        $model = new ClienteModel();
        $this->jsonResponse($model->editar($id, $nome, $cpf, $telefone));
    }

    public function apiDeletar(): void {
        $this->requireAuthJson();
        $data = $this->getJsonInput();
        $id   = intval($data['id'] ?? 0);
        $model = new ClienteModel();
        $model->deletar($id);
        $this->jsonResponse(['success' => true]);
    }
}
