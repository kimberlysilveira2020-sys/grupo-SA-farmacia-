<?php
require_once 'config.php';
$pdo = Config::getDbConnection();
$log = [];

$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

// Corrige ativo NULL
$pdo->exec("UPDATE produtos SET ativo = 1 WHERE ativo IS NULL");
$log[] = "✅ ativo NULL corrigidos";

// Busca duplicados
$dups = $pdo->query("
    SELECT nome, fabricante, MIN(id) as manter, MAX(id) as remover, COUNT(*) as qtd
    FROM produtos
    GROUP BY nome, fabricante
    HAVING COUNT(*) > 1
")->fetchAll();

if (empty($dups)) {
    $log[] = "✅ Nenhum duplicado encontrado";
} else {
    foreach ($dups as $d) {
        $pdo->exec("UPDATE lotes SET produto_id={$d['manter']} WHERE produto_id={$d['remover']}");
        $pdo->exec("UPDATE itens_venda SET produto_id={$d['manter']} WHERE produto_id={$d['remover']}");
        try { $pdo->exec("UPDATE pedido_itens SET produto_id={$d['manter']} WHERE produto_id={$d['remover']}"); } catch(Exception $e){}
        $pdo->exec("DELETE FROM produtos WHERE id={$d['remover']}");
        $log[] = "🗑️ Duplicado removido: '{$d['nome']}' — ID {$d['remover']} deletado, mantido ID {$d['manter']}";
    }
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

// Cria UNIQUE se não existir
try {
    $u = $pdo->query("SHOW INDEX FROM produtos WHERE Key_name='uq_nome_fabricante'")->fetchAll();
    if (empty($u)) {
        $pdo->exec("ALTER TABLE produtos ADD UNIQUE INDEX uq_nome_fabricante (nome, fabricante)");
        $log[] = "✅ UNIQUE constraint criada — banco bloqueia duplicatas para sempre";
    } else {
        $log[] = "✅ UNIQUE constraint já existia";
    }
} catch(Exception $e) { $log[] = "⚠️ ".$e->getMessage(); }

$produtos = $pdo->query("SELECT id, nome, fabricante, ativo FROM produtos ORDER BY nome")->fetchAll();
?>
<!DOCTYPE html><html><head><meta charset="utf-8">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head><body class="p-4">
<h4>🔧 Correção Concluída</h4>
<ul class="list-group mb-4">
<?php foreach($log as $l): ?><li class="list-group-item"><?=$l?></li><?php endforeach; ?>
</ul>
<h5>Produtos no banco agora (<?=count($produtos)?>):</h5>
<table class="table table-sm table-bordered">
<thead class="table-dark"><tr><th>ID</th><th>Nome</th><th>Fabricante</th><th>Ativo</th></tr></thead>
<tbody>
<?php foreach($produtos as $p): ?>
<tr><td><?=$p['id']?></td><td><?=htmlspecialchars($p['nome'])?></td><td><?=htmlspecialchars($p['fabricante'])?></td><td><?=$p['ativo']?'✅':'❌'?></td></tr>
<?php endforeach; ?>
</tbody></table>
<div class="alert alert-warning">⚠️ Apague este arquivo agora.</div>
<a href="produtos.php" class="btn btn-success">← Voltar para Estoque</a>
</body></html>
