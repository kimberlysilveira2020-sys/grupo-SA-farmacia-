<?php
require_once 'config.php';
$pdo = Config::getDbConnection();

// Garante que a coluna existe
$pdo->exec("ALTER TABLE produtos ADD COLUMN IF NOT EXISTS ativo TINYINT(1) NOT NULL DEFAULT 1");

// Atualiza todos os registros que estão NULL para 1 (ativo)
$stmt = $pdo->exec("UPDATE produtos SET ativo = 1 WHERE ativo IS NULL");

// Conta resultados
$total = $pdo->query("SELECT COUNT(*) FROM produtos WHERE ativo = 1")->fetchColumn();
$inativos = $pdo->query("SELECT COUNT(*) FROM produtos WHERE ativo = 0")->fetchColumn();

echo "<h3>✅ Correção aplicada!</h3>";
echo "<p>Produtos ativos: <strong>$total</strong></p>";
echo "<p>Produtos inativos (deletados): <strong>$inativos</strong></p>";
echo "<hr><p>Pode apagar este arquivo agora.</p>";
?>
