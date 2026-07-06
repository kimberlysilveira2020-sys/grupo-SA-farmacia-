<?php
require_once 'config.php';
$pdo = Config::getDbConnection();

// Busca duplicados
$stmt = $pdo->query("
    SELECT nome, fabricante, COUNT(*) as qtd, GROUP_CONCAT(id ORDER BY id ASC) as ids
    FROM produtos
    GROUP BY nome, fabricante
    HAVING COUNT(*) > 1
");
$duplicados = $stmt->fetchAll();

if (empty($duplicados)) {
    echo "<p style='color:green;font-family:sans-serif;font-size:18px;'>✅ Nenhum produto duplicado encontrado!</p>";
    echo "<p style='font-family:sans-serif;'><a href='produtos.php'>← Voltar para Estoque</a></p>";
    exit;
}

// Executa limpeza se confirmado
if (isset($_GET['confirmar'])) {
    $deletados = 0;
    foreach ($duplicados as $d) {
        $ids = explode(',', $d['ids']);
        array_shift($ids); // mantém o menor ID
        foreach ($ids as $idDel) {
            // Move lotes para o ID que vai ficar
            $idManter = explode(',', $d['ids'])[0];
            $pdo->prepare("UPDATE lotes SET produto_id = ? WHERE produto_id = ?")->execute([$idManter, $idDel]);
            $pdo->prepare("UPDATE itens_venda SET produto_id = ? WHERE produto_id = ?")->execute([$idManter, $idDel]);
            $pdo->prepare("DELETE FROM produtos WHERE id = ?")->execute([$idDel]);
            $deletados++;
        }
    }
    echo "<div style='font-family:sans-serif;padding:20px;'>";
    echo "<h2 style='color:green;'>✅ Limpeza concluída!</h2>";
    echo "<p><strong>$deletados</strong> produto(s) duplicado(s) removido(s). Lotes e vendas foram preservados.</p>";
    echo "<p><a href='produtos.php' style='background:#1976D2;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;'>← Voltar para Estoque</a></p>";
    echo "<p style='color:red;font-size:12px;'>⚠️ Apague o arquivo limpar_duplicados.php do servidor após usar.</p>";
    echo "</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Limpar Duplicados</title>
<style>body{font-family:sans-serif;padding:30px;max-width:700px;margin:0 auto;}
table{width:100%;border-collapse:collapse;}th,td{border:1px solid #ddd;padding:10px;text-align:left;}
th{background:#f5f5f5;}.btn{padding:12px 28px;border-radius:6px;font-size:16px;cursor:pointer;border:none;}
.btn-danger{background:#d32f2f;color:#fff;}.btn-secondary{background:#757575;color:#fff;margin-right:10px;}
.warn{background:#fff3e0;border:1px solid #ff9800;padding:15px;border-radius:8px;margin:20px 0;}
</style></head>
<body>
<h2>🔍 Produtos Duplicados Encontrados</h2>
<div class="warn">
    ⚠️ Os produtos abaixo aparecem mais de uma vez no banco. O botão abaixo <strong>mantém apenas o registro mais antigo</strong> (menor ID) e transfere todos os lotes e vendas para ele.
</div>
<table>
    <thead><tr><th>Produto</th><th>Fabricante</th><th>Qtd. cópias</th><th>IDs no banco</th></tr></thead>
    <tbody>
    <?php foreach ($duplicados as $d): ?>
    <tr>
        <td><?= htmlspecialchars($d['nome']) ?></td>
        <td><?= htmlspecialchars($d['fabricante']) ?></td>
        <td style="color:red;font-weight:bold;"><?= $d['qtd'] ?>x</td>
        <td><?= $d['ids'] ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<br>
<a href="produtos.php" class="btn btn-secondary">Cancelar</a>
<a href="limpar_duplicados.php?confirmar=1" class="btn btn-danger"
   onclick="return confirm('Confirmar limpeza? Esta ação não pode ser desfeita.')">
   🗑️ Limpar Duplicados Agora
</a>
</body>
</html>
