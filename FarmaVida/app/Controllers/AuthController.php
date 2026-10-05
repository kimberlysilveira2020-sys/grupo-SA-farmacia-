<?php
class AuthController extends Controller
{
    public function index(): void
    {
        if (isset($_SESSION['usuario_id'])) { header("Location: dashboard.php"); exit; }

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login_usuario = trim($_POST['usuario'] ?? '');
            $senha = trim($_POST['senha'] ?? '');

            if (!empty($login_usuario) && !empty($senha)) {
                // Busca o usuário pelo Model antes de validar sua senha.
                $model = new UsuarioModel();
                $stmt = $model->loginBuscarUsuarios();
                $stmt->execute([$login_usuario]);
                $usuario = $stmt->fetch();

                if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nome'] = $usuario['nome'];
                    $_SESSION['usuario_cargo'] = $usuario['cargo'];
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $erro = 'Usuário ou senha inválidos.';
                }
            }
        }
        $page_title = "Login";
        $hide_navbar = true;
        $this->render('auth/login', get_defined_vars());
    }
}
