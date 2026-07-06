<?php
require_once '../config.php';
if (empty($_SESSION['loja_cliente_id'])) {
    header('Location: login.php');
    exit;
}
$clienteNome = $_SESSION['loja_cliente_nome'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Meus Pedidos | Farmácia Vida Saudável</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --verde: #1a8a3c; --verde-med: #2dab58; --verde-dark: #116028;
  --verde-claro: #e8f5ee; --cinza: #f4f7f5; --texto: #1a2332; --texto-sec: #5a6a7a;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--cinza); color: var(--texto); }

.loja-header {
  background: var(--verde); padding: 14px 24px;
  display: flex; align-items: center; gap: 16px;
  box-shadow: 0 2px 12px rgba(0,0,0,.15);
}
.logo-wrap { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; }
.logo-icon {
  width: 38px; height: 38px; background: rgba(255,255,255,.2);
  border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px;
}
.logo-nome { font-weight: 800; font-size: 16px; }
.btn-hdr {
  background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
  color: #fff; border-radius: 10px; padding: 8px 14px; font-size: 13px;
  font-weight: 600; font-family: inherit; cursor: pointer; text-decoration: none;
  display: flex; align-items: center; gap: 6px; transition: background .2s;
}
.btn-hdr:hover { background: rgba(255,255,255,.28); color: #fff; }
.ms-auto { margin-left: auto; }

.page-wrap { max-width: 800px; margin: 0 auto; padding: 32px 16px; }
.page-title { font-size: 24px; font-weight: 800; margin-bottom: 24px; }

.pedido-card {
  background: #fff; border-radius: 16px;
  box-shadow: 0 2px 16px rgba(0,0,0,.07);
  margin-bottom: 16px; overflow: hidden;
}
.pedido-header {
  display: flex; align-items: center; gap: 12px;
  padding: 16px 20px; border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
}
.pedido-num { font-weight: 800; font-size: 16px; }
.pedido-data { font-size: 12px; color: var(--texto-sec); }
.pedido-total { font-size: 16px; font-weight: 800; color: var(--verde-dark); margin-left: auto; }

.status-badge {
  padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;
}
.status-pendente   { background: #fff3e0; color: #e65100; }
.status-confirmado { background: #e3f2fd; color: #1565c0; }
.status-pronto     { background: #e8f5ee; color: var(--verde); }
.status-entregue   { background: #f3e5f5; color: #6a1b9a; }
.status-cancelado  { background: #fce4ec; color: #b71c1c; }

.pedido-itens { padding: 16px 20px; display: none; }
.pedido-itens.aberto { display: block; }
.item-linha {
  display: flex; align-items: center; justify-content: space-between;
  padding: 8px 0; border-bottom: 1px solid #f5f5f5; font-size: 14px;
}
.item-linha:last-child { border-bottom: none; }
.item-nome { font-weight: 600; }
.item-qtd  { color: var(--texto-sec); }
.item-preco { font-weight: 700; color: var(--verde-dark); }

.empty-state {
  text-align: center; padding: 60px 24px; color: var(--texto-sec);
}
.empty-state .icon { font-size: 56px; margin-bottom: 16px; }
.empty-state h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
</style>
</head>
<body>

<header class="loja-header">
  <a href="index.php" class="logo-wrap">
    <div class="logo-icon">🌿</div>
    <div class="logo-nome">Farmácia Vida Saudável</div>
  </a>
  <a href="index.php" class="btn-hdr ms-auto">
    <i class="bi bi-shop"></i> Loja
  </a>
  <button class="btn-hdr" onclick="fazerLogout()">
    <i class="bi bi-box-arrow-right"></i> Sair
  </button>
</header>

<div class="page-wrap">
  <div class="page-title">
    👤 <?= htmlspecialchars($clienteNome) ?> — Meus Pedidos
  </div>
  <div id="listaPedidos">
    <div class="empty-state"><div class="icon">⏳</div><h3>Carregando...</h3></div>
  </div>
</div>

<script>
async function carregarPedidos() {
  const res  = await fetch('loja_api.php?endpoint=meus_pedidos');
  const data = await res.json();
  const lista = document.getElementById('listaPedidos');

  if (!data.success || !data.pedidos.length) {
    lista.innerHTML = `
      <div class="empty-state">
        <div class="icon">🛒</div>
        <h3>Nenhum pedido ainda</h3>
        <p>Seus pedidos aparecerão aqui após a compra.</p>
        <a href="index.php" style="display:inline-block;margin-top:16px;background:var(--verde);color:#fff;padding:10px 24px;border-radius:10px;text-decoration:none;font-weight:700">Ver produtos</a>
      </div>`;
    return;
  }

  const statusLabel = {
    pendente: 'Aguardando', confirmado: 'Confirmado',
    pronto: 'Pronto p/ retirada', entregue: 'Entregue', cancelado: 'Cancelado'
  };

  lista.innerHTML = data.pedidos.map(p => {
    const data_fmt = new Date(p.criado_em).toLocaleString('pt-BR');
    const total_fmt = parseFloat(p.total).toLocaleString('pt-BR', {style:'currency', currency:'BRL'});
    const status = p.status || 'pendente';
    return `
      <div class="pedido-card">
        <div class="pedido-header" onclick="toggleItens(${p.id}, this)">
          <div>
            <div class="pedido-num">Pedido #${p.id}</div>
            <div class="pedido-data">${data_fmt} · ${p.qtd_itens} item(s)</div>
          </div>
          <span class="status-badge status-${status}">${statusLabel[status] || status}</span>
          <div class="pedido-total">${total_fmt}</div>
          <i class="bi bi-chevron-down" style="color:#aaa"></i>
        </div>
        <div class="pedido-itens" id="itens-${p.id}">
          <div style="color:#aaa;text-align:center;padding:12px">Carregando itens...</div>
        </div>
      </div>`;
  }).join('');
}

async function toggleItens(id, header) {
  const div = document.getElementById('itens-' + id);
  const icon = header.querySelector('.bi-chevron-down, .bi-chevron-up');
  if (div.classList.contains('aberto')) {
    div.classList.remove('aberto');
    icon.className = 'bi bi-chevron-down';
    return;
  }
  div.classList.add('aberto');
  icon.className = 'bi bi-chevron-up';

  // Busca itens se ainda não carregou
  if (div.dataset.loaded) return;
  try {
    const res  = await fetch(`../api.php?endpoint=venda_itens&id=${id}`);
    // Tenta API interna primeiro, se falhar usa os dados que já tem
    const data = await res.json();
    if (data.success && data.itens.length) {
      div.innerHTML = data.itens.map(i => {
        const sub = (i.preco * i.quantidade).toLocaleString('pt-BR', {style:'currency', currency:'BRL'});
        return `
          <div class="item-linha">
            <span class="item-nome">${escHtml(i.produto_nome)}</span>
            <span class="item-qtd">x${i.quantidade}</span>
            <span class="item-preco">${sub}</span>
          </div>`;
      }).join('');
    } else {
      div.innerHTML = '<div style="color:#aaa;text-align:center;padding:12px">Detalhes não disponíveis.</div>';
    }
  } catch(e) {
    div.innerHTML = '<div style="color:#aaa;text-align:center;padding:12px">Erro ao carregar itens.</div>';
  }
  div.dataset.loaded = '1';
}

async function fazerLogout() {
  await fetch('loja_api.php?endpoint=cliente_logout', { method:'POST' });
  window.location.href = 'login.php';
}

function escHtml(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

carregarPedidos();
</script>
</body>
</html>
