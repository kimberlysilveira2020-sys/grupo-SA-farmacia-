<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../Models/UsuarioModel.php';

class AuthController extends Controller {

    public function index(): void {
        $this->login();
    }

    public function login(): void {
        if (isset($_SESSION['usuario_id'])) {
            $this->redirect(BASE_URL . 'dashboard');
        }

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = trim($_POST['usuario'] ?? '');
            $senha = trim($_POST['senha'] ?? '');

            if ($login && $senha) {
                $model   = new UsuarioModel();
                $usuario = $model->buscarPorLogin($login);
                if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
                    $_SESSION['usuario_id']    = $usuario['id'];
                    $_SESSION['usuario_nome']  = $usuario['nome'];
                    $_SESSION['usuario_cargo'] = $usuario['cargo'];
                    $this->redirect(BASE_URL . 'dashboard');
                } else {
                    $erro = 'Usuário ou senha inválidos.';
                }
            }
        }

        $page_title  = 'Login';
        $hide_navbar = true;
        $this->view('layouts/header', compact('page_title', 'hide_navbar'));
        $this->view('auth/login', compact('erro'));
        $this->view('layouts/footer');
    }

    public function logout(): void {
        session_unset();
        session_destroy();
        $this->redirect(BASE_URL . 'auth/login');
    }

    public function cadastro(): void {
        if (isset($_SESSION['usuario_id'])) {
            $this->redirect(BASE_URL . 'dashboard');
        }

        $erro    = '';
        $sucesso = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome  = trim($_POST['nome']  ?? '');
            $login = trim($_POST['login'] ?? '');
            $senha = trim($_POST['senha'] ?? '');
            $cargo = trim($_POST['cargo'] ?? '');
            $cargosPermitidos = ['Atendente', 'Farmaceutico', 'Gerente'];

            if ($nome && $login && $senha && in_array($cargo, $cargosPermitidos)) {
                $model = new UsuarioModel();
                if ($model->loginExiste($login)) {
                    $erro = 'Este usuário/login já está em uso.';
                } else {
                    $model->criar($nome, $login, password_hash($senha, PASSWORD_DEFAULT), $cargo);
                    $sucesso = 'Cadastro realizado! Você já pode fazer login.';
                }
            } else {
                $erro = 'Preencha todos os campos corretamente.';
            }
        }

        $page_title  = 'Cadastrar Usuário';
        $hide_navbar = true;
        $this->view('layouts/header', compact('page_title', 'hide_navbar'));
        $this->view('auth/cadastro', compact('erro', 'sucesso'));
        $this->view('layouts/footer');
    }
}
