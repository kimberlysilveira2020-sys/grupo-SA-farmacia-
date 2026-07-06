<div class="container-fluid fade-in">

    <h2 class="mb-4">
        <i class="bi bi-file-earmark-bar-graph"></i> Relatórios Gerenciais
    </h2>

    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <i class="bi bi-exclamation-triangle-fill"></i> Lotes Vencendo (Próximos 30 Dias)
        </div>
        <div class="card-body">
            <?php if (!empty($lotes_vencendo)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Fabricante</th>
                            <th>Nº Lote</th>
                            <th>Validade</th>
                            <th>Dias Restantes</th>
                            <th>Quantidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lotes_vencendo as $lote): ?>
                        <?php $is_critical = ($lote['dias_para_vencer'] <= 7); ?>
                        <tr class="<?= $is_critical ? 'table-danger' : '' ?>">
                            <td><strong><?= htmlspecialchars($lote['produto_nome']) ?></strong></td>
                            <td><?= htmlspecialchars($lote['fabricante']) ?></td>
                            <td><?= htmlspecialchars($lote['numero_lote']) ?></td>
                            <td><?= date('d/m/Y', strtotime($lote['data_validade'])) ?></td>
                            <td>
                                <span class="badge <?= $is_critical ? 'bg-danger' : 'bg-warning text-dark' ?>">
                                    <?= $lote['dias_para_vencer'] ?> dia(s)
                                </span>
                            </td>
                            <td><?= $lote['qtd_atual'] ?> un.</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-success mb-0">
                <i class="bi bi-check-circle"></i> Nenhum lote vencendo nos próximos 30 dias.
            </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-clock-history"></i> Últimas Vendas
        </div>
        <div class="card-body">
            <?php if (!empty($vendas)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Data/Hora</th>
                            <th>Vendedor</th>
                            <th>Valor Total</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendas as $venda): ?>
                        <tr>
                            <td><strong>#<?= $venda['id'] ?></strong></td>
                            <td><?= date('d/m/Y H:i:s', strtotime($venda['data_venda'])) ?></td>
                            <td>
                                <?= htmlspecialchars($venda['vendedor']) ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($venda['cargo_vendedor']) ?></span>
                            </td>
                            <td>
                                <strong class="text-success">R$ <?= number_format($venda['total'], 2, ',', '.') ?></strong>
                            </td>
                            <td>
                                <?php if (!empty($venda['supervisor_liberacao'])): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-shield-check"></i> Controlado
                                </span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Comum</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <hr>
            <div class="row text-center">
                <div class="col-md-6">
                    <h5>Total de Vendas</h5>
                    <h3 class="text-primary"><?= $total_vendas_count ?></h3>
                </div>
                <div class="col-md-6">
                    <h5>Valor Total</h5>
                    <h3 class="text-success">R$ <?= number_format($valor_total_vendas, 2, ',', '.') ?></h3>
                </div>
            </div>
            <?php else: ?>
            <p class="text-muted mb-0">Nenhuma venda registrada.</p>
            <?php endif; ?>
        </div>
    </div>

</div>