<?php
require_once 'config.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$page_title = "Pedidos Online";
$extra_css = <<<CSS
<style>
/* ── Cards de resumo ─────────────────────────────── */
.resumo-cards { display:flex; gap:14px; flex-wrap:wrap; margin-bottom:22px; }
.resumo-card {
    flex:1; min-width:140px; border-radius:12px; padding:16px 20px;
    display:flex; align-items:center; gap:14px; background:#fff;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
}
.resumo-card .icon { font-size:2rem; width:44px; text-align:center; }
.resumo-card .info .num  { font-size:1.5rem; font-weight:800; line-height:1; }
.resumo-card .info .label{ font-size:.75rem; color:#666; margin-top:2px; }

/* ── Tabela ──────────────────────────────────────── */
.tbl-pedidos { width:100%; border-collapse:collapse; font-size:.875rem; }
.tbl-pedidos thead th {
    background:#f8f9fa; padding:10px 14px; text-align:left;
    font-weight:700; color:#555; border-bottom:2px solid #dee2e6;
    white-space:nowrap;
}
.tbl-pedidos tbody tr { border-bottom:1px solid #eee; transition:background .12s; }
.tbl-pedidos tbody tr:hover { background:#f5f9f5; }
.tbl-pedidos td { padding:10px 14px; vertical-align:middle; }

/* ── Badges de status ────────────────────────────── */
.badge-status {
    display:inline-block; padding:4px 10px; border-radius:20px;
    font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px;
}
.bs-pendente   { background:#fff3cd; color:#856404; }
.bs-confirmado { background:#d1e7dd; color:#0a3622; }
.bs-cancelado  { background:#f8d7da; color:#58151c; }

/* ── Badges pagamento ────────────────────────────── */
.badge-pgto {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 9px; border-radius:20px; font-size:.72rem; font-weight:600;
}
.bp-pix     { background:#e8f5e9; color:#1b5e20; }
.bp-credito { background:#e3f2fd; color:#0d47a1; }
.bp-boleto  { background:#fff8e1; color:#e65100; }
.bp-paypal  { background:#e8eaf6; color:#1a237e; }
.bp-pago    { background:#c8e6c9; color:#1b5e20; }

/* ── Filtros ─────────────────────────────────────── */
.filtros-bar {
    display:flex; gap:10px; align-items:center; flex-wrap:wrap;
    background:#fff; padding:14px 18px; border-radius:10px;
    box-shadow:0 1px 6px rgba(0,0,0,.07); margin-bottom:18px;
}
.filtros-bar input, .filtros-bar select {
    border:1px solid #ced4da; border-radius:8px; padding:7px 12px;
    font-size:.87rem; outline:none;
}
.filtros-bar input:focus, .filtros-bar select:focus { border-color:#198754; }

/* ── Modal detalhes ──────────────────────────────── */
.modal-header-verde { background:#198754 !important; color:#fff !important; }
.modal-header-verde .btn-close { filter:invert(1); }

.detalhe-bloco {
    background:#f8f9fa; border-radius:10px; padding:14px 18px; margin-bottom:14px;
}
.detalhe-bloco h6 { font-weight:700; color:#198754; margin-bottom:10px; font-size:.85rem; }

.itens-list { list-style:none; padding:0; margin:0; }
.itens-list li {
    display:flex; justify-content:space-between; align-items:center;
    padding:8px 0; border-bottom:1px solid #e9ecef; font-size:.87rem;
}
.itens-list li:last-child { border-bottom:none; }
.itens-list .qtd { font-weight:700; color:#198754; min-width:32px; }
.itens-list .preco { font-weight:700; white-space:nowrap; }

/* PIX QR no modal */
.pix-modal-wrap { text-align:center; margin:10px 0; }
.pix-modal-wrap canvas { border:3px solid #198754; border-radius:10px; padding:8px; background:#fff; }
.pix-codigo-bloco {
    background:#f1f8f3; border:1px solid #c3e6cb; border-radius:8px;
    padding:10px 14px; font-size:.72rem; word-break:break-all;
    color:#155724; display:flex; align-items:center; gap:8px; margin-top:8px;
}
.pix-codigo-bloco span { flex:1; }

/* Ações rápidas */
.acoes-rapidas { display:flex; gap:6px; flex-wrap:wrap; }

/* Loading overlay */
#loading-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(255,255,255,.7); z-index:9999;
    align-items:center; justify-content:center;
}
#loading-overlay.ativo { display:flex; }

/* Tabela vazia */
.tbl-vazio { text-align:center; padding:40px; color:#aaa; }
.tbl-vazio i { font-size:2.5rem; display:block; margin-bottom:8px; }
</style>
CSS;

include 'header.php';
?>

<div id="loading-overlay">
    <div class="spinner-border text-success" role="status"></div>
</div>

<div class="px-3">

    <!-- Cabeçalho -->
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-bag-check-fill text-success me-2"></i>Pedidos Online</h4>
            <small class="text-muted">Gerencie todos os pedidos realizados pela loja virtual</small>
        </div>
        <button class="btn btn-success btn-sm" onclick="carregarPedidos()">
            <i class="bi bi-arrow-clockwise"></i> Atualizar
        </button>
    </div>

    <!-- Cards de resumo -->
    <div class="resumo-cards" id="resumo-cards">
        <div class="resumo-card">
            <div class="icon text-secondary"><i class="bi bi-hourglass-split"></i></div>
            <div class="info">
                <div class="num" id="res-pendente">—</div>
                <div class="label">Pendentes</div>
            </div>
        </div>
        <div class="resumo-card">
            <div class="icon text-success"><i class="bi bi-check-circle-fill"></i></div>
            <div class="info">
                <div class="num" id="res-confirmado">—</div>
                <div class="label">Confirmados</div>
            </div>
        </div>
        <div class="resumo-card">
            <div class="icon text-danger"><i class="bi bi-x-circle-fill"></i></div>
            <div class="info">
                <div class="num" id="res-cancelado">—</div>
                <div class="label">Cancelados</div>
            </div>
        </div>
        <div class="resumo-card">
            <div class="icon text-primary"><i class="bi bi-currency-dollar"></i></div>
            <div class="info">
                <div class="num" id="res-total">—</div>
                <div class="label">Total Confirmados</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filtros-bar">
        <input type="text" id="busca" placeholder="🔍  Buscar por cliente, e-mail ou nº pedido…"
               style="flex:1;min-width:200px;" oninput="debounceCarregar()">
        <select id="filtro-status" onchange="carregarPedidos()">
            <option value="todos">Todos os status</option>
            <option value="pendente">Pendentes</option>
            <option value="confirmado">Confirmados</option>
            <option value="cancelado">Cancelados</option>
        </select>
        <select id="filtro-pgto" onchange="carregarPedidos()">
            <option value="">Todos pagamentos</option>
            <option value="pix">PIX</option>
            <option value="credito">Crédito</option>
            <option value="boleto">Boleto</option>
            <option value="paypal">PayPal</option>
        </select>
    </div>

    <!-- Tabela -->
    <div style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.08);overflow:auto;">
        <table class="tbl-pedidos">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Itens</th>
                    <th>Total</th>
                    <th>Pagamento</th>
                    <th>Status</th>
                    <th style="text-align:center;">Ações</th>
                </tr>
            </thead>
            <tbody id="tbody-pedidos">
                <tr><td colspan="8" class="tbl-vazio"><i class="bi bi-hourglass-split"></i>Carregando pedidos…</td></tr>
            </tbody>
        </table>
    </div>

</div>

<!-- ══ MODAL DETALHES ══ -->
<div class="modal fade" id="modalDetalhes" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header modal-header-verde">
                <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Detalhes do Pedido <span id="md-numero"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-body-detalhes">
                <div class="text-center py-4"><div class="spinner-border text-success"></div></div>
            </div>
            <div class="modal-footer" id="modal-footer-detalhes"></div>
        </div>
    </div>
</div>

<!-- ══ MODAL CONFIRMAR AÇÃO ══ -->
<div class="modal fade" id="modalConfirmar" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;border:none;box-shadow:0 8px 32px rgba(0,0,0,.18);">
            <div class="modal-header border-0 pb-0" id="mc-header" style="padding:24px 24px 10px;">
                <div style="display:flex;align-items:center;gap:12px;width:100%;">
                    <div id="mc-icon" style="width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.4rem;"></div>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold" id="mc-titulo"></h5>
                        <small id="mc-subtitulo" style="color:#888;"></small>
                    </div>
                </div>
            </div>
            <div class="modal-body" id="mc-corpo" style="padding:14px 24px 10px;font-size:.9rem;color:#444;"></div>
            <div class="modal-footer border-0" style="padding:10px 24px 20px;gap:10px;">
                <button class="btn btn-light fw-600" style="border-radius:10px;padding:9px 22px;" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Cancelar
                </button>
                <button class="btn fw-bold" id="mc-btn-ok" style="border-radius:10px;padding:9px 22px;min-width:130px;">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const API = 'api.php';
let _acaoPendente = null;
let _debounce = null;

// ════════════════════════════════════════════════════
// UTILITÁRIOS
// ════════════════════════════════════════════════════
const fmtBRL = v => 'R$ ' + parseFloat(v).toFixed(2).replace('.',',').replace(/\B(?=(\d{3})+(?!\d))/g,'.');
const fmtData = s => {
    if (!s) return '—';
    const d = new Date(s.replace(' ','T'));
    return d.toLocaleDateString('pt-BR') + ' ' + d.toLocaleTimeString('pt-BR',{hour:'2-digit',minute:'2-digit'});
};
const loading = ativo => {
    document.getElementById('loading-overlay').classList.toggle('ativo', ativo);
};

function debounceCarregar() {
    clearTimeout(_debounce);
    _debounce = setTimeout(carregarPedidos, 400);
}

// ════════════════════════════════════════════════════
// CARREGAR PEDIDOS
// ════════════════════════════════════════════════════
async function carregarPedidos() {
    const busca  = document.getElementById('busca').value.trim();
    const status = document.getElementById('filtro-status').value;
    const pgto   = document.getElementById('filtro-pgto').value;

    loading(true);
    try {
        const url = `${API}?endpoint=pedidos_loja_listar&status=${encodeURIComponent(status)}&busca=${encodeURIComponent(busca)}`;
        const r   = await fetch(url);
        const d   = await r.json();
        if (!d.success) throw new Error(d.message);

        // Filtro de pagamento (client-side para não complicar a query)
        let pedidos = d.pedidos;
        if (pgto) pedidos = pedidos.filter(p => p.forma_pagamento === pgto);

        renderResumo(d.resumo, d.pedidos);
        renderTabela(pedidos);
    } catch(e) {
        console.error(e);
        document.getElementById('tbody-pedidos').innerHTML =
            `<tr><td colspan="8" class="tbl-vazio"><i class="bi bi-exclamation-triangle text-danger"></i>${e.message}</td></tr>`;
    } finally { loading(false); }
}

function renderResumo(resumo, todos) {
    const get = (k, field) => resumo[k] ? resumo[k][field] : 0;
    document.getElementById('res-pendente').textContent   = get('pendente','qtd');
    document.getElementById('res-confirmado').textContent = get('confirmado','qtd');
    document.getElementById('res-cancelado').textContent  = get('cancelado','qtd');
    document.getElementById('res-total').textContent      = fmtBRL(get('confirmado','soma'));
}

function renderTabela(pedidos) {
    const tbody = document.getElementById('tbody-pedidos');
    if (!pedidos.length) {
        tbody.innerHTML = `<tr><td colspan="8" class="tbl-vazio"><i class="bi bi-inbox"></i>Nenhum pedido encontrado</td></tr>`;
        return;
    }

    const pgtoLabel = { pix:'PIX', credito:'Crédito', boleto:'Boleto', paypal:'PayPal' };
    const pgtoIcon  = { pix:'bi-qr-code', credito:'bi-credit-card-fill', boleto:'bi-upc-scan', paypal:'bi-paypal' };

    tbody.innerHTML = pedidos.map(p => {
        const badgeSt = `<span class="badge-status bs-${p.status}">${p.status}</span>`;

        let badgePgto = `<span class="badge-pgto bp-${p.forma_pagamento}">
            <i class="bi ${pgtoIcon[p.forma_pagamento]||'bi-cash'}"></i>
            ${pgtoLabel[p.forma_pagamento]||p.forma_pagamento}
        </span>`;
        if (p.forma_pagamento === 'pix' && p.pix_pago == 1)
            badgePgto += ` <span class="badge-pgto bp-pago" style="margin-left:4px;"><i class="bi bi-check-lg"></i> Pago</span>`;

        return `<tr>
            <td><strong>#${p.id}</strong></td>
            <td>
                <div style="font-weight:600;">${esc(p.cliente_nome)}</div>
                <small class="text-muted">${esc(p.cliente_email||'')}</small>
            </td>
            <td style="white-space:nowrap;font-size:.8rem;">${fmtData(p.criado_em)}</td>
            <td style="text-align:center;">${p.qtd_itens}</td>
            <td><strong class="text-success">${fmtBRL(p.total)}</strong></td>
            <td>${badgePgto}</td>
            <td>${badgeSt}</td>
            <td>
                <div class="acoes-rapidas justify-content-center">
                    <button class="btn btn-sm btn-outline-success" title="Ver detalhes" onclick="verDetalhes(${p.id})">
                        <i class="bi bi-eye"></i>
                    </button>
                    ${p.status === 'pendente' ? `
                    <button class="btn btn-sm btn-success" title="Confirmar pedido" onclick="alterarStatus(${p.id},'confirmado')">
                        <i class="bi bi-check-lg"></i>
                    </button>` : ''}
                    ${p.status !== 'cancelado' ? `
                    <button class="btn btn-sm btn-outline-danger" title="Cancelar pedido" onclick="alterarStatus(${p.id},'cancelado')">
                        <i class="bi bi-x-lg"></i>
                    </button>` : ''}
                    <button class="btn btn-sm btn-outline-secondary" title="Excluir pedido" onclick="excluirPedido(${p.id})">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// ════════════════════════════════════════════════════
// VER DETALHES
// ════════════════════════════════════════════════════
async function verDetalhes(id) {
    document.getElementById('md-numero').textContent = `#${id}`;
    document.getElementById('modal-body-detalhes').innerHTML =
        '<div class="text-center py-4"><div class="spinner-border text-success"></div></div>';
    document.getElementById('modal-footer-detalhes').innerHTML = '';

    const modal = new bootstrap.Modal(document.getElementById('modalDetalhes'));
    modal.show();

    const r = await fetch(`${API}?endpoint=pedido_loja_detalhes&id=${id}`);
    const d = await r.json();
    if (!d.success) {
        document.getElementById('modal-body-detalhes').innerHTML =
            `<div class="alert alert-danger">${d.message}</div>`;
        return;
    }

    const p = d.pedido;
    const pgtoNomes = { pix:'PIX', credito:'Cartão de Crédito', boleto:'Boleto Bancário', paypal:'PayPal' };

    // Itens
    const itensHTML = p.itens.map(i => `
        <li>
            <div>
                <span class="qtd">${i.quantidade}×</span>
                <span>${esc(i.produto_nome)}</span>
                <small class="text-muted ms-1">(${esc(i.fabricante||'')})</small>
            </div>
            <span class="preco text-success">${fmtBRL(i.preco * i.quantidade)}</span>
        </li>`).join('');

    // Bloco PIX
    let pixHTML = '';
    if (p.forma_pagamento === 'pix') {
        const pago = p.pix_pago == 1;
        pixHTML = `
        <div class="detalhe-bloco">
            <h6><i class="bi bi-qr-code me-1"></i>Informações PIX</h6>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                <span>Status do pagamento:</span>
                ${pago
                    ? '<span class="badge-pgto bp-pago"><i class="bi bi-check-circle-fill"></i> PAGO</span>'
                    : '<span class="badge-status bs-pendente">AGUARDANDO PAGAMENTO</span>'}
            </div>
            ${p.pix_txid ? `<div class="pix-modal-wrap">
                <canvas id="det-qr-canvas"></canvas>
            </div>
            <div class="pix-codigo-bloco">
                <span id="det-pix-payload">Gerando payload…</span>
                <button class="btn btn-sm btn-success" onclick="copiarDetPix()">
                    <i class="bi bi-clipboard"></i>
                </button>
            </div>` : '<p class="text-muted mb-0" style="font-size:.82rem;">Payload PIX não disponível para este pedido.</p>'}
            ${!pago ? `<button class="btn btn-success btn-sm mt-2 w-100" onclick="confirmarPix(${p.id})">
                <i class="bi bi-check-circle-fill"></i> Marcar PIX como Pago
            </button>` : ''}
        </div>`;
    }

    // Bloco Boleto
    let boletoHTML = '';
    if (p.forma_pagamento === 'boleto' && p.boleto_codigo) {
        boletoHTML = `
        <div class="detalhe-bloco">
            <h6><i class="bi bi-upc-scan me-1"></i>Boleto Bancário</h6>
            <p class="mb-1" style="font-size:.82rem;"><strong>Código:</strong></p>
            <div class="pix-codigo-bloco">
                <span>${esc(p.boleto_codigo)}</span>
                <button class="btn btn-sm btn-success" onclick="navigator.clipboard.writeText('${esc(p.boleto_codigo)}').then(()=>alert('Copiado!'))">
                    <i class="bi bi-clipboard"></i>
                </button>
            </div>
            <p class="mb-0 mt-2" style="font-size:.82rem;">
                <i class="bi bi-calendar-event text-warning"></i>
                Vencimento: <strong>${p.boleto_vencimento ? new Date(p.boleto_vencimento+'T00:00').toLocaleDateString('pt-BR') : '—'}</strong>
            </p>
        </div>`;
    }

    document.getElementById('modal-body-detalhes').innerHTML = `
        <div class="detalhe-bloco">
            <h6><i class="bi bi-person-fill me-1"></i>Cliente</h6>
            <div class="row g-2" style="font-size:.87rem;">
                <div class="col-6"><strong>Nome:</strong> ${esc(p.cliente_nome)}</div>
                <div class="col-6"><strong>E-mail:</strong> ${esc(p.cliente_email||'—')}</div>
                <div class="col-6"><strong>Telefone:</strong> ${esc(p.cliente_tel||'—')}</div>
                <div class="col-6"><strong>CPF:</strong> ${esc(p.cliente_cpf||'—')}</div>
            </div>
        </div>

        <div class="detalhe-bloco">
            <h6><i class="bi bi-receipt me-1"></i>Pedido</h6>
            <div class="row g-2" style="font-size:.87rem;">
                <div class="col-6"><strong>Número:</strong> #${p.id}</div>
                <div class="col-6"><strong>Data:</strong> ${fmtData(p.criado_em)}</div>
                <div class="col-6"><strong>Pagamento:</strong> ${pgtoNomes[p.forma_pagamento]||p.forma_pagamento}</div>
                <div class="col-6"><strong>Status:</strong> <span class="badge-status bs-${p.status}">${p.status}</span></div>
                <div class="col-12"><strong>Total:</strong> <span class="text-success fw-bold">${fmtBRL(p.total)}</span></div>
            </div>
        </div>

        <div class="detalhe-bloco">
            <h6><i class="bi bi-box-seam me-1"></i>Itens do Pedido</h6>
            <ul class="itens-list">${itensHTML}</ul>
            <div style="text-align:right;font-weight:800;font-size:1rem;margin-top:10px;color:#198754;">
                Total: ${fmtBRL(p.total)}
            </div>
        </div>

        ${pixHTML}
        ${boletoHTML}
    `;

    // Botões do footer
    let footerBtns = '';
    if (p.status === 'pendente') {
        footerBtns += `<button class="btn btn-success" onclick="alterarStatusModal(${p.id},'confirmado')">
            <i class="bi bi-check-lg"></i> Confirmar Pedido
        </button>`;
    }
    if (p.status !== 'cancelado') {
        footerBtns += `<button class="btn btn-outline-danger" onclick="alterarStatusModal(${p.id},'cancelado')">
            <i class="bi bi-x-lg"></i> Cancelar Pedido
        </button>`;
    }
    footerBtns += `<button class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>`;
    document.getElementById('modal-footer-detalhes').innerHTML = footerBtns;

    // Gera QR Code se PIX com txid
    if (p.forma_pagamento === 'pix' && p.pix_txid) {
        gerarQRPixAdmin(p.id, parseFloat(p.total));
    }
}

async function gerarQRPixAdmin(pedidoId, total) {
    const fd = new FormData();
    fd.append('total', total.toFixed(2));
    fd.append('pedido_id', pedidoId);
    try {
        const r = await fetch('loja/loja_api.php?endpoint=pix_preview', {method:'POST', body:fd});
        const d = await r.json();
        if (d.success) {
            document.getElementById('det-pix-payload').textContent = d.pix_payload;
            const canvas = document.getElementById('det-qr-canvas');
            if (canvas) {
                const size = 160; canvas.width = size; canvas.height = size;
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = () => canvas.getContext('2d').drawImage(img, 0, 0, size, size);
                img.src = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(d.pix_payload)}&format=png&margin=4`;
            }
        }
    } catch(e) { console.error('QR PIX:', e); }
}

function copiarDetPix() {
    const txt = document.getElementById('det-pix-payload').textContent;
    navigator.clipboard.writeText(txt).then(()=>{ alert('Código PIX copiado!'); });
}

// ════════════════════════════════════════════════════
// MODAL DE CONFIRMAÇÃO CUSTOMIZADO
// ════════════════════════════════════════════════════
let _confirmarCallback = null;

const _confirmarCfg = {
    confirmado: {
        icon: 'bi-check-circle-fill', iconBg: '#d1e7dd', iconColor: '#0a3622',
        titulo: 'Confirmar Pedido', subtitulo: 'Esta ação irá aprovar o pedido',
        btnClass: 'btn-success', btnLabel: '<i class="bi bi-check-lg me-1"></i>Sim, confirmar'
    },
    cancelado: {
        icon: 'bi-x-circle-fill', iconBg: '#f8d7da', iconColor: '#58151c',
        titulo: 'Cancelar Pedido', subtitulo: 'O pedido será marcado como cancelado',
        btnClass: 'btn-danger', btnLabel: '<i class="bi bi-x-lg me-1"></i>Sim, cancelar'
    },
    deletar: {
        icon: 'bi-trash3-fill', iconBg: '#fff3cd', iconColor: '#856404',
        titulo: 'Excluir Pedido', subtitulo: 'Esta ação não pode ser desfeita',
        btnClass: 'btn-warning text-dark', btnLabel: '<i class="bi bi-trash3 me-1"></i>Sim, excluir'
    },
    pix: {
        icon: 'bi-qr-code-scan', iconBg: '#d1e7dd', iconColor: '#0a3622',
        titulo: 'Confirmar Pagamento PIX', subtitulo: 'Marcar como recebido',
        btnClass: 'btn-success', btnLabel: '<i class="bi bi-check-circle-fill me-1"></i>Confirmar PIX'
    }
};

function showConfirmar(tipo, id, mensagem, callback) {
    const cfg = _confirmarCfg[tipo];
    _confirmarCallback = callback;

    document.getElementById('mc-icon').style.background    = cfg.iconBg;
    document.getElementById('mc-icon').style.color         = cfg.iconColor;
    document.getElementById('mc-icon').innerHTML           = `<i class="bi ${cfg.icon}"></i>`;
    document.getElementById('mc-titulo').textContent       = cfg.titulo;
    document.getElementById('mc-subtitulo').textContent    = cfg.subtitulo;
    document.getElementById('mc-corpo').innerHTML          = mensagem;

    const btn = document.getElementById('mc-btn-ok');
    btn.className  = `btn fw-bold ${cfg.btnClass}`;
    btn.style      = 'border-radius:10px;padding:9px 22px;min-width:130px;';
    btn.innerHTML  = cfg.btnLabel;
    btn.onclick    = () => {
        bootstrap.Modal.getInstance(document.getElementById('modalConfirmar'))?.hide();
        callback();
    };

    new bootstrap.Modal(document.getElementById('modalConfirmar')).show();
}

// ════════════════════════════════════════════════════
// AÇÕES
// ════════════════════════════════════════════════════
async function alterarStatus(id, novoStatus) {
    const msgs = {
        confirmado: `Deseja <strong>confirmar</strong> o pedido <strong>#${id}</strong>?<br><small class="text-muted">O status será atualizado para Confirmado.</small>`,
        cancelado:  `Deseja <strong>cancelar</strong> o pedido <strong>#${id}</strong>?<br><small class="text-muted">O cliente será notificado do cancelamento.</small>`
    };
    showConfirmar(novoStatus, id, msgs[novoStatus] || '', () => _alterarStatus(id, novoStatus));
}

async function alterarStatusModal(id, novoStatus) {
    bootstrap.Modal.getInstance(document.getElementById('modalDetalhes'))?.hide();
    setTimeout(() => alterarStatus(id, novoStatus), 300);
}

async function _alterarStatus(id, status) {
    loading(true);
    const fd = new FormData();
    fd.append('id', id); fd.append('status', status);
    const r = await fetch(`${API}?endpoint=pedido_loja_status`, {method:'POST', body:fd});
    const d = await r.json();
    loading(false);
    if (d.success) carregarPedidos();
    else alert('Erro: ' + d.message);
}

async function confirmarPix(id) {
    showConfirmar('pix', id,
        `Confirmar recebimento do pagamento PIX do pedido <strong>#${id}</strong>?<br>
         <small class="text-muted">O status do pedido será alterado para <strong>Confirmado</strong>.</small>`,
        async () => {
            loading(true);
            const fd = new FormData(); fd.append('id', id);
            const r = await fetch(`${API}?endpoint=pedido_loja_pix_confirmar`, {method:'POST', body:fd});
            const d = await r.json();
            loading(false);
            if (d.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalDetalhes'))?.hide();
                carregarPedidos();
            } else alert('Erro: ' + d.message);
        }
    );
}

async function excluirPedido(id) {
    showConfirmar('deletar', id,
        `Excluir permanentemente o pedido <strong>#${id}</strong>?<br>
         <small class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Todos os itens do pedido também serão removidos.</small>`,
        async () => {
            loading(true);
            const fd = new FormData(); fd.append('id', id);
            const r = await fetch(`${API}?endpoint=pedido_loja_deletar`, {method:'POST', body:fd});
            const d = await r.json();
            loading(false);
            if (d.success) carregarPedidos();
            else alert('Erro: ' + d.message);
        }
    );
}

function esc(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Init
carregarPedidos();
</script>

<?php include 'footer.php'; ?>
