<?php
require_once 'config.php';

// Destrói todas as variáveis da sessão (desloga o usuário)
session_unset();
session_destroy();

// Redireciona para a tela de login
header("Location: login.php");
exit;
?>
