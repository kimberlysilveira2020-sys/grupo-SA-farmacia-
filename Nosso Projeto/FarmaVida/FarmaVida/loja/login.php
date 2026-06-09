<?php
require_once '../config.php';
// Redireciona se já logado
if (!empty($_SESSION['loja_cliente_id'])) {
    header('Location: index.php');
    exit;
}
$modo = $_GET['modo'] ?? 'login'; // login | cadastro
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Entrar | Farmácia Vida Saudável</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --verde: #1a8a3c;
    --verde-claro: #e8f5ee;
    --verde-med: #2dab58;
    --cinza: #f4f6f8;
    --texto: #1a2332;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--cinza);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  /* ── TOPO ── */
  .loja-top {
    background: var(--verde);
    padding: 14px 24px;
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
  }
  .loja-top .logo-icon {
    width: 36px; height: 36px;
    background: #fff;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
  }
  .loja-top .logo-text { color: #fff; font-weight: 800; font-size: 18px; }
  .loja-top .logo-sub  { color: rgba(255,255,255,.7); font-size: 12px; margin-left: 4px; }

  /* ── CARD ── */
  .auth-wrap {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 16px;
  }
  .auth-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 40px rgba(0,0,0,.10);
    width: 100%;
    max-width: 460px;
    overflow: hidden;
  }
  .auth-header {
    background: linear-gradient(135deg, var(--verde) 0%, var(--verde-med) 100%);
    padding: 32px 36px 28px;
    color: #fff;
  }
  .auth-header h1 { font-size: 26px; font-weight: 800; margin-bottom: 4px; }
  .auth-header p  { font-size: 14px; opacity: .85; }

  .auth-tabs {
    display: flex;
    border-bottom: 2px solid #f0f0f0;
    background: #fafafa;
  }
  .auth-tab {
    flex: 1;
    padding: 14px;
    text-align: center;
    font-weight: 600;
    font-size: 14px;
    color: #888;
    cursor: pointer;
    transition: all .2s;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    text-decoration: none;
  }
  .auth-tab.active {
    color: var(--verde);
    border-color: var(--verde);
    background: #fff;
  }

  .auth-body { padding: 32px 36px; }

  .form-label { font-weight: 600; font-size: 13px; color: var(--texto); margin-bottom: 6px; }
  .form-control {
    border-radius: 10px;
    border: 1.5px solid #e0e0e0;
    padding: 11px 14px;
    font-size: 14px;
    transition: border .2s;
  }
  .form-control:focus {
    border-color: var(--verde);
    box-shadow: 0 0 0 3px rgba(26,138,60,.12);
  }
  .btn-entrar {
    width: 100%;
    background: var(--verde);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 13px;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: background .2s, transform .1s;
    margin-top: 8px;
  }
  .btn-entrar:hover { background: #157030; }
  .btn-entrar:active { transform: scale(.98); }
  .btn-entrar:disabled { background: #aaa; cursor: not-allowed; }

  .divider {
    text-align: center;
    color: #aaa;
    font-size: 12px;
    margin: 20px 0;
    position: relative;
  }
  .divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0; right: 0;
    height: 1px;
    background: #eee;
  }
  .divider span {
    background: #fff;
    padding: 0 12px;
    position: relative;
  }

  .alerta {
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 13px;
    margin-bottom: 16px;
    display: none;
  }
  .alerta.erro   { background: #ffeaea; color: #c0392b; border: 1px solid #f5c6c6; }
  .alerta.ok     { background: #e8f5ee; color: #1a8a3c; border: 1px solid #b2dfcc; }
  .alerta.visivel { display: block; }

  .senha-toggle {
    position: relative;
  }
  .senha-toggle .toggle-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #888;
    font-size: 16px;
  }

  .footer-loja {
    text-align: center;
    padding: 20px;
    color: #aaa;
    font-size: 12px;
  }
</style>
</head>
<body>

<a href="index.php" class="loja-top" style="text-decoration:none">
  <div class="logo-icon">🌿</div>
  <span class="logo-text">Farmácia Vida Saudável</span>
  <span class="logo-sub">Portal do Cliente</span>
</a>

<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-header">
      <h1><?= $modo === 'cadastro' ? 'Criar conta' : 'Bem-vindo!' ?></h1>
      <p><?= $modo === 'cadastro' ? 'Preencha seus dados para se cadastrar' : 'Acesse sua conta para ver pedidos e promoções' ?></p>
    </div>

    <div class="auth-tabs">
      <a href="?modo=login"    class="auth-tab <?= $modo==='login'    ? 'active' : '' ?>">Entrar</a>
      <a href="?modo=cadastro" class="auth-tab <?= $modo==='cadastro' ? 'active' : '' ?>">Criar conta</a>
    </div>

    <div class="auth-body">

      <div id="alerta" class="alerta"></div>

      <?php if ($modo === 'login'): ?>
      <!-- ── FORM LOGIN ── -->
      <div id="formLogin">
        <div class="mb-3">
          <label class="form-label">E-mail</label>
          <input type="email" id="loginEmail" class="form-control" placeholder="seu@email.com" autocomplete="email">
        </div>
        <div class="mb-3">
          <label class="form-label">Senha</label>
          <div class="senha-toggle">
            <input type="password" id="loginSenha" class="form-control" placeholder="Sua senha" autocomplete="current-password">
            <button type="button" class="toggle-btn" onclick="toggleSenha('loginSenha', this)">👁</button>
          </div>
        </div>
        <button class="btn-entrar" id="btnLogin" onclick="fazerLogin()">Entrar</button>
      </div>

      <?php else: ?>
      <!-- ── FORM CADASTRO ── -->
      <div id="formCadastro">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Nome completo *</label>
            <input type="text" id="cadNome" class="form-control" placeholder="Seu nome">
          </div>
          <div class="col-12">
            <label class="form-label">E-mail *</label>
            <input type="email" id="cadEmail" class="form-control" placeholder="seu@email.com">
          </div>
          <div class="col-md-6">
            <label class="form-label">CPF</label>
            <input type="text" id="cadCpf" class="form-control" placeholder="000.000.000-00" maxlength="14">
          </div>
          <div class="col-md-6">
            <label class="form-label">Telefone</label>
            <input type="text" id="cadTelefone" class="form-control" placeholder="(00) 00000-0000" maxlength="15">
          </div>
          <div class="col-12">
            <label class="form-label">Senha *</label>
            <div class="senha-toggle">
              <input type="password" id="cadSenha" class="form-control" placeholder="Mínimo 6 caracteres">
              <button type="button" class="toggle-btn" onclick="toggleSenha('cadSenha', this)">👁</button>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label">Confirmar senha *</label>
            <input type="password" id="cadSenha2" class="form-control" placeholder="Repita a senha">
          </div>
        </div>
        <button class="btn-entrar" id="btnCadastrar" onclick="fazerCadastro()" style="margin-top:20px">Criar minha conta</button>
      </div>
      <?php endif; ?>

    </div><!-- auth-body -->
  </div><!-- auth-card -->
</div><!-- auth-wrap -->

<div class="footer-loja">
  © <?= date('Y') ?> Farmácia Vida Saudável &nbsp;·&nbsp; Portal exclusivo para clientes
</div>

<script>
function mostrarAlerta(msg, tipo = 'erro') {
  const el = document.getElementById('alerta');
  el.textContent = msg;
  el.className = 'alerta visivel ' + tipo;
}

function toggleSenha(id, btn) {
  const inp = document.getElementById(id);
  if (inp.type === 'password') { inp.type = 'text';     btn.textContent = '🙈'; }
  else                          { inp.type = 'password'; btn.textContent = '👁'; }
}

// Máscara CPF
document.getElementById('cadCpf')?.addEventListener('input', function(e) {
  let v = e.target.value.replace(/\D/g,'').slice(0,11);
  v = v.replace(/(\d{3})(\d)/,'$1.$2')
       .replace(/(\d{3})(\d)/,'$1.$2')
       .replace(/(\d{3})(\d{1,2})$/,'$1-$2');
  e.target.value = v;
});

// Máscara telefone
document.getElementById('cadTelefone')?.addEventListener('input', function(e) {
  let v = e.target.value.replace(/\D/g,'').slice(0,11);
  if (v.length <= 10) v = v.replace(/(\d{2})(\d{4})(\d{0,4})/,'($1) $2-$3');
  else                v = v.replace(/(\d{2})(\d{5})(\d{0,4})/,'($1) $2-$3');
  e.target.value = v;
});

async function fazerLogin() {
  const email = document.getElementById('loginEmail').value.trim();
  const senha = document.getElementById('loginSenha').value;
  if (!email || !senha) return mostrarAlerta('Preencha e-mail e senha.');

  const btn = document.getElementById('btnLogin');
  btn.disabled = true;
  btn.textContent = 'Entrando...';

  try {
    const fd = new FormData();
    fd.append('email', email);
    fd.append('senha', senha);
    const res = await fetch('loja_api.php?endpoint=login', { method:'POST', body:fd });
    const data = await res.json();

    if (data.success) {
      mostrarAlerta('Bem-vindo, ' + data.nome + '! Redirecionando...', 'ok');
      setTimeout(() => window.location.href = 'index.php', 1000);
    } else {
      mostrarAlerta(data.message);
      btn.disabled = false;
      btn.textContent = 'Entrar';
    }
  } catch(e) {
    mostrarAlerta('Erro de conexão. Tente novamente.');
    btn.disabled = false;
    btn.textContent = 'Entrar';
  }
}

async function fazerCadastro() {
  const nome    = document.getElementById('cadNome').value.trim();
  const email   = document.getElementById('cadEmail').value.trim();
  const cpf     = document.getElementById('cadCpf').value;
  const tel     = document.getElementById('cadTelefone').value;
  const senha   = document.getElementById('cadSenha').value;
  const senha2  = document.getElementById('cadSenha2').value;

  if (!nome || !email || !senha) return mostrarAlerta('Preencha os campos obrigatórios (*).');
  if (senha !== senha2)          return mostrarAlerta('As senhas não coincidem.');
  if (senha.length < 6)          return mostrarAlerta('A senha deve ter ao menos 6 caracteres.');

  const btn = document.getElementById('btnCadastrar');
  btn.disabled = true;
  btn.textContent = 'Cadastrando...';

  try {
    const fd = new FormData();
    fd.append('nome', nome);
    fd.append('email', email);
    fd.append('cpf', cpf);
    fd.append('telefone', tel);
    fd.append('senha', senha);
    const res = await fetch('loja_api.php?endpoint=registrar', { method:'POST', body:fd });
    const data = await res.json();

    if (data.success) {
      mostrarAlerta('Conta criada! Redirecionando...', 'ok');
      setTimeout(() => window.location.href = 'index.php', 1200);
    } else {
      mostrarAlerta(data.message);
      btn.disabled = false;
      btn.textContent = 'Criar minha conta';
    }
  } catch(e) {
    mostrarAlerta('Erro de conexão. Tente novamente.');
    btn.disabled = false;
    btn.textContent = 'Criar minha conta';
  }
}

// Enter para login
document.getElementById('loginSenha')?.addEventListener('keydown', e => {
  if (e.key === 'Enter') fazerLogin();
});
</script>
</body>
</html>
