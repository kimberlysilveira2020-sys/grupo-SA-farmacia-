<?php
require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$pdo = Config::getDbConnection();

// Consulta produtos ativos com somatório de estoque por lotes
$stmt = $pdo->query("
    SELECT p.id, p.nome, p.fabricante, p.categoria, p.foto,
           p.preco_venda, p.descricao,
           COALESCE(est.estoque_total, 0)                AS estoque_total,
           est.validade_mais_proxima                      AS validade_mais_proxima,
           DATEDIFF(est.validade_mais_proxima, CURDATE()) AS dias_para_vencer
    FROM produtos p
    LEFT JOIN (
        SELECT produto_id,
               SUM(qtd_atual)     AS estoque_total,
               MIN(data_validade) AS validade_mais_proxima
        FROM lotes
        WHERE qtd_atual > 0
        GROUP BY produto_id
    ) est ON est.produto_id = p.id
    WHERE p.ativo = 1
    ORDER BY p.nome ASC
");
$produtos = $stmt->fetchAll();

foreach ($produtos as &$produto) {
    $dias = $produto['dias_para_vencer'];
    if ($dias !== null && $dias <= 30) {
        $produto['tem_desconto'] = true;
        $produto['percentual_desconto'] = 20;
        $produto['preco_original'] = $produto['preco_venda'];
        $produto['preco_venda'] = round($produto['preco_venda'] * 0.80, 2);
    } else {
        $produto['tem_desconto'] = false;
    }
}

$page_title = "Gestão de Produtos"; 
include 'header.php'; 
?>

<div class="container-fluid fade-in">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-box-seam"></i> Gestão de Estoque e Produtos
        </h2>
        <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalNovoProduto">
            <i class="bi bi-plus-circle"></i> Novo Produto
        </button>
    </div>

    <?php if (!empty($produtos)): ?>
    <div class="accordion" id="accordionProdutos">
        <?php foreach ($produtos as $produto): ?>
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading-<?= $produto['id'] ?>">
                <button class="accordion-button collapsed" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapse-<?= $produto['id'] ?>"
                    aria-expanded="false"
                    aria-controls="collapse-<?= $produto['id'] ?>">
                    <span class="d-flex justify-content-between w-100 me-3 align-items-center">
                        <span class="d-flex align-items-center gap-2">
                            <?php if (!empty($produto['foto']) && file_exists('uploads/produtos/' . $produto['foto'])): ?>
                            <img src="uploads/produtos/<?= htmlspecialchars($produto['foto']) ?>" 
                                 alt="<?= htmlspecialchars($produto['nome']) ?>"
                                 style="width:38px; height:38px; object-fit:cover; border-radius:6px; border:1px solid #dee2e6; cursor:zoom-in;"
                                 onclick="abrirLightbox(this.src, '<?= htmlspecialchars($produto['nome'], ENT_QUOTES) ?>'); event.stopPropagation();">
                            <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center rounded" 
                                 style="width:38px;height:38px;background:#e9ecef;color:#6c757d;font-size:.9rem;">
                                <i class="bi bi-image"></i>
                            </div>
                            <?php endif; ?>
                            <span>
                                <strong><?= htmlspecialchars($produto['nome']) ?></strong>
                                <span class="badge bg-secondary ms-2"><?= htmlspecialchars($produto['fabricante']) ?></span>
                                <?php if ($produto['categoria'] === 'Controlado'): ?>
                                <span class="badge badge-controlado ms-1">Controlado</span>
                                <?php endif; ?>
                            </span>
                        </span>
                        <span class="d-flex gap-2 align-items-center">
                            <span class="badge bg-success">R$ <?= number_format($produto['preco_venda'], 2, ',', '.') ?></span>
                            <span class="badge bg-primary">Estoque: <?= $produto['estoque_total'] ?></span>
                        </span>
                    </span>
                </button>
            </h2>

            <div id="collapse-<?= $produto['id'] ?>" class="accordion-collapse collapse">
                <div class="accordion-body">

                    <div class="row mb-3">
                        <!-- Foto do produto -->
                        <div class="col-md-2 text-center">
                            <?php if (!empty($produto['foto']) && file_exists('uploads/produtos/' . $produto['foto'])): ?>
                            <img src="uploads/produtos/<?= htmlspecialchars($produto['foto']) ?>"
                                 alt="<?= htmlspecialchars($produto['nome']) ?>"
                                 class="img-thumbnail" 
                                 style="max-width:110px; max-height:110px; object-fit:cover; cursor:zoom-in;"
                                 onclick="abrirLightbox(this.src, '<?= htmlspecialchars($produto['nome'], ENT_QUOTES) ?>')">
                            <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center rounded border bg-light" 
                                 style="width:110px;height:110px;margin:0 auto;color:#adb5bd;">
                                <i class="bi bi-image" style="font-size:2.5rem;"></i>
                            </div>
                            <small class="text-muted d-block mt-1">Sem foto</small>
                            <?php endif; ?>
                        </div>
                        <!-- Detalhes -->
                        <div class="col-md-10">
                            <div class="row g-2 mb-3">
                                <div class="col-auto">
                                    <span class="text-muted small">Preço:</span>
                                    <strong class="text-success ms-1">R$ <?= number_format($produto['preco_venda'], 2, ',', '.') ?></strong>
                                    <?php if ($produto['tem_desconto']): ?>
                                    <span class="badge bg-danger ms-1">-<?= $produto['percentual_desconto'] ?>% proximo venc.</span>
                                    <small class="text-muted text-decoration-line-through ms-1">R$ <?= number_format($produto['preco_original'], 2, ',', '.') ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-auto">
                                    <span class="text-muted small">Estoque:</span>
                                    <strong class="ms-1"><?= $produto['estoque_total'] ?> un.</strong>
                                </div>
                                <?php if (!empty($produto['descricao'])): ?>
                                <div class="col-12">
                                    <small class="text-muted"><?= htmlspecialchars($produto['descricao']) ?></small>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-success btn-sm btn-adicionar-lote"
                                    data-produto-id="<?= $produto['id'] ?>" 
                                    data-produto-nome="<?= htmlspecialchars($produto['nome']) ?>">
                                    <i class="bi bi-plus-circle"></i> Adicionar Lote
                                </button>
                                <button type="button" class="btn btn-warning btn-sm btn-editar-produto"
                                    data-id="<?= $produto['id'] ?>" 
                                    data-nome="<?= htmlspecialchars($produto['nome']) ?>"
                                    data-fabricante="<?= htmlspecialchars($produto['fabricante']) ?>" 
                                    data-categoria="<?= htmlspecialchars($produto['categoria']) ?>"
                                    data-preco="<?= number_format($produto['preco_venda'], 2, '.', '') ?>"
                                    data-descricao="<?= htmlspecialchars($produto['descricao'] ?? '') ?>">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <button type="button" class="btn btn-danger btn-sm btn-deletar-produto"
                                    data-id="<?= $produto['id'] ?>">
                                    <i class="bi bi-trash"></i> Excluir
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tabela-lotes-<?= $produto['id'] ?>">
                            <thead class="table-primary">
                                <tr>
                                    <th>Nº Lote</th>
                                    <th>Data de Validade</th>
                                    <th>Dias Restantes</th>
                                    <th>Quantidade</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <span class="spinner-border spinner-border-sm"></span>
                                        Carregando lotes...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        Nenhum produto cadastrado. Clique em "Novo Produto" para começar.
    </div>
    <?php endif; ?>

</div>

<!-- ===== MODAL NOVO PRODUTO ===== -->
<div class="modal fade" id="modalNovoProduto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle"></i> Cadastrar Novo Produto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNovoProduto" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <!-- Coluna esquerda -->
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome Comercial *</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="fabricante" class="form-label">Fabricante *</label>
                                <input type="text" class="form-control" id="fabricante" name="fabricante" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="categoria" class="form-label">Categoria *</label>
                                    <select class="form-select" id="categoria" name="categoria" required>
                                        <option value="">Selecione...</option>
                                        <option value="Comum">Comum</option>
                                        <option value="Controlado">Controlado</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="preco_venda" class="form-label">Preço de Venda (R$) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="number" class="form-control" id="preco_venda" name="preco_venda" step="0.01" min="0.01" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="descricao" class="form-label">Descrição</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                            </div>
                        </div>
                        <!-- Coluna direita - Foto -->
                        <div class="col-md-4">
                            <label class="form-label">Foto do Produto</label>
                            <div id="foto-preview-box" class="d-flex align-items-center justify-content-center rounded border bg-light mb-2"
                                 style="height:160px; cursor:pointer; position:relative; overflow:hidden;"
                                 onclick="document.getElementById('foto').click()">
                                <div id="foto-placeholder" class="text-center text-muted">
                                    <i class="bi bi-camera-fill" style="font-size:2.5rem;"></i>
                                    <p class="small mt-1 mb-0">Clique para adicionar foto</p>
                                    <p class="small text-muted">JPG, PNG (máx. 2MB)</p>
                                </div>
                                <img id="foto-preview-img" src="" alt="Preview" 
                                     style="display:none; width:100%; height:100%; object-fit:cover; position:absolute; top:0; left:0;">
                            </div>
                            <input type="file" class="d-none" id="foto" name="foto" accept="image/jpeg,image/png,image/webp"
                                   onchange="previewFoto(this, 'foto-preview-img', 'foto-placeholder')">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="document.getElementById('foto').click()">
                                <i class="bi bi-upload"></i> Selecionar Imagem
                            </button>
                        </div>
                    </div>

                    <!-- Seção: Lote Inicial (opcional) -->
                    <hr>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="incluir_lote_inicial" onchange="toggleLoteInicial(this.checked)">
                            <label class="form-check-label fw-bold" for="incluir_lote_inicial">
                                <i class="bi bi-box-seam"></i> Já adicionar estoque inicial
                            </label>
                        </div>
                    </div>
                    <div id="secao-lote-inicial" class="d-none">
                        <div class="p-3 rounded" style="background:#f0f7ff; border:1px solid #b3d4f5;">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nº do Lote *</label>
                                    <input type="text" class="form-control" id="lote_numero" name="lote_numero">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Data de Validade *</label>
                                    <input type="date" class="form-control" id="lote_validade" name="lote_validade">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Quantidade em Estoque *</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="lote_quantidade" name="lote_quantidade" min="1">
                                        <span class="input-group-text">un.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Salvar Produto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL ADICIONAR LOTE ===== -->
<div class="modal fade" id="modalNovoLote" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-box"></i> Adicionar Lote ao Estoque
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNovoLote">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="produto_nome_display">Produto</label>
                        <input type="text" class="form-control" id="produto_nome_display" readonly>
                        <input type="hidden" id="produto_id_lote" name="produto_id">
                    </div>
                    <div class="mb-3">
                        <label for="numero_lote" class="form-label">Número do Lote *</label>
                        <input type="text" class="form-control" id="numero_lote" name="numero_lote" required>
                    </div>
                    <div class="mb-3">
                        <label for="data_validade" class="form-label">Data de Validade *</label>
                        <input type="date" class="form-control" id="data_validade" name="data_validade" required>
                    </div>
                    <div class="mb-3">
                        <label for="qtd_atual" class="form-label">Quantidade *</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="qtd_atual" name="qtd_atual" min="1" required>
                            <span class="input-group-text">un.</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Adicionar Lote
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL EDITAR PRODUTO ===== -->
<div class="modal fade" id="modalEditarProduto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i> Editar Produto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarProduto" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="edit_produto_id" name="id">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="edit_nome" class="form-label">Nome Comercial *</label>
                                <input type="text" class="form-control" id="edit_nome" name="nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_fabricante" class="form-label">Fabricante *</label>
                                <input type="text" class="form-control" id="edit_fabricante" name="fabricante" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_categoria" class="form-label">Categoria *</label>
                                    <select class="form-select" id="edit_categoria" name="categoria" required>
                                        <option value="Comum">Comum</option>
                                        <option value="Controlado">Controlado</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_preco_venda" class="form-label">Preço de Venda (R$) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="number" class="form-control" id="edit_preco_venda" name="preco_venda" step="0.01" min="0.01" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_descricao" class="form-label">Descrição</label>
                                <textarea class="form-control" id="edit_descricao" name="descricao" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Foto do Produto</label>
                            <div id="edit-foto-preview-box" class="d-flex align-items-center justify-content-center rounded border bg-light mb-2"
                                 style="height:160px; cursor:pointer; position:relative; overflow:hidden;"
                                 onclick="document.getElementById('edit_foto').click()">
                                <div id="edit-foto-placeholder" class="text-center text-muted">
                                    <i class="bi bi-camera-fill" style="font-size:2.5rem;"></i>
                                    <p class="small mt-1 mb-0">Clique para trocar a foto</p>
                                </div>
                                <img id="edit-foto-preview-img" src="" alt="Preview"
                                     style="display:none; width:100%; height:100%; object-fit:cover; position:absolute; top:0; left:0;">
                            </div>
                            <input type="file" class="d-none" id="edit_foto" name="foto" accept="image/jpeg,image/png,image/webp"
                                   onchange="previewFoto(this, 'edit-foto-preview-img', 'edit-foto-placeholder')">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="document.getElementById('edit_foto').click()">
                                <i class="bi bi-upload"></i> Trocar Imagem
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Preview de foto antes do upload
    function previewFoto(input, imgId, placeholderId) {
        const file = input.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            alert('Imagem muito grande. O tamanho máximo é 2MB.');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById(imgId);
            const ph = document.getElementById(placeholderId);
            img.src = e.target.result;
            img.style.display = 'block';
            ph.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }

    function toggleLoteInicial(checked) {
        const sec = document.getElementById('secao-lote-inicial');
        sec.classList.toggle('d-none', !checked);
        ['lote_numero','lote_validade','lote_quantidade'].forEach(id => {
            document.getElementById(id).required = checked;
        });
    }

    // ── Salvar novo produto ──────────────────────────────────────────────
    let _salvandoProduto = false;
    document.getElementById('formNovoProduto').addEventListener('submit', async function(event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        if (_salvandoProduto) return;
        _salvandoProduto = true;

        const form = this;
        const btn  = form.querySelector('[type=submit]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Salvando...';

        const formData = new FormData(form);

        try {
            const res    = await fetch('api.php?endpoint=produtos_criar', { method: 'POST', body: formData });
            const result = await res.json();

            if (result.success) {
                // Fecha modal primeiro, depois recarrega para evitar flash visual
                const modalEl = document.getElementById('modalNovoProduto');
                const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modalEl.addEventListener('hidden.bs.modal', function onHidden() {
                    modalEl.removeEventListener('hidden.bs.modal', onHidden);
                    window.location.replace(window.location.pathname);
                }, { once: true });
                bsModal.hide();
            } else {
                alert('Erro: ' + result.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-save"></i> Salvar Produto';
                _salvandoProduto = false;
            }
        } catch (e) {
            alert('Erro ao salvar produto');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save"></i> Salvar Produto';
            _salvandoProduto = false;
        }
    });

    // ── Salvar lote ─────────────────────────────────────────────────────
    document.getElementById('formNovoLote').addEventListener('submit', async function(event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        const btn = this.querySelector('[type=submit]');
        if (btn.disabled) return;
        btn.disabled = true;
        try {
            const res    = await fetch('api.php?endpoint=lotes_criar', { method: 'POST', body: new FormData(this) });
            const result = await res.json();
            if (result.success) location.reload();
            else { alert('Erro: ' + result.message); btn.disabled = false; }
        } catch (e) { alert('Erro ao salvar lote'); btn.disabled = false; }
    });

    // ── Salvar edição ────────────────────────────────────────────────────
    document.getElementById('formEditarProduto').addEventListener('submit', async function(event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        const btn = this.querySelector('[type=submit]');
        if (btn.disabled) return;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Salvando...';
        try {
            const res    = await fetch('api.php?endpoint=produtos_editar_form', { method: 'POST', body: new FormData(this) });
            const result = await res.json();
            if (result.success) location.reload();
            else {
                alert('Erro: ' + result.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-save"></i> Salvar Alterações';
            }
        } catch (e) {
            alert('Erro ao atualizar produto');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save"></i> Salvar Alterações';
        }
    });

    // ── Deletar produto ──────────────────────────────────────────────────
    async function deletarProduto(id) {
        if (!confirm('Excluir este produto e todos os seus lotes?')) return;
        try {
            const res    = await fetch('api.php?endpoint=produtos_deletar', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id })
            });
            const result = await res.json();
            if (result.success) {
                if (result.aviso) alert('Aviso: ' + result.aviso);
                location.reload();
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (e) { alert('Erro ao excluir produto'); }
    }

    // ── Delegação de eventos (um único listener no container) ────────────
    document.getElementById('accordionProdutos').addEventListener('click', function(e) {
        // Botão Editar
        const btnEditar = e.target.closest('.btn-editar-produto');
        if (btnEditar) {
            e.stopPropagation();
            document.getElementById('edit_produto_id').value  = btnEditar.dataset.id;
            document.getElementById('edit_nome').value        = btnEditar.dataset.nome;
            document.getElementById('edit_fabricante').value  = btnEditar.dataset.fabricante;
            document.getElementById('edit_categoria').value   = btnEditar.dataset.categoria;
            document.getElementById('edit_preco_venda').value = btnEditar.dataset.preco;
            document.getElementById('edit_descricao').value   = btnEditar.dataset.descricao;
            document.getElementById('edit-foto-preview-img').style.display = 'none';
            document.getElementById('edit-foto-placeholder').style.display = '';
            new bootstrap.Modal(document.getElementById('modalEditarProduto')).show();
            return;
        }

        // Botão Adicionar Lote
        const btnLote = e.target.closest('.btn-adicionar-lote');
        if (btnLote) {
            e.stopPropagation();
            document.getElementById('produto_id_lote').value      = btnLote.dataset.produtoId;
            document.getElementById('produto_nome_display').value = btnLote.dataset.produtoNome;
            new bootstrap.Modal(document.getElementById('modalNovoLote')).show();
            return;
        }

        // Botão Deletar
        const btnDel = e.target.closest('.btn-deletar-produto');
        if (btnDel) {
            e.stopPropagation();
            deletarProduto(btnDel.dataset.id);
            return;
        }
    });

    // ── Accordion: carrega lotes lazy ao abrir painel ─────────────────
    document.querySelectorAll('.accordion-collapse').forEach(collapseEl => {
        collapseEl.addEventListener('show.bs.collapse', function() {
            const produtoId = this.id.replace('collapse-', '');
            carregarLotes(produtoId);
        });
    });

    async function carregarLotes(produtoId) {
        const tabela = document.querySelector(`#tabela-lotes-${produtoId} tbody`);
        if (!tabela || tabela.dataset.loaded) return;
        tabela.dataset.loaded = '1';
        try {
            const res  = await fetch(`api.php?endpoint=lotes_listar&produto_id=${produtoId}`);
            const data = await res.json();
            if (data.success && data.lotes.length > 0) {
                tabela.innerHTML = data.lotes.map(l => {
                    const badge = l.vencendo
                        ? `<span class="badge bg-danger">${l.dias_para_vencer} dia(s)</span>`
                        : `<span class="badge bg-success">OK</span>`;
                    return `<tr>
                        <td>${l.numero_lote}</td>
                        <td>${new Date(l.data_validade + 'T00:00:00').toLocaleDateString('pt-BR')}</td>
                        <td>${l.dias_para_vencer} dia(s)</td>
                        <td>${l.qtd_atual} un.</td>
                        <td>${badge}</td>
                    </tr>`;
                }).join('');
            } else {
                tabela.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Nenhum lote em estoque.</td></tr>';
            }
        } catch(err) {
            tabela.innerHTML = '<tr><td colspan="5" class="text-danger text-center">Erro ao carregar lotes.</td></tr>';
        }
    }
</script>

<!-- ===== MODAL LIGHTBOX ===== -->
<div class="modal fade" id="modalLightbox" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:min(90vw, 700px);">
        <div class="modal-content border-0" style="background:transparent;">
            <div class="modal-header border-0 pb-1" style="background:rgba(0,0,0,.55); border-radius:10px 10px 0 0;">
                <h6 class="modal-title text-white fw-bold" id="lightbox-titulo"></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center" style="background:#111; border-radius:0 0 10px 10px;">
                <img id="lightbox-img" src="" alt=""
                     style="max-width:100%; max-height:75vh; object-fit:contain; border-radius:0 0 10px 10px;">
            </div>
        </div>
    </div>
</div>

<script>
    function abrirLightbox(src, nome) {
        document.getElementById('lightbox-img').src   = src;
        document.getElementById('lightbox-titulo').textContent = nome;
        new bootstrap.Modal(document.getElementById('modalLightbox')).show();
    }
</script>

<?php include 'footer.php'; ?>