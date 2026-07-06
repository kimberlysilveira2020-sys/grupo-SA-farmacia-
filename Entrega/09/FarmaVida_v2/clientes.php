<?php
require_once 'config.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$pdo = Config::getDbConnection();

$busca = trim($_GET['busca'] ?? '');
if ($busca) {
    $stmt = $pdo->prepare("
        SELECT c.*, COUNT(v.id) AS total_compras, COALESCE(SUM(v.total), 0) AS valor_total
        FROM clientes c
        LEFT JOIN vendas v ON v.cliente_id = c.id
        WHERE c.nome LIKE ? OR c.cpf LIKE ? OR c.telefone LIKE ?
        GROUP BY c.id ORDER BY c.nome ASC
    ");
    $like = "%$busca%";
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(v.id) AS total_compras, COALESCE(SUM(v.total), 0) AS valor_total
        FROM clientes c
        LEFT JOIN vendas v ON v.cliente_id = c.id
        GROUP BY c.id ORDER BY c.nome ASC
    ");
}
$clientes = $stmt->fetchAll();
$total_clientes = count($clientes);

$page_title = "Clientes";
include 'header.php';
?>

<div class="container-fluid fade-in pb-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="mb-0"><i class="bi bi-people-fill"></i> Clientes</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoCliente">
            <i class="bi bi-person-plus-fill"></i> Novo Cliente
        </button>
    </div>

    <!-- Cards de resumo -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <i class="bi bi-people-fill text-primary" style="font-size:2rem;"></i>
                <h4 class="fw-bold mb-0 mt-1"><?= $total_clientes ?></h4>
                <small class="text-muted">Total de Clientes</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <i class="bi bi-receipt text-success" style="font-size:2rem;"></i>
                <h4 class="fw-bold mb-0 mt-1"><?= array_sum(array_column($clientes, 'total_compras')) ?></h4>
                <small class="text-muted">Total de Compras</small>
            </div>
        </div>
    </div>

    <!-- Busca -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="campo-busca" placeholder="Buscar por nome, CPF ou telefone..."
                       value="<?= htmlspecialchars($busca) ?>" onkeydown="if(event.key==='Enter') buscarClientes()">
                <button class="btn btn-primary" onclick="buscarClientes()">Buscar</button>
                <?php if ($busca): ?>
                <a href="clientes.php" class="btn btn-outline-secondary">Limpar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (!empty($clientes)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:#e8f5e9;">
                        <tr>
                            <th class="ps-3">Nome</th>
                            <th>CPF</th>
                            <th>Telefone</th>
                            <th class="text-center">Compras</th>
                            <th class="text-end">Total Gasto</th>
                            <th class="text-center pe-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $c): ?>
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                         style="width:36px;height:36px;background:#1976D2;font-size:.85rem;flex-shrink:0;">
                                        <?= mb_strtoupper(mb_substr($c['nome'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars($c['nome']) ?></strong>
                                        <br><small class="text-muted">Desde <?= date('d/m/Y', strtotime($c['criado_em'])) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= $c['cpf'] ? htmlspecialchars($c['cpf']) : '<span class="text-muted">—</span>' ?></td>
                            <td><?= $c['telefone'] ? htmlspecialchars($c['telefone']) : '<span class="text-muted">—</span>' ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= $c['total_compras'] ?></span>
                            </td>
                            <td class="text-end fw-bold text-success">
                                R$ <?= number_format($c['valor_total'], 2, ',', '.') ?>
                            </td>
                            <td class="text-center pe-3">
                                <button class="btn btn-sm btn-outline-warning btn-editar-cliente"
                                    data-id="<?= $c['id'] ?>"
                                    data-nome="<?= htmlspecialchars($c['nome'], ENT_QUOTES) ?>"
                                    data-cpf="<?= htmlspecialchars($c['cpf'] ?? '', ENT_QUOTES) ?>"
                                    data-telefone="<?= htmlspecialchars($c['telefone'] ?? '', ENT_QUOTES) ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger ms-1 btn-deletar-cliente"
                                    data-id="<?= $c['id'] ?>" data-nome="<?= htmlspecialchars($c['nome'], ENT_QUOTES) ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-person-x" style="font-size:3rem;"></i>
                <p class="mt-2">
                    <?= $busca ? 'Nenhum cliente encontrado para "' . htmlspecialchars($busca) . '".' : 'Nenhum cliente cadastrado ainda.' ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ══ MODAL NOVO CLIENTE ════════════════════════════════════════════ -->
<div class="modal fade" id="modalNovoCliente" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill"></i> Novo Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="feedback-novo-cliente"></div>
                <div class="mb-3">
                    <label class="form-label">Nome Completo *</label>
                    <input type="text" class="form-control" id="novo_nome" placeholder="Ex: João da Silva" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">CPF</label>
                    <input type="text" class="form-control" id="novo_cpf" placeholder="000.000.000-00" maxlength="14"
                           oninput="mascararCPF(this)">
                </div>
                <div class="mb-3">
                    <label class="form-label">Telefone</label>
                    <input type="text" class="form-control" id="novo_telefone" placeholder="(00) 00000-0000" maxlength="15"
                           oninput="mascararTelefone(this)">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" onclick="salvarCliente()">
                    <i class="bi bi-save"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL EDITAR CLIENTE ═════════════════════════════════════════ -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="feedback-editar-cliente"></div>
                <input type="hidden" id="edit_id">
                <div class="mb-3">
                    <label class="form-label">Nome Completo *</label>
                    <input type="text" class="form-control" id="edit_nome" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">CPF</label>
                    <input type="text" class="form-control" id="edit_cpf" maxlength="14" oninput="mascararCPF(this)">
                </div>
                <div class="mb-3">
                    <label class="form-label">Telefone</label>
                    <input type="text" class="form-control" id="edit_telefone" maxlength="15" oninput="mascararTelefone(this)">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-warning" onclick="atualizarCliente()">
                    <i class="bi bi-save"></i> Salvar Alterações
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function buscarClientes() {
        const v = document.getElementById('campo-busca').value.trim();
        window.location.href = 'clientes.php' + (v ? '?busca=' + encodeURIComponent(v) : '');
    }

    function mascararCPF(input) {
        let v = input.value.replace(/\D/g,'').substring(0,11);
        if (v.length > 9) v = v.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,2})/,'$1.$2.$3-$4');
        else if (v.length > 6) v = v.replace(/^(\d{3})(\d{3})(\d{0,3})/,'$1.$2.$3');
        else if (v.length > 3) v = v.replace(/^(\d{3})(\d{0,3})/,'$1.$2');
        input.value = v;
    }

    function mascararTelefone(input) {
        let v = input.value.replace(/\D/g,'').substring(0,11);
        if (v.length > 10) v = v.replace(/^(\d{2})(\d{5})(\d{4})/,'($1) $2-$3');
        else if (v.length > 6) v = v.replace(/^(\d{2})(\d{4})(\d{0,4})/,'($1) $2-$3');
        else if (v.length > 2) v = v.replace(/^(\d{2})(\d{0,5})/,'($1) $2');
        input.value = v;
    }

    async function salvarCliente() {
        const nome = document.getElementById('novo_nome').value.trim();
        const fb   = document.getElementById('feedback-novo-cliente');
        if (!nome) { fb.innerHTML = '<div class="alert alert-warning py-2">Informe o nome do cliente.</div>'; return; }
        fb.innerHTML = '<div class="alert alert-info py-2"><span class="spinner-border spinner-border-sm me-1"></span>Salvando...</div>';
        const fd = new FormData();
        fd.append('nome', nome);
        fd.append('cpf', document.getElementById('novo_cpf').value);
        fd.append('telefone', document.getElementById('novo_telefone').value);
        const res  = await fetch('api.php?endpoint=cliente_criar', { method:'POST', body:fd });
        const data = await res.json();
        if (data.success) location.reload();
        else fb.innerHTML = `<div class="alert alert-danger py-2">${data.message}</div>`;
    }

    async function atualizarCliente() {
        const id   = document.getElementById('edit_id').value;
        const nome = document.getElementById('edit_nome').value.trim();
        const fb   = document.getElementById('feedback-editar-cliente');
        if (!nome) { fb.innerHTML = '<div class="alert alert-warning py-2">Informe o nome.</div>'; return; }
        fb.innerHTML = '<div class="alert alert-info py-2"><span class="spinner-border spinner-border-sm me-1"></span>Salvando...</div>';
        const fd = new FormData();
        fd.append('id', id);
        fd.append('nome', nome);
        fd.append('cpf', document.getElementById('edit_cpf').value);
        fd.append('telefone', document.getElementById('edit_telefone').value);
        const res  = await fetch('api.php?endpoint=cliente_editar', { method:'POST', body:fd });
        const data = await res.json();
        if (data.success) location.reload();
        else fb.innerHTML = `<div class="alert alert-danger py-2">${data.message}</div>`;
    }

    async function deletarCliente(id, nome) {
        if (!confirm(`Excluir o cliente "${nome}"?`)) return;
        const res  = await fetch('api.php?endpoint=cliente_deletar', {
            method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id})
        });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Erro: ' + data.message);
    }

    document.querySelectorAll('.btn-editar-cliente').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value       = this.dataset.id;
            document.getElementById('edit_nome').value     = this.dataset.nome;
            document.getElementById('edit_cpf').value      = this.dataset.cpf;
            document.getElementById('edit_telefone').value = this.dataset.telefone;
            new bootstrap.Modal(document.getElementById('modalEditarCliente')).show();
        });
    });

    document.querySelectorAll('.btn-deletar-cliente').forEach(btn => {
        btn.addEventListener('click', function() {
            deletarCliente(this.dataset.id, this.dataset.nome);
        });
    });
</script>

<?php include 'footer.php'; ?>
