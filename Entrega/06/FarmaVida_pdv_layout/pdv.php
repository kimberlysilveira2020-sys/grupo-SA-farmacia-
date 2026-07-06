<?php
require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$pdo = Config::getDbConnection();

$stmt = $pdo->query("
    SELECT p.id, p.nome, p.fabricante, p.categoria, p.foto,
           COALESCE(SUM(el.qtd_atual), 0) AS estoque_total,
           MIN(el.data_validade) AS validade_mais_proxima,
           p.preco_venda, p.descricao,
           DATEDIFF(MIN(el.data_validade), CURDATE()) AS dias_para_vencer 
    FROM produtos p 
    LEFT JOIN lotes el ON p.id = el.produto_id AND el.qtd_atual > 0 
    GROUP BY p.id, p.nome, p.fabricante, p.categoria, p.preco_venda, p.descricao, p.foto
    ORDER BY p.nome ASC
");
$produtos = $stmt->fetchAll();

foreach ($produtos as &$produto) {
    $dias = $produto['dias_para_vencer'];
    if ($dias !== null && $dias <= 30) {
        $produto['tem_desconto']        = true;
        $produto['percentual_desconto'] = 20;
        $produto['preco_original']      = $produto['preco_venda'];
        $produto['preco_venda']         = round($produto['preco_venda'] * 0.80, 2);
    } else {
        $produto['tem_desconto'] = false;
    }
}

$page_title = "PDV - Ponto de Venda";
include 'header.php';
?>

<!-- PDV ocupa a altura restante da viewport -->
<div class="pdv-wrapper">

    <!-- ══════════ COLUNA ESQUERDA — PRODUTOS ══════════ -->
    <div class="pdv-left">

        <div class="pdv-search-bar">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="busca-produto" class="form-control border-start-0 ps-0"
                       placeholder="Buscar produto ou fabricante... (F2)" autofocus
                       style="font-size:1rem; border-radius:0 8px 8px 0;">
            </div>
        </div>

        <div id="grid-produtos" class="pdv-grid">
            <?php if (!empty($produtos)): ?>
                <?php foreach ($produtos as $produto):
                    $tem_desconto     = !empty($produto['tem_desconto']);
                    $preco_db         = number_format($produto['preco_venda'], 2, '.', '');
                    $preco_br         = number_format($produto['preco_venda'], 2, ',', '.');
                    $preco_orig_br    = isset($produto['preco_original']) ? number_format($produto['preco_original'], 2, ',', '.') : '';
                    $sem_estoque      = $produto['estoque_total'] <= 0;
                ?>
                <div class="produto-card <?= $tem_desconto ? 'produto-card--promo' : '' ?> <?= $sem_estoque ? 'produto-card--esgotado' : '' ?>"
                     data-nome="<?= htmlspecialchars($produto['nome']) ?>"
                     data-fabricante="<?= htmlspecialchars($produto['fabricante']) ?>">

                    <!-- Foto -->
                    <div class="produto-card__foto">
                        <?php if (!empty($produto['foto']) && file_exists('uploads/produtos/' . $produto['foto'])): ?>
                        <img src="uploads/produtos/<?= htmlspecialchars($produto['foto']) ?>"
                             alt="<?= htmlspecialchars($produto['nome']) ?>">
                        <?php else: ?>
                        <div class="produto-card__foto-placeholder">
                            <i class="bi bi-capsule"></i>
                        </div>
                        <?php endif; ?>
                        <?php if ($tem_desconto): ?>
                        <span class="produto-card__badge-promo">-<?= $produto['percentual_desconto'] ?>%</span>
                        <?php endif; ?>
                    </div>

                    <!-- Info -->
                    <div class="produto-card__info">
                        <p class="produto-card__nome"><?= htmlspecialchars($produto['nome']) ?></p>
                        <p class="produto-card__fab"><?= htmlspecialchars($produto['fabricante']) ?></p>

                        <?php if ($produto['categoria'] === 'Controlado'): ?>
                        <span class="badge bg-warning text-dark produto-card__controlado">
                            <i class="bi bi-shield-exclamation"></i> Controlado
                        </span>
                        <?php endif; ?>

                        <div class="produto-card__preco-wrap">
                            <?php if ($tem_desconto): ?>
                            <span class="produto-card__preco-orig">R$ <?= $preco_orig_br ?></span>
                            <span class="produto-card__preco produto-card__preco--promo">R$ <?= $preco_br ?></span>
                            <?php else: ?>
                            <span class="produto-card__preco">R$ <?= $preco_br ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="produto-card__estoque <?= $sem_estoque ? 'text-danger' : 'text-success' ?>">
                            <i class="bi bi-<?= $sem_estoque ? 'x-circle' : 'check-circle' ?>"></i>
                            <?= $sem_estoque ? 'Sem estoque' : $produto['estoque_total'] . ' un.' ?>
                        </div>
                    </div>

                    <!-- Botão -->
                    <button class="btn-adicionar-produto produto-card__btn"
                            data-produto-id="<?= $produto['id'] ?>"
                            data-produto-nome="<?= htmlspecialchars($produto['nome']) ?>"
                            data-produto-preco="<?= $preco_db ?>"
                            data-produto-categoria="<?= htmlspecialchars($produto['categoria']) ?>"
                            data-produto-estoque="<?= $produto['estoque_total'] ?>"
                            <?= $sem_estoque ? 'disabled' : '' ?>>
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="alert alert-warning m-3">
                <i class="bi bi-exclamation-triangle"></i> Nenhum produto em estoque.
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════ COLUNA DIREITA — CARRINHO ══════════ -->
    <div class="pdv-right">

        <div class="pdv-right__header">
            <i class="bi bi-cart3"></i> Carrinho de Compras
            <span id="carrinho-count" class="badge bg-white text-dark ms-2" style="font-size:.75rem;">0</span>
        </div>

        <div class="carrinho-lista" id="lista-carrinho">
            <div class="carrinho-vazio">
                <i class="bi bi-cart-x"></i>
                <p>Carrinho vazio</p>
                <small>Adicione produtos ao lado</small>
            </div>
        </div>

        <div class="pdv-right__footer">
            <div class="carrinho-total">
                <span class="carrinho-total__label">TOTAL</span>
                <span class="carrinho-total__valor" id="total-venda">R$ 0,00</span>
            </div>

            <button class="btn-finalizar" id="btn-finalizar-venda" onclick="finalizarVenda()" disabled>
                <i class="bi bi-check-circle-fill"></i> FINALIZAR VENDA
                <small class="d-block" style="font-size:.7rem; opacity:.8;">F12</small>
            </button>

            <button class="btn-cancelar" onclick="cancelarVenda()">
                <i class="bi bi-x-circle"></i> Cancelar venda <small>(ESC)</small>
            </button>
        </div>
    </div>

</div>

<!-- ══════════ MODAL: MEDICAMENTO CONTROLADO ══════════ -->
<div class="modal fade modal-controlado" id="modalControlado" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-shield-exclamation"></i> Medicamento Controlado
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong>Atenção!</strong> Este medicamento requer autorização especial.
                </div>
                <h6>Produto: <strong id="nome-produto-controlado"></strong></h6>
                <hr>
                <div class="mb-3">
                    <label for="receita-upload" class="form-label">
                        <i class="bi bi-file-earmark-medical"></i> Upload da Receita Médica *
                    </label>
                    <input type="file" class="form-control" id="receita-upload" accept=".pdf,.jpg,.jpeg,.png" required>
                    <small class="text-muted">Formatos aceitos: PDF, JPG, PNG (máx. 5MB)</small>
                </div>
                <div class="mb-3">
                    <label for="senha-supervisor" class="form-label">
                        <i class="bi bi-lock-fill"></i> Senha do Supervisor *
                    </label>
                    <input type="password" class="form-control" id="senha-supervisor"
                           placeholder="Senha do farmacêutico responsável" required>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="mostrar-senha-supervisor">
                    <label class="form-check-label" for="mostrar-senha-supervisor">
                        <i class="bi bi-eye" id="icon-senha-supervisor"></i> Mostrar senha
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="confirmarControlado()">
                    <i class="bi bi-check-circle"></i> Confirmar e Adicionar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ══════════════════════════════════════════════
   PDV — LAYOUT DE DUAS COLUNAS FIXAS
══════════════════════════════════════════════ */
.pdv-wrapper {
    display: flex;
    height: calc(100vh - 56px); /* altura da navbar */
    overflow: hidden;
    background: #f4f6f9;
}

/* ── COLUNA ESQUERDA ── */
.pdv-left {
    flex: 1 1 60%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    padding: 16px;
    border-right: 1px solid #dee2e6;
}

.pdv-search-bar {
    margin-bottom: 14px;
    flex-shrink: 0;
}

.pdv-search-bar .form-control {
    font-size: .97rem;
    border: 2px solid #1976D2;
    border-left: none;
    border-radius: 0 8px 8px 0;
    box-shadow: none;
}
.pdv-search-bar .form-control:focus { border-color: #0D47A1; box-shadow: none; }
.pdv-search-bar .input-group-text { border: 2px solid #1976D2; border-right: none; border-radius: 8px 0 0 8px; }

.pdv-grid {
    flex: 1;
    overflow-y: auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 12px;
    padding-right: 4px;
}

/* ── CARD PRODUTO ── */
.produto-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    position: relative;
    transition: box-shadow .2s, transform .15s;
}
.produto-card:hover { box-shadow: 0 4px 16px rgba(25,118,210,.15); transform: translateY(-2px); }
.produto-card--promo { border-color: #e53935; }
.produto-card--esgotado { opacity: .55; }

.produto-card__foto {
    width: 100%;
    height: 100px;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    background: #f0f2f5;
}
.produto-card__foto img { width: 100%; height: 100%; object-fit: cover; }
.produto-card__foto-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    color: #bdbdbd; font-size: 2.2rem;
}
.produto-card__badge-promo {
    position: absolute; top: 6px; left: 6px;
    background: #e53935; color: #fff;
    font-size: .7rem; font-weight: 700;
    padding: 2px 7px; border-radius: 20px;
    box-shadow: 0 2px 6px rgba(229,57,53,.4);
}

.produto-card__info { flex: 1; }
.produto-card__nome { font-size: .92rem; font-weight: 700; color: #212121; margin: 0 0 2px; line-height: 1.2; }
.produto-card__fab  { font-size: .75rem; color: #757575; margin: 0 0 4px; }
.produto-card__controlado { font-size: .7rem; margin-bottom: 4px; }

.produto-card__preco-wrap { display: flex; align-items: baseline; gap: 6px; margin: 4px 0; }
.produto-card__preco { font-size: 1.05rem; font-weight: 700; color: #2e7d32; }
.produto-card__preco--promo { color: #e53935; }
.produto-card__preco-orig { font-size: .78rem; color: #9e9e9e; text-decoration: line-through; }

.produto-card__estoque { font-size: .75rem; }

.produto-card__btn {
    width: 100%;
    padding: 7px;
    border: none;
    border-radius: 8px;
    background: #1976D2;
    color: #fff;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: background .15s;
    flex-shrink: 0;
}
.produto-card__btn:hover:not(:disabled) { background: #1565C0; }
.produto-card__btn:disabled { background: #bdbdbd; cursor: not-allowed; }

/* ── COLUNA DIREITA ── */
.pdv-right {
    flex: 0 0 360px;
    display: flex;
    flex-direction: column;
    background: #fff;
    box-shadow: -2px 0 12px rgba(0,0,0,.07);
}

.pdv-right__header {
    background: linear-gradient(135deg, #1976D2, #0D47A1);
    color: #fff;
    font-size: 1.1rem;
    font-weight: 700;
    padding: 16px 20px;
    flex-shrink: 0;
}

.carrinho-lista {
    flex: 1;
    overflow-y: auto;
    padding: 12px 16px;
}

.carrinho-vazio {
    height: 100%;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    color: #bdbdbd; text-align: center;
}
.carrinho-vazio i { font-size: 3rem; margin-bottom: 8px; }
.carrinho-vazio p { font-size: 1rem; margin: 0; font-weight: 600; }
.carrinho-vazio small { font-size: .8rem; }

.carrinho-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}
.carrinho-item__nome { font-size: .88rem; font-weight: 700; margin: 0 0 2px; }
.carrinho-item__detalhe { font-size: .78rem; color: #757575; margin: 0; }
.carrinho-item__subtotal { font-weight: 700; color: #2e7d32; font-size: .92rem; white-space: nowrap; }
.carrinho-item__controles { display: flex; align-items: center; gap: 4px; }
.carrinho-item__controles button {
    width: 26px; height: 26px; border: none; border-radius: 6px;
    background: #e3f2fd; color: #1976D2; font-weight: 700; font-size: .9rem;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: background .1s;
}
.carrinho-item__controles button:hover { background: #bbdefb; }
.carrinho-item__controles button.btn-remover { background: #ffebee; color: #c62828; }
.carrinho-item__controles button.btn-remover:hover { background: #ffcdd2; }
.carrinho-item__qtd { font-size: .88rem; font-weight: 700; min-width: 22px; text-align: center; }

.pdv-right__footer {
    padding: 14px 16px;
    border-top: 1px solid #e0e0e0;
    flex-shrink: 0;
}

.carrinho-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #1976D2;
    color: #fff;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 12px;
}
.carrinho-total__label { font-size: .8rem; font-weight: 600; opacity: .85; letter-spacing: .5px; }
.carrinho-total__valor { font-size: 1.6rem; font-weight: 800; }

.btn-finalizar {
    width: 100%;
    padding: 13px;
    background: #2e7d32;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: background .15s;
    margin-bottom: 8px;
}
.btn-finalizar:hover:not(:disabled) { background: #1b5e20; }
.btn-finalizar:disabled { background: #a5d6a7; cursor: not-allowed; }

.btn-cancelar {
    width: 100%;
    padding: 9px;
    background: transparent;
    color: #c62828;
    border: 1.5px solid #c62828;
    border-radius: 10px;
    font-size: .88rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s, color .15s;
}
.btn-cancelar:hover { background: #ffebee; }

/* scrollbar discreta */
.pdv-grid::-webkit-scrollbar,
.carrinho-lista::-webkit-scrollbar { width: 5px; }
.pdv-grid::-webkit-scrollbar-thumb,
.carrinho-lista::-webkit-scrollbar-thumb { background: #bdbdbd; border-radius: 4px; }

/* responsivo */
@media (max-width: 768px) {
    .pdv-wrapper { flex-direction: column; height: auto; overflow: visible; }
    .pdv-left { border-right: none; border-bottom: 1px solid #dee2e6; height: 55vh; }
    .pdv-right { flex: none; height: 45vh; }
}
</style>

<script src="pdv_logic.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('mostrar-senha-supervisor').addEventListener('change', function () {
            const s = document.getElementById('senha-supervisor');
            const i = document.getElementById('icon-senha-supervisor');
            s.type = this.checked ? 'text' : 'password';
            i.className = this.checked ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });
</script>

<?php include 'footer.php'; ?>
