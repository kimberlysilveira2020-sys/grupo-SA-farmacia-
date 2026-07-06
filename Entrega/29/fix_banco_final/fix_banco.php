<?php
require_once 'config.php';
$pdo = Config::getDbConnection();
$log = [];

// 1. Corrige coluna ativo
$col = $pdo->query("SHOW COLUMNS FROM produtos LIKE 'ativo'")->fetchAll();
if (empty($col)) {
    $pdo->exec("ALTER TABLE produtos ADD COLUMN `ativo` TINYINT(1) NOT NULL DEFAULT 1");
    $log[] = "✅ Coluna ativo criada";
}
$pdo->exec("UPDATE produtos SET ativo = 1 WHERE ativo IS NULL");
$log[] = "✅ Valores NULL corrigidos para ativo=1";

// 2. Mostra duplicados antes
$dups = $pdo->query("SELECT nome, fabricante, COUNT(*) as qtd, MIN(id) as id_manter, MAX(id) as id_remover FROM produtos GROUP BY nome, fabricante HAVING COUNT(*) > 1")->fetchAll();
$log[] = "📋 Grupos duplicados encontrados: " . count($dups);

// 3. Desativa FK, remove duplicados, reativa FK
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
foreach ($dups as $d) {
    // Migra lotes do id_remover para id_manter
    $pdo->exec("UPDATE lotes SET produto_id = {$d['id_manter']} WHERE produto_id = {$d['id_remover']}");
    // Migra itens_venda
    $pdo->exec("UPDATE itens_venda SET produto_id = {$d['id_manter']} WHERE produto_id = {$d['id_remover']}");
    // Migra pedido_itens
    $pdo->exec("UPDATE pedido_itens SET produto_id = {$d['id_manter']} WHERE produto_id = {$d['id_remover']}");
    // Agora deleta o duplicado
    $pdo->exec("DELETE FROM produtos WHERE id = {$d['id_remover']}");
    $log[] = "🗑️ Duplicado removido: '{$d['nome']}' (ID {$d['id_remover']} → mantido ID {$d['id_manter']})";
}
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

// 4. Cria UNIQUE constraint
$hasUniq = $pdo->query("SHOW INDEX FROM produtos WHERE Key_name = 'uq_nome_fabricante'")->fetchAll();
if (empty($hasUniq)) {
    try {
        $pdo->exec("ALTER TABLE produtos ADD UNIQUE INDEX uq_nome_fabricante (nome, fabricante)");
        $log[] = "✅ UNIQUE constraint criada — duplicação impossível daqui em diante";
    } catch (Exception $e) {
        $log[] = "⚠️ Erro ao criar UNIQUE: " . $e->getMessage();
    }
} else {
    $log[] = "✅ UNIQUE constraint já existia";
}

// 5. Lista final
$depois = $pdo->query("SELECT id, nome, fabricante, ativo FROM produtos ORDER BY nome")->fetchAll();
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Fix Banco</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head><body class="p-4">
<h4>🔧 Correção do Banco</h4>
<ul class="list-group mb-4">
<?php foreach ($log as $l): ?>
<li class="list-group-item"><?= $l ?></li>
<?php endforeach; ?>
</ul>
<h5>Produtos após limpeza (<?= count($depois) ?>):</h5>
<table class="table table-bordered table-sm">
<thead class="table-dark"><tr><th>ID</th><th>Nome</th><th>Fabricante</th><th>Ativo</th></tr></thead>
<tbody>
<?php foreach ($depois as $p): ?>
<tr><td><?= $p['id'] ?></td><td><?= htmlspecialchars($p['nome']) ?></td>
<td><?= htmlspecialchars($p['fabricante']) ?></td><td><?= $p['ativo'] ? '✅' : '❌' ?></td></tr>
<?php endforeach; ?>
</tbody></table>
<div class="alert alert-warning mt-3">⚠️ Apague este arquivo após usar.</div>
<a href="produtos.php" class="btn btn-primary">← Voltar para Estoque</a>
</body></html>
