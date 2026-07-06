<?php
require_once 'config.php';
$pdo = Config::getDbConnection();

// Delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['del_id'])) {
    $id = (int)$_POST['del_id'];
    $pdo->prepare("DELETE FROM lotes WHERE produto_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM produtos WHERE id=?")->execute([$id]);
    header("Location: diagnostico.php?deleted=$id");
    exit;
}

$produtos = $pdo->query("SELECT id, nome, fabricante, ativo FROM produtos ORDER BY id ASC")->fetchAll();
?><!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Diagnóstico</title>
<style>
body{font-family:sans-serif;padding:20px;}
table{width:100%;border-collapse:collapse;margin-top:15px;}
th,td{border:1px solid #ccc;padding:8px 12px;}
th{background:#eee;}
.btn-del{padding:6px 14px;background:#d32f2f;color:#fff;border:none;border-radius:4px;cursor:pointer;}
.ok{background:#e8f5e9}.warn{background:#fff8e1}
</style></head>
<body>
<?php if(isset($_GET['deleted'])): ?>
<p style="color:green;font-weight:bold">✅ Produto ID <?=(int)$_GET['deleted']?> removido com sucesso!</p>
<?php endif ?>
<h2>Todos os produtos no banco (<?= count($produtos) ?> registros)</h2>
<table>
    <thead><tr><th>ID</th><th>Nome</th><th>Tamanho nome</th><th>Fabricante</th><th>ativo</th><th>Ação</th></tr></thead>
    <tbody>
    <?php foreach($produtos as $p): ?>
    <tr class="<?= $p['ativo'] ? 'ok' : 'warn' ?>">
        <td><strong><?= $p['id'] ?></strong></td>
        <td><?= htmlspecialchars($p['nome']) ?></td>
        <td style="color:#999;font-size:12px"><?= strlen($p['nome']) ?> chars</td>
        <td><?= htmlspecialchars($p['fabricante']) ?></td>
        <td><?= $p['ativo'] ?></td>
        <td>
            <form method="post" onsubmit="return confirm('Deletar ID <?= $p['id'] ?> (<?= htmlspecialchars($p['nome'], ENT_QUOTES) ?>)?')">
                <input type="hidden" name="del_id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn-del">🗑 Deletar</button>
            </form>
        </td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>
<p style="margin-top:20px"><a href="produtos.php">← Voltar para Estoque</a></p>
<p style="color:red;font-size:12px">⚠️ Apague este arquivo do servidor após usar.</p>
</body></html>
