<?php require_once 'loja_config.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Farmácia Vida Saudável — Sua saúde em boas mãos</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
/* ══════════════════════════════════════════════════════════
   VARIÁVEIS & RESET
══════════════════════════════════════════════════════════ */
:root {
    --verde:      #1a7a4a;
    --verde-esc:  #0f5233;
    --verde-clr:  #e8f5ee;
    --verde-md:   #2da05e;
    --laranja:    #f5820d;
    --laranja-clr:#fff4e5;
    --cinza-bg:   #f6f7f9;
    --cinza-bord: #e2e6ea;
    --texto:      #1c2b3a;
    --texto-sub:  #5a6a7a;
    --branco:     #ffffff;
    --sombra-sm:  0 2px 8px rgba(0,0,0,.08);
    --sombra-md:  0 4px 20px rgba(0,0,0,.12);
    --r:          12px;
}
*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
html { scroll-behavior:smooth; }
body { font-family:'Nunito',sans-serif; background:var(--cinza-bg); color:var(--texto); }
a { text-decoration:none; color:inherit; }
img { max-width:100%; }
button { cursor:pointer; font-family:inherit; }

/* ══════════════════════════════════════════════════════════
   TOPBAR
══════════════════════════════════════════════════════════ */
.topbar {
    background:var(--verde-esc);
    color:#fff;
    font-size:.78rem;
    padding:6px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
}
.topbar a { color:#a8d8b8; }
.topbar a:hover { color:#fff; }

/* ══════════════════════════════════════════════════════════
   HEADER PRINCIPAL
══════════════════════════════════════════════════════════ */
.site-header {
    background:var(--verde);
    padding:14px 20px;
    display:flex;
    align-items:center;
    gap:16px;
    flex-wrap:wrap;
    position:sticky;
    top:0;
    z-index:100;
    box-shadow:0 2px 12px rgba(0,0,0,.2);
}

.logo-wrap { display:flex; align-items:center; gap:10px; flex-shrink:0; }
.logo-icon { font-size:2rem; color:#fff; }
.logo-text { line-height:1; }
.logo-text strong { display:block; font-size:1.15rem; font-weight:900; color:#fff; letter-spacing:-.3px; }
.logo-text span { font-size:.72rem; color:#a8d8b8; font-weight:600; }

/* Barra de busca */
.search-bar {
    flex:1;
    min-width:260px;
    display:flex;
    background:#fff;
    border-radius:30px;
    overflow:hidden;
    box-shadow:var(--sombra-sm);
}
.search-bar input {
    flex:1;
    border:none;
    outline:none;
    padding:11px 18px;
    font-size:.92rem;
    font-family:inherit;
    color:var(--texto);
}
.search-bar button {
    background:var(--laranja);
    border:none;
    color:#fff;
    padding:0 20px;
    font-size:1.1rem;
    transition:background .15s;
}
.search-bar button:hover { background:#d96f0a; }

/* Ações do header */
.header-actions { display:flex; align-items:center; gap:12px; flex-shrink:0; }
.btn-header {
    background:rgba(255,255,255,.15);
    border:1.5px solid rgba(255,255,255,.3);
    color:#fff;
    border-radius:30px;
    padding:8px 16px;
    font-size:.82rem;
    font-weight:700;
    display:flex;
    align-items:center;
    gap:6px;
    transition:background .15s;
    white-space:nowrap;
}
.btn-header:hover { background:rgba(255,255,255,.25); }
.btn-header.destaque { background:var(--laranja); border-color:var(--laranja); }
.btn-header.destaque:hover { background:#d96f0a; }

.cart-btn { position:relative; }
.cart-count {
    position:absolute; top:-6px; right:-6px;
    background:#e53935; color:#fff;
    border-radius:50%; width:18px; height:18px;
    font-size:.65rem; font-weight:800;
    display:flex; align-items:center; justify-content:center;
}

/* ══════════════════════════════════════════════════════════
   NAV CATEGORIAS
══════════════════════════════════════════════════════════ */
.cat-nav {
    background:#fff;
    border-bottom:1px solid var(--cinza-bord);
    padding:0 20px;
    display:flex;
    gap:0;
    overflow-x:auto;
    scrollbar-width:none;
}
.cat-nav::-webkit-scrollbar { display:none; }
.cat-nav a {
    padding:12px 18px;
    font-size:.82rem;
    font-weight:700;
    color:var(--texto-sub);
    white-space:nowrap;
    border-bottom:2px solid transparent;
    transition:color .15s, border-color .15s;
}
.cat-nav a:hover, .cat-nav a.ativo {
    color:var(--verde);
    border-bottom-color:var(--verde);
}

/* ══════════════════════════════════════════════════════════
   LAYOUT PRINCIPAL
══════════════════════════════════════════════════════════ */
.main-wrap { max-width:1400px; margin:0 auto; padding:24px 20px; }

/* ══════════════════════════════════════════════════════════
   BANNER CAROUSEL
══════════════════════════════════════════════════════════ */
.banner-section { margin-bottom:32px; }
.banner-track {
    position:relative;
    border-radius:var(--r);
    overflow:hidden;
    min-height:220px;
    box-shadow:var(--sombra-md);
}
.banner-slide {
    display:none;
    width:100%;
    min-height:220px;
    align-items:center;
    justify-content:center;
    text-align:center;
    padding:48px 60px;
    animation:fadeIn .4s ease;
}
.banner-slide.ativo { display:flex; }
.banner-slide img {
    position:absolute; top:0; left:0;
    width:100%; height:100%; object-fit:cover;
    z-index:0;
}
.banner-slide__content {
    position:relative; z-index:1;
    color:#fff;
}
.banner-slide__content h2 {
    font-size:2rem;
    font-weight:900;
    text-shadow:0 2px 10px rgba(0,0,0,.3);
    margin-bottom:8px;
}
.banner-slide__content p {
    font-size:1.05rem;
    opacity:.92;
    text-shadow:0 1px 4px rgba(0,0,0,.3);
}
@keyframes fadeIn { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:none; } }

.banner-dots {
    display:flex;
    justify-content:center;
    gap:6px;
    margin-top:10px;
}
.banner-dots button {
    width:8px; height:8px;
    border-radius:50%;
    border:none;
    background:var(--cinza-bord);
    transition:all .2s;
    padding:0;
}
.banner-dots button.ativo { background:var(--verde); width:20px; border-radius:4px; }

.banner-arrow {
    position:absolute; top:50%; transform:translateY(-50%);
    z-index:2; background:rgba(0,0,0,.35);
    border:none; color:#fff;
    width:40px; height:40px; border-radius:50%;
    font-size:1.1rem;
    display:flex; align-items:center; justify-content:center;
    transition:background .15s;
}
.banner-arrow:hover { background:rgba(0,0,0,.6); }
.banner-arrow.prev { left:12px; }
.banner-arrow.next { right:12px; }

/* ══════════════════════════════════════════════════════════
   SEÇÃO PROMOÇÃO RÁPIDA
══════════════════════════════════════════════════════════ */
.promo-strip {
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(180px,1fr));
    gap:12px;
    margin-bottom:32px;
}
.promo-card {
    background:#fff;
    border-radius:var(--r);
    padding:16px;
    display:flex;
    align-items:center;
    gap:12px;
    box-shadow:var(--sombra-sm);
    border:1px solid var(--cinza-bord);
    transition:box-shadow .2s, transform .15s;
    cursor:default;
}
.promo-card:hover { box-shadow:var(--sombra-md); transform:translateY(-2px); }
.promo-card__icon {
    font-size:1.8rem;
    width:48px; height:48px;
    border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0;
}
.promo-card p { font-size:.78rem; color:var(--texto-sub); margin:2px 0 0; }
.promo-card strong { font-size:.9rem; }

/* ══════════════════════════════════════════════════════════
   GRID DE PRODUTOS
══════════════════════════════════════════════════════════ */
.secao-titulo {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:16px;
}
.secao-titulo h2 {
    font-size:1.25rem;
    font-weight:800;
    color:var(--texto);
}
.secao-titulo h2 span { color:var(--verde); }
.secao-titulo a { font-size:.82rem; color:var(--verde); font-weight:700; }

.produtos-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));
    gap:16px;
    margin-bottom:32px;
}

.produto-card {
    background:#fff;
    border-radius:var(--r);
    overflow:hidden;
    box-shadow:var(--sombra-sm);
    border:1px solid var(--cinza-bord);
    display:flex;
    flex-direction:column;
    transition:box-shadow .2s, transform .15s;
    position:relative;
}
.produto-card:hover { box-shadow:var(--sombra-md); transform:translateY(-3px); }

.produto-card__foto {
    width:100%; height:160px;
    background:var(--cinza-bg);
    display:flex; align-items:center; justify-content:center;
    position:relative;
    overflow:hidden;
}
.produto-card__foto img { width:100%; height:100%; object-fit:cover; transition:transform .3s; }
.produto-card:hover .produto-card__foto img { transform:scale(1.05); }
.produto-card__foto-ph { font-size:3.5rem; color:#ccc; }

.badge-desc {
    position:absolute; top:10px; left:10px;
    background:#e53935; color:#fff;
    font-size:.68rem; font-weight:800;
    padding:3px 9px; border-radius:20px;
}
.badge-ctrl {
    position:absolute; top:10px; right:10px;
    background:#f5820d; color:#fff;
    font-size:.65rem; font-weight:700;
    padding:2px 7px; border-radius:20px;
}

.produto-card__body { padding:12px; flex:1; display:flex; flex-direction:column; }
.produto-card__nome {
    font-size:.88rem;
    font-weight:800;
    line-height:1.25;
    margin-bottom:3px;
    display:-webkit-box;
    -webkit-line-clamp:2;
    -webkit-box-orient:vertical;
    overflow:hidden;
}
.produto-card__fab { font-size:.72rem; color:var(--texto-sub); margin-bottom:8px; }
.produto-card__preco-wrap { margin-top:auto; }
.produto-card__preco-orig { font-size:.75rem; color:#999; text-decoration:line-through; }
.produto-card__preco { font-size:1.2rem; font-weight:900; color:var(--verde); }
.produto-card__preco.promo { color:#e53935; }

.produto-card__footer { padding:0 12px 12px; }
.btn-add-cart {
    width:100%;
    background:var(--verde);
    color:#fff;
    border:none;
    border-radius:8px;
    padding:9px;
    font-size:.85rem;
    font-weight:700;
    display:flex; align-items:center; justify-content:center; gap:6px;
    transition:background .15s, transform .1s;
}
.btn-add-cart:hover { background:var(--verde-esc); }
.btn-add-cart:active { transform:scale(.97); }
.btn-add-cart.esgotado { background:#bdbdbd; cursor:not-allowed; }

/* ══════════════════════════════════════════════════════════
   PAGINAÇÃO
══════════════════════════════════════════════════════════ */
.paginacao {
    display:flex;
    justify-content:center;
    gap:6px;
    margin-bottom:40px;
    flex-wrap:wrap;
}
.paginacao button {
    padding:8px 14px;
    border:1.5px solid var(--cinza-bord);
    background:#fff;
    border-radius:8px;
    font-size:.82rem;
    font-weight:700;
    color:var(--texto);
    transition:all .15s;
}
.paginacao button:hover { border-color:var(--verde); color:var(--verde); }
.paginacao button.ativo { background:var(--verde); border-color:var(--verde); color:#fff; }
.paginacao button:disabled { opacity:.4; cursor:not-allowed; }

/* ══════════════════════════════════════════════════════════
   EMPTY / LOADING
══════════════════════════════════════════════════════════ */
.estado-vazio {
    text-align:center;
    padding:60px 20px;
    color:var(--texto-sub);
    grid-column:1/-1;
}
.estado-vazio i { font-size:4rem; display:block; margin-bottom:12px; opacity:.3; }
.spinner {
    width:40px; height:40px;
    border:4px solid var(--cinza-bord);
    border-top-color:var(--verde);
    border-radius:50%;
    animation:spin .7s linear infinite;
    margin:60px auto;
    grid-column:1/-1;
}
@keyframes spin { to { transform:rotate(360deg); } }

/* ══════════════════════════════════════════════════════════
   DRAWER CARRINHO
══════════════════════════════════════════════════════════ */
.cart-overlay {
    position:fixed; inset:0;
    background:rgba(0,0,0,.45);
    z-index:200;
    opacity:0; pointer-events:none;
    transition:opacity .25s;
}
.cart-overlay.aberto { opacity:1; pointer-events:all; }

.cart-drawer {
    position:fixed; top:0; right:0;
    width:min(420px,100vw);
    height:100%;
    background:#fff;
    z-index:201;
    display:flex;
    flex-direction:column;
    transform:translateX(100%);
    transition:transform .3s cubic-bezier(.4,0,.2,1);
    box-shadow:-4px 0 30px rgba(0,0,0,.15);
}
.cart-drawer.aberto { transform:none; }

.cart-drawer__header {
    background:var(--verde);
    color:#fff;
    padding:18px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-shrink:0;
}
.cart-drawer__header h3 { font-size:1.1rem; font-weight:800; }
.cart-drawer__header button { background:none; border:none; color:#fff; font-size:1.3rem; }

.cart-drawer__body { flex:1; overflow-y:auto; padding:16px; }

.cart-item {
    display:flex;
    gap:12px;
    padding:12px 0;
    border-bottom:1px solid var(--cinza-bord);
}
.cart-item__foto {
    width:64px; height:64px;
    border-radius:8px;
    object-fit:cover;
    background:var(--cinza-bg);
    flex-shrink:0;
}
.cart-item__foto-ph {
    width:64px; height:64px;
    border-radius:8px;
    background:var(--cinza-bg);
    display:flex; align-items:center; justify-content:center;
    color:#ccc; font-size:1.6rem; flex-shrink:0;
}
.cart-item__info { flex:1; min-width:0; }
.cart-item__nome { font-size:.85rem; font-weight:700; margin-bottom:4px; }
.cart-item__preco { font-size:.82rem; color:var(--verde); font-weight:700; }
.cart-item__ctrl {
    display:flex; align-items:center; gap:6px; margin-top:8px;
}
.cart-item__ctrl button {
    width:28px; height:28px;
    border-radius:6px; border:none;
    background:var(--verde-clr); color:var(--verde);
    font-weight:800; font-size:1rem;
    display:flex; align-items:center; justify-content:center;
    transition:background .1s;
}
.cart-item__ctrl button:hover { background:#c8e6da; }
.cart-item__ctrl button.rm { background:#ffebee; color:#c62828; }
.cart-item__ctrl button.rm:hover { background:#ffcdd2; }
.cart-item__qtd { font-size:.88rem; font-weight:800; min-width:20px; text-align:center; }

.cart-drawer__footer {
    padding:16px;
    border-top:1px solid var(--cinza-bord);
    flex-shrink:0;
}
.cart-total {
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:var(--verde);
    color:#fff;
    border-radius:10px;
    padding:14px 18px;
    margin-bottom:12px;
}
.cart-total__label { font-size:.78rem; font-weight:600; opacity:.85; }
.cart-total__valor { font-size:1.5rem; font-weight:900; }
.btn-checkout {
    width:100%;
    background:var(--laranja);
    color:#fff;
    border:none;
    border-radius:10px;
    padding:13px;
    font-size:1rem;
    font-weight:800;
    transition:background .15s;
}
.btn-checkout:hover { background:#d96f0a; }
.btn-checkout:disabled { background:#bdbdbd; cursor:not-allowed; }
.cart-vazio {
    text-align:center;
    padding:60px 20px;
    color:var(--texto-sub);
}
.cart-vazio i { font-size:4rem; display:block; margin-bottom:8px; opacity:.3; }

/* ══════════════════════════════════════════════════════════
   MODAIS (LOGIN / REGISTRO / PEDIDO)
══════════════════════════════════════════════════════════ */
.modal-overlay {
    position:fixed; inset:0;
    background:rgba(0,0,0,.5);
    z-index:300;
    display:flex; align-items:center; justify-content:center;
    opacity:0; pointer-events:none;
    transition:opacity .25s;
    padding:16px;
}
.modal-overlay.aberto { opacity:1; pointer-events:all; }
.modal-box {
    background:#fff;
    border-radius:16px;
    width:100%; max-width:440px;
    max-height:90vh;
    overflow-y:auto;
    box-shadow:0 20px 60px rgba(0,0,0,.25);
    transform:scale(.95);
    transition:transform .25s;
}
.modal-overlay.aberto .modal-box { transform:none; }
.modal-box__header {
    background:var(--verde);
    color:#fff;
    padding:20px 24px;
    border-radius:16px 16px 0 0;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.modal-box__header h3 { font-size:1.1rem; font-weight:800; }
.modal-box__header button { background:none; border:none; color:#fff; font-size:1.4rem; }
.modal-box__body { padding:24px; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:.82rem; font-weight:700; margin-bottom:5px; color:var(--texto-sub); }
.form-group input, .form-group select {
    width:100%;
    padding:11px 14px;
    border:1.5px solid var(--cinza-bord);
    border-radius:8px;
    font-size:.92rem;
    font-family:inherit;
    outline:none;
    transition:border-color .15s;
}
.form-group input:focus { border-color:var(--verde); }
.btn-form {
    width:100%;
    background:var(--verde);
    color:#fff;
    border:none;
    border-radius:10px;
    padding:13px;
    font-size:1rem;
    font-weight:800;
    transition:background .15s;
    margin-top:4px;
}
.btn-form:hover { background:var(--verde-esc); }
.form-switch {
    text-align:center;
    margin-top:16px;
    font-size:.82rem;
    color:var(--texto-sub);
}
.form-switch a { color:var(--verde); font-weight:700; }
.feedback-msg {
    padding:10px 14px;
    border-radius:8px;
    font-size:.82rem;
    margin-bottom:14px;
    display:none;
}
.feedback-msg.erro { background:#ffebee; color:#c62828; display:block; }
.feedback-msg.ok   { background:#e8f5e9; color:#2e7d32; display:block; }

/* ══════════════════════════════════════════════════════════
   TOAST
══════════════════════════════════════════════════════════ */
.toast-wrap {
    position:fixed;
    bottom:24px; right:24px;
    z-index:400;
    display:flex;
    flex-direction:column;
    gap:8px;
}
.toast {
    background:var(--verde-esc);
    color:#fff;
    padding:12px 18px;
    border-radius:10px;
    font-size:.85rem;
    font-weight:700;
    box-shadow:var(--sombra-md);
    display:flex; align-items:center; gap:8px;
    animation:slideUp .3s ease;
    max-width:320px;
}
.toast.erro { background:#c62828; }
@keyframes slideUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:none; } }

/* ══════════════════════════════════════════════════════════
   FOOTER
══════════════════════════════════════════════════════════ */
.site-footer {
    background:var(--verde-esc);
    color:#a8d8b8;
    padding:48px 20px 24px;
    margin-top:40px;
}
.footer-grid {
    max-width:1400px;
    margin:0 auto;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:32px;
    margin-bottom:32px;
}
.footer-col h4 {
    color:#fff;
    font-size:.9rem;
    font-weight:800;
    margin-bottom:14px;
    letter-spacing:.5px;
    text-transform:uppercase;
}
.footer-col a {
    display:block;
    color:#a8d8b8;
    font-size:.82rem;
    margin-bottom:8px;
    transition:color .15s;
}
.footer-col a:hover { color:#fff; }
.footer-logo strong { font-size:1.2rem; font-weight:900; color:#fff; }
.footer-logo p { font-size:.78rem; margin-top:8px; line-height:1.5; }
.footer-bottom {
    max-width:1400px;
    margin:0 auto;
    border-top:1px solid rgba(255,255,255,.1);
    padding-top:20px;
    text-align:center;
    font-size:.75rem;
    opacity:.65;
}

/* ══════════════════════════════════════════════════════════
   RESPONSIVO
══════════════════════════════════════════════════════════ */
@media(max-width:640px) {
    .site-header { padding:10px 14px; gap:10px; }
    .topbar { display:none; }
    .logo-text strong { font-size:1rem; }
    .main-wrap { padding:16px 12px; }
    .produtos-grid { grid-template-columns:repeat(2,1fr); gap:10px; }
    .produto-card__foto { height:130px; }
    .banner-slide__content h2 { font-size:1.4rem; }
    .banner-slide { padding:32px 24px; }
}
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <span><i class="bi bi-telephone-fill"></i> (47) 9 9000-0000 &nbsp;|&nbsp; <i class="bi bi-whatsapp"></i> WhatsApp</span>
    <span>
        <i class="bi bi-clock"></i> Seg–Sex 8h–19h &nbsp;|&nbsp;
        <a href="#" onclick="abrirModal('login')">
            <i class="bi bi-person-circle"></i>
            <span id="topbar-nome"><?= clienteLogado() ? 'Olá, '.htmlspecialchars(clienteNome()) : 'Entrar / Cadastrar' ?></span>
        </a>
    </span>
</div>

<!-- HEADER -->
<header class="site-header">
    <div class="logo-wrap">
        <i class="bi bi-heart-pulse-fill logo-icon"></i>
        <div class="logo-text">
            <strong>Vida Saudável</strong>
            <span>Farmácia Online</span>
        </div>
    </div>

    <div class="search-bar">
        <input type="text" id="busca-input" placeholder="Busque por produto, medicamento, fabricante..." onkeydown="if(event.key==='Enter')buscar()">
        <button onclick="buscar()"><i class="bi bi-search"></i></button>
    </div>

    <div class="header-actions">
        <?php if(clienteLogado()): ?>
        <button class="btn-header" onclick="abrirModal('pedidos')">
            <i class="bi bi-bag-check"></i> Meus Pedidos
        </button>
        <button class="btn-header" onclick="logout()">
            <i class="bi bi-box-arrow-right"></i> Sair
        </button>
        <?php else: ?>
        <button class="btn-header" onclick="abrirModal('login')">
            <i class="bi bi-person-circle"></i> Entrar
        </button>
        <button class="btn-header destaque" onclick="abrirModal('registro')">
            <i class="bi bi-person-plus"></i> Cadastrar
        </button>
        <?php endif; ?>
        <button class="btn-header cart-btn" onclick="toggleCarrinho()">
            <i class="bi bi-cart3"></i> Carrinho
            <span class="cart-count" id="cart-count">0</span>
        </button>
    </div>
</header>

<!-- NAV CATEGORIAS -->
<nav class="cat-nav">
    <a href="#" class="ativo" onclick="filtrarCategoria('',this)">Todos</a>
    <a href="#" onclick="filtrarCategoria('Comum',this)"><i class="bi bi-capsule"></i> Medicamentos</a>
    <a href="#" onclick="filtrarCategoria('Dermocosméticos',this)"><i class="bi bi-stars"></i> Dermocosméticos</a>
    <a href="#" onclick="filtrarCategoria('Higiene',this)"><i class="bi bi-droplet"></i> Higiene</a>
    <a href="#" onclick="filtrarCategoria('Vitaminas',this)"><i class="bi bi-heart"></i> Vitaminas</a>
    <a href="#" onclick="filtrarCategoria('Beleza',this)"><i class="bi bi-flower1"></i> Beleza</a>
</nav>

<!-- CONTEÚDO PRINCIPAL -->
<div class="main-wrap">

    <!-- BANNER -->
    <section class="banner-section">
        <div class="banner-track" id="banner-track">
            <div class="banner-slide ativo" style="background:var(--verde);">
                <div class="banner-slide__content">
                    <h2>🌿 Sua saúde em boas mãos</h2>
                    <p>Medicamentos, higiene e beleza com os melhores preços</p>
                </div>
            </div>
        </div>
        <div class="banner-dots" id="banner-dots">
            <button class="ativo"></button>
        </div>
    </section>

    <!-- DESTAQUES RÁPIDOS -->
    <div class="promo-strip">
        <div class="promo-card">
            <div class="promo-card__icon" style="background:#e8f5ee;color:var(--verde);">
                <i class="bi bi-truck"></i>
            </div>
            <div>
                <strong>Retire na Loja</strong>
                <p>Peça online e retire hoje</p>
            </div>
        </div>
        <div class="promo-card">
            <div class="promo-card__icon" style="background:#fff4e5;color:var(--laranja);">
                <i class="bi bi-percent"></i>
            </div>
            <div>
                <strong>Promoções Diárias</strong>
                <p>Descontos exclusivos online</p>
            </div>
        </div>
        <div class="promo-card">
            <div class="promo-card__icon" style="background:#e3f2fd;color:#1976D2;">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <strong>100% Seguro</strong>
                <p>Produtos certificados ANVISA</p>
            </div>
        </div>
        <div class="promo-card">
            <div class="promo-card__icon" style="background:#fce4ec;color:#c2185b;">
                <i class="bi bi-headset"></i>
            </div>
            <div>
                <strong>Atendimento</strong>
                <p>Seg–Sex das 8h às 19h</p>
            </div>
        </div>
    </div>

    <!-- PRODUTOS -->
    <section>
        <div class="secao-titulo">
            <h2>Nossos <span>Produtos</span></h2>
            <span id="total-label" style="font-size:.82rem;color:var(--texto-sub);"></span>
        </div>
        <div class="produtos-grid" id="produtos-grid">
            <div class="spinner"></div>
        </div>
        <div class="paginacao" id="paginacao"></div>
    </section>
</div>

<!-- FOOTER -->
<footer class="site-footer">
    <div class="footer-grid">
        <div class="footer-col footer-logo">
            <strong><i class="bi bi-heart-pulse-fill"></i> Farmácia Vida Saudável</strong>
            <p>Cuidando da sua saúde e bem-estar com produtos de qualidade e preços acessíveis.</p>
        </div>
        <div class="footer-col">
            <h4>Institucional</h4>
            <a href="#">Sobre Nós</a>
            <a href="#">Nossa Missão</a>
            <a href="#">Trabalhe Conosco</a>
            <a href="#">Política de Privacidade</a>
        </div>
        <div class="footer-col">
            <h4>Atendimento</h4>
            <a href="#"><i class="bi bi-whatsapp"></i> WhatsApp</a>
            <a href="#"><i class="bi bi-envelope"></i> contato@farmavidasaudavel.com.br</a>
            <a href="#">Seg–Sex: 8h às 19h</a>
        </div>
        <div class="footer-col">
            <h4>Categorias</h4>
            <a href="#" onclick="filtrarCategoria('Comum',null)">Medicamentos</a>
            <a href="#" onclick="filtrarCategoria('Vitaminas',null)">Vitaminas</a>
            <a href="#" onclick="filtrarCategoria('Higiene',null)">Higiene Pessoal</a>
            <a href="#" onclick="filtrarCategoria('Beleza',null)">Beleza</a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© <?= date('Y') ?> Farmácia Vida Saudável. Todos os direitos reservados. As informações deste site não substituem orientação médica.</p>
    </div>
</footer>

<!-- ══ CARRINHO DRAWER ══ -->
<div class="cart-overlay" id="cart-overlay" onclick="fecharCarrinho()"></div>
<div class="cart-drawer" id="cart-drawer">
    <div class="cart-drawer__header">
        <h3><i class="bi bi-cart3"></i> Meu Carrinho</h3>
        <button onclick="fecharCarrinho()"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="cart-drawer__body" id="cart-body"></div>
    <div class="cart-drawer__footer">
        <div class="cart-total">
            <span class="cart-total__label">TOTAL</span>
            <span class="cart-total__valor" id="cart-total-val">R$ 0,00</span>
        </div>
        <button class="btn-checkout" id="btn-checkout" onclick="checkout()" disabled>
            <i class="bi bi-bag-check-fill"></i> Finalizar Pedido
        </button>
    </div>
</div>

<!-- ══ MODAL LOGIN ══ -->
<div class="modal-overlay" id="modal-login">
    <div class="modal-box">
        <div class="modal-box__header">
            <h3><i class="bi bi-person-circle"></i> Entrar na sua conta</h3>
            <button onclick="fecharModal('login')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-box__body">
            <div class="feedback-msg" id="fb-login"></div>
            <div class="form-group">
                <label>E-mail</label>
                <input type="email" id="login-email" placeholder="seu@email.com">
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" id="login-senha" placeholder="••••••" onkeydown="if(event.key==='Enter')fazerLogin()">
            </div>
            <button class="btn-form" onclick="fazerLogin()">Entrar</button>
            <div class="form-switch">
                Não tem conta? <a href="#" onclick="trocarModal('login','registro')">Cadastre-se grátis</a>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL REGISTRO ══ -->
<div class="modal-overlay" id="modal-registro">
    <div class="modal-box">
        <div class="modal-box__header">
            <h3><i class="bi bi-person-plus"></i> Criar sua conta</h3>
            <button onclick="fecharModal('registro')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-box__body">
            <div class="feedback-msg" id="fb-registro"></div>
            <div class="form-group">
                <label>Nome Completo *</label>
                <input type="text" id="reg-nome" placeholder="Seu nome completo">
            </div>
            <div class="form-group">
                <label>E-mail *</label>
                <input type="email" id="reg-email" placeholder="seu@email.com">
            </div>
            <div class="form-group">
                <label>CPF</label>
                <input type="text" id="reg-cpf" placeholder="000.000.000-00" maxlength="14" oninput="mascaraCPF(this)">
            </div>
            <div class="form-group">
                <label>Telefone</label>
                <input type="text" id="reg-tel" placeholder="(00) 00000-0000" maxlength="15" oninput="mascaraTel(this)">
            </div>
            <div class="form-group">
                <label>Senha * <small style="color:#999;">(mín. 6 caracteres)</small></label>
                <input type="password" id="reg-senha" placeholder="••••••">
            </div>
            <button class="btn-form" onclick="fazerRegistro()">Criar minha conta</button>
            <div class="form-switch">
                Já tem conta? <a href="#" onclick="trocarModal('registro','login')">Entrar</a>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL MEUS PEDIDOS ══ -->
<div class="modal-overlay" id="modal-pedidos">
    <div class="modal-box" style="max-width:560px;">
        <div class="modal-box__header">
            <h3><i class="bi bi-bag-check"></i> Meus Pedidos</h3>
            <button onclick="fecharModal('pedidos')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-box__body" id="pedidos-body">
            <div class="spinner" style="margin:30px auto;width:30px;height:30px;"></div>
        </div>
    </div>
</div>

<!-- ══ TOASTS ══ -->
<div class="toast-wrap" id="toast-wrap"></div>

<script>
// ════════════════════════════════════════════════════
// ESTADO
// ════════════════════════════════════════════════════
let carrinho    = JSON.parse(localStorage.getItem('fvs_cart') || '[]');
let paginaAtual = 1;
let totalPaginas= 1;
let buscaAtual  = '';
let catAtual    = '';
let banners     = [];
let bannerIdx   = 0;
let bannerTimer = null;

// ════════════════════════════════════════════════════
// INIT
// ════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    carregarBanners();
    carregarProdutos();
    atualizarCarrinho();
});

// ════════════════════════════════════════════════════
// BANNERS
// ════════════════════════════════════════════════════
async function carregarBanners() {
    try {
        const r = await fetch('loja_api.php?endpoint=banners');
        const d = await r.json();
        if (!d.success || !d.banners.length) return;
        banners = d.banners;
        renderBanners();
    } catch(e) {}
}

function renderBanners() {
    const track = document.getElementById('banner-track');
    const dots  = document.getElementById('banner-dots');
    track.innerHTML = '';
    dots.innerHTML  = '';

    banners.forEach((b, i) => {
        const slide = document.createElement('div');
        slide.className = 'banner-slide' + (i===0?' ativo':'');
        if (b.imagem_url) {
            slide.innerHTML = `<img src="${b.imagem_url}" alt="${b.titulo}">
                <div class="banner-slide__content">
                    <h2>${b.titulo}</h2>
                    ${b.descricao ? `<p>${b.descricao}</p>` : ''}
                </div>`;
        } else {
            slide.style.background = b.cor_fundo || 'var(--verde)';
            slide.innerHTML = `<div class="banner-slide__content">
                <h2>${b.titulo}</h2>
                ${b.descricao ? `<p>${b.descricao}</p>` : ''}
            </div>`;
        }
        track.appendChild(slide);

        const dot = document.createElement('button');
        if (i===0) dot.className = 'ativo';
        dot.onclick = () => irBanner(i);
        dots.appendChild(dot);
    });

    // Setas
    if (banners.length > 1) {
        track.insertAdjacentHTML('beforeend', `
            <button class="banner-arrow prev" onclick="irBanner(${banners.length}-1 < bannerIdx-1 ? bannerIdx-1 : bannerIdx-1 < 0 ? ${banners.length}-1 : bannerIdx-1)"><i class="bi bi-chevron-left"></i></button>
            <button class="banner-arrow next" onclick="proximoBanner()"><i class="bi bi-chevron-right"></i></button>
        `);
        bannerTimer = setInterval(proximoBanner, 5000);
    }
}

function proximoBanner() { irBanner((bannerIdx + 1) % banners.length); }
function irBanner(idx) {
    const slides = document.querySelectorAll('.banner-slide');
    const dots   = document.querySelectorAll('.banner-dots button');
    if (!slides.length) return;
    slides[bannerIdx].classList.remove('ativo');
    dots[bannerIdx]?.classList.remove('ativo');
    bannerIdx = (idx + slides.length) % slides.length;
    slides[bannerIdx].classList.add('ativo');
    dots[bannerIdx]?.classList.add('ativo');
    if (bannerTimer) { clearInterval(bannerTimer); bannerTimer = setInterval(proximoBanner,5000); }
}

// ════════════════════════════════════════════════════
// PRODUTOS
// ════════════════════════════════════════════════════
async function carregarProdutos(pagina=1) {
    paginaAtual = pagina;
    const grid  = document.getElementById('produtos-grid');
    grid.innerHTML = '<div class="spinner"></div>';

    const params = new URLSearchParams({
        endpoint:'produtos', q:buscaAtual, categoria:catAtual, pagina
    });
    try {
        const r = await fetch('loja_api.php?'+params);
        const d = await r.json();
        totalPaginas = d.paginas || 1;

        document.getElementById('total-label').textContent =
            d.total ? `${d.total} produto(s) encontrado(s)` : '';

        if (!d.produtos?.length) {
            grid.innerHTML = `<div class="estado-vazio">
                <i class="bi bi-search"></i>
                <p>Nenhum produto encontrado.</p>
            </div>`;
            document.getElementById('paginacao').innerHTML = '';
            return;
        }
        grid.innerHTML = d.produtos.map(p => cardProduto(p)).join('');
        renderPaginacao();
    } catch(e) {
        grid.innerHTML = '<div class="estado-vazio"><i class="bi bi-exclamation-triangle"></i><p>Erro ao carregar produtos.</p></div>';
    }
}

function cardProduto(p) {
    const temDesc    = p.desconto > 0;
    const precoFmt   = fmtBRL(p.preco_venda);
    const origFmt    = temDesc ? fmtBRL(p.preco_original) : '';
    const esgotado   = p.estoque <= 0;
    const fotoEl     = p.foto_url
        ? `<img src="${p.foto_url}" alt="${p.nome}" loading="lazy">`
        : `<i class="bi bi-capsule produto-card__foto-ph"></i>`;

    return `<div class="produto-card">
        <div class="produto-card__foto">
            ${fotoEl}
            ${temDesc ? `<span class="badge-desc">-${p.desconto}%</span>` : ''}
            ${p.categoria==='Controlado' ? '<span class="badge-ctrl">Controlado</span>' : ''}
        </div>
        <div class="produto-card__body">
            <p class="produto-card__nome">${p.nome}</p>
            <p class="produto-card__fab">${p.fabricante || ''}</p>
            <div class="produto-card__preco-wrap">
                ${temDesc ? `<div class="produto-card__preco-orig">R$ ${origFmt}</div>` : ''}
                <div class="produto-card__preco${temDesc?' promo':''}">R$ ${precoFmt}</div>
            </div>
        </div>
        <div class="produto-card__footer">
            <button class="btn-add-cart${esgotado?' esgotado':''}"
                onclick="adicionarCarrinho(${JSON.stringify(p).replace(/"/g,'&quot;')})"
                ${esgotado?'disabled':''}>
                <i class="bi bi-${esgotado?'x-circle':'cart-plus'}"></i>
                ${esgotado?'Esgotado':'Adicionar'}
            </button>
        </div>
    </div>`;
}

function renderPaginacao() {
    const el = document.getElementById('paginacao');
    if (totalPaginas <= 1) { el.innerHTML=''; return; }
    let html = '';
    if (paginaAtual > 1) html += `<button onclick="carregarProdutos(${paginaAtual-1})"><i class="bi bi-chevron-left"></i></button>`;
    for (let i=1; i<=totalPaginas; i++) {
        if (i===1 || i===totalPaginas || Math.abs(i-paginaAtual)<=1)
            html += `<button class="${i===paginaAtual?'ativo':''}" onclick="carregarProdutos(${i})">${i}</button>`;
        else if (Math.abs(i-paginaAtual)===2)
            html += `<button disabled>…</button>`;
    }
    if (paginaAtual < totalPaginas) html += `<button onclick="carregarProdutos(${paginaAtual+1})"><i class="bi bi-chevron-right"></i></button>`;
    el.innerHTML = html;
}

function buscar() {
    buscaAtual = document.getElementById('busca-input').value.trim();
    carregarProdutos(1);
}

function filtrarCategoria(cat, el) {
    catAtual = cat;
    document.querySelectorAll('.cat-nav a').forEach(a => a.classList.remove('ativo'));
    if (el) el.classList.add('ativo');
    carregarProdutos(1);
}

// ════════════════════════════════════════════════════
// CARRINHO
// ════════════════════════════════════════════════════
function adicionarCarrinho(produto) {
    const idx = carrinho.findIndex(i => i.id === produto.id);
    if (idx >= 0) carrinho[idx].quantidade++;
    else carrinho.push({ ...produto, quantidade:1 });
    salvarCarrinho();
    atualizarCarrinho();
    toast(`✓ ${produto.nome} adicionado ao carrinho`);
}

function alterarQtdCarrinho(idx, delta) {
    carrinho[idx].quantidade += delta;
    if (carrinho[idx].quantidade <= 0) carrinho.splice(idx,1);
    salvarCarrinho(); atualizarCarrinho();
}

function removerCarrinho(idx) {
    carrinho.splice(idx,1);
    salvarCarrinho(); atualizarCarrinho();
}

function salvarCarrinho() { localStorage.setItem('fvs_cart', JSON.stringify(carrinho)); }

function atualizarCarrinho() {
    const total   = carrinho.reduce((s,i) => s + i.preco_venda*i.quantidade, 0);
    const qtdTotal= carrinho.reduce((s,i) => s + i.quantidade, 0);

    document.getElementById('cart-count').textContent = qtdTotal;
    document.getElementById('cart-total-val').textContent = 'R$ '+fmtBRL(total);
    document.getElementById('btn-checkout').disabled = carrinho.length === 0;

    const body = document.getElementById('cart-body');
    if (!carrinho.length) {
        body.innerHTML = `<div class="cart-vazio">
            <i class="bi bi-cart-x"></i><p>Seu carrinho está vazio</p>
            <small>Adicione produtos para continuar</small>
        </div>`;
        return;
    }

    body.innerHTML = carrinho.map((item,i) => `
        <div class="cart-item">
            ${item.foto_url
                ? `<img class="cart-item__foto" src="${item.foto_url}" alt="${item.nome}">`
                : `<div class="cart-item__foto-ph"><i class="bi bi-capsule"></i></div>`}
            <div class="cart-item__info">
                <p class="cart-item__nome">${item.nome}</p>
                <p class="cart-item__preco">R$ ${fmtBRL(item.preco_venda)} cada</p>
                <div class="cart-item__ctrl">
                    <button onclick="alterarQtdCarrinho(${i},-1)">−</button>
                    <span class="cart-item__qtd">${item.quantidade}</span>
                    <button onclick="alterarQtdCarrinho(${i},1)">+</button>
                    <button class="rm" onclick="removerCarrinho(${i})"><i class="bi bi-trash3"></i></button>
                    <span style="margin-left:auto;font-weight:800;color:var(--verde)">
                        R$ ${fmtBRL(item.preco_venda * item.quantidade)}
                    </span>
                </div>
            </div>
        </div>`).join('');
}

function toggleCarrinho() {
    document.getElementById('cart-drawer').classList.toggle('aberto');
    document.getElementById('cart-overlay').classList.toggle('aberto');
}
function fecharCarrinho() {
    document.getElementById('cart-drawer').classList.remove('aberto');
    document.getElementById('cart-overlay').classList.remove('aberto');
}

// ════════════════════════════════════════════════════
// CHECKOUT
// ════════════════════════════════════════════════════
async function checkout() {
    <?php if(!clienteLogado()): ?>
    fecharCarrinho();
    abrirModal('login');
    toast('Faça login para finalizar seu pedido', true);
    return;
    <?php endif; ?>

    if (!carrinho.length) return;
    const itens = carrinho.map(i=>({produto_id:i.id,quantidade:i.quantidade,preco:i.preco_venda}));
    const fd = new FormData();
    fd.append('itens', JSON.stringify(itens));

    try {
        const r = await fetch('loja_api.php?endpoint=pedido_criar',{method:'POST',body:fd});
        const d = await r.json();
        if (d.success) {
            carrinho = []; salvarCarrinho(); atualizarCarrinho(); fecharCarrinho();
            toast(`🎉 Pedido #${d.pedido_id} realizado com sucesso!`);
        } else {
            toast(d.message, true);
        }
    } catch(e) { toast('Erro ao finalizar pedido.', true); }
}

// ════════════════════════════════════════════════════
// AUTH
// ════════════════════════════════════════════════════
async function fazerLogin() {
    const email = document.getElementById('login-email').value;
    const senha = document.getElementById('login-senha').value;
    const fb    = document.getElementById('fb-login');
    fb.className = 'feedback-msg';

    const fd = new FormData();
    fd.append('email',email); fd.append('senha',senha);
    const r = await fetch('loja_api.php?endpoint=login',{method:'POST',body:fd});
    const d = await r.json();

    if (d.success) { location.reload(); }
    else { fb.textContent = d.message; fb.className = 'feedback-msg erro'; }
}

async function fazerRegistro() {
    const fb = document.getElementById('fb-registro');
    fb.className = 'feedback-msg';

    const fd = new FormData();
    fd.append('nome',    document.getElementById('reg-nome').value);
    fd.append('email',   document.getElementById('reg-email').value);
    fd.append('cpf',     document.getElementById('reg-cpf').value);
    fd.append('telefone',document.getElementById('reg-tel').value);
    fd.append('senha',   document.getElementById('reg-senha').value);

    const r = await fetch('loja_api.php?endpoint=registrar',{method:'POST',body:fd});
    const d = await r.json();

    if (d.success) { location.reload(); }
    else { fb.textContent = d.message; fb.className = 'feedback-msg erro'; }
}

async function logout() {
    await fetch('loja_api.php?endpoint=logout',{method:'POST'});
    location.reload();
}

// ════════════════════════════════════════════════════
// MEUS PEDIDOS
// ════════════════════════════════════════════════════
async function carregarPedidos() {
    const body = document.getElementById('pedidos-body');
    const r = await fetch('loja_api.php?endpoint=meus_pedidos');
    const d = await r.json();
    if (!d.success || !d.pedidos.length) {
        body.innerHTML = '<div class="estado-vazio" style="grid-column:unset;"><i class="bi bi-bag-x"></i><p>Nenhum pedido ainda.</p></div>';
        return;
    }
    const statusCor = {pendente:'#f5820d', confirmado:'#2e7d32', cancelado:'#c62828'};
    body.innerHTML = d.pedidos.map(p=>`
        <div style="padding:12px 0;border-bottom:1px solid #eee;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                <strong>#${p.id}</strong>
                <span style="background:${statusCor[p.status]||'#999'};color:#fff;padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:700;">${p.status.toUpperCase()}</span>
            </div>
            <div style="font-size:.78rem;color:#666;">${new Date(p.criado_em).toLocaleDateString('pt-BR',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'})}</div>
            <div style="font-size:.8rem;color:#444;margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${p.produtos_nomes}</div>
            <div style="font-weight:800;color:var(--verde);margin-top:4px;">Total: R$ ${fmtBRL(p.total)}</div>
        </div>`).join('');
}

// ════════════════════════════════════════════════════
// MODAIS
// ════════════════════════════════════════════════════
function abrirModal(id) {
    document.getElementById('modal-'+id).classList.add('aberto');
    if (id==='pedidos') carregarPedidos();
}
function fecharModal(id) { document.getElementById('modal-'+id).classList.remove('aberto'); }
function trocarModal(fechar,abrir) { fecharModal(fechar); abrirModal(abrir); }

document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', function(e) {
        if (e.target===this) this.classList.remove('aberto');
    });
});

// ════════════════════════════════════════════════════
// UTILS
// ════════════════════════════════════════════════════
function fmtBRL(v) {
    return Number(v).toFixed(2).replace('.',',').replace(/\B(?=(\d{3})+(?!\d))/g,'.');
}

function toast(msg, erro=false) {
    const wrap = document.getElementById('toast-wrap');
    const el   = document.createElement('div');
    el.className = 'toast' + (erro?' erro':'');
    el.innerHTML = `<i class="bi bi-${erro?'exclamation-circle':'check-circle-fill'}"></i> ${msg}`;
    wrap.appendChild(el);
    setTimeout(()=>el.remove(), 3500);
}

function mascaraCPF(input) {
    let v=input.value.replace(/\D/g,'').substring(0,11);
    if(v.length>9) v=v.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,2})/,'$1.$2.$3-$4');
    else if(v.length>6) v=v.replace(/^(\d{3})(\d{3})(\d{0,3})/,'$1.$2.$3');
    else if(v.length>3) v=v.replace(/^(\d{3})(\d{0,3})/,'$1.$2');
    input.value=v;
}
function mascaraTel(input) {
    let v=input.value.replace(/\D/g,'').substring(0,11);
    if(v.length>10) v=v.replace(/^(\d{2})(\d{5})(\d{4})/,'($1) $2-$3');
    else if(v.length>6) v=v.replace(/^(\d{2})(\d{4})(\d{0,4})/,'($1) $2-$3');
    else if(v.length>2) v=v.replace(/^(\d{2})(\d{0,5})/,'($1) $2');
    input.value=v;
}
</script>
</body>
</html>
