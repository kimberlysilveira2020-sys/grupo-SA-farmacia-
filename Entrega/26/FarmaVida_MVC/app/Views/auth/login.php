<div class="login-container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card login-card fade-in" style="width:100%;max-width:450px;">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="bi bi-heart-pulse-fill text-primary-custom" style="font-size:4rem;"></i>
                <h2 class="mt-3 fw-bold">Farmácia Vida Saudável</h2>
                <p class="text-muted">Sistema de Gestão</p>
            </div>
            <?php if (!empty($erro)): ?>
            <div class="alert alert-danger text-center">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($erro) ?>
            </div>
            <?php endif; ?>
            <form method="POST" action="" autocomplete="off">
                <div class="mb-3">
                    <label for="usuario" class="form-label"><i class="bi bi-person-fill"></i> Usuário</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="senha" class="form-label"><i class="bi bi-lock-fill"></i> Senha</label>
                    <input type="password" class="form-control" id="senha" name="senha" required autocomplete="new-password">
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
                    <a href="<?= BASE_URL ?>auth/cadastro" class="btn btn-outline-secondary">
                        <i class="bi bi-person-plus-fill"></i> Criar nova conta
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.getElementById('mostrar-senha-login').addEventListener('change', function() {
    const s = document.getElementById('senha');
    const i = document.getElementById('icon-senha-login');
    s.type = this.checked ? 'text' : 'password';
    i.className = this.checked ? 'bi bi-eye' : 'bi bi-eye-slash';
});
</script>
