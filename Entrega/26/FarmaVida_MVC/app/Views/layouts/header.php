<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Sistema de Farmácia' ?> - Vida Saudável</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/style.css">
    <?= isset($extra_css) ? $extra_css : '' ?>
</head>
<body>

<?php if (isset($_SESSION['usuario_id']) && !isset($hide_navbar)): ?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom bg-success">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>dashboard">
            <i class="bi bi-heart-pulse-fill"></i>
            <strong>Farmácia Vida Saudável</strong>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/dashboard') ? 'active' : '' ?>" href="<?= BASE_URL ?>dashboard">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/vendas') ? 'active' : '' ?>" href="<?= BASE_URL ?>vendas">
                        <i class="bi bi-cash-stack"></i> Vendas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/clientes') ? 'active' : '' ?>" href="<?= BASE_URL ?>clientes">
                        <i class="bi bi-people-fill"></i> Clientes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/produtos') ? 'active' : '' ?>" href="<?= BASE_URL ?>produtos">
                        <i class="bi bi-box-seam"></i> Estoque
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/relatorios') ? 'active' : '' ?>" href="<?= BASE_URL ?>relatorios">
                        <i class="bi bi-file-earmark-bar-graph"></i> Relatórios
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <span class="text-white">
                    <i class="bi bi-person-circle"></i>
                    Olá, <strong><?= htmlspecialchars($_SESSION['usuario_nome'] ?? '') ?></strong>
                    <span class="badge bg-light text-dark ms-1"><?= htmlspecialchars($_SESSION['usuario_cargo'] ?? '') ?></span>
                </span>
                <a href="<?= BASE_URL ?>auth/logout" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </a>
            </div>
        </div>
    </div>
</nav>
<?php endif; ?>

<?php if (isset($_SESSION['flash_messages'])): ?>
<div class="container mt-3">
    <?php foreach ($_SESSION['flash_messages'] as $flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['category']) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endforeach; ?>
    <?php unset($_SESSION['flash_messages']); ?>
</div>
<?php endif; ?>

<main class="<?= isset($_SESSION['usuario_id']) ? 'container-fluid' : '' ?> mt-4">
