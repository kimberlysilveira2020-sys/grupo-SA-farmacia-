<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/UsuarioModel.php';

class AuthController extends BaseController {

    public function login(): void {
        if (isset($_SESSION['usuario_id'])) {
            $this->redirect('dashboard.php');
        }
        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $loginUsuario = trim($_POST['usuario'] ?? '');
            $senha        = trim($_POST['senha'] ?? '');
            if (!empty($loginUsuario) && !empty($senha)) {
                $model   = new UsuarioModel();
                $usuario = $model->buscarPorLogin($loginUsuario);
                if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
                    $_SESSION['usuario_id']    = $usuario['id'];
                    $_SESSION['usuario_nome']  = $usuario['nome'];
                    $_SESSION['usuario_cargo'] = $usuario['cargo'];
                    $this->redirect('dashboard.php');
                } else {
                    $erro = 'Usuário ou senha inválidos.';
                }
            }
        }
        $page_title  = "Login";
        $hide_navbar = true;
        include __DIR__ . '/../Views/layouts/header.php';
        include __DIR__ . '/../Views/auth/login.php';
        include __DIR__ . '/../Views/layouts/footer.php';
    }

    public function cadastrar(): void {
        if (isset($_SESSION['usuario_id'])) {
            $this->redirect('dashboard.php');
        }
        $erro   = '';
        $sucesso = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome  = trim($_POST['nome'] ?? '');
            $login = trim($_POST['login'] ?? '');
            $senha = trim($_POST['senha'] ?? '');
            $cargo = trim($_POST['cargo'] ?? '');
            $cargosPermitidos = ['Atendente', 'Farmaceutico', 'Gerente'];
            if (!empty($nome) && !empty($login) && !empty($senha) && !empty($cargo)) {
                if (!in_array($cargo, $cargosPermitidos)) {
                    $erro = 'Cargo inválido selecionado.';
                } else {
                    try {
                        $model = new UsuarioModel();
                        $res   = $model->criar($nome, $login, $senha, $cargo);
                        if ($res['success']) {
                            $sucesso = 'Cadastro realizado com sucesso! Você já pode fazer login.';
                        } else {
                            $erro = $res['message'];
                        }
                    } catch (\PDOException $e) {
                        $erro = 'Erro ao salvar no banco de dados: ' . $e->getMessage();
                    }
                }
            } else {
                $erro = 'Por favor, preencha todos os campos.';
            }
        }
        $page_title  = "Cadastrar Usuário";
        $hide_navbar = true;
        include __DIR__ . '/../Views/layouts/header.php';
        include __DIR__ . '/../Views/auth/cadastrar.php';
        include __DIR__ . '/../Views/layouts/footer.php';
    }

    public function logout(): void {
        session_unset();
        session_destroy();
        $this->redirect('login.php');
    }
}
