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
<nav class="cat-nav" id="cat-nav">
    <a href="#" class="ativo" onclick="filtrarCategoria('',this)" data-cat="">Todos</a>
    <!-- categorias carregadas via JS -->
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
            <div id="footer-cats">
                <a href="#" onclick="filtrarCategoria('Comum',null);return false;">Medicamentos</a>
                <a href="#" onclick="filtrarCategoria('Vitaminas',null);return false;">Vitaminas</a>
                <a href="#" onclick="filtrarCategoria('Higiene',null);return false;">Higiene</a>
                <a href="#" onclick="filtrarCategoria('Beleza',null);return false;">Beleza</a>
            </div>
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

<!-- ══ MODAL FORMA DE PAGAMENTO ══ -->
<div class="modal-overlay" id="modal-pagamento">
    <div class="modal-box" style="max-width:440px;">
        <div class="modal-box__header">
            <h3><i class="bi bi-credit-card"></i> Como deseja pagar?</h3>
            <button onclick="fecharModal('pagamento')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-box__body" style="padding:20px;">
            <p style="color:#666;font-size:.84rem;margin-bottom:16px;text-align:center;">Selecione a forma de pagamento</p>
            <div style="display:flex;flex-direction:column;gap:10px;">

                <!-- PIX -->
                <button class="pgto-btn pgto-pix-btn" onclick="selecionarPagamento('pix')">
                    <div class="pix-icon-wrap">
                        <svg viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" class="pix-svg-icon">
                          <defs>
                            <linearGradient id="pixGrad" x1="0" y1="0" x2="44" y2="44" gradientUnits="userSpaceOnUse">
                              <stop offset="0%" stop-color="#32d583"/>
                              <stop offset="100%" stop-color="#0a8a4a"/>
                            </linearGradient>
                          </defs>
                          <rect width="44" height="44" rx="12" fill="url(#pixGrad)"/>
                          <path d="M22 8 L28.5 14.5 L22 21 L15.5 14.5 Z" fill="white" opacity="0.95"/>
                          <path d="M35 21 L28.5 14.5 L22 21 L28.5 27.5 Z" fill="white" opacity="0.95"/>
                          <path d="M22 34 L28.5 27.5 L22 21 L15.5 27.5 Z" fill="white" opacity="0.95"/>
                          <path d="M9 21 L15.5 14.5 L22 21 L15.5 27.5 Z" fill="white" opacity="0.95"/>
                        </svg>
                    </div>
                    <div class="pgto-info">
                        <span class="pgto-titulo pix-label">PIX</span>
                        <span class="pgto-sub">Pagamento instantâneo · QR Code na hora</span>
                    </div>
                    <span class="pix-badge-instant">Instantâneo</span>
                    <i class="bi bi-chevron-right pix-chev"></i>
                </button>

                <!-- Cartão de Crédito -->
                <button class="pgto-btn" onclick="selecionarPagamento('cartao')" style="border-color:#1565c0;background:#e8f0fe;">
                    <i class="bi bi-credit-card-2-front-fill" style="font-size:1.7rem;color:#1565c0;width:44px;text-align:center;flex-shrink:0;"></i>
                    <div class="pgto-info">
                        <span class="pgto-titulo" style="color:#1565c0;">Cartão de Crédito</span>
                        <span class="pgto-sub">Visa, Mastercard, Elo, Hipercard · até 12x</span>
                    </div>
                    <i class="bi bi-chevron-right" style="color:#1565c0;margin-left:auto;"></i>
                </button>

                <!-- Boleto -->
                <button class="pgto-btn" onclick="selecionarPagamento('boleto')" style="border-color:#555;background:#f5f5f5;">
                    <i class="bi bi-upc-scan" style="font-size:1.7rem;color:#444;width:44px;text-align:center;flex-shrink:0;"></i>
                    <div class="pgto-info">
                        <span class="pgto-titulo" style="color:#333;">Boleto Bancário</span>
                        <span class="pgto-sub">Vencimento em 3 dias · pague em bancos e lotéricas</span>
                    </div>
                    <i class="bi bi-chevron-right" style="color:#777;margin-left:auto;"></i>
                </button>

                <!-- Retirada -->
                <button class="pgto-btn" onclick="selecionarPagamento('retirada')" style="border-color:#bdbdbd;background:#fafafa;">
                    <i class="bi bi-shop" style="font-size:1.7rem;color:#777;width:44px;text-align:center;flex-shrink:0;"></i>
                    <div class="pgto-info">
                        <span class="pgto-titulo" style="color:#444;">Pagar na Retirada</span>
                        <span class="pgto-sub">Dinheiro ou cartão ao retirar na farmácia</span>
                    </div>
                    <i class="bi bi-chevron-right" style="color:#aaa;margin-left:auto;"></i>
                </button>

            </div>
            <div id="fb-pagamento" style="margin-top:12px;"></div>
        </div>
    </div>
</div>
<style>
.pgto-btn {
    display:flex;align-items:center;gap:14px;
    border:2px solid #ddd;border-radius:12px;
    padding:14px 16px;cursor:pointer;
    transition:filter .15s, transform .1s, box-shadow .15s;text-align:left;
    font-family:inherit;width:100%;background:#fff;
}
.pgto-btn:hover   { filter:brightness(.97); transform:translateY(-1px); box-shadow:0 4px 14px rgba(0,0,0,.1); }
.pgto-btn:active  { transform:translateY(0); }
.pgto-info        { display:flex;flex-direction:column;gap:2px;flex:1; }
.pgto-titulo      { font-weight:800;font-size:.93rem; }
.pgto-sub         { font-size:.73rem;color:#666; }

/* ── Botão PIX moderno ── */
.pgto-pix-btn {
    border-color: #1daa5c;
    background: linear-gradient(135deg, #f0fdf6 0%, #e6f9ef 100%);
    position: relative;
    overflow: hidden;
}
.pgto-pix-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(50,213,131,.08) 0%, rgba(10,138,74,.12) 100%);
    opacity: 0;
    transition: opacity .2s;
}
.pgto-pix-btn:hover::before { opacity: 1; }
.pgto-pix-btn:hover { filter: none; box-shadow: 0 6px 20px rgba(29,170,92,.25); border-color:#0a8a4a; }

.pix-icon-wrap {
    flex-shrink: 0;
    width: 44px; height: 44px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(10,138,74,.35);
    transition: transform .2s, box-shadow .2s;
}
.pgto-pix-btn:hover .pix-icon-wrap {
    transform: scale(1.06) rotate(-3deg);
    box-shadow: 0 6px 16px rgba(10,138,74,.4);
}
.pix-svg-icon { width: 44px; height: 44px; display: block; }

.pix-label {
    color: #0a6e3b;
    font-size: 1rem;
    letter-spacing: .2px;
}

.pix-badge-instant {
    flex-shrink: 0;
    font-size: .65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .5px;
    background: linear-gradient(135deg, #32d583, #0a8a4a);
    color: #fff;
    padding: 3px 8px;
    border-radius: 20px;
    margin-left: auto;
    white-space: nowrap;
    box-shadow: 0 2px 6px rgba(10,138,74,.3);
    animation: pixPulse 2.4s ease-in-out infinite;
}
@keyframes pixPulse {
    0%, 100% { box-shadow: 0 2px 6px rgba(10,138,74,.3); }
    50%       { box-shadow: 0 2px 12px rgba(10,138,74,.55); }
}

.pix-chev {
    color: #1daa5c;
    margin-left: 6px;
    font-size: .9rem;
    transition: transform .2s;
}
.pgto-pix-btn:hover .pix-chev { transform: translateX(3px); }
</style>

<!-- ══ MODAL CARTÃO DE CRÉDITO ══ -->
<div class="modal-overlay" id="modal-cartao">
    <div class="modal-box" style="max-width:480px;">
        <div class="modal-box__header" style="background:linear-gradient(135deg,#1565c0,#0d47a1);">
            <h3><i class="bi bi-credit-card-2-front-fill"></i> Cartão de Crédito</h3>
            <button onclick="voltarPagamento('cartao')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-box__body" style="padding:20px 22px;" id="cartao-body">
            <!-- conteúdo injetado via JS -->
        </div>
    </div>
</div>
<style>
/* Cartão visual */
.card-preview {
    width:100%;max-width:340px;height:200px;border-radius:16px;margin:0 auto 20px;
    background:linear-gradient(135deg,#1565c0,#42a5f5);
    color:#fff;padding:24px;box-sizing:border-box;
    display:flex;flex-direction:column;justify-content:space-between;
    box-shadow:0 8px 24px rgba(21,101,192,.35);position:relative;overflow:hidden;
    font-family:'Courier New',monospace;
}
.card-preview::before {
    content:'';position:absolute;top:-40px;right:-40px;
    width:180px;height:180px;border-radius:50%;
    background:rgba(255,255,255,.1);
}
.card-preview .card-chip   { width:38px;height:28px;background:#f0c040;border-radius:5px;margin-bottom:18px; }
.card-preview .card-number { font-size:1.15rem;letter-spacing:3px;margin-bottom:18px; }
.card-preview .card-bot    { display:flex;justify-content:space-between;font-size:.75rem;opacity:.85; }
.bandeira-icon             { font-size:1.6rem; }
/* Tabs cartão */
.cartao-tabs { display:flex;gap:0;margin-bottom:20px;border:1.5px solid #1565c0;border-radius:10px;overflow:hidden; }
.cartao-tab  { flex:1;padding:9px;text-align:center;font-size:.82rem;font-weight:700;cursor:pointer;
               background:#fff;color:#1565c0;border:none;font-family:inherit;transition:background .15s; }
.cartao-tab.ativo { background:#1565c0;color:#fff; }
/* Grid 2 colunas */
.form-row { display:flex;gap:12px; }
.form-row .form-group { flex:1; }
/* Cartões salvos */
.cartao-salvo {
    display:flex;align-items:center;gap:12px;
    border:2px solid #e0e0e0;border-radius:10px;
    padding:12px 14px;cursor:pointer;
    transition:border-color .15s, background .15s;
    margin-bottom:8px;background:#fff;
    font-family:inherit;width:100%;text-align:left;
}
.cartao-salvo:hover, .cartao-salvo.selecionado { border-color:#1565c0;background:#e8f0fe; }
.cartao-salvo .cs-info  { flex:1; }
.cartao-salvo .cs-nome  { font-weight:700;font-size:.87rem;color:#1565c0; }
.cartao-salvo .cs-sub   { font-size:.75rem;color:#666;margin-top:2px; }
.cartao-salvo .cs-del   { background:none;border:none;color:#e53935;font-size:1.1rem;cursor:pointer;padding:4px; }
</style>

<!-- ══ MODAL BOLETO ══ -->
<div class="modal-overlay" id="modal-boleto">
    <div class="modal-box" style="max-width:520px;">
        <div class="modal-box__header" style="background:linear-gradient(135deg,#424242,#212121);">
            <h3><i class="bi bi-upc-scan"></i> Boleto Bancário</h3>
            <button onclick="voltarPagamento('boleto')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-box__body" style="padding:24px;" id="boleto-body">
            <div class="spinner" style="margin:30px auto;width:36px;height:36px;border-color:#424242;border-top-color:transparent;"></div>
        </div>
    </div>
</div>
<style>
#boleto-body .boleto-valor    { font-size:1.9rem;font-weight:800;color:#212121;margin:4px 0 20px; }
#boleto-body .boleto-linha    { background:#f5f5f5;border:1.5px dashed #999;border-radius:8px;
                                padding:12px 14px;font-family:'Courier New',monospace;font-size:.78rem;
                                word-break:break-all;color:#333;cursor:pointer;transition:background .2s;margin-bottom:10px; }
#boleto-body .boleto-linha:hover { background:#e8f5e9; }
#boleto-body .btn-copiar-boleto { width:100%;background:#333;color:#fff;border:none;border-radius:8px;
                                  padding:11px;font-weight:700;font-size:.9rem;cursor:pointer;margin-bottom:12px; }
#boleto-body .btn-copiar-boleto:hover { background:#111; }
#boleto-body .barras { display:flex;align-items:flex-end;gap:1px;height:60px;margin:14px auto;width:280px;justify-content:center; }
#boleto-body .barras span { display:inline-block;background:#000;width:2px; }
#boleto-body .boleto-info  { background:#fffde7;border:1px solid #f9a825;border-radius:8px;
                              padding:12px 14px;font-size:.78rem;color:#555;line-height:1.7;margin-top:12px; }
#boleto-body .boleto-pedido { font-size:.8rem;color:#888;margin-bottom:4px; }
</style>

<!-- ══ MODAL PAYPAL LOADING ══ -->
<!-- ══ MODAL PIX ══ -->
<div class="modal-overlay" id="modal-pix">
    <div class="modal-box" style="max-width:480px;">
        <div class="modal-box__header" style="background:linear-gradient(135deg,#1daa5c,#128446);color:#fff;">
            <h3><i class="bi bi-qr-code"></i> Pagar com PIX</h3>
            <button onclick="tentarFecharPix()" style="color:#fff;"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-box__body" id="pix-body" style="text-align:center;padding:28px 20px;">
            <div class="spinner" style="margin:30px auto;width:36px;height:36px;border-color:#1daa5c;border-top-color:transparent;"></div>
            <p style="color:#666;margin-top:12px;">Gerando QR Code...</p>
        </div>
    </div>
</div>
<style>
#pix-body .pix-valor    { font-size:2rem;font-weight:800;color:#128446;margin:4px 0 18px; }
#pix-body .pix-qr       { display:inline-block;padding:14px;background:#fff;border:2px solid #e0e0e0;border-radius:12px;margin-bottom:16px; }
#pix-body .pix-copiacola{ width:100%;background:#f5f5f5;border:1px dashed #aaa;border-radius:8px;
                           padding:10px 12px;font-size:.72rem;word-break:break-all;
                           color:#333;cursor:pointer;text-align:left;transition:background .2s; }
#pix-body .pix-copiacola:hover { background:#e8f5e9; }
#pix-body .btn-copiar   { margin-top:10px;width:100%;background:#1daa5c;color:#fff;border:none;
                           border-radius:8px;padding:11px;font-weight:700;font-size:.9rem;cursor:pointer; }
#pix-body .btn-copiar:hover { background:#128446; }
#pix-body .pix-instrucoes{ font-size:.78rem;color:#666;line-height:1.6;margin-top:14px;text-align:left; }
#pix-body .pix-pedido   { font-size:.8rem;color:#888;margin-bottom:4px; }
#pix-body .pix-ok-btn   { width:100%;margin-top:18px;background:#e8f5e9;color:#1b5e20;
                           border:1.5px solid #1daa5c;border-radius:8px;padding:10px;
                           font-weight:700;cursor:pointer; }
</style>

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

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// ════════════════════════════════════════════════════
// ESTADO
const CLIENTE_LOGADO = <?= clienteLogado() ? 'true' : 'false' ?>;
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
    carregarCategorias();
    carregarBanners();
    carregarProdutos();
    atualizarCarrinho();

    // Delegated click for product cards (data-produto)
    document.getElementById('produtos-grid').addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-add-cart');
        if (!btn || btn.disabled) return;
        try {
            const produto = JSON.parse(decodeURIComponent(btn.dataset.produto));
            adicionarCarrinho(produto);
        } catch(err) { console.error('Erro ao parsear produto', err); }
    });
});

// ════════════════════════════════════════════════════
// CATEGORIAS
// ════════════════════════════════════════════════════
async function carregarCategorias() {
    try {
        const r = await fetch('loja_api.php?endpoint=categorias');
        const d = await r.json();
        if (!d.success || !d.categorias.length) return;

        const nav = document.getElementById('cat-nav');
        // Remove links antigos exceto "Todos"
        nav.querySelectorAll('a[data-cat]').forEach(a => { if (a.dataset.cat !== '') a.remove(); });

        d.categorias.forEach(c => {
            const a = document.createElement('a');
            a.href = '#';
            a.dataset.cat = c.nome;
            a.innerHTML = `<i class="bi ${c.icone || 'bi-tag'}"></i> ${c.nome}`;
            a.onclick = function(e) { e.preventDefault(); filtrarCategoria(c.nome, this); };
            nav.appendChild(a);
        });

        // Atualiza links do footer
        const footerCats = document.getElementById('footer-cats');
        if (footerCats) {
            footerCats.innerHTML = d.categorias.slice(0,6).map(c =>
                `<a href="#" onclick="filtrarCategoria('${c.nome.replace(/'/g,"\\'")}',null);return false;">
                    <i class="bi ${c.icone||'bi-tag'}"></i> ${c.nome}
                </a>`
            ).join('');
        }
    } catch(e) { console.warn('Categorias: ', e); }
}

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
            <button class="banner-arrow prev" onclick="anteriorBanner()"><i class="bi bi-chevron-left"></i></button>
            <button class="banner-arrow next" onclick="proximoBanner()"><i class="bi bi-chevron-right"></i></button>
        `);
        bannerTimer = setInterval(proximoBanner, 5000);
    }
}

function proximoBanner() { irBanner((bannerIdx + 1) % banners.length); }
function anteriorBanner() { irBanner((bannerIdx - 1 + banners.length) % banners.length); }
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
                data-produto="${encodeURIComponent(JSON.stringify(p))}"
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
// ── Estado do PIX ──────────────────────────────────────────────────
let pixPedidoId   = null;  // ID do pedido aguardando pagamento
let pixConfirmado = false;  // true somente quando o usuário clica "Já paguei"
let _ultimoPix    = null;   // cache dos dados para restaurar o QR Code
let _itensCheckout = [];    // itens pendentes até a forma de pagamento ser escolhida

// Passo 1: Finalizar Pedido → abre seleção de pagamento
function checkout() {
    if (!CLIENTE_LOGADO) {
        fecharCarrinho();
        abrirModal('login');
        toast('Faça login para finalizar seu pedido', true);
        return;
    }
    if (!carrinho.length) return;

    // Guarda os itens e abre o seletor de pagamento
    _itensCheckout = carrinho.map(i=>({produto_id:i.id,quantidade:i.quantidade,preco:i.preco_venda}));
    fecharCarrinho();
    document.getElementById('fb-pagamento').innerHTML = '';
    abrirModal('pagamento');
}

// Passo 2: usuário escolhe a forma — dispara o fluxo certo
function voltarPagamento(fechar) {
    fecharModal(fechar);
    abrirModal('pagamento');
}

async function selecionarPagamento(forma) {
    fecharModal('pagamento');
    if      (forma === 'pix')    await iniciarFluxoPix();
    else if (forma === 'cartao') abrirFluxoCartao();
    else if (forma === 'boleto') await iniciarFluxoBoleto();
    else                         await finalizarPedidoRetirada();
}

// ══════════════════════════════════════════════════════════════════
// CARTÃO DE CRÉDITO
// ══════════════════════════════════════════════════════════════════
let cartaoSelecionadoId = null;

async function abrirFluxoCartao() {
    abrirModal('cartao');
    await renderCartaoBody();
}

async function renderCartaoBody(aba = 'salvos') {
    const body = document.getElementById('cartao-body');
    body.innerHTML = `<div class="spinner" style="margin:20px auto;width:32px;height:32px;border-color:#1565c0;border-top-color:transparent;"></div>`;
    const r = await fetch('loja_api.php?endpoint=cartoes_listar');
    const d = await r.json();
    const cartoes = d.cartoes || [];
    const tabAtual = (cartoes.length === 0) ? 'novo' : aba;
    const total = _itensCheckout.reduce((s,i)=>s+i.quantidade*i.preco,0);
    const totalFmt = 'R$ ' + fmtBRL(total);
    body.innerHTML = `
        <div class="cartao-tabs">
            ${cartoes.length > 0 ? `<button class="cartao-tab ${tabAtual==='salvos'?'ativo':''}" onclick="renderCartaoBody('salvos')">Meus Cartões</button>` : ''}
            <button class="cartao-tab ${tabAtual==='novo'?'ativo':''}" onclick="renderCartaoBody('novo')">
                <i class="bi bi-plus-circle"></i> Novo Cartão
            </button>
        </div>
        <div id="cartao-aba"></div>`;
    if (tabAtual === 'salvos') renderAbaCartoesSalvos(cartoes, totalFmt, total);
    else                       renderAbaNovoCartao(totalFmt, total);
}

function renderAbaCartoesSalvos(cartoes, totalFmt, total) {
    const bandIcons  = {visa:'bi-credit-card',mastercard:'bi-credit-card-fill',elo:'bi-credit-card-2-front',hipercard:'bi-credit-card-2-back',amex:'bi-credit-card-2-front-fill',outro:'bi-credit-card'};
    const bandColors = {visa:'#1a237e',mastercard:'#b71c1c',elo:'#1b5e20',hipercard:'#880e4f',amex:'#004d40',outro:'#37474f'};
    let html = cartoes.map(c => `
        <button class="cartao-salvo ${cartaoSelecionadoId==c.id?'selecionado':''}" onclick="selecionarCartaoSalvo(${c.id},this)">
            <i class="bi ${bandIcons[c.bandeira]||'bi-credit-card'}" style="font-size:1.6rem;color:${bandColors[c.bandeira]||'#333'};width:36px;"></i>
            <div class="cs-info">
                <div class="cs-nome">${c.apelido||c.bandeira.toUpperCase()} •••• ${c.ultimos4}</div>
                <div class="cs-sub">${c.nome_titular} · ${c.mes_validade}/${c.ano_validade}</div>
            </div>
            <button class="cs-del" onclick="excluirCartao(event,${c.id})" title="Remover"><i class="bi bi-trash3"></i></button>
        </button>`).join('');
    html += `
        <div id="parcelas-row" class="form-group" style="margin-top:14px;${cartaoSelecionadoId?'':'display:none'}">
            <label>Parcelamento</label>
            <select id="sel-parcelas">${gerarOpcoesParcelas(total)}</select>
        </div>
        <div id="fb-cartao" class="feedback-msg"></div>
        <button onclick="pagarComCartaoSalvo()" id="btn-pagar-salvo"
            style="width:100%;background:#1565c0;color:#fff;border:none;border-radius:10px;
                   padding:13px;font-weight:800;font-size:.95rem;cursor:pointer;margin-top:12px;
                   ${cartaoSelecionadoId?'':'opacity:.5;pointer-events:none;'}">
            <i class="bi bi-lock-fill"></i> Pagar ${totalFmt}
        </button>`;
    document.getElementById('cartao-aba').innerHTML = html;
}

function renderAbaNovoCartao(totalFmt, total) {
    document.getElementById('cartao-aba').innerHTML = `
        <div class="card-preview" id="card-prev">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div class="card-chip"></div>
                <span id="prev-bandeira" style="font-size:1.8rem;">💳</span>
            </div>
            <div class="card-number" id="prev-numero">•••• •••• •••• ••••</div>
            <div class="card-bot">
                <div><div style="font-size:.6rem;opacity:.7;margin-bottom:2px;">TITULAR</div><span id="prev-nome">SEU NOME</span></div>
                <div><div style="font-size:.6rem;opacity:.7;margin-bottom:2px;">VALIDADE</div><span id="prev-val">MM/AA</span></div>
            </div>
        </div>
        <div class="form-group">
            <label>Número do Cartão</label>
            <input type="tel" id="cc-numero" placeholder="0000 0000 0000 0000" maxlength="19"
                   oninput="mascaraCartao(this)" style="font-family:'Courier New',monospace;letter-spacing:2px;">
        </div>
        <div class="form-group">
            <label>Nome no Cartão</label>
            <input type="text" id="cc-nome" placeholder="NOME COMO NO CARTÃO"
                   oninput="this.value=this.value.toUpperCase();document.getElementById('prev-nome').textContent=this.value||'SEU NOME'">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Mês / Ano</label>
                <input type="tel" id="cc-val" placeholder="MM/AA" maxlength="5" oninput="mascaraVal(this)">
            </div>
            <div class="form-group">
                <label>CVV</label>
                <input type="tel" id="cc-cvv" placeholder="•••" maxlength="4" oninput="this.value=this.value.replace(/\D/g,'')">
            </div>
        </div>
        <div class="form-group">
            <label>Parcelamento</label>
            <select id="cc-parcelas">${gerarOpcoesParcelas(total)}</select>
        </div>
        <label style="display:flex;align-items:center;gap:8px;font-size:.82rem;color:#555;margin-bottom:14px;cursor:pointer;">
            <input type="checkbox" id="cc-salvar" checked> Salvar este cartão para compras futuras
        </label>
        <div id="fb-novo-cartao" class="feedback-msg"></div>
        <button onclick="pagarNovoCartao()"
            style="width:100%;background:#1565c0;color:#fff;border:none;border-radius:10px;
                   padding:13px;font-weight:800;font-size:.95rem;cursor:pointer;">
            <i class="bi bi-lock-fill"></i> Pagar ${totalFmt}
        </button>`;
}

function gerarOpcoesParcelas(total) {
    const max = total >= 100 ? 12 : total >= 50 ? 6 : total >= 20 ? 3 : 1;
    let html = '';
    for (let p=1; p<=max; p++) {
        const v = (total/p).toFixed(2).replace('.',',');
        html += `<option value="${p}">${p}x de R$ ${v}${p===1?' (à vista)':''}</option>`;
    }
    return html;
}

function selecionarCartaoSalvo(id, el) {
    cartaoSelecionadoId = id;
    document.querySelectorAll('.cartao-salvo').forEach(b=>b.classList.remove('selecionado'));
    el.classList.add('selecionado');
    document.getElementById('parcelas-row').style.display='';
    const btn = document.getElementById('btn-pagar-salvo');
    if (btn) { btn.style.opacity='1'; btn.style.pointerEvents='auto'; }
}

async function excluirCartao(ev, id) {
    ev.stopPropagation();
    if (!confirm('Remover este cartão?')) return;
    const fd=new FormData(); fd.append('cartao_id',id);
    await fetch('loja_api.php?endpoint=cartao_excluir',{method:'POST',body:fd});
    if (cartaoSelecionadoId===id) cartaoSelecionadoId=null;
    await renderCartaoBody('salvos');
}

const bandEmoji = {visa:'💙',mastercard:'🔴',elo:'🟢',hipercard:'🟣',amex:'🔵',outro:'💳'};
const bandGrad  = {
    visa:       'linear-gradient(135deg,#1a237e,#42a5f5)',
    mastercard: 'linear-gradient(135deg,#b71c1c,#ff8f00)',
    elo:        'linear-gradient(135deg,#1b5e20,#69f0ae)',
    hipercard:  'linear-gradient(135deg,#880e4f,#f48fb1)',
    amex:       'linear-gradient(135deg,#004d40,#26c6da)',
    outro:      'linear-gradient(135deg,#424242,#9e9e9e)'
};

function mascaraCartao(input) {
    let v=input.value.replace(/\D/g,'').substring(0,16);
    let band='outro';
    if (/^4/.test(v))                    band='visa';
    else if (/^5[1-5]|^2[2-7]/.test(v)) band='mastercard';
    else if (/^3[47]/.test(v))           band='amex';
    else if (/^6(?:011|5)/.test(v))      band='discover';
    else if (/^(?:606282|3841)/.test(v)) band='hipercard';
    else if (/^(?:4011|4312|4389|4514|4576|5041|5066|5067|509|6277|6362|6363|650|6516|6550)/.test(v)) band='elo';
    input.value = v.replace(/(.{4})/g,'$1 ').trim();
    const p=document.getElementById('card-prev');
    if (p) p.style.background=bandGrad[band]||bandGrad.outro;
    const pn=document.getElementById('prev-numero');
    if (pn) pn.textContent=(v+'•'.repeat(16-v.length)).replace(/(.{4})/g,'$1 ').trim();
    const pb=document.getElementById('prev-bandeira');
    if (pb) pb.textContent=bandEmoji[band]||'💳';
}

function mascaraVal(input) {
    let v=input.value.replace(/\D/g,'').substring(0,4);
    if (v.length>2) v=v.substring(0,2)+'/'+v.substring(2);
    input.value=v;
    const pv=document.getElementById('prev-val');
    if (pv) pv.textContent=v||'MM/AA';
}

async function pagarNovoCartao() {
    const numero  = document.getElementById('cc-numero').value.replace(/\s/g,'');
    const nome    = document.getElementById('cc-nome').value.trim();
    const val     = document.getElementById('cc-val').value;
    const cvv     = document.getElementById('cc-cvv').value.trim();
    const parcelas= document.getElementById('cc-parcelas').value;
    const salvar  = document.getElementById('cc-salvar').checked;
    const fb      = document.getElementById('fb-novo-cartao');
    const [mes, anoC] = val.split('/');
    const anoFull = anoC ? '20'+anoC : '';
    fb.className='feedback-msg'; fb.textContent='';

    if (numero.length < 13) { fb.className='feedback-msg erro'; fb.textContent='Número de cartão inválido.'; return; }
    if (!nome)               { fb.className='feedback-msg erro'; fb.textContent='Informe o nome do titular.'; return; }
    if (!mes||!anoC||mes<'01'||mes>'12') { fb.className='feedback-msg erro'; fb.textContent='Validade inválida.'; return; }
    if (cvv.length < 3)      { fb.className='feedback-msg erro'; fb.textContent='CVV inválido.'; return; }

    let cartaoId = 0;
    if (salvar) {
        const fdS=new FormData();
        fdS.append('numero',numero); fdS.append('nome_titular',nome);
        fdS.append('mes',mes); fdS.append('ano',anoFull); fdS.append('cvv',cvv);
        const rs=await fetch('loja_api.php?endpoint=cartao_salvar',{method:'POST',body:fdS});
        const ds=await rs.json();
        if (ds.success) cartaoId=ds.id;
    }

    const fd=new FormData();
    fd.append('itens',JSON.stringify(_itensCheckout));
    fd.append('cartao_id',cartaoId||0);
    fd.append('parcelas',parcelas);
    fb.className='feedback-msg'; fb.textContent='Processando pagamento...'; fb.style.display='block';

    const rp=await fetch('loja_api.php?endpoint=pagar_cartao',{method:'POST',body:fd});
    const dp=await rp.json();
    if (dp.success) {
        fecharModal('cartao');
        carrinho=[]; salvarCarrinho(); atualizarCarrinho();
        toast(`✅ Pedido #${dp.pedido_id} aprovado! ${dp.parcelas}x no cartão •••• ${dp.ultimos4}`);
    } else {
        fb.className='feedback-msg erro'; fb.textContent=dp.message;
    }
}

async function pagarComCartaoSalvo() {
    if (!cartaoSelecionadoId) return;
    const fb=document.getElementById('fb-cartao');
    const parcelas=document.getElementById('sel-parcelas')?.value||1;
    fb.className='feedback-msg'; fb.textContent='Processando...'; fb.style.display='block';
    const fd=new FormData();
    fd.append('itens',JSON.stringify(_itensCheckout));
    fd.append('cartao_id',cartaoSelecionadoId);
    fd.append('parcelas',parcelas);
    const r=await fetch('loja_api.php?endpoint=pagar_cartao',{method:'POST',body:fd});
    const d=await r.json();
    if (d.success) {
        fecharModal('cartao');
        carrinho=[]; salvarCarrinho(); atualizarCarrinho();
        toast(`✅ Pedido #${d.pedido_id} aprovado! ${d.parcelas}x no cartão •••• ${d.ultimos4}`);
    } else {
        fb.className='feedback-msg erro'; fb.textContent=d.message;
    }
}

// ══════════════════════════════════════════════════════════════════
// BOLETO BANCÁRIO
// ══════════════════════════════════════════════════════════════════
async function iniciarFluxoBoleto() {
    abrirModal('boleto');
    document.getElementById('boleto-body').innerHTML =
        `<div class="spinner" style="margin:30px auto;width:36px;height:36px;border-color:#424242;border-top-color:transparent;"></div>
         <p style="text-align:center;color:#666;margin-top:12px;">Gerando boleto...</p>`;
    const fd=new FormData();
    fd.append('itens',JSON.stringify(_itensCheckout));
    const r=await fetch('loja_api.php?endpoint=gerar_boleto',{method:'POST',body:fd});
    const d=await r.json();
    if (d.success) {
        carrinho=[]; salvarCarrinho(); atualizarCarrinho();
        renderBoleto(d);
    } else {
        document.getElementById('boleto-body').innerHTML=
            `<div style="color:#c62828;padding:20px;text-align:center;">
                <i class="bi bi-exclamation-triangle-fill" style="font-size:2rem;"></i><br>
                <p>${d.message}</p>
                <button onclick="voltarPagamento('boleto')" style="margin-top:12px;background:#eee;border:none;border-radius:8px;padding:10px 20px;cursor:pointer;">Voltar</button>
             </div>`;
    }
}

function renderBoleto(d) {
    const barras = gerarBarras(d.codigo_barras);
    const linhaEsc = d.linha_digitavel.replace(/'/g,"\\'");
    document.getElementById('boleto-body').innerHTML = `
        <p class="boleto-pedido">Pedido <strong>#${d.pedido_id}</strong></p>
        <p class="boleto-valor">R$ ${fmtBRL(d.total)}</p>
        <div style="text-align:center;">
            <div class="barras">${barras}</div>
            <p style="font-size:.65rem;color:#aaa;margin-bottom:12px;">Código de barras representativo</p>
        </div>
        <p style="font-size:.75rem;color:#666;margin-bottom:6px;font-weight:700;">Linha Digitável:</p>
        <div class="boleto-linha" onclick="copiarBoleto('${linhaEsc}')" title="Clique para copiar">${d.linha_digitavel}</div>
        <button class="btn-copiar-boleto" onclick="copiarBoleto('${linhaEsc}')">
            <i class="bi bi-clipboard-check"></i> Copiar linha digitável
        </button>
        <div class="boleto-info">
            <strong>📋 Informações do boleto:</strong><br>
            Beneficiário: <strong>${d.beneficiario}</strong><br>
            Vencimento: <strong>${d.vencimento}</strong><br>
            Valor: <strong>R$ ${fmtBRL(d.total)}</strong><br><br>
            ⚠️ <strong>Atenção:</strong> Este boleto vence em 3 dias corridos. Após o vencimento o pedido será cancelado.
            Pague em qualquer banco, lotérica ou app de pagamentos.
        </div>
        <button onclick="fecharModal('boleto')" style="width:100%;margin-top:16px;background:#e0e0e0;color:#333;
                border:none;border-radius:8px;padding:11px;font-weight:700;cursor:pointer;">
            <i class="bi bi-check2"></i> Entendido — fechar
        </button>`;
}

function gerarBarras(codigo) {
    let html='';
    const digits=(codigo||'').replace(/\D/g,'').padEnd(44,'0').split('').map(Number);
    let i=0;
    while (i<80) {
        const d=digits[i%digits.length];
        const w=(d%3)+1; const h=40+(d*2);
        const clr=(i%5===0)?'#fff':'#000';
        html+=`<span style="width:${w}px;height:${h}px;background:${clr};display:inline-block;"></span>`;
        i++;
    }
    return html;
}

function copiarBoleto(linha) {
    navigator.clipboard.writeText(linha).then(()=>{
        const el=document.querySelector('.boleto-linha');
        if(el){el.style.background='#c8e6c9';setTimeout(()=>el.style.background='',700);}
        toast('✅ Linha digitável copiada!');
    }).catch(()=>{
        const ta=document.createElement('textarea');ta.value=linha;document.body.appendChild(ta);ta.select();document.execCommand('copy');document.body.removeChild(ta);
        toast('✅ Linha digitável copiada!');
    });
}

// Fluxo PIX — gera QR Code
async function iniciarFluxoPix() {
    // Reseta estado
    pixPedidoId   = null;
    pixConfirmado = false;

    // Abre modal PIX com loading
    document.getElementById('pix-body').innerHTML = `
        <div class="spinner" style="margin:30px auto;width:36px;height:36px;border-color:#1daa5c;border-top-color:transparent;"></div>
        <p style="color:#666;margin-top:12px;">Gerando QR Code...</p>`;
    document.getElementById('modal-pix').classList.add('aberto');

    const fd = new FormData();
    fd.append('itens', JSON.stringify(_itensCheckout));

    try {
        const r = await fetch('loja_api.php?endpoint=gerar_pix',{method:'POST',body:fd});
        const d = await r.json();
        if (d.success) {
            pixPedidoId = d.pedido_id;
            renderizarPix(d);
            carrinho = []; salvarCarrinho(); atualizarCarrinho();
        } else {
            document.getElementById('pix-body').innerHTML =
                `<div style="color:#c62828;padding:20px 16px;">
                    <i class="bi bi-exclamation-triangle-fill"></i> ${d.message}
                    <button class="pix-ok-btn" style="margin-top:14px;background:#fce4e4;color:#c62828;border-color:#f44336;"
                            onclick="fecharPixSemPagamento()">Fechar</button>
                 </div>`;
        }
    } catch(e) {
        document.getElementById('pix-body').innerHTML =
            `<div style="color:#c62828;padding:20px 16px;">
                <i class="bi bi-exclamation-triangle-fill"></i> Erro ao gerar PIX. Tente novamente.
                <button class="pix-ok-btn" style="margin-top:14px;background:#fce4e4;color:#c62828;border-color:#f44336;"
                        onclick="fecharPixSemPagamento()">Fechar</button>
             </div>`;
    }
}

// Fluxo Retirada — cria pedido direto, sem PIX
async function finalizarPedidoRetirada() {
    const fd = new FormData();
    fd.append('itens', JSON.stringify(_itensCheckout));
    try {
        const r = await fetch('loja_api.php?endpoint=pedido_criar',{method:'POST',body:fd});
        const d = await r.json();
        if (d.success) {
            carrinho = []; salvarCarrinho(); atualizarCarrinho();
            toast(`🎉 Pedido #${d.pedido_id} realizado! Pague ao retirar na farmácia.`);
        } else {
            toast(d.message, true);
        }
    } catch(e) {
        toast('Erro ao finalizar pedido. Tente novamente.', true);
    }
}

function renderizarPix(d) {
    _ultimoPix = d; // cache para poder restaurar o QR Code via "Voltar ao PIX"
    const totalFmt = 'R$ ' + fmtBRL(d.total);
    const body = document.getElementById('pix-body');
    const payloadEscapado = d.payload.replace(/'/g, "\\'");

    body.innerHTML = `
        <p class="pix-pedido">Pedido <strong>#${d.pedido_id}</strong> — aguardando pagamento</p>
        <p class="pix-valor">${totalFmt}</p>
        <div class="pix-qr" id="pix-qr-canvas"></div>
        <p style="font-size:.72rem;color:#888;margin-bottom:6px;">Ou copie o código Pix Copia e Cola:</p>
        <div class="pix-copiacola" id="pix-cc" onclick="copiarPix(this,'${payloadEscapado}')"
             title="Clique para copiar">${d.payload}</div>
        <button class="btn-copiar" onclick="copiarPix(document.getElementById('pix-cc'),'${payloadEscapado}')">
            <i class="bi bi-clipboard-check"></i> Copiar código PIX
        </button>
        <div class="pix-instrucoes">
            <strong>Como pagar:</strong><br>
            1. Abra o app do seu banco e acesse o PIX<br>
            2. Escaneie o QR Code <strong>ou</strong> cole o código acima<br>
            3. Confirme o valor de <strong>${totalFmt}</strong> e finalize
        </div>
        <div style="display:flex;gap:10px;margin-top:18px;">
            <button class="pix-ok-btn" style="background:#fce4e4;color:#c62828;border-color:#f44336;flex:1;"
                    onclick="fecharPixSemPagamento()">
                <i class="bi bi-x-circle"></i> Cancelar pedido
            </button>
            <button class="pix-ok-btn" style="flex:1;" onclick="confirmarPagamentoPix()">
                <i class="bi bi-check-circle"></i> Já paguei
            </button>
        </div>`;

    new QRCode(document.getElementById('pix-qr-canvas'), {
        text: d.payload,
        width: 200,
        height: 200,
        correctLevel: QRCode.CorrectLevel.M
    });
}

function copiarPix(el, payload) {
    navigator.clipboard.writeText(payload).then(() => {
        const orig = el.style.background;
        el.style.background = '#c8e6c9';
        setTimeout(()=>{ el.style.background = orig; }, 800);
        toast('✅ Código PIX copiado!');
    }).catch(()=>{
        const ta = document.createElement('textarea');
        ta.value = payload;
        document.body.appendChild(ta);
        ta.select(); document.execCommand('copy');
        document.body.removeChild(ta);
        toast('✅ Código PIX copiado!');
    });
}

// Usuário declara que pagou — confirma no servidor antes de fechar o modal
async function confirmarPagamentoPix() {
    if (!pixPedidoId) return;

    const body = document.getElementById('pix-body');

    // Desabilita os botões e exibe loading enquanto aguarda o servidor
    const btnConfirmar = body.querySelector('button[onclick="confirmarPagamentoPix()"]');
    const btnCancelar  = body.querySelector('button[onclick="fecharPixSemPagamento()"]');
    if (btnConfirmar) { btnConfirmar.disabled = true; btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:14px;height:14px;border-width:2px;"></span> Confirmando...'; }
    if (btnCancelar)  { btnCancelar.disabled  = true; }

    try {
        const fd = new FormData();
        fd.append('pedido_id', pixPedidoId);
        const r = await fetch('loja_api.php?endpoint=confirmar_pix', { method: 'POST', body: fd });
        const d = await r.json();

        if (d.success) {
            pixConfirmado = true;
            const idConfirmado = pixPedidoId;
            pixPedidoId = null;
            document.getElementById('modal-pix').classList.remove('aberto');
            toast('✅ Pedido #' + idConfirmado + ' registrado! Confirmaremos após a compensação do PIX.');
        } else {
            // Restaura botões e mostra erro inline
            if (btnConfirmar) { btnConfirmar.disabled = false; btnConfirmar.innerHTML = '<i class="bi bi-check-circle"></i> Já paguei'; }
            if (btnCancelar)  { btnCancelar.disabled  = false; }
            toast('⚠️ ' + (d.message || 'Erro ao registrar pagamento. Tente novamente.'), true);
        }
    } catch(e) {
        if (btnConfirmar) { btnConfirmar.disabled = false; btnConfirmar.innerHTML = '<i class="bi bi-check-circle"></i> Já paguei'; }
        if (btnCancelar)  { btnCancelar.disabled  = false; }
        toast('⚠️ Erro de conexão. Tente novamente.', true);
    }
}

// Usuário fechou sem pagar — cancela o pedido no banco
async function fecharPixSemPagamento() {
    const idParaCancelar = pixPedidoId;
    pixPedidoId   = null;
    pixConfirmado = false;
    document.getElementById('modal-pix').classList.remove('aberto');

    if (idParaCancelar) {
        const fd = new FormData();
        fd.append('pedido_id', idParaCancelar);
        try {
            await fetch('loja_api.php?endpoint=cancelar_pix', {method:'POST', body:fd});
        } catch(e) { /* silencioso — o pedido permanece pendente no banco */ }
        toast('❌ Pedido cancelado. Nenhum valor foi cobrado.', true);
    }
}

// Intercepta o clique no overlay (fora do modal) e o botão X
document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.getElementById('modal-pix');

    // Clique fora do modal-box
    overlay.addEventListener('click', function(e) {
        if (e.target === this) tentarFecharPix();
    });
});

function tentarFecharPix() {
    if (!pixPedidoId) {
        // Nenhum pedido pendente — fecha normalmente
        document.getElementById('modal-pix').classList.remove('aberto');
        return;
    }
    // Existe pedido pendente — confirma com o usuário
    const body = document.getElementById('pix-body');
    body.innerHTML = `
        <div style="text-align:center;padding:24px 12px;">
            <i class="bi bi-exclamation-triangle-fill" style="font-size:2.8rem;color:#f5820d;"></i>
            <h3 style="margin:14px 0 6px;font-size:1.1rem;">Pagamento não confirmado</h3>
            <p style="color:#666;font-size:.88rem;margin-bottom:20px;">
                O QR Code foi gerado mas ainda não confirmamos seu pagamento.<br>
                Deseja <strong>cancelar</strong> o pedido ou <strong>voltar</strong> para finalizar o pagamento?
            </p>
            <div style="display:flex;gap:10px;">
                <button class="pix-ok-btn"
                        style="flex:1;background:#fce4e4;color:#c62828;border-color:#f44336;"
                        onclick="fecharPixSemPagamento()">
                    <i class="bi bi-trash3"></i> Cancelar pedido
                </button>
                <button class="pix-ok-btn" style="flex:1;" onclick="voltarParaQrCode()">
                    <i class="bi bi-arrow-left-circle"></i> Voltar ao PIX
                </button>
            </div>
        </div>`;
}

function voltarParaQrCode() {
    if (_ultimoPix) renderizarPix(_ultimoPix);
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
