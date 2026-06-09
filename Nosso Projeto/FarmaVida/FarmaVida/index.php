<?php
/**
 * Página de Entrada Principal (Root Redirect)
 * Arquivo: index.php
 */

// 1. Importa o arquivo de configuração que já inicia a sessão de forma segura
require_once 'config.php';

// 2. Verifica se o usuário já está autenticado no sistema
if (isset($_SESSION['usuario_id'])) {
    // Se o usuário já estiver logado, redireciona para o Dashboard
    header("Location: dashboard.php");
    exit;
} else {
    // Se não houver sessão ativa, redireciona para a página de login
    header("Location: login.php");
    exit;
}
?>