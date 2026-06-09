<?php
require_once 'config.php';
$pdo = Config::getDbConnection();

// Garante coluna ativo
$cols = $pdo->query("SHOW COLUMNS FROM produtos LIKE 'ativo'")->fetchAll();
if (empty($cols)) {
    $pdo->exec("ALTER TABLE produtos ADD COLUMN `ativo` TINYINT(1) NOT NULL DEFAULT 1");
    $pdo->exec("UPDATE produtos SET ativo = 1");
}

$stmt = $pdo->query("
    SELECT nome, fabricante, COUNT(*) as qtd, GROUP_CONCAT(id ORDER BY id ASC) as ids
    FROM produtos
    GROUP BY nome, fabricante
    HAVING COUNT(*) > 1
");
$duplicados = $stmt->fetchAll();

if (isset($_GET['confirmar'])) {
    $deletados = 0;
    foreach ($duplicados as $d) {
        $ids = explode(',', $d['ids']);
        $idManter = array_shift($ids);
        foreach ($ids as $idDel) {
            $pdo->prepare("UPDATE lotes SET produto_id=? WHERE produto_id=?")->execute([$idManter,$idDel]);
            $pdo->prepare("UPDATE itens_venda SET produto_id=? WHERE produto_id=?")->execute([$idManter,$idDel]);
            $pdo->prepare("DELETE FROM produtos WHERE id=?")->execute([$idDel]);
            $deletados++;
        }
    }
    echo "<div style='font-family:sans-serif;padding:30px;'><h2 style='color:green'>✅ $deletados duplicado(s) removido(s)!</h2>";
    echo "<p><a href='produtos.php' style='background:#1976D2;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;'>← Voltar para Estoque</a></p>";
    echo "<p style='color:red;font-size:12px;margin-top:20px'>⚠️ Apague este arquivo do servidor agora.</p></div>";
    exit;
}

if (empty($duplicados)) {
    echo "<div style='font-family:sans-serif;padding:30px;'><h2 style='color:green'>✅ Nenhum duplicado encontrado!</h2>";
    echo "<p><a href='produtos.php'>← Voltar</a></p></div>"; exit;
}
?><!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Limpar Duplicados</title>
<style>body{font-family:sans-serif;padding:30px;max-width:700px}table{width:100%;border-collapse:collapse}
th,td{border:1px solid #ddd;padding:10px}th{background:#f5f5f5}.warn{background:#fff3e0;border:1px solid #ff9800;padding:15px;border-radius:8px;margin:20px 0}
.btn{padding:12px 24px;border-radius:6px;border:none;cursor:pointer;font-size:15px;text-decoration:none;display:inline-block;margin-top:15px}
.red{background:#d32f2f;color:#fff}.gray{background:#757575;color:#fff;margin-right:10px}</style></head><body>
<h2>🔍 Produtos Duplicados</h2>
<div class="warn">⚠️ Estes produtos aparecem mais de uma vez. A limpeza mantém o <strong>registro mais antigo</strong> e transfere todos os lotes.</div>
<table><thead><tr><th>Produto</th><th>Fabricante</th><th>Cópias</th><th>IDs</th></tr></thead><tbody>
<?php foreach($duplicados as $d): ?>
<tr><td><?=htmlspecialchars($d['nome'])?></td><td><?=htmlspecialchars($d['fabricante'])?></td>
<td style="color:red;font-weight:bold"><?=$d['qtd']?>x</td><td><?=$d['ids']?></td></tr>
<?php endforeach ?>
</tbody></table>
<a href="produtos.php" class="btn gray">Cancelar</a>
<a href="limpar_duplicados.php?confirmar=1" class="btn red" onclick="return confirm('Confirmar limpeza? Não pode ser desfeito.')">🗑️ Limpar Agora</a>
</body></html>
