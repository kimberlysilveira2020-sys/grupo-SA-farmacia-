<?php
require_once 'config.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$pdo = Config::getDbConnection();

// ── Caixa atual do usuário ──────────────────────────────────────────
$stmtCaixa = $pdo->prepare("SELECT * FROM caixa WHERE usuario_id = ? AND status = 'aberto' ORDER BY aberto_em DESC LIMIT 1");
$stmtCaixa->execute([$_SESSION['usuario_id']]);
$caixaAberto = $stmtCaixa->fetch();

// ── Totais do caixa atual ───────────────────────────────────────────
$totalCaixa = 0;
$qtdVendasCaixa = 0;
if ($caixaAberto) {
    $stmtTot = $pdo->prepare("SELECT COUNT(*) AS qtd, COALESCE(SUM(total),0) AS soma FROM vendas WHERE caixa_id = ?");
    $stmtTot->execute([$caixaAberto['id']]);
    $totRow = $stmtTot->fetch();
    $totalCaixa    = $totRow['soma'];
    $qtdVendasCaixa = $totRow['qtd'];
}

// ── Filtros da listagem ─────────────────────────────────────────────
$filtroData  = $_GET['data']  ?? date('Y-m-d');
$filtroData2 = $_GET['data2'] ?? date('Y-m-d');

$stmtVendas = $pdo->prepare("
    SELECT v.id, v.data_venda, v.total, v.supervisor_liberacao,
           u.nome AS vendedor, u.cargo AS cargo_vendedor,
           c.nome AS cliente_nome,
           cx.id AS caixa_id
    FROM vendas v
    INNER JOIN usuarios u ON v.usuario_id = u.id
    LEFT JOIN clientes c ON v.cliente_id = c.id
    LEFT JOIN caixa cx   ON v.caixa_id   = cx.id
    WHERE DATE(v.data_venda) BETWEEN ? AND ?
    ORDER BY v.data_venda DESC
");
$stmtVendas->execute([$filtroData, $filtroData2]);
$vendas = $stmtVendas->fetchAll();

$totalPeriodo = array_sum(array_column($vendas, 'total'));
$qtdPeriodo   = count($vendas);

// ── Histórico de caixas ─────────────────────────────────────────────
$stmtHist = $pdo->prepare("
    SELECT cx.*, u.nome AS operador,
           COUNT(v.id) AS qtd_vendas,
           COALESCE(SUM(v.total),0) AS total_vendas
    FROM caixa cx
    INNER JOIN usuarios u ON cx.usuario_id = u.id
    LEFT JOIN vendas v ON v.caixa_id = cx.id
    GROUP BY cx.id
    ORDER BY cx.aberto_em DESC
    LIMIT 20
");
$stmtHist->execute();
$historicoCaixas = $stmtHist->fetchAll();

$page_title = "Vendas & Caixa";
include 'header.php';
?>

<div class="container-fluid fade-in pb-5">

    <h2 class="mb-4"><i class="bi bi-cash-stack"></i> Vendas & Controle de Caixa</h2>

    <!-- ══ PAINEL DO CAIXA ══════════════════════════════════════════ -->
    <div class="row g-3 mb-4">

        <!-- Status do caixa -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <?php if ($caixaAberto): ?>
                    <div class="mb-2">
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="bi bi-unlock-fill"></i> Caixa Aberto
                        </span>
                    </div>
                    <p class="text-muted mb-1 small">Aberto em: <strong><?= date('d/m/Y H:i', strtotime($caixaAberto['aberto_em'])) ?></strong></p>
                    <p class="text-muted mb-3 small">Troco inicial: <strong>R$ <?= number_format($caixaAberto['valor_abertura'], 2, ',', '.') ?></strong></p>
                    <button class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#modalFecharCaixa">
                        <i class="bi bi-lock-fill"></i> Fechar Caixa
                    </button>
                    <?php else: ?>
                    <div class="mb-2">
                        <span class="badge bg-secondary fs-6 px-3 py-2">
                            <i class="bi bi-lock-fill"></i> Caixa Fechado
                        </span>
                    </div>
                    <p class="text-muted mb-3 small">Nenhum caixa aberto no momento.</p>
                    <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#modalAbrirCaixa">
                        <i class="bi bi-unlock-fill"></i> Abrir Caixa
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Resumo do caixa atual -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg,#1976D2,#0D47A1); color:#fff;">
                <div class="card-body py-4">
                    <p class="mb-1 opacity-75 small"><i class="bi bi-receipt"></i> Vendas no caixa atual</p>
                    <h2 class="fw-bold mb-0"><?= $qtdVendasCaixa ?></h2>
                    <hr style="border-color:rgba(255,255,255,.3);">
                    <p class="mb-1 opacity-75 small"><i class="bi bi-cash"></i> Total arrecadado</p>
                    <h3 class="fw-bold mb-0">R$ <?= number_format($totalCaixa, 2, ',', '.') ?></h3>
                    <?php if ($caixaAberto): ?>
                    <small class="opacity-75">+ R$ <?= number_format($caixaAberto['valor_abertura'], 2, ',', '.') ?> (troco)</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Resumo do período filtrado -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg,#2e7d32,#1b5e20); color:#fff;">
                <div class="card-body py-4">
                    <p class="mb-1 opacity-75 small"><i class="bi bi-calendar-range"></i> Vendas no período</p>
                    <h2 class="fw-bold mb-0"><?= $qtdPeriodo ?></h2>
                    <hr style="border-color:rgba(255,255,255,.3);">
                    <p class="mb-1 opacity-75 small"><i class="bi bi-cash"></i> Total do período</p>
                    <h3 class="fw-bold mb-0">R$ <?= number_format($totalPeriodo, 2, ',', '.') ?></h3>
                    <small class="opacity-75"><?= date('d/m/Y', strtotime($filtroData)) ?> → <?= date('d/m/Y', strtotime($filtroData2)) ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ RELATÓRIO DE VENDAS ══════════════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"
             style="background:#f8f9fa; border-bottom:1px solid #dee2e6;">
            <span class="fw-bold"><i class="bi bi-table"></i> Relatório de Vendas</span>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <input type="date" id="filtro-data-ini" class="form-control form-control-sm" value="<?= $filtroData ?>" style="width:140px;">
                <span class="text-muted small">até</span>
                <input type="date" id="filtro-data-fim" class="form-control form-control-sm" value="<?= $filtroData2 ?>" style="width:140px;">
                <button class="btn btn-primary btn-sm" onclick="filtrarVendas()">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($vendas)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:#e3f2fd;">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Data/Hora</th>
                            <th>Vendedor</th>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th class="text-end pe-3">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendas as $v): ?>
                        <tr style="cursor:pointer;" onclick="verItensVenda(<?= $v['id'] ?>)">
                            <td class="ps-3 text-muted">#<?= $v['id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($v['data_venda'])) ?></td>
                            <td>
                                <?= htmlspecialchars($v['vendedor']) ?>
                                <span class="badge bg-secondary ms-1" style="font-size:.65rem;"><?= htmlspecialchars($v['cargo_vendedor']) ?></span>
                            </td>
                            <td><?= $v['cliente_nome'] ? htmlspecialchars($v['cliente_nome']) : '<span class="text-muted">—</span>' ?></td>
                            <td>
                                <?php if (!empty($v['supervisor_liberacao'])): ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-shield-check"></i> Controlado</span>
                                <?php else: ?>
                                <span class="badge bg-light text-secondary">Comum</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-3 fw-bold text-success">R$ <?= number_format($v['total'], 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot style="background:#f8f9fa;">
                        <tr>
                            <td colspan="5" class="ps-3 fw-bold">Total do período</td>
                            <td class="text-end pe-3 fw-bold text-success fs-5">R$ <?= number_format($totalPeriodo, 2, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-receipt" style="font-size:3rem;"></i>
                <p class="mt-2">Nenhuma venda encontrada no período selecionado.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══ HISTÓRICO DE CAIXAS ══════════════════════════════════════ -->
    <div class="card border-0 shadow-sm">
        <div class="card-header fw-bold" style="background:#f8f9fa;">
            <i class="bi bi-clock-history"></i> Histórico de Caixas
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:#f3e5f5;">
                        <tr>
                            <th class="ps-3">Operador</th>
                            <th>Abertura</th>
                            <th>Fechamento</th>
                            <th>Troco Inicial</th>
                            <th>Vendas</th>
                            <th>Total Vendido</th>
                            <th class="pe-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historicoCaixas as $cx): ?>
                        <tr>
                            <td class="ps-3"><strong><?= htmlspecialchars($cx['operador']) ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($cx['aberto_em'])) ?></td>
                            <td><?= $cx['fechado_em'] ? date('d/m/Y H:i', strtotime($cx['fechado_em'])) : '—' ?></td>
                            <td>R$ <?= number_format($cx['valor_abertura'], 2, ',', '.') ?></td>
                            <td><?= $cx['qtd_vendas'] ?></td>
                            <td class="fw-bold text-success">R$ <?= number_format($cx['total_vendas'], 2, ',', '.') ?></td>
                            <td class="pe-3">
                                <?php if ($cx['status'] === 'aberto'): ?>
                                <span class="badge bg-success">Aberto</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Fechado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($historicoCaixas)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Nenhum caixa registrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ══ MODAL ABRIR CAIXA ════════════════════════════════════════════ -->
<div class="modal fade" id="modalAbrirCaixa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-unlock-fill"></i> Abrir Caixa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Valor de Abertura (Troco Inicial)</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="number" class="form-control form-control-lg" id="valor_abertura"
                               step="0.01" min="0" value="0.00" placeholder="0,00">
                    </div>
                    <small class="text-muted">Informe o valor em espécie disponível para troco.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observação <span class="text-muted">(opcional)</span></label>
                    <textarea class="form-control" id="obs_abertura" rows="2"></textarea>
                </div>
                <div id="feedback-caixa-abrir"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success" onclick="abrirCaixa()">
                    <i class="bi bi-check-circle"></i> Confirmar Abertura
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL FECHAR CAIXA ═══════════════════════════════════════════ -->
<div class="modal fade" id="modalFecharCaixa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-lock-fill"></i> Fechar Caixa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2">
                    <i class="bi bi-info-circle"></i>
                    Vendas realizadas: <strong><?= $qtdVendasCaixa ?></strong> &nbsp;|&nbsp;
                    Total: <strong>R$ <?= number_format($totalCaixa, 2, ',', '.') ?></strong>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Valor em Caixa no Fechamento</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="number" class="form-control form-control-lg" id="valor_fechamento"
                               step="0.01" min="0" placeholder="0,00">
                    </div>
                    <small class="text-muted">Informe o total em espécie contado no caixa.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observação <span class="text-muted">(opcional)</span></label>
                    <textarea class="form-control" id="obs_fechamento" rows="2"></textarea>
                </div>
                <div id="feedback-caixa-fechar"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger" onclick="fecharCaixa(<?= $caixaAberto['id'] ?? 0 ?>)">
                    <i class="bi bi-lock-fill"></i> Confirmar Fechamento
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL ITENS DA VENDA ═════════════════════════════════════════ -->
<div class="modal fade" id="modalItensVenda" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#1976D2; color:#fff;">
                <h5 class="modal-title"><i class="bi bi-receipt"></i> Detalhes da Venda <span id="venda-id-titulo"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-itens-body">
                <div class="text-center py-3"><span class="spinner-border"></span></div>
            </div>
        </div>
    </div>
</div>

<script>
    function filtrarVendas() {
        const ini = document.getElementById('filtro-data-ini').value;
        const fim = document.getElementById('filtro-data-fim').value;
        window.location.href = `vendas.php?data=${ini}&data2=${fim}`;
    }

    async function abrirCaixa() {
        const valor = document.getElementById('valor_abertura').value;
        const obs   = document.getElementById('obs_abertura').value;
        const fb    = document.getElementById('feedback-caixa-abrir');
        fb.innerHTML = '<div class="alert alert-info py-2"><span class="spinner-border spinner-border-sm me-1"></span>Abrindo...</div>';
        const fd = new FormData();
        fd.append('valor_abertura', valor);
        fd.append('observacao', obs);
        const res  = await fetch('api.php?endpoint=caixa_abrir', { method:'POST', body:fd });
        const data = await res.json();
        if (data.success) { location.reload(); }
        else { fb.innerHTML = `<div class="alert alert-danger py-2">${data.message}</div>`; }
    }

    async function fecharCaixa(id) {
        const valor = document.getElementById('valor_fechamento').value;
        const obs   = document.getElementById('obs_fechamento').value;
        const fb    = document.getElementById('feedback-caixa-fechar');
        if (!valor) { fb.innerHTML = '<div class="alert alert-warning py-2">Informe o valor de fechamento.</div>'; return; }
        fb.innerHTML = '<div class="alert alert-info py-2"><span class="spinner-border spinner-border-sm me-1"></span>Fechando...</div>';
        const fd = new FormData();
        fd.append('id', id);
        fd.append('valor_fechamento', valor);
        fd.append('observacao', obs);
        const res  = await fetch('api.php?endpoint=caixa_fechar', { method:'POST', body:fd });
        const data = await res.json();
        if (data.success) { location.reload(); }
        else { fb.innerHTML = `<div class="alert alert-danger py-2">${data.message}</div>`; }
    }

    async function verItensVenda(id) {
        document.getElementById('venda-id-titulo').textContent = '#' + id;
        document.getElementById('modal-itens-body').innerHTML = '<div class="text-center py-3"><span class="spinner-border"></span></div>';
        new bootstrap.Modal(document.getElementById('modalItensVenda')).show();
        const res  = await fetch(`api.php?endpoint=venda_itens&id=${id}`);
        const data = await res.json();
        if (data.success) {
            const rows = data.itens.map(i => `
                <tr>
                    <td>${i.produto_nome}</td>
                    <td class="text-center">${i.quantidade}</td>
                    <td class="text-end">R$ ${parseFloat(i.preco).toFixed(2).replace('.',',')}</td>
                    <td class="text-end fw-bold">R$ ${(i.quantidade * i.preco).toFixed(2).replace('.',',')}</td>
                </tr>`).join('');
            document.getElementById('modal-itens-body').innerHTML = `
                <table class="table">
                    <thead class="table-primary"><tr><th>Produto</th><th class="text-center">Qtd</th><th class="text-end">Preço Unit.</th><th class="text-end">Subtotal</th></tr></thead>
                    <tbody>${rows}</tbody>
                    <tfoot class="table-success"><tr><td colspan="3" class="fw-bold">Total</td><td class="text-end fw-bold">R$ ${parseFloat(data.total).toFixed(2).replace('.',',')}</td></tr></tfoot>
                </table>`;
        } else {
            document.getElementById('modal-itens-body').innerHTML = '<div class="alert alert-danger">Erro ao carregar itens.</div>';
        }
    }
</script>

<?php include 'footer.php'; ?>
