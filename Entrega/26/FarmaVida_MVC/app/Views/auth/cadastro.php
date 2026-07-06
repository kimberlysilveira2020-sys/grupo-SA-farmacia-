<div class="login-container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card login-card fade-in shadow" style="width:100%;max-width:450px;">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="bi bi-person-badge-fill text-success" style="font-size:3.5rem;"></i>
                <h3 class="mt-2 fw-bold">Novo Usuário</h3>
                <p class="text-muted">Crie sua conta de acesso</p>
            </div>
            <?php if (!empty($erro)): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>
            <?php if (!empty($sucesso)): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>
            <form method="POST" action="" autocomplete="off">
                <div class="mb-3">
                    <label for="nome" class="form-label"><i class="bi bi-person"></i> Nome Completo</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>
                <div class="mb-3">
                    <label for="login" class="form-label"><i class="bi bi-person-fill"></i> Usuário (Login)</label>
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
                    <a href="<?= BASE_URL ?>auth/login" class="btn btn-link text-decoration-none text-muted mt-2 text-center">
                        Já tem uma conta? Voltar ao Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.getElementById('mostrar-senha-cadastro').addEventListener('change', function() {
    const s = document.getElementById('senha');
    const i = document.getElementById('icon-senha-cadastro');
    s.type = this.checked ? 'text' : 'password';
    i.className = this.checked ? 'bi bi-eye' : 'bi bi-eye-slash';
});
</script>
