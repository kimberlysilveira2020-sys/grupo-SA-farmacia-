<?php

require_once 'config.php';

// Se já estiver logado, manda pro dashboard
if (isset($_SESSION['usuario_id'])) { header("Location: dashboard.php"); exit; }

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $login = trim($_POST['login'] ?? ''); 
    $senha = trim($_POST['senha'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');

    $cargos_permitidos = ['Atendente', 'Farmaceutico', 'Gerente'];

    if (!empty($nome) && !empty($login) && !empty($senha) && !empty($cargo)) {
        if (!in_array($cargo, $cargos_permitidos)) {
            $erro = 'Cargo inválido selecionado.';
        } else {
            try {
                // 1. Conecta ao banco de dados diretamente
                $pdo = Config::getDbConnection();
                
                // 2. Verifica se o login já existe para evitar duplicação
                $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE login = ?");
                $stmtCheck->execute([$login]);
                
                if ($stmtCheck->fetch()) {
                    $erro = 'Este usuário/login já está em uso. Escolha outro.';
                } else {
                    // 3. Criptografa a senha e SALVA no banco via injeção direta PDO
                    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, login, senha_hash, cargo) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$nome, $login, $senha_hash, $cargo]);
                    
                    $sucesso = 'Cadastro realizado com sucesso! Você já pode fazer login.';
                }
            } catch (PDOException $e) {
                $erro = 'Erro ao salvar no banco de dados: ' . $e->getMessage();
            }
        }
    } else {
        $erro = 'Por favor, preencha todos os campos.';
    }
}

$page_title = "Cadastrar Usuário";
$hide_navbar = true; 
include 'header.php'; 
?>

<div class="login-container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card login-card fade-in shadow" style="width: 100%; max-width: 450px;">
        <div class="card-body p-4">
            
            <div class="text-center mb-4">
                <i class="bi bi-person-badge-fill text-success" style="font-size: 3.5rem;"></i>
                <h3 class="mt-2 fw-bold">Novo Usuário</h3>
                <p class="text-muted">Crie sua conta de acesso</p>
            </div>

            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($sucesso)): ?>
                <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>

            <form method="POST" action="cadastrar.php" autocomplete="off">
                <div class="mb-3">
                    <label for="nome" class="form-label"><i class="bi bi-person"></i> Nome Completo</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>

                <div class="mb-3">
                    <label for="login" class="form-label"><i class="bi bi-person-fill"></i> Nome de Usuário (Login)</label>
                    <input type="text" class="form-control" id="login" name="login" required>
                </div>

                <div class="mb-3">
                    <label for="cargo" class="form-label"><i class="bi bi-briefcase-fill"></i> Cargo</label>
                    <select class="form-select" id="cargo" name="cargo" required>
                        <option value="" disabled selected>Selecione uma função...</option>
                        <option value="Atendente">Atendente</option>
                        <option value="Farmaceutico">Farmacêutico</option>
                        <option value="Gerente">Gerente</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="senha" class="form-label"><i class="bi bi-lock-fill"></i> Senha</label>
                    <input type="password" class="form-control" id="senha" name="senha" required>
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="mostrar-senha-cadastro">
                    <label class="form-check-label" for="mostrar-senha-cadastro">
                        <i class="bi bi-eye-slash" id="icon-senha-cadastro"></i> Mostrar senha
                    </label>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg"></i> Concluir Cadastro
                    </button>
                    <a href="login.php" class="btn btn-link text-decoration-none text-muted mt-2 text-center">
                        Já tem uma conta? Voltar ao Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    body { background-color: #f8f9fa; }
</style>

<script>
    document.getElementById('mostrar-senha-cadastro').addEventListener('change', function () {
        const senhaInput = document.getElementById('senha');
        const icon = document.getElementById('icon-senha-cadastro');
        if (this.checked) {
            senhaInput.type = 'text'; icon.className = 'bi bi-eye';
        } else {
            senhaInput.type = 'password'; icon.className = 'bi bi-eye-slash';
        }
    });
</script>

<?php include 'footer.php'; ?>