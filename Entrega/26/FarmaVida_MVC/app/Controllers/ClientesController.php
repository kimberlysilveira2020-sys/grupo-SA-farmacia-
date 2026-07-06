<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../Models/ClienteModel.php';

class ClientesController extends Controller {

    private function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function index(): void {
        $this->requireAuth();
        $model    = new ClienteModel();
        $busca    = trim($_GET['busca'] ?? '');
        $clientes = $model->listarTodos($busca);
        $contagem = $model->contar();
        $page_title = 'Clientes';
        $this->view('layouts/header', compact('page_title'));
        $this->view('clientes/index', compact('clientes', 'busca', 'contagem'));
        $this->view('layouts/footer');
    }

    public function criar(): void {
        $this->requireAuth();
        $nome     = trim($_POST['nome'] ?? '');
        $cpf      = preg_replace('/\D/', '', $_POST['cpf'] ?? '') ?: null;
        $telefone = trim($_POST['telefone'] ?? '') ?: null;
        if (!$nome) $this->json(['success' => false, 'message' => 'Nome obrigatório.']);
        try {
            (new ClienteModel())->criar($nome, $cpf, $telefone);
            $this->json(['success' => true]);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'CPF já cadastrado.']);
        }
    }

    public function editar(): void {
        $this->requireAuth();
        $id       = intval($_POST['id'] ?? 0);
        $nome     = trim($_POST['nome'] ?? '');
        $cpf      = preg_replace('/\D/', '', $_POST['cpf'] ?? '') ?: null;
        $telefone = trim($_POST['telefone'] ?? '') ?: null;
        if (!$id || !$nome) $this->json(['success' => false, 'message' => 'Dados inválidos.']);
        try {
            (new ClienteModel())->editar($id, $nome, $cpf, $telefone);
            $this->json(['success' => true]);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'CPF já cadastrado para outro cliente.']);
        }
    }

    public function deletar(): void {
        $this->requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = intval($data['id'] ?? 0);
        (new ClienteModel())->deletar($id);
        $this->json(['success' => true]);
    }
}
