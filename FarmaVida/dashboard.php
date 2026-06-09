<?php
require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$pdo = Config::getDbConnection();

// Banners ativos e dentro do período
$stmtBanners = $pdo->query("
    SELECT * FROM banners 
    WHERE ativo = 1 
      AND (data_inicio IS NULL OR data_inicio <= CURDATE())
      AND (data_fim IS NULL OR data_fim >= CURDATE())
    ORDER BY ordem ASC
");
$banners = $stmtBanners->fetchAll();

// Lotes Vencendo (30 dias)
$stmtLotes = $pdo->query("
    SELECT el.id AS lote_id, p.nome AS produto_nome, p.fabricante, el.numero_lote, el.data_validade, el.qtd_atual, DATEDIFF(el.data_validade, CURDATE()) AS dias_para_vencer 
    FROM lotes el 
    INNER JOIN produtos p ON el.produto_id = p.id 
    WHERE el.data_validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND el.qtd_atual > 0 
    ORDER BY el.data_validade ASC
");
$lotes_vencendo = $stmtLotes->fetchAll();
$total_lotes_vencendo = count($lotes_vencendo);

// Vendas Recentes
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

    <!-- ===== BANNER DE PROMOÇÕES ===== -->
    <?php if (!empty($banners)): ?>
    <div class="card mb-4 mt-3 border-0 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #1976D2, #0D47A1); color:white; border:none; border-radius: 10px 10px 0 0;">
            <span><i class="bi bi-megaphone-fill me-2"></i><strong>Promoções em Destaque</strong></span>
            <?php if ($_SESSION['usuario_cargo'] === 'Gerente'): ?>
            <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#modalGerenciarBanners">
                <i class="bi bi-gear-fill"></i> Gerenciar
            </button>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <div id="carouselBanners" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
                <div class="carousel-indicators">
                    <?php foreach ($banners as $i => $b): ?>
                    <button type="button" data-bs-target="#carouselBanners" data-bs-slide-to="<?= $i ?>" <?= $i === 0 ? 'class="active"' : '' ?>></button>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-inner" style="border-radius: 0 0 10px 10px; overflow:hidden;">
                    <?php foreach ($banners as $i => $banner): ?>
                    <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                        <?php if (!empty($banner['imagem']) && file_exists('uploads/banners/' . $banner['imagem'])): ?>
                            <img src="uploads/banners/<?= htmlspecialchars($banner['imagem']) ?>" class="d-block w-100" style="max-height:260px; object-fit:cover;" alt="<?= htmlspecialchars($banner['titulo']) ?>">
                            <div class="carousel-caption d-flex flex-column justify-content-end" style="background:linear-gradient(0deg, rgba(0,0,0,.65) 0%, transparent 100%); bottom:0; left:0; right:0; padding:24px 20px 20px;">
                                <h4 class="fw-bold mb-1"><?= htmlspecialchars($banner['titulo']) ?></h4>
                                <?php if (!empty($banner['descricao'])): ?>
                                <p class="mb-0" style="font-size:.95rem;"><?= htmlspecialchars($banner['descricao']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="banner-slide d-flex align-items-center justify-content-center" style="background: <?= htmlspecialchars($banner['cor_fundo']) ?>; min-height:220px; padding: 40px 60px;">
                                <div class="text-white text-center">
                                    <h3 class="fw-bold mb-2" style="font-size:1.7rem; text-shadow:0 2px 8px rgba(0,0,0,.3);">
                                        <?= htmlspecialchars($banner['titulo']) ?>
                                    </h3>
                                    <?php if (!empty($banner['descricao'])): ?>
                                    <p class="mb-0 fs-5" style="opacity:.92;">
                                        <?= htmlspecialchars($banner['descricao']) ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($banners) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselBanners" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselBanners" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <!-- ===== FIM BANNER ===== -->

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
            <a href="vendas.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="bi bi-cart-fill text-primary"></i>
                        <h3>Vendas</h3>
                        <p class="text-muted">Controle de Vendas</p>
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

<!-- ===== MODAL GERENCIAR BANNERS (só para Gerente) ===== -->
<?php if (($_SESSION['usuario_cargo'] ?? '') === 'Gerente'): ?>
<div class="modal fade" id="modalGerenciarBanners" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg,#1976D2,#0D47A1); color:white;">
                <h5 class="modal-title"><i class="bi bi-megaphone-fill me-2"></i>Gerenciar Banners de Promoção</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <!-- Lista de banners existentes -->
                <h6 class="fw-bold mb-3"><i class="bi bi-list-ul"></i> Banners Cadastrados</h6>
                <div id="lista-banners">
                    <?php foreach ($banners as $b): ?>
                    <div class="d-flex align-items-center justify-content-between p-2 mb-2 rounded border" style="background:<?= htmlspecialchars($b['cor_fundo']) ?>18;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded" style="width:12px;height:40px;background:<?= htmlspecialchars($b['cor_fundo']) ?>;"></div>
                            <div>
                                <strong><?= htmlspecialchars($b['titulo']) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($b['descricao'] ?? '') ?></small>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" onclick="deletarBanner(<?= $b['id'] ?>)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($banners)): ?>
                    <p class="text-muted text-center">Nenhum banner cadastrado.</p>
                    <?php endif; ?>
                </div>

                <hr>

                <!-- Formulário novo banner -->
                <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle"></i> Adicionar Novo Banner</h6>
                <div id="feedback-banner"></div>
                <div class="mb-3">
                    <label class="form-label">Título *</label>
                    <input type="text" class="form-control" id="banner_titulo" placeholder="Ex: 🌿 Semana da Saúde" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <input type="text" class="form-control" id="banner_descricao" placeholder="Texto de apoio exibido no banner">
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Cor de Fundo</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="banner_cor" value="#1976D2">
                            <span class="input-group-text" id="preview-cor" style="background:#1976D2; width:40px;"></span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Data Início</label>
                        <input type="date" class="form-control" id="banner_inicio">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Data Fim</label>
                        <input type="date" class="form-control" id="banner_fim">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Imagem do Banner <small class="text-muted">(opcional – JPG/PNG, max 2MB)</small></label>
                    <input type="file" class="form-control" id="banner_imagem" accept="image/jpeg,image/png,image/webp">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="salvarBanner()">
                    <i class="bi bi-save"></i> Salvar Banner
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    let lotesExpanded = false;

    function toggleLotesVencendo() {
        const lotesExtras = document.querySelectorAll('.lote-extra');
        const icon = document.getElementById('icon-toggle-lotes');
        const text = document.getElementById('text-toggle-lotes');
        const totalLotes = Number("<?= $total_lotes_vencendo ?>");

        if (lotesExpanded) {
            lotesExtras.forEach(lote => lote.classList.add('d-none'));
            icon.className = 'bi bi-chevron-down';
            text.textContent = `Ver todos (${totalLotes} lotes)`;
            lotesExpanded = false;
        } else {
            lotesExtras.forEach(lote => lote.classList.remove('d-none'));
            icon.className = 'bi bi-chevron-up';
            text.textContent = 'Ver menos';
            lotesExpanded = true;
        }
    }

    // Preview cor do banner
    document.getElementById('banner_cor')?.addEventListener('input', function() {
        document.getElementById('preview-cor').style.background = this.value;
    });

    async function salvarBanner() {
        const titulo = document.getElementById('banner_titulo').value.trim();
        if (!titulo) { alert('Informe o título do banner.'); return; }

        const fd = new FormData();
        fd.append('titulo', titulo);
        fd.append('descricao', document.getElementById('banner_descricao').value);
        fd.append('cor_fundo', document.getElementById('banner_cor').value);
        fd.append('data_inicio', document.getElementById('banner_inicio').value);
        fd.append('data_fim', document.getElementById('banner_fim').value);

        const imgFile = document.getElementById('banner_imagem').files[0];
        if (imgFile) fd.append('imagem', imgFile);

        const fb = document.getElementById('feedback-banner');
        fb.innerHTML = '<div class="alert alert-info py-2"><span class="spinner-border spinner-border-sm me-2"></span>Salvando...</div>';

        try {
            const res = await fetch('api.php?endpoint=banner_criar', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                fb.innerHTML = '<div class="alert alert-success py-2"><i class="bi bi-check-circle-fill me-1"></i>Banner salvo! Recarregando...</div>';
                setTimeout(() => location.reload(), 1200);
            } else {
                fb.innerHTML = `<div class="alert alert-danger py-2">${data.message}</div>`;
            }
        } catch(e) {
            fb.innerHTML = '<div class="alert alert-danger py-2">Erro ao salvar banner.</div>';
        }
    }

    async function deletarBanner(id) {
        if (!confirm('Remover este banner?')) return;
        const res = await fetch('api.php?endpoint=banner_deletar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Erro: ' + data.message);
    }
</script>

<?php include 'footer.php'; ?>