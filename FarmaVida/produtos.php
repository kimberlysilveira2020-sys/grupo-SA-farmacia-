<?php
require_once 'config.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$pdo = Config::getDbConnection();

// Garante tabela de categorias com dados padrão
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `categorias` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `nome` varchar(100) NOT NULL,
      `icone` varchar(50) DEFAULT 'bi-tag',
      `ativo` tinyint(1) DEFAULT 1,
      `ordem` int(11) DEFAULT 0,
      `criado_em` datetime DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `nome` (`nome`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
");

// Insere categorias padrão se a tabela estiver vazia
$qtdCat = (int)$pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
if ($qtdCat === 0) {
    $pdo->exec("
        INSERT IGNORE INTO `categorias` (`nome`, `icone`, `ordem`) VALUES
        ('Comum', 'bi-capsule', 1),
        ('Genérico', 'bi-capsule-pill', 2),
        ('Controlado', 'bi-shield-lock', 3),
        ('Antibiótico', 'bi-bacteria', 4),
        ('Vitaminas', 'bi-heart', 5),
        ('Suplementos', 'bi-activity', 6),
        ('Dermocosméticos', 'bi-stars', 7),
        ('Higiene', 'bi-droplet', 8),
        ('Beleza', 'bi-flower1', 9),
        ('Infantil', 'bi-emoji-smile', 10),
        ('Ortopédico', 'bi-bandaid', 11),
        ('Hospitalar', 'bi-hospital', 12)
    ");
}

// Busca categorias
$categorias = $pdo->query("SELECT * FROM categorias WHERE ativo=1 ORDER BY ordem ASC, nome ASC")->fetchAll();

// Busca produtos com estoque
$busca    = trim($_GET['q'] ?? '');
$catFiltro= trim($_GET['cat'] ?? '');
$pagina   = max(1, (int)($_GET['p'] ?? 1));
$por_pag  = Config::PRODUTOS_POR_PAGINA;
$offset   = ($pagina - 1) * $por_pag;

$where  = ["p.ativo = 1"];
$params = [];
if ($busca)    { $where[] = "(p.nome LIKE ? OR p.fabricante LIKE ?)"; $params[] = "%$busca%"; $params[] = "%$busca%"; }
if ($catFiltro){ $where[] = "p.categoria = ?"; $params[] = $catFiltro; }
$whereSQL = 'WHERE ' . implode(' AND ', $where);

$total = (int)$pdo->prepare("SELECT COUNT(*) FROM produtos p $whereSQL")->execute($params) ? 0 : 0;
$stC = $pdo->prepare("SELECT COUNT(*) FROM produtos p $whereSQL");
$stC->execute($params);
$total = (int)$stC->fetchColumn();
$totalPags = max(1, ceil($total / $por_pag));

$stP = $pdo->prepare("
    SELECT p.id, p.nome, p.fabricante, p.categoria, p.preco_venda, p.receita_obrigatoria, p.foto,
           COALESCE(SUM(l.qtd_atual),0) AS estoque_total,
           COUNT(l.id) AS num_lotes
    FROM produtos p
    LEFT JOIN lotes l ON l.produto_id = p.id AND l.qtd_atual > 0
    $whereSQL
    GROUP BY p.id
    ORDER BY p.nome ASC
    LIMIT $por_pag OFFSET $offset
");
$stP->execute($params);
$produtos = $stP->fetchAll();

$page_title = "Estoque / Produtos";
include 'header.php';
?>

<style>
.prod-table th { vertical-align: middle; white-space: nowrap; }
.prod-table td { vertical-align: middle; }
.badge-ctrl  { background:#ff9800; color:#fff; }
.badge-sem   { background:#e53935; color:#fff; }
.badge-ok    { background:#2e7d32; color:#fff; }
.foto-thumb  { width:48px;height:48px;object-fit:cover;border-radius:8px;background:#f0f0f0; }
.foto-ph     { width:48px;height:48px;border-radius:8px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#bbb;font-size:1.3rem; }

/* ── Modal categorias ── */
#lista-cats .cat-item {
    display:flex;align-items:center;gap:8px;padding:7px 10px;
    border:1px solid #dee2e6;border-radius:8px;margin-bottom:6px;background:#f8f9fa;
}
#lista-cats .cat-item span { flex:1;font-weight:600; }
#lista-cats .cat-item .btn-rm-cat {
    background:none;border:none;color:#c62828;font-size:1.1rem;padding:0 4px;cursor:pointer;
}
#lista-cats .cat-item .btn-rm-cat:hover { color:#e53935; }

.btn-add-cat-inline {
    border:none;background:var(--bs-success);color:#fff;border-radius:6px;
    width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;
    font-size:1rem;font-weight:700;cursor:pointer;margin-left:4px;vertical-align:middle;
    transition:background .15s;
}
.btn-add-cat-inline:hover { background:#155724; }
.btn-rm-cat-inline {
    border:none;background:#e53935;color:#fff;border-radius:6px;
    width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;
    font-size:1rem;font-weight:700;cursor:pointer;margin-left:2px;vertical-align:middle;
    transition:background .15s;
}
.btn-rm-cat-inline:hover { background:#b71c1c; }

.select-cat-wrap { display:inline-flex;align-items:center;gap:0; }
.select-cat-wrap select { border-radius:6px 0 0 6px !important; }
</style>

<div class="container-fluid fade-in">

    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <h2 class="mb-0"><i class="bi bi-box-seam"></i> Estoque de Produtos</h2>
        <button class="btn btn-success" onclick="abrirModalProduto(null)">
            <i class="bi bi-plus-circle-fill"></i> Novo Produto
        </button>
    </div>

    <!-- Filtros -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Buscar por nome ou fabricante..." value="<?= htmlspecialchars($busca) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="cat" class="form-select form-select-sm">
                        <option value="">Todas as categorias</option>
                        <?php foreach($categorias as $c): ?>
                        <option value="<?= htmlspecialchars($c['nome']) ?>" <?= $catFiltro===$c['nome']?'selected':'' ?>>
                            <?= htmlspecialchars($c['nome']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Filtrar</button>
                    <a href="produtos.php" class="btn btn-outline-secondary btn-sm ms-1">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-table"></i> <?= $total ?> produto(s) encontrado(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover prod-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Foto</th>
                        <th>Produto</th>
                        <th>Fabricante</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th>Estoque</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($produtos)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-inbox"></i> Nenhum produto encontrado.</td></tr>
                <?php else: foreach($produtos as $p): ?>
                <tr>
                    <td><small class="text-muted">#<?= $p['id'] ?></small></td>
                    <td>
                        <?php if(!empty($p['foto'])): ?>
                        <img class="foto-thumb" src="uploads/produtos/<?= htmlspecialchars($p['foto']) ?>" alt="">
                        <?php else: ?>
                        <div class="foto-ph"><i class="bi bi-capsule"></i></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($p['nome']) ?></strong>
                        <?php if($p['receita_obrigatoria']): ?>
                        <span class="badge badge-ctrl ms-1" style="font-size:.65rem;">Controlado</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($p['fabricante']) ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($p['categoria']) ?></span></td>
                    <td><strong class="text-success">R$ <?= number_format($p['preco_venda'],2,',','.') ?></strong></td>
                    <td>
                        <?php $est = (int)$p['estoque_total']; ?>
                        <span class="badge <?= $est===0?'badge-sem':($est<5?'badge-ctrl':'badge-ok') ?>">
                            <?= $est ?> un.
                        </span>
                        <small class="text-muted ms-1">(<?= $p['num_lotes'] ?> lote(s))</small>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick='editarProduto(<?= json_encode($p) ?>)' title="Editar">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success ms-1" onclick="abrirModalLote(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nome'])) ?>')" title="Adicionar Lote">
                            <i class="bi bi-plus-circle"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger ms-1" onclick="deletarProduto(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nome'])) ?>')" title="Remover">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($totalPags > 1): ?>
        <div class="card-footer d-flex justify-content-center gap-1 flex-wrap">
            <?php if($pagina>1): ?>
            <a href="?q=<?=urlencode($busca)?>&cat=<?=urlencode($catFiltro)?>&p=<?=$pagina-1?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
            <?php endif; ?>
            <?php for($i=1;$i<=$totalPags;$i++): if($i===1||$i===$totalPags||abs($i-$pagina)<=1): ?>
            <a href="?q=<?=urlencode($busca)?>&cat=<?=urlencode($catFiltro)?>&p=<?=$i?>" class="btn btn-sm <?=$i===$pagina?'btn-primary':'btn-outline-secondary'?>"><?=$i?></a>
            <?php elseif(abs($i-$pagina)===2): ?><span class="btn btn-sm disabled">…</span><?php endif; endfor; ?>
            <?php if($pagina<$totalPags): ?>
            <a href="?q=<?=urlencode($busca)?>&cat=<?=urlencode($catFiltro)?>&p=<?=$pagina+1?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ══ MODAL PRODUTO (criar/editar) ══ -->
<div class="modal fade" id="modalProduto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalProdutoTitulo"><i class="bi bi-box-seam"></i> Produto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="fb-produto"></div>
                <input type="hidden" id="prod-id">

                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label fw-bold">Nome do Produto *</label>
                        <input type="text" class="form-control" id="prod-nome" placeholder="Ex: Dipirona 500mg">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Fabricante *</label>
                        <input type="text" class="form-control" id="prod-fabricante" placeholder="Ex: EMS, Medley...">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            Categoria *
                            <button type="button" class="btn-add-cat-inline" onclick="abrirModalCategorias()" title="Gerenciar categorias">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                            <button type="button" class="btn-rm-cat-inline" onclick="abrirModalCatRemover()" title="Remover categoria">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                        </label>
                        <select class="form-select" id="prod-categoria">
                            <option value="">Selecione...</option>
                            <?php foreach($categorias as $c): ?>
                            <option value="<?= htmlspecialchars($c['nome']) ?>"><?= htmlspecialchars($c['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Preço de Venda (R$) *</label>
                        <input type="number" class="form-control" id="prod-preco" step="0.01" min="0.01" placeholder="0,00">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Controlado?</label>
                        <select class="form-select" id="prod-ctrl">
                            <option value="0">Não</option>
                            <option value="1">Sim (Receita)</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Descrição</label>
                        <textarea class="form-control" id="prod-desc" rows="2" placeholder="Descrição opcional do produto..."></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Foto do Produto</label>
                        <input type="file" class="form-control" id="prod-foto" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">JPG, PNG ou WEBP – máx. 2MB</small>
                        <div id="foto-preview" class="mt-2"></div>
                    </div>
                </div>

                <!-- Lote inicial (só na criação) -->
                <div id="bloco-lote-inicial" class="mt-3 p-3 rounded" style="background:#f0faf4;border:1px dashed #2e7d32;">
                    <h6 class="fw-bold text-success mb-2"><i class="bi bi-layers"></i> Lote Inicial (opcional)</h6>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Número do Lote</label>
                            <input type="text" class="form-control form-control-sm" id="lote-num" placeholder="Ex: L2024001">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Data de Validade</label>
                            <input type="date" class="form-control form-control-sm" id="lote-val">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quantidade</label>
                            <input type="number" class="form-control form-control-sm" id="lote-qtd" min="1" placeholder="0">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="salvarProduto()">
                    <i class="bi bi-save"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL LOTE ══ -->
<div class="modal fade" id="modalLote" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-layers"></i> Adicionar Lote</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="fb-lote"></div>
                <input type="hidden" id="lote-produto-id">
                <p class="text-muted">Produto: <strong id="lote-produto-nome"></strong></p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Número do Lote *</label>
                    <input type="text" class="form-control" id="novo-lote-num" placeholder="Ex: L2024001">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Data de Validade *</label>
                    <input type="date" class="form-control" id="novo-lote-val">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Quantidade *</label>
                    <input type="number" class="form-control" id="novo-lote-qtd" min="1" placeholder="Quantidade inicial">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarLote()">
                    <i class="bi bi-save"></i> Salvar Lote
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL GERENCIAR CATEGORIAS (ADICIONAR) ══ -->
<div class="modal fade" id="modalCategorias" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-tags-fill"></i> Adicionar Nova Categoria</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="fb-nova-cat"></div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nome da Categoria *</label>
                    <input type="text" class="form-control" id="nova-cat-nome" placeholder="Ex: Fitoterápicos">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Ícone <small class="text-muted">(Bootstrap Icons)</small></label>
                    <div class="input-group">
                        <span class="input-group-text"><i id="preview-icone" class="bi bi-tag"></i></span>
                        <input type="text" class="form-control" id="nova-cat-icone" value="bi-tag" placeholder="bi-capsule" oninput="atualizarIcone()">
                    </div>
                    <small class="text-muted">Veja ícones em <a href="https://icons.getbootstrap.com" target="_blank">icons.getbootstrap.com</a></small>
                </div>
                <hr>
                <h6 class="fw-bold"><i class="bi bi-list-ul"></i> Categorias Atuais</h6>
                <div id="lista-cats-preview" class="mt-2" style="max-height:200px;overflow-y:auto;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" onclick="adicionarCategoria()">
                    <i class="bi bi-plus-circle"></i> Adicionar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL REMOVER CATEGORIA ══ -->
<div class="modal fade" id="modalCatRemover" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash3-fill"></i> Remover Categoria</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="fb-rm-cat"></div>
                <p class="text-muted">Selecione a categoria que deseja remover. Produtos vinculados a ela <strong>não serão afetados</strong>, apenas a categoria do menu.</p>
                <div id="lista-cats-remover"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
// ════════════════════════════════════
// CATEGORIAS (carregadas do servidor)
// ════════════════════════════════════
let categorias = <?= json_encode(array_values($categorias)) ?>;

function recarregarSelectCats(valorAtual) {
    const sel = document.getElementById('prod-categoria');
    const val = valorAtual ?? sel.value;
    sel.innerHTML = '<option value="">Selecione...</option>' +
        categorias.map(c => `<option value="${esc(c.nome)}" ${c.nome===val?'selected':''}>${esc(c.nome)}</option>`).join('');
}

function atualizarIcone() {
    const v = document.getElementById('nova-cat-icone').value.trim();
    document.getElementById('preview-icone').className = 'bi ' + v;
}

function listarCatsPreview() {
    const el = document.getElementById('lista-cats-preview');
    if (!el) return;
    el.innerHTML = categorias.length ? categorias.map(c => `
        <div class="d-flex align-items-center gap-2 p-1 mb-1 rounded border" style="background:#f8f9fa;">
            <i class="bi ${esc(c.icone||'bi-tag')} text-success"></i>
            <span class="flex-grow-1 fw-bold">${esc(c.nome)}</span>
        </div>`).join('') : '<p class="text-muted">Nenhuma categoria.</p>';
}

function listarCatsRemover() {
    const el = document.getElementById('lista-cats-remover');
    if (!el) return;
    el.innerHTML = categorias.length ? categorias.map(c => `
        <div class="d-flex align-items-center gap-2 p-2 mb-1 rounded border" style="background:#fff5f5;">
            <i class="bi ${esc(c.icone||'bi-tag')} text-danger"></i>
            <span class="flex-grow-1 fw-bold">${esc(c.nome)}</span>
            <button class="btn btn-sm btn-outline-danger" onclick="removerCategoria(${c.id}, '${esc(c.nome)}')">
                <i class="bi bi-trash3"></i> Remover
            </button>
        </div>`).join('') : '<p class="text-muted">Nenhuma categoria cadastrada.</p>';
}

function abrirModalCategorias() {
    document.getElementById('nova-cat-nome').value = '';
    document.getElementById('nova-cat-icone').value = 'bi-tag';
    document.getElementById('fb-nova-cat').innerHTML = '';
    atualizarIcone();
    listarCatsPreview();
    new bootstrap.Modal(document.getElementById('modalCategorias')).show();
}

function abrirModalCatRemover() {
    document.getElementById('fb-rm-cat').innerHTML = '';
    listarCatsRemover();
    new bootstrap.Modal(document.getElementById('modalCatRemover')).show();
}

async function adicionarCategoria() {
    const nome  = document.getElementById('nova-cat-nome').value.trim();
    const icone = document.getElementById('nova-cat-icone').value.trim() || 'bi-tag';
    const fb    = document.getElementById('fb-nova-cat');
    if (!nome) { fb.innerHTML = '<div class="alert alert-danger py-2">Informe o nome da categoria.</div>'; return; }

    const fd = new FormData();
    fd.append('nome', nome);
    fd.append('icone', icone);
    const r = await fetch('api.php?endpoint=categoria_criar', { method:'POST', body:fd });
    const d = await r.json();

    if (d.success) {
        categorias.push({ id: d.id, nome, icone, ativo:1, ordem:0 });
        fb.innerHTML = '<div class="alert alert-success py-2"><i class="bi bi-check-circle-fill"></i> Categoria adicionada!</div>';
        document.getElementById('nova-cat-nome').value = '';
        recarregarSelectCats(nome);
        listarCatsPreview();
    } else {
        fb.innerHTML = `<div class="alert alert-danger py-2">${d.message}</div>`;
    }
}

async function removerCategoria(id, nome) {
    if (!confirm(`Remover a categoria "${nome}"?\nProdutos vinculados não serão deletados.`)) return;
    const r = await fetch('api.php?endpoint=categoria_remover', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({id})
    });
    const d = await r.json();
    if (d.success) {
        categorias = categorias.filter(c => c.id !== id);
        recarregarSelectCats(null);
        listarCatsRemover();
        listarCatsPreview();
        document.getElementById('fb-rm-cat').innerHTML =
            `<div class="alert alert-success py-2"><i class="bi bi-check-circle-fill"></i> Categoria "${nome}" removida.</div>`;
    } else {
        document.getElementById('fb-rm-cat').innerHTML = `<div class="alert alert-danger py-2">${d.message}</div>`;
    }
}

// ════════════════════════════════════
// PRODUTOS
// ════════════════════════════════════
function abrirModalProduto(prod) {
    document.getElementById('fb-produto').innerHTML = '';
    document.getElementById('prod-id').value       = '';
    document.getElementById('prod-nome').value     = '';
    document.getElementById('prod-fabricante').value = '';
    document.getElementById('prod-preco').value    = '';
    document.getElementById('prod-desc').value     = '';
    document.getElementById('prod-ctrl').value     = '0';
    document.getElementById('prod-foto').value     = '';
    document.getElementById('foto-preview').innerHTML = '';
    document.getElementById('lote-num').value      = '';
    document.getElementById('lote-val').value      = '';
    document.getElementById('lote-qtd').value      = '';
    document.getElementById('bloco-lote-inicial').style.display = 'block';
    document.getElementById('modalProdutoTitulo').innerHTML     = '<i class="bi bi-plus-circle"></i> Novo Produto';
    recarregarSelectCats('');
    new bootstrap.Modal(document.getElementById('modalProduto')).show();
}

function editarProduto(p) {
    document.getElementById('fb-produto').innerHTML = '';
    document.getElementById('prod-id').value        = p.id;
    document.getElementById('prod-nome').value      = p.nome;
    document.getElementById('prod-fabricante').value= p.fabricante;
    document.getElementById('prod-preco').value     = p.preco_venda;
    document.getElementById('prod-desc').value      = p.descricao || '';
    document.getElementById('prod-ctrl').value      = p.receita_obrigatoria ? '1' : '0';
    document.getElementById('prod-foto').value      = '';
    document.getElementById('bloco-lote-inicial').style.display = 'none';
    document.getElementById('modalProdutoTitulo').innerHTML      = '<i class="bi bi-pencil-fill"></i> Editar Produto';
    if (p.foto) {
        document.getElementById('foto-preview').innerHTML =
            `<img src="uploads/produtos/${p.foto}" style="height:60px;border-radius:6px;"> <small class="text-muted ms-2">Foto atual</small>`;
    } else {
        document.getElementById('foto-preview').innerHTML = '';
    }
    recarregarSelectCats(p.categoria);
    new bootstrap.Modal(document.getElementById('modalProduto')).show();
}

async function salvarProduto() {
    const id     = document.getElementById('prod-id').value;
    const fb     = document.getElementById('fb-produto');
    const fd     = new FormData();

    fd.append('nome',       document.getElementById('prod-nome').value.trim());
    fd.append('fabricante', document.getElementById('prod-fabricante').value.trim());
    fd.append('categoria',  document.getElementById('prod-categoria').value);
    fd.append('preco_venda',document.getElementById('prod-preco').value);
    fd.append('descricao',  document.getElementById('prod-desc').value.trim());

    const fotoFile = document.getElementById('prod-foto').files[0];
    if (fotoFile) fd.append('foto', fotoFile);

    let endpoint;
    if (id) {
        fd.append('id', id);
        endpoint = 'produtos_editar_form';
    } else {
        fd.append('lote_numero',    document.getElementById('lote-num').value.trim());
        fd.append('lote_validade',  document.getElementById('lote-val').value);
        fd.append('lote_quantidade',document.getElementById('lote-qtd').value);
        endpoint = 'produtos_criar';
    }

    fb.innerHTML = '<div class="alert alert-info py-2"><span class="spinner-border spinner-border-sm me-2"></span>Salvando...</div>';
    try {
        const r = await fetch(`api.php?endpoint=${endpoint}`, { method:'POST', body:fd });
        const d = await r.json();
        if (d.success) {
            fb.innerHTML = '<div class="alert alert-success py-2"><i class="bi bi-check-circle-fill"></i> Salvo com sucesso! Recarregando...</div>';
            setTimeout(() => location.reload(), 900);
        } else {
            fb.innerHTML = `<div class="alert alert-danger py-2">${d.message}</div>`;
        }
    } catch(e) { fb.innerHTML = '<div class="alert alert-danger py-2">Erro de comunicação.</div>'; }
}

async function deletarProduto(id, nome) {
    if (!confirm(`Remover o produto "${nome}"?`)) return;
    const r = await fetch('api.php?endpoint=produtos_deletar', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({id})
    });
    const d = await r.json();
    if (d.success) {
        if (d.aviso) alert('⚠️ ' + d.aviso);
        location.reload();
    } else { alert('Erro: ' + d.message); }
}

// ════════════════════════════════════
// LOTES
// ════════════════════════════════════
function abrirModalLote(prodId, prodNome) {
    document.getElementById('lote-produto-id').value = prodId;
    document.getElementById('lote-produto-nome').textContent = prodNome;
    document.getElementById('novo-lote-num').value  = '';
    document.getElementById('novo-lote-val').value  = '';
    document.getElementById('novo-lote-qtd').value  = '';
    document.getElementById('fb-lote').innerHTML    = '';
    new bootstrap.Modal(document.getElementById('modalLote')).show();
}

async function salvarLote() {
    const fb = document.getElementById('fb-lote');
    const fd = new FormData();
    fd.append('produto_id',   document.getElementById('lote-produto-id').value);
    fd.append('numero_lote',  document.getElementById('novo-lote-num').value.trim());
    fd.append('data_validade',document.getElementById('novo-lote-val').value);
    fd.append('qtd_atual',    document.getElementById('novo-lote-qtd').value);

    fb.innerHTML = '<div class="alert alert-info py-2"><span class="spinner-border spinner-border-sm me-2"></span>Salvando lote...</div>';
    try {
        const r = await fetch('api.php?endpoint=lotes_criar', { method:'POST', body:fd });
        const d = await r.json();
        if (d.success) {
            fb.innerHTML = '<div class="alert alert-success py-2"><i class="bi bi-check-circle-fill"></i> Lote adicionado! Recarregando...</div>';
            setTimeout(() => location.reload(), 900);
        } else { fb.innerHTML = `<div class="alert alert-danger py-2">${d.message}</div>`; }
    } catch(e) { fb.innerHTML = '<div class="alert alert-danger py-2">Erro de comunicação.</div>'; }
}

// ════════════════════════════════════
// UTIL
// ════════════════════════════════════
function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Preview foto selecionada
document.getElementById('prod-foto')?.addEventListener('change', function() {
    const f = this.files[0];
    if (!f) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('foto-preview').innerHTML =
            `<img src="${e.target.result}" style="height:60px;border-radius:6px;"> <small class="text-muted ms-2">Nova foto selecionada</small>`;
    };
    reader.readAsDataURL(f);
});
</script>

<?php include 'footer.php'; ?>
