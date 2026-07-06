<?php
require_once '../config.php';
$clienteLogado = !empty($_SESSION['loja_cliente_id']);
$clienteNome   = $_SESSION['loja_cliente_nome'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Farmácia Vida Saudável – Loja Online</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
<style>
:root {
  --verde:       #1a8a3c;
  --verde-med:   #2dab58;
  --verde-claro: #e8f5ee;
  --verde-dark:  #116028;
  --laranja:     #f57c00;
  --cinza-bg:    #f4f7f5;
  --cinza-card:  #ffffff;
  --texto:       #1a2332;
  --texto-sec:   #5a6a7a;
  --borda:       #e4ece8;
  --shadow:      0 2px 16px rgba(0,0,0,.08);
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--cinza-bg); color: var(--texto); }

/* ── HEADER ── */
.loja-header {
  background: var(--verde);
  position: sticky;
  top: 0;
  z-index: 1000;
  box-shadow: 0 2px 12px rgba(0,0,0,.15);
}
.header-top {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px 24px;
}
.logo-wrap {
  display: flex; align-items: center; gap: 10px;
  text-decoration: none; color: #fff;
  white-space: nowrap;
}
.logo-icon {
  width: 40px; height: 40px;
  background: rgba(255,255,255,.2);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
}
.logo-nome  { font-weight: 800; font-size: 17px; line-height: 1.1; }
.logo-sub   { font-size: 11px; opacity: .7; }

.search-bar {
  flex: 1;
  max-width: 560px;
  position: relative;
}
.search-bar input {
  width: 100%;
  padding: 10px 44px 10px 16px;
  border-radius: 12px;
  border: none;
  font-size: 14px;
  font-family: inherit;
  background: rgba(255,255,255,.15);
  color: #fff;
  backdrop-filter: blur(4px);
  transition: background .2s;
}
.search-bar input::placeholder { color: rgba(255,255,255,.6); }
.search-bar input:focus { outline: none; background: rgba(255,255,255,.25); }
.search-bar .search-icon {
  position: absolute; right: 14px; top: 50%;
  transform: translateY(-50%);
  color: rgba(255,255,255,.7);
  font-size: 16px;
  pointer-events: none;
}

.header-actions { display: flex; align-items: center; gap: 8px; margin-left: auto; }

.btn-hdr {
  background: rgba(255,255,255,.15);
  border: 1px solid rgba(255,255,255,.25);
  color: #fff;
  border-radius: 10px;
  padding: 8px 14px;
  font-size: 13px;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: background .2s;
  white-space: nowrap;
  text-decoration: none;
  display: flex; align-items: center; gap: 6px;
}
.btn-hdr:hover  { background: rgba(255,255,255,.28); color: #fff; }
.btn-carrinho {
  position: relative;
  background: rgba(255,255,255,.2);
}
.badge-cart {
  position: absolute;
  top: -6px; right: -6px;
  background: var(--laranja);
  color: #fff;
  border-radius: 50%;
  width: 18px; height: 18px;
  font-size: 11px;
  font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  display: none;
}

/* ── BANNERS CARROSSEL ── */
.banner-wrap { position: relative; overflow: hidden; max-height: 340px; }
.banner-slides { display: flex; transition: transform .5s ease; height: 100%; }
.banner-slide {
  min-width: 100%;
  height: 280px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}
.banner-slide .banner-bg {
  position: absolute; inset: 0;
  background-size: cover; background-position: center;
  filter: brightness(.55);
}
.banner-content {
  position: relative;
  text-align: center;
  color: #fff;
  padding: 24px;
  z-index: 1;
}
.banner-content h2 { font-size: clamp(22px, 4vw, 38px); font-weight: 800; margin-bottom: 8px; text-shadow: 0 2px 8px rgba(0,0,0,.4); }
.banner-content p  { font-size: clamp(14px, 2vw, 18px); opacity: .9; }

.banner-dots {
  position: absolute; bottom: 14px; left: 50%; transform: translateX(-50%);
  display: flex; gap: 8px;
}
.banner-dots span {
  width: 8px; height: 8px;
  background: rgba(255,255,255,.5);
  border-radius: 50%;
  cursor: pointer;
  transition: background .2s;
}
.banner-dots span.active { background: #fff; }
.banner-nav {
  position: absolute;
  top: 50%; transform: translateY(-50%);
  background: rgba(255,255,255,.2);
  border: none;
  color: #fff;
  width: 40px; height: 40px;
  border-radius: 50%;
  font-size: 20px;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  backdrop-filter: blur(4px);
  transition: background .2s;
}
.banner-nav:hover   { background: rgba(255,255,255,.35); }
.banner-nav.prev    { left: 16px; }
.banner-nav.next    { right: 16px; }
.banner-sem {
  background: linear-gradient(135deg, var(--verde-dark) 0%, var(--verde-med) 100%);
  height: 200px;
  display: flex; align-items: center; justify-content: center;
  color: #fff; text-align: center; padding: 24px;
}
.banner-sem h2 { font-size: 28px; font-weight: 800; }
.banner-sem p  { font-size: 15px; opacity: .85; margin-top: 6px; }

/* ── LAYOUT PRINCIPAL ── */
.loja-body {
  display: flex;
  max-width: 1280px;
  margin: 0 auto;
  padding: 24px 16px;
  gap: 24px;
  align-items: flex-start;
}

/* ── SIDEBAR CATEGORIAS ── */
.sidebar {
  width: 220px;
  flex-shrink: 0;
  position: sticky;
  top: 72px;
}
.sidebar-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: var(--shadow);
  padding: 16px 0;
  overflow: hidden;
}
.sidebar-title {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 1px;
  text-transform: uppercase;
  color: #aaa;
  padding: 0 16px 12px;
}
.cat-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  color: var(--texto-sec);
  transition: all .15s;
  border-left: 3px solid transparent;
  text-decoration: none;
}
.cat-item:hover { background: var(--verde-claro); color: var(--verde); }
.cat-item.active {
  background: var(--verde-claro);
  color: var(--verde);
  border-color: var(--verde);
  font-weight: 700;
}
.cat-badge {
  margin-left: auto;
  background: #f0f0f0;
  color: #888;
  border-radius: 20px;
  padding: 1px 8px;
  font-size: 11px;
}
.cat-item.active .cat-badge { background: var(--verde-claro); color: var(--verde); }

/* ── GRADE DE PRODUTOS ── */
.produtos-wrap { flex: 1; min-width: 0; }
.produtos-topo {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 8px;
}
.produtos-topo h2 { font-size: 18px; font-weight: 700; }
.produtos-topo .total-txt { font-size: 13px; color: var(--texto-sec); }

.grade-produtos {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 16px;
}

.card-produto {
  background: #fff;
  border-radius: 16px;
  box-shadow: var(--shadow);
  overflow: hidden;
  cursor: pointer;
  transition: transform .2s, box-shadow .2s;
  display: flex;
  flex-direction: column;
}
.card-produto:hover { transform: translateY(-4px); box-shadow: 0 8px 28px rgba(0,0,0,.12); }

.card-foto {
  width: 100%;
  aspect-ratio: 1;
  object-fit: cover;
  background: var(--verde-claro);
  display: flex; align-items: center; justify-content: center;
  font-size: 40px;
  color: #ccc;
}
.card-foto img { width: 100%; height: 100%; object-fit: cover; }
.card-foto-placeholder {
  width: 100%;
  aspect-ratio: 1;
  background: var(--verde-claro);
  display: flex; align-items: center; justify-content: center;
  font-size: 44px;
}

.card-info { padding: 12px; flex: 1; display: flex; flex-direction: column; gap: 4px; }
.card-cat  { font-size: 10px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; color: var(--verde); }
.card-nome { font-size: 14px; font-weight: 700; color: var(--texto); line-height: 1.3; }
.card-fab  { font-size: 11px; color: var(--texto-sec); }
.card-preco {
  font-size: 18px;
  font-weight: 800;
  color: var(--verde-dark);
  margin-top: auto;
  padding-top: 8px;
}
.card-preco small { font-size: 12px; font-weight: 500; color: var(--texto-sec); }

.btn-add {
  width: 100%;
  background: var(--verde);
  color: #fff;
  border: none;
  padding: 10px;
  font-size: 13px;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  transition: background .2s;
  display: flex; align-items: center; justify-content: center; gap: 6px;
}
.btn-add:hover { background: var(--verde-dark); }
.btn-add.adicionado { background: var(--laranja); }

/* ── PAGINAÇÃO ── */
.paginacao {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 32px;
}
.pag-btn {
  width: 36px; height: 36px;
  border-radius: 8px;
  border: 1.5px solid var(--borda);
  background: #fff;
  font-size: 14px;
  font-family: inherit;
  cursor: pointer;
  transition: all .15s;
  display: flex; align-items: center; justify-content: center;
}
.pag-btn:hover  { border-color: var(--verde); color: var(--verde); }
.pag-btn.active { background: var(--verde); color: #fff; border-color: var(--verde); }
.pag-btn:disabled { opacity: .35; cursor: not-allowed; }

/* ── LOADING / EMPTY ── */
.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.2s infinite;
  border-radius: 12px;
}
@keyframes shimmer { to { background-position: -200% 0; } }
.empty-state {
  text-align: center;
  padding: 60px 24px;
  color: var(--texto-sec);
}
.empty-state .icon { font-size: 56px; margin-bottom: 16px; }
.empty-state h3    { font-size: 18px; font-weight: 700; margin-bottom: 8px; }

/* ── CARRINHO SIDEBAR ── */
.cart-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,.4);
  z-index: 1400;
  display: none;
  backdrop-filter: blur(2px);
}
.cart-overlay.open { display: block; }
.cart-panel {
  position: fixed;
  top: 0; right: -420px;
  width: 420px; max-width: 100vw;
  height: 100vh;
  background: #fff;
  z-index: 1500;
  display: flex; flex-direction: column;
  box-shadow: -4px 0 24px rgba(0,0,0,.15);
  transition: right .3s ease;
}
.cart-panel.open { right: 0; }
.cart-header {
  background: var(--verde);
  color: #fff;
  padding: 20px 20px 16px;
  display: flex; align-items: center; justify-content: space-between;
}
.cart-header h3 { font-size: 18px; font-weight: 700; }
.btn-close-cart {
  background: rgba(255,255,255,.2);
  border: none;
  color: #fff;
  width: 32px; height: 32px;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
}
.cart-items { flex: 1; overflow-y: auto; padding: 16px; }
.cart-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 0;
  border-bottom: 1px solid #f0f0f0;
}
.cart-item-foto {
  width: 56px; height: 56px;
  border-radius: 10px;
  object-fit: cover;
  background: var(--verde-claro);
  flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 24px;
}
.cart-item-info { flex: 1; min-width: 0; }
.cart-item-nome { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cart-item-preco { font-size: 14px; font-weight: 800; color: var(--verde-dark); }
.cart-item-qtd {
  display: flex; align-items: center; gap: 6px;
}
.qtd-btn {
  width: 26px; height: 26px;
  border-radius: 6px;
  border: 1.5px solid var(--borda);
  background: #fff;
  font-size: 14px;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all .15s;
}
.qtd-btn:hover { border-color: var(--verde); color: var(--verde); }
.qtd-num { font-weight: 700; font-size: 14px; min-width: 20px; text-align: center; }

.cart-vazio {
  text-align: center;
  padding: 60px 24px;
  color: var(--texto-sec);
}
.cart-vazio .icon { font-size: 52px; margin-bottom: 12px; }

.cart-footer {
  padding: 16px 20px;
  border-top: 2px solid #f0f0f0;
}
.cart-total {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 12px;
}
.cart-total span { font-size: 15px; font-weight: 600; }
.cart-total strong { font-size: 22px; font-weight: 800; color: var(--verde-dark); }
.btn-finalizar {
  width: 100%;
  background: var(--verde);
  color: #fff;
  border: none;
  border-radius: 12px;
  padding: 14px;
  font-size: 15px;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  transition: background .2s;
}
.btn-finalizar:hover { background: var(--verde-dark); }
.btn-finalizar:disabled { background: #ccc; cursor: not-allowed; }

/* ── MODAL PEDIDO OK ── */
.modal-ok {
  display: none;
  position: fixed; inset: 0;
  z-index: 2000;
  background: rgba(0,0,0,.5);
  align-items: center; justify-content: center;
}
.modal-ok.open { display: flex; }
.modal-ok-card {
  background: #fff;
  border-radius: 20px;
  padding: 48px 40px;
  text-align: center;
  max-width: 360px;
  width: 90%;
  box-shadow: 0 20px 60px rgba(0,0,0,.2);
  animation: popIn .3s ease;
}
@keyframes popIn { from { transform: scale(.85); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.modal-ok-card .icone { font-size: 64px; margin-bottom: 16px; }
.modal-ok-card h3 { font-size: 22px; font-weight: 800; margin-bottom: 8px; color: var(--verde); }
.modal-ok-card p  { color: var(--texto-sec); font-size: 14px; margin-bottom: 24px; }
.btn-ok {
  background: var(--verde);
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 12px 32px;
  font-size: 15px;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
}

/* ── TOAST ── */
.toast-stack {
  position: fixed;
  bottom: 24px; right: 24px;
  z-index: 3000;
  display: flex; flex-direction: column; gap: 8px;
}
.toast-item {
  background: var(--verde-dark);
  color: #fff;
  padding: 12px 18px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 600;
  box-shadow: 0 4px 16px rgba(0,0,0,.2);
  animation: slideIn .25s ease;
}
@keyframes slideIn { from { transform: translateX(80px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

/* ── RESPONSIVO ── */
@media (max-width: 768px) {
  .sidebar { display: none; }
  .loja-body { padding: 16px 12px; }
  .grade-produtos { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
  .header-top { flex-wrap: wrap; }
  .search-bar { order: 3; flex: 0 0 100%; max-width: 100%; }
}
</style>
</head>
<body>

<!-- ── HEADER ── -->
<header class="loja-header">
  <div class="header-top">
    <a href="index.php" class="logo-wrap">
      <div class="logo-icon">🌿</div>
      <div>
        <div class="logo-nome">Farmácia Vida Saudável</div>
        <div class="logo-sub">Cuidando de você</div>
      </div>
    </a>

    <div class="search-bar">
      <input type="text" id="inputBusca" placeholder="Buscar produtos, fabricantes..." autocomplete="off">
      <i class="bi bi-search search-icon"></i>
    </div>

    <div class="header-actions">
      <?php if ($clienteLogado): ?>
        <span class="btn-hdr" style="cursor:default">
          <i class="bi bi-person-circle"></i>
          <span id="nomeCliente"><?= htmlspecialchars($clienteNome) ?></span>
        </span>
        <button class="btn-hdr" onclick="fazerLogout()">
          <i class="bi bi-box-arrow-right"></i> Sair
        </button>
      <?php else: ?>
        <a href="login.php" class="btn-hdr">
          <i class="bi bi-person"></i> Entrar
        </a>
        <a href="login.php?modo=cadastro" class="btn-hdr" style="background:rgba(255,255,255,.28)">
          <i class="bi bi-person-plus"></i> Cadastrar
        </a>
      <?php endif; ?>
      <button class="btn-hdr btn-carrinho" id="btnCarrinho" onclick="abrirCarrinho()">
        <i class="bi bi-cart3"></i> Carrinho
        <span class="badge-cart" id="badgeCart">0</span>
      </button>
    </div>
  </div>
</header>

<!-- ── BANNERS ── -->
<div id="bannerArea"></div>

<!-- ── BODY ── -->
<div class="loja-body">

  <!-- Sidebar categorias -->
  <aside class="sidebar">
    <div class="sidebar-card">
      <div class="sidebar-title">Categorias</div>
      <div id="listaCategorias">
        <div class="cat-item active" data-cat="" onclick="filtrarCategoria('', this)">
          <i class="bi bi-grid-3x3-gap"></i> Todos
        </div>
      </div>
    </div>
  </aside>

  <!-- Grade produtos -->
  <div class="produtos-wrap">
    <div class="produtos-topo">
      <h2 id="tituloSecao">Produtos</h2>
      <span class="total-txt" id="totalTxt"></span>
    </div>
    <div class="grade-produtos" id="gradeProdutos">
      <!-- skeletons -->
      <?php for ($i=0;$i<8;$i++): ?>
      <div class="skeleton" style="height:280px; border-radius:16px"></div>
      <?php endfor; ?>
    </div>
    <div class="paginacao" id="paginacao"></div>
  </div>

</div>

<!-- ── CARRINHO ── -->
<div class="cart-overlay" id="cartOverlay" onclick="fecharCarrinho()"></div>
<div class="cart-panel" id="cartPanel">
  <div class="cart-header">
    <h3><i class="bi bi-cart3"></i> Meu Carrinho</h3>
    <button class="btn-close-cart" onclick="fecharCarrinho()">✕</button>
  </div>
  <div class="cart-items" id="cartItems"></div>
  <div class="cart-footer" id="cartFooter" style="display:none">
    <div class="cart-total">
      <span>Total:</span>
      <strong id="cartTotal">R$ 0,00</strong>
    </div>
    <button class="btn-finalizar" id="btnFinalizar" onclick="finalizarPedido()">
      <i class="bi bi-check-circle"></i> Fazer Pedido
    </button>
  </div>
</div>

<!-- ── MODAL PEDIDO OK ── -->
<div class="modal-ok" id="modalOk">
  <div class="modal-ok-card">
    <div class="icone">🎉</div>
    <h3>Pedido enviado!</h3>
    <p>Seu pedido foi recebido com sucesso. Nossa equipe entrará em contato para confirmar.</p>
    <button class="btn-ok" onclick="fecharModalOk()">Ótimo!</button>
  </div>
</div>

<!-- ── TOASTS ── -->
<div class="toast-stack" id="toastStack"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Estado global ──
const state = {
  busca:     '',
  categoria: '',
  pagina:    1,
  carrinho:  JSON.parse(localStorage.getItem('fv_cart') || '[]'),
};
let bannerTimer = null;
let bannerAtual = 0;
let bannerTotal = 0;

// ── Inicialização ──
document.addEventListener('DOMContentLoaded', () => {
  carregarBanners();
  carregarCategorias();
  carregarProdutos();
  renderizarCarrinho();

  document.getElementById('inputBusca').addEventListener('input', debounce(e => {
    state.busca  = e.target.value.trim();
    state.pagina = 1;
    carregarProdutos();
  }, 400));
});

// ── Banners ──
async function carregarBanners() {
  try {
    const res = await fetch('loja_api.php?endpoint=vitrine_banners');
    const data = await res.json();
    const area = document.getElementById('bannerArea');

    if (!data.success || !data.banners.length) {
      area.innerHTML = `
        <div class="banner-sem">
          <div>
            <h2>🌿 Farmácia Vida Saudável</h2>
            <p>Cuidando da sua saúde com qualidade e carinho</p>
          </div>
        </div>`;
      return;
    }

    bannerTotal = data.banners.length;
    const slides = data.banners.map((b, i) => {
      const bg = b.imagem
        ? `style="background-image:url('../uploads/banners/${b.imagem}')"`
        : `style="background:${b.cor_fundo}"`;
      return `
        <div class="banner-slide">
          <div class="banner-bg" ${bg}></div>
          <div class="banner-content">
            <h2>${escHtml(b.titulo)}</h2>
            ${b.descricao ? `<p>${escHtml(b.descricao)}</p>` : ''}
          </div>
        </div>`;
    }).join('');

    const dots = data.banners.map((_, i) =>
      `<span class="${i===0?'active':''}" onclick="irBanner(${i})"></span>`
    ).join('');

    area.innerHTML = `
      <div class="banner-wrap">
        <div class="banner-slides" id="bannerSlides">${slides}</div>
        ${bannerTotal > 1 ? `
        <button class="banner-nav prev" onclick="navBanner(-1)">‹</button>
        <button class="banner-nav next" onclick="navBanner(1)">›</button>
        <div class="banner-dots" id="bannerDots">${dots}</div>` : ''}
      </div>`;

    if (bannerTotal > 1) bannerTimer = setInterval(() => navBanner(1), 5000);
  } catch(e) {}
}

function navBanner(dir) {
  bannerAtual = (bannerAtual + dir + bannerTotal) % bannerTotal;
  irBanner(bannerAtual);
}
function irBanner(idx) {
  bannerAtual = idx;
  const slides = document.getElementById('bannerSlides');
  if (slides) slides.style.transform = `translateX(-${idx * 100}%)`;
  document.querySelectorAll('#bannerDots span').forEach((d,i) => d.classList.toggle('active', i===idx));
}

// ── Categorias ──
async function carregarCategorias() {
  try {
    const res  = await fetch('loja_api.php?endpoint=vitrine_categorias');
    const data = await res.json();
    if (!data.success) return;

    const lista = document.getElementById('listaCategorias');
    const extra = data.categorias.map(c => `
      <div class="cat-item" data-cat="${escHtml(c.categoria)}" onclick="filtrarCategoria('${escHtml(c.categoria)}', this)">
        <i class="bi bi-tag"></i> ${escHtml(c.categoria)}
        <span class="cat-badge">${c.qtd}</span>
      </div>`).join('');
    lista.innerHTML += extra;
  } catch(e) {}
}

function filtrarCategoria(cat, el) {
  state.categoria = cat;
  state.pagina    = 1;
  document.querySelectorAll('.cat-item').forEach(i => i.classList.remove('active'));
  el.classList.add('active');
  carregarProdutos();
}

// ── Produtos ──
async function carregarProdutos() {
  const grade = document.getElementById('gradeProdutos');
  grade.innerHTML = Array(8).fill(`<div class="skeleton" style="height:280px;border-radius:16px"></div>`).join('');

  const params = new URLSearchParams({
    endpoint:  'vitrine_produtos',
    pagina:    state.pagina,
    busca:     state.busca,
    categoria: state.categoria,
  });

  try {
    const res  = await fetch(`loja_api.php?${params}`);
    const data = await res.json();
    if (!data.success) throw new Error();

    document.getElementById('totalTxt').textContent = `${data.total} produto${data.total !== 1 ? 's' : ''}`;
    document.getElementById('tituloSecao').textContent =
      state.categoria ? state.categoria : state.busca ? `"${state.busca}"` : 'Produtos';

    if (!data.produtos.length) {
      grade.innerHTML = `
        <div class="empty-state" style="grid-column:1/-1">
          <div class="icon">🔍</div>
          <h3>Nenhum produto encontrado</h3>
          <p>Tente buscar por outro termo ou categoria.</p>
        </div>`;
      document.getElementById('paginacao').innerHTML = '';
      return;
    }

    grade.innerHTML = data.produtos.map(p => {
      const foto = p.foto
        ? `<div class="card-foto"><img src="../uploads/produtos/${escHtml(p.foto)}" alt="${escHtml(p.nome)}" loading="lazy"></div>`
        : `<div class="card-foto-placeholder">💊</div>`;
      const preco = parseFloat(p.preco_venda).toLocaleString('pt-BR', {style:'currency',currency:'BRL'});
      return `
        <div class="card-produto">
          ${foto}
          <div class="card-info">
            <div class="card-cat">${escHtml(p.categoria||'')}</div>
            <div class="card-nome">${escHtml(p.nome)}</div>
            <div class="card-fab">${escHtml(p.fabricante||'')}</div>
            <div class="card-preco">${preco}</div>
          </div>
          <button class="btn-add" id="btn-${p.id}" onclick="addCarrinho(${p.id},'${escHtml(p.nome)}',${p.preco_venda},'${p.foto||''}')">
            <i class="bi bi-cart-plus"></i> Adicionar
          </button>
        </div>`;
    }).join('');

    renderizarPaginacao(data.paginas, data.pagina);
  } catch(e) {
    grade.innerHTML = `<div class="empty-state" style="grid-column:1/-1"><div class="icon">⚠️</div><h3>Erro ao carregar</h3><p>Tente novamente.</p></div>`;
  }
}

function renderizarPaginacao(total, atual) {
  const pag = document.getElementById('paginacao');
  if (total <= 1) { pag.innerHTML = ''; return; }
  let html = `<button class="pag-btn" onclick="irPagina(${atual-1})" ${atual===1?'disabled':''}>‹</button>`;
  for (let i = 1; i <= total; i++) {
    if (i === 1 || i === total || Math.abs(i - atual) <= 1) {
      html += `<button class="pag-btn ${i===atual?'active':''}" onclick="irPagina(${i})">${i}</button>`;
    } else if (Math.abs(i - atual) === 2) {
      html += `<span class="pag-btn" style="cursor:default;border:none">…</span>`;
    }
  }
  html += `<button class="pag-btn" onclick="irPagina(${atual+1})" ${atual===total?'disabled':''}>›</button>`;
  pag.innerHTML = html;
}

function irPagina(n) {
  state.pagina = n;
  carregarProdutos();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── Carrinho ──
function addCarrinho(id, nome, preco, foto) {
  const idx = state.carrinho.findIndex(i => i.produto_id === id);
  if (idx >= 0) {
    state.carrinho[idx].quantidade++;
  } else {
    state.carrinho.push({ produto_id: id, nome, preco: parseFloat(preco), foto, quantidade: 1 });
  }
  salvarCarrinho();
  renderizarCarrinho();
  toast('✅ ' + nome + ' adicionado!');

  const btn = document.getElementById(`btn-${id}`);
  if (btn) {
    btn.classList.add('adicionado');
    btn.textContent = '✓ Adicionado';
    setTimeout(() => {
      btn.classList.remove('adicionado');
      btn.innerHTML = '<i class="bi bi-cart-plus"></i> Adicionar';
    }, 1500);
  }
}

function alterarQtd(id, delta) {
  const idx = state.carrinho.findIndex(i => i.produto_id === id);
  if (idx < 0) return;
  state.carrinho[idx].quantidade += delta;
  if (state.carrinho[idx].quantidade <= 0) state.carrinho.splice(idx, 1);
  salvarCarrinho();
  renderizarCarrinho();
}

function renderizarCarrinho() {
  const items  = document.getElementById('cartItems');
  const footer = document.getElementById('cartFooter');
  const badge  = document.getElementById('badgeCart');
  const total  = state.carrinho.reduce((s, i) => s + i.preco * i.quantidade, 0);
  const qtd    = state.carrinho.reduce((s, i) => s + i.quantidade, 0);

  badge.textContent = qtd;
  badge.style.display = qtd > 0 ? 'flex' : 'none';

  if (!state.carrinho.length) {
    items.innerHTML = `<div class="cart-vazio"><div class="icon">🛒</div><p>Seu carrinho está vazio.</p></div>`;
    footer.style.display = 'none';
    return;
  }

  items.innerHTML = state.carrinho.map(i => {
    const foto = i.foto
      ? `<img src="../uploads/produtos/${escHtml(i.foto)}" class="cart-item-foto" alt="" style="border-radius:10px">`
      : `<div class="cart-item-foto">💊</div>`;
    const sub = (i.preco * i.quantidade).toLocaleString('pt-BR', {style:'currency', currency:'BRL'});
    return `
      <div class="cart-item">
        ${foto}
        <div class="cart-item-info">
          <div class="cart-item-nome">${escHtml(i.nome)}</div>
          <div class="cart-item-preco">${sub}</div>
        </div>
        <div class="cart-item-qtd">
          <button class="qtd-btn" onclick="alterarQtd(${i.produto_id}, -1)">−</button>
          <span class="qtd-num">${i.quantidade}</span>
          <button class="qtd-btn" onclick="alterarQtd(${i.produto_id}, 1)">+</button>
        </div>
      </div>`;
  }).join('');

  document.getElementById('cartTotal').textContent =
    total.toLocaleString('pt-BR', {style:'currency', currency:'BRL'});
  footer.style.display = 'block';
}

function abrirCarrinho() {
  document.getElementById('cartPanel').classList.add('open');
  document.getElementById('cartOverlay').classList.add('open');
}
function fecharCarrinho() {
  document.getElementById('cartPanel').classList.remove('open');
  document.getElementById('cartOverlay').classList.remove('open');
}

async function finalizarPedido() {
  <?php if (!$clienteLogado): ?>
    fecharCarrinho();
    window.location.href = 'login.php';
    return;
  <?php endif; ?>

  if (!state.carrinho.length) return;
  const btn = document.getElementById('btnFinalizar');
  btn.disabled = true;
  btn.textContent = 'Enviando...';

  try {
    const fd = new FormData();
    fd.append('itens', JSON.stringify(state.carrinho));
    const res  = await fetch('loja_api.php?endpoint=pedido_criar', { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) {
      state.carrinho = [];
      salvarCarrinho();
      renderizarCarrinho();
      fecharCarrinho();
      document.getElementById('modalOk').classList.add('open');
    } else {
      toast('❌ ' + data.message);
    }
  } catch(e) {
    toast('❌ Erro ao enviar pedido.');
  }
  btn.disabled = false;
  btn.textContent = 'Fazer Pedido';
}

function fecharModalOk() { document.getElementById('modalOk').classList.remove('open'); }

async function fazerLogout() {
  await fetch('loja_api.php?endpoint=cliente_logout', { method:'POST' });
  window.location.reload();
}

// ── Helpers ──
function salvarCarrinho() { localStorage.setItem('fv_cart', JSON.stringify(state.carrinho)); }
function escHtml(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
function debounce(fn, delay) {
  let t;
  return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
}
function toast(msg) {
  const el = document.createElement('div');
  el.className = 'toast-item';
  el.textContent = msg;
  document.getElementById('toastStack').appendChild(el);
  setTimeout(() => el.remove(), 3000);
}
</script>
</body>
</html>
