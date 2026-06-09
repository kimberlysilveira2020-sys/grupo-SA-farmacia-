<?php

require_once 'config.php'; // Apenas config agora

if (isset($_SESSION['usuario_id'])) { header("Location: dashboard.php"); exit; }

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_usuario = trim($_POST['usuario'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (!empty($login_usuario) && !empty($senha)) {
        // Conexão direta e Query
        $pdo = Config::getDbConnection();
        $stmt = $pdo->prepare("SELECT id, nome, login, senha_hash, cargo FROM usuarios WHERE login = ?");
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
include 'header.php'; 
?>

<div class="login-container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card login-card fade-in" style="width: 100%; max-width: 450px;">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="bi bi-heart-pulse-fill text-primary-custom" style="font-size: 4rem;"></i>
                <h2 class="mt-3 fw-bold">Farmácia Vida Saudável</h2>
                <p class="text-muted">Sistema de Gestão</p>
            </div>

            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger text-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" autocomplete="off">
                <div class="mb-3">
                    <label for="usuario" class="form-label">
                        <i class="bi bi-person-fill"></i> Usuário
                    </label>
                    <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Digite seu usuário" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="senha" class="form-label">
                        <i class="bi bi-lock-fill"></i> Senha
                    </label>
                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite sua senha" required autocomplete="new-password">
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="mostrar-senha-login">
                    <label class="form-check-label" for="mostrar-senha-login">
                        <i class="bi bi-eye-slash" id="icon-senha-login"></i> Mostrar senha
                    </label>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right"></i> Entrar no Sistema
                    </button>
                    <a href="cadastrar.php" class="btn btn-outline-secondary">
                        <i class="bi bi-person-plus-fill"></i> Criar nova conta
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    body {
        margin: 0;
        padding: 0;
        background-color: var(--cor-fundo); 
    }
</style>

<script>
    // Toggle mostrar/ocultar senha
    document.getElementById('mostrar-senha-login').addEventListener('change', function () {
        const senhaInput = document.getElementById('senha');
        const icon = document.getElementById('icon-senha-login');

        if (this.checked) {
            senhaInput.type = 'text';
            icon.className = 'bi bi-eye';
        } else {
            senhaInput.type = 'password';
            icon.className = 'bi bi-eye-slash';
        }
    });
</script>