<?php
require_once 'config.php';
$pdo = Config::getDbConnection();
$log = [];

// 1. Garante coluna ativo com DEFAULT 1
try {
    $col = $pdo->query("SHOW COLUMNS FROM produtos LIKE 'ativo'")->fetchAll();
    if (empty($col)) {
        $pdo->exec("ALTER TABLE produtos ADD COLUMN `ativo` TINYINT(1) NOT NULL DEFAULT 1");
        $log[] = "✅ Coluna 'ativo' criada";
    } else {
        $log[] = "✅ Coluna 'ativo' já existe";
    }
} catch(Exception $e) { $log[] = "❌ Erro ativo: " . $e->getMessage(); }

// 2. Atualiza todos os NULL para 1
try {
    $n = $pdo->exec("UPDATE produtos SET ativo = 1 WHERE ativo IS NULL");
    $log[] = "✅ $n produto(s) com ativo=NULL corrigidos para 1";
} catch(Exception $e) { $log[] = "❌ Erro update: " . $e->getMessage(); }

// 3. Mostra todos os produtos antes da limpeza
$todos = $pdo->query("SELECT id, nome, fabricante, ativo FROM produtos ORDER BY nome, id")->fetchAll();
$log[] = "📋 Produtos no banco: " . count($todos);

// 4. Remove duplicados mantendo o menor ID
try {
    $pdo->exec("
        DELETE p1 FROM produtos p1
        INNER JOIN produtos p2
        ON p1.nome = p2.nome AND p1.fabricante = p2.fabricante AND p1.id > p2.id
    ");
    $log[] = "✅ Duplicados removidos";
} catch(Exception $e) { $log[] = "❌ Erro remover duplicados: " . $e->getMessage(); }

// 5. Tenta adicionar UNIQUE constraint para bloquear duplicatas no banco
try {
    // Remove se já existe
    $pdo->exec("ALTER TABLE produtos DROP INDEX IF EXISTS uq_nome_fabricante");
} catch(Exception $e) {}
try {
    $pdo->exec("ALTER TABLE produtos ADD UNIQUE INDEX uq_nome_fabricante (nome, fabricante)");
    $log[] = "✅ UNIQUE constraint adicionada — banco agora bloqueia duplicatas fisicamente";
} catch(Exception $e) {
    $log[] = "⚠️ UNIQUE já existia ou erro: " . $e->getMessage();
}

// 6. Lista final
$final = $pdo->query("SELECT id, nome, fabricante, ativo FROM produtos ORDER BY nome")->fetchAll();

?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Fix Banco</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head><body class="p-4">
<h4>🔧 Correção do Banco de Dados</h4>
<ul class="list-group mb-4">
<?php foreach ($log as $l): ?>
<li class="list-group-item"><?= $l ?></li>
<?php endforeach; ?>
</ul>

<h5>Produtos após limpeza (<?= count($final) ?>):</h5>
<table class="table table-bordered table-sm">
<thead class="table-dark"><tr><th>ID</th><th>Nome</th><th>Fabricante</th><th>Ativo</th></tr></thead>
<tbody>
<?php foreach ($final as $p): ?>
<tr>
  <td><?= $p['id'] ?></td>
  <td><?= htmlspecialchars($p['nome']) ?></td>
  <td><?= htmlspecialchars($p['fabricante']) ?></td>
  <td><?= $p['ativo'] ? '✅' : '❌' ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="alert alert-warning">⚠️ Apague este arquivo do servidor após usar.</div>
<a href="produtos.php" class="btn btn-primary">← Voltar para Estoque</a>
</body></html>
