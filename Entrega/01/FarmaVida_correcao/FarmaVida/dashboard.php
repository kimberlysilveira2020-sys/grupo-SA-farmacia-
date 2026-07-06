<?php

require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$pdo = Config::getDbConnection();

// Consulta direta: Lotes Vencendo (30 dias)
$stmtLotes = $pdo->query("
    SELECT el.id AS lote_id, p.nome AS produto_nome, p.fabricante, el.numero_lote, el.data_validade, el.qtd_atual, DATEDIFF(el.data_validade, CURDATE()) AS dias_para_vencer 
    FROM lotes el 
    INNER JOIN produtos p ON el.produto_id = p.id 
    WHERE el.data_validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND el.qtd_atual > 0 
    ORDER BY el.data_validade ASC
");
$lotes_vencendo = $stmtLotes->fetchAll();
$total_lotes_vencendo = count($lotes_vencendo);

// Consulta direta: Vendas Recentes
$stmtVendas = $pdo->query("
    SELECT v.id, v.data_venda, v.total, u.nome AS vendedor, u.cargo AS cargo_vendedor, v.supervisor_liberacao 
    FROM vendas v 
    INNER JOIN usuarios u ON v.usuario_id = u.id 
    ORDER BY v.data_venda DESC LIMIT 10
");
$vendas_recentes = $stmtVendas->fetchAll();

$page_title = "Dashboard";
include 'header.php'; 
?>

<div class="container-fluid fade-in">

    <div class="dashboard-header">
        <h1 class="mb-2">
            <i class="bi bi-speedometer2"></i> Dashboard - Visão Geral
        </h1>
        <p class="mb-0">Bem-vindo ao sistema de gestão da Farmácia Vida Saudável, <strong><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?></strong>!</p>
    </div>

    <?php if ($total_lotes_vencendo > 0): ?>
    <div class="alert-validade">
        <h4>
            <i class="bi bi-exclamation-triangle-fill"></i>
            ATENÇÃO: Lotes com Validade Próxima do Vencimento!
        </h4>
        <p class="mb-3">Os seguintes lotes vencem nos próximos 30 dias. Tome providências!</p>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-danger">
                    <tr>
                        <th>Produto</th>
                        <th>Fabricante</th>
                        <th>Nº Lote</th>
                        <th>Data de Validade</th>
                        <th>Dias Restantes</th>
                        <th>Quantidade</th>
                    </tr>
                </thead>
                <tbody id="lotes-tbody">
                    <?php foreach ($lotes_vencendo as $index => $lote): ?>
                    <?php 
                        // Regras de CSS baseadas na urgência e no limite de exibição inicial
                        $urgente_class = ($lote['dias_para_vencer'] <= 7) ? 'table-danger-custom' : '';
                        $oculto_class = ($index >= 5) ? 'lote-extra d-none' : '';
                    ?>
                    <tr class="<?= $urgente_class ?> <?= $oculto_class ?>">
                        <td><strong><?= htmlspecialchars($lote['produto_nome']) ?></strong></td>
                        <td><?= htmlspecialchars($lote['fabricante']) ?></td>
                        <td><?= htmlspecialchars($lote['numero_lote']) ?></td>
                        <td><?= date('d/m/Y', strtotime($lote['data_validade'])) ?></td>
                        <td>
                            <span class="badge badge-vencendo">
                                <?= $lote['dias_para_vencer'] ?> dia(s)
                            </span>
                        </td>
                        <td><?= $lote['qtd_atual'] ?> un.</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_lotes_vencendo > 5): ?>
        <div class="text-center mt-3">
            <button class="btn btn-outline-danger" id="btn-toggle-lotes" onclick="toggleLotesVencendo()">
                <i class="bi bi-chevron-down" id="icon-toggle-lotes"></i>
                <span id="text-toggle-lotes">Ver todos (<?= $total_lotes_vencendo ?> lotes)</span>
            </button>
        </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-success mt-4">
        <i class="bi bi-check-circle-fill"></i>
        <strong>Tudo certo!</strong> Não há lotes vencendo nos próximos 30 dias.
    </div>
    <?php endif; ?>

    <div class="row mt-4">
        <div class="col-md-4 mb-4">
            <a href="pdv.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="bi bi-cart-fill text-primary"></i>
                        <h3>PDV</h3>
                        <p class="text-muted">Ponto de Venda</p>
                        <button class="btn btn-primary">
                            <i class="bi bi-arrow-right-circle"></i> Acessar
                        </button>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4 mb-4">
            <a href="produtos.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="bi bi-box-seam text-secondary"></i>
                        <h3>Estoque</h3>
                        <p class="text-muted">Produtos e Lotes</p>
                        <button class="btn btn-success">
                            <i class="bi bi-arrow-right-circle"></i> Gerenciar
                        </button>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4 mb-4">
            <a href="relatorios.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="bi bi-file-earmark-bar-graph text-warning"></i>
                        <h3>Relatórios</h3>
                        <p class="text-muted">Análises</p>
                        <button class="btn btn-warning">
                            <i class="bi bi-arrow-right-circle"></i> Ver
                        </button>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <?php if (!empty($vendas_recentes)): ?>
    <div class="card mt-4 mb-5">
        <div class="card-header">
            <i class="bi bi-clock-history"></i> Últimas Vendas
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Data/Hora</th>
                            <th>Vendedor</th>
                            <th>Total</th>
                            <th>Supervisor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendas_recentes as $venda): ?>
                        <tr>
                            <td><strong>#<?= $venda['id'] ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($venda['data_venda'])) ?></td>
                            <td><?= htmlspecialchars($venda['vendedor']) ?></td>
                            <td><strong class="text-success">R$ <?= number_format($venda['total'], 2, ',', '.') ?></strong></td>
                            <td>
                                <?php if (!empty($venda['supervisor_liberacao'])): ?>
                                <span class="badge bg-warning text-dark">Controlado</span>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
    // Toggle para mostrar/ocultar todos os lotes vencendo
    let lotesExpanded = false;

    function toggleLotesVencendo() {
        const lotesExtras = document.querySelectorAll('.lote-extra');
        const icon = document.getElementById('icon-toggle-lotes');
        const text = document.getElementById('text-toggle-lotes');
        const totalLotes = Number("<?= $total_lotes_vencendo ?>");

        if (lotesExpanded) {
            // Recolher
            lotesExtras.forEach(lote => lote.classList.add('d-none'));
            icon.className = 'bi bi-chevron-down';
            text.textContent = `Ver todos (${totalLotes} lotes)`;
            lotesExpanded = false;
        } else {
            // Expandir
            lotesExtras.forEach(lote => lote.classList.remove('d-none'));
            icon.className = 'bi bi-chevron-up';
            text.textContent = 'Ver menos';
            lotesExpanded = true;
        }
    }
</script>

<?php 
// 4. Inclui o rodapé
include 'footer.php'; 
?>