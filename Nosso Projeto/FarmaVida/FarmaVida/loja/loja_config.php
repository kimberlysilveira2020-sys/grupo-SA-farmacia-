<?php
// Aponta para o config.php do sistema interno (um nível acima, na mesma pasta)
require_once __DIR__ . '/../config.php';

// Sessão do cliente da loja — prefixo diferente para não conflitar com funcionários
define('LOJA_SESSION_PREFIX', 'cliente_loja_');

function clienteLogado(): bool {
    return !empty($_SESSION[LOJA_SESSION_PREFIX . 'id']);
}

function clienteId(): int {
    return (int)($_SESSION[LOJA_SESSION_PREFIX . 'id'] ?? 0);
}

function clienteNome(): string {
    return $_SESSION[LOJA_SESSION_PREFIX . 'nome'] ?? '';
}

function jsonResp(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
