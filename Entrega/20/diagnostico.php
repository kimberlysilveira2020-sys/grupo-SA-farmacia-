<?php
require_once 'config.php';
$pdo = Config::getDbConnection();

$produtos = $pdo->query("SELECT id, nome, fabricante, ativo, created_at FROM produtos ORDER BY id ASC")->fetchAll();
// fallback se não tiver created_at
if (empty($produtos)) {
    $produtos = $pdo->query("SELECT id, nome, fabricante, ativo FROM produtos ORDER BY id ASC")->fetchAll();
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Diagnóstico</title>
<style>
body{font-family:sans-serif;padding:20px;}
table{width:100%;border-collapse:collapse;margin-top:15px;}
th,td{border:1px solid #ccc;padding:8px 12px;text-align:left;}
th{background:#eee;}
.del{background:#ffebee;}
.btn{padding:8px 18px;background:#d32f2f;color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:14px;margin-top:5px;}
</style></head>
<body>
<h2>Todos os produtos no banco (incluindo duplicados)</h2>
<table>
    <thead><tr><th>ID</th><th>Nome (raw)</th><th>Fabricante (raw)</th><th>ativo</th><th>Ação</th></tr></thead>
    <tbody>
    <?php foreach($produtos as $p): ?>
    <tr>
        <td><?= $p['id'] ?></td>
        <td><?= htmlspecialchars($p['nome']) ?> <small style="color:#999">(<?= strlen($p['nome']) ?> chars)</small></td>
        <td><?= htmlspecialchars($p['fabricante']) ?></td>
        <td><?= $p['ativo'] ?? 'N/A' ?></td>
        <td>
            <form method="post" style="display:inline" onsubmit="return confirm('Deletar ID <?= $p['id'] ?>?')">
                <input type="hidden" name="del_id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn">🗑 Deletar este</button>
            </form>
        </td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['del_id'])) {
    $id = (int)$_POST['del_id'];
    $pdo->prepare("DELETE FROM lotes WHERE produto_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM produtos WHERE id=?")->execute([$id]);
    echo "<p style='color:green;font-weight:bold'>✅ Produto ID $id deletado. <a href='diagnostico.php'>Recarregar</a></p>";
}
?>

<p style="margin-top:20px"><a href="produtos.php">← Voltar para Estoque</a></p>
<p style="color:red;font-size:12px">⚠️ Apague este arquivo após usar.</p>
</body></html>
