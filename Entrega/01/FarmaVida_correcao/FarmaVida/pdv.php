<?php

require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$pdo = Config::getDbConnection();

// Consulta direta de produtos com estoque calculado
$stmt = $pdo->query("
    SELECT p.id, p.nome, p.fabricante, p.categoria, COALESCE(SUM(el.qtd_atual), 0) AS estoque_total, MIN(el.data_validade) AS validade_mais_proxima, p.preco_venda, p.descricao, DATEDIFF(MIN(el.data_validade), CURDATE()) AS dias_para_vencer 
    FROM produtos p 
    LEFT JOIN lotes el ON p.id = el.produto_id AND el.qtd_atual > 0 
    GROUP BY p.id, p.nome, p.fabricante, p.categoria, p.preco_venda, p.descricao 
    ORDER BY p.nome ASC
");
$produtos = $stmt->fetchAll();

// Aplica descontos via PHP para exibição
foreach ($produtos as &$produto) {
    $dias = $produto['dias_para_vencer'];
    if ($dias !== null && $dias <= 30) {
        $produto['tem_desconto'] = true;
        $produto['percentual_desconto'] = 20;
        $produto['preco_original'] = $produto['preco_venda'];
        $produto['preco_venda'] = round($produto['preco_venda'] * 0.80, 2);
    } else {
        $produto['tem_desconto'] = false;
    }
}

// Em pdv.php mude apenas o nome abaixo para "PDV - Ponto de Venda"
$page_title = "Gestão de Produtos"; 
include 'header.php'; 
?>

<div class="container-fluid p-0">

    <div class="row g-0 pdv-container">

        <div class="col-md-7 pdv-left">
            <h3 class="mb-3">
                <i class="bi bi-search"></i> Buscar Produtos
            </h3>

            <input type="text" id="busca-produto" class="pdv-search"
                placeholder="Digite o nome do produto ou fabricante... (F2)" autofocus>

            <div id="grid-produtos">
                <?php if (!empty($produtos)): ?>
                    <?php foreach ($produtos as $produto): ?>
                    
                    <?php 
                        // Variáveis auxiliares para facilitar a leitura no HTML
                        $tem_desconto = !empty($produto['tem_desconto']) ? true : false;
                        $borda_card = $tem_desconto ? 'border-warning' : '';
                        
                        // Formatação de preços
                        $preco_venda_db = number_format($produto['preco_venda'], 2, '.', ''); // Para os data-attributes (JS)
                        $preco_venda_br = number_format($produto['preco_venda'], 2, ',', '.'); // Para exibição na tela
                        $preco_original_br = isset($produto['preco_original']) ? number_format($produto['preco_original'], 2, ',', '.') : '0,00';
                    ?>

                    <div class="produto-card <?= $borda_card ?>"
                        data-nome="<?= htmlspecialchars($produto['nome']) ?>" 
                        data-fabricante="<?= htmlspecialchars($produto['fabricante']) ?>">
                        
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h5><?= htmlspecialchars($produto['nome']) ?></h5>
                                <small class="text-muted"><?= htmlspecialchars($produto['fabricante']) ?></small>

                                <?php if ($produto['categoria'] === 'Controlado'): ?>
                                <span class="badge badge-controlado d-block mt-2">
                                    <i class="bi bi-shield-exclamation"></i> Medicamento Controlado
                                </span>
                                <?php endif; ?>

                                <?php if ($tem_desconto): ?>
                                <div class="mt-2">
                                    <span class="badge bg-danger fs-6 animate__animated animate__pulse animate__infinite">
                                        🏷️ -<?= $produto['percentual_desconto'] ?>% PROMOÇÃO
                                    </span>
                                    <br>
                                    <small class="text-muted">Vence em <?= $produto['dias_para_vencer'] ?> dias</small>
                                </div>
                                <?php endif; ?>

                                <div class="mt-2">
                                    <?php if ($tem_desconto): ?>
                                        <span class="text-decoration-line-through text-muted">R$ <?= $preco_original_br ?></span>
                                        <span class="preco text-danger fw-bold fs-4">R$ <?= $preco_venda_br ?></span>
                                    <?php else: ?>
                                        <span class="preco">R$ <?= $preco_venda_br ?></span>
                                    <?php endif; ?>
                                    <br>
                                    <small class="text-secondary">
                                        <i class="bi bi-box"></i> Estoque: <?= $produto['estoque_total'] ?>
                                    </small>
                                </div>
                            </div>

                            <button class="btn btn-success btn-adicionar-produto" 
                                data-produto-id="<?= $produto['id'] ?>"
                                data-produto-nome="<?= htmlspecialchars($produto['nome']) ?>" 
                                data-produto-preco="<?= $preco_venda_db ?>"
                                data-produto-categoria="<?= htmlspecialchars($produto['categoria']) ?>"
                                data-produto-estoque="<?= $produto['estoque_total'] ?>">
                                <i class="bi bi-plus-circle"></i> Adicionar
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Nenhum produto disponível no estoque.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-5 pdv-right">
            <h3 class="mb-4">
                <i class="bi bi-cart-fill"></i> Carrinho de Compras
            </h3>

            <div class="carrinho-lista" id="lista-carrinho">
                <p class="text-center text-muted">Carrinho vazio</p>
            </div>

            <div class="carrinho-total">
                <h6 class="mb-1">TOTAL</h6>
                <h2 id="total-venda">R$ 0,00</h2>
            </div>

            <button class="btn btn-finalizar mb-2" id="btn-finalizar-venda" onclick="finalizarVenda()" disabled>
                <i class="bi bi-check-circle"></i> FINALIZAR VENDA (F12)
            </button>

            <button class="btn btn-outline-danger w-100" onclick="cancelarVenda()">
                <i class="bi bi-x-circle"></i> Cancelar (ESC)
            </button>
        </div>

    </div>
</div>

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
                        placeholder="Digite a senha do farmacêutico responsável" required>
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="mostrar-senha-supervisor">
                    <label class="form-check-label" for="mostrar-senha-supervisor">
                        <i class="bi bi-eye" id="icon-senha-supervisor"></i> Mostrar senha
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-warning" onclick="confirmarControlado()">
                    <i class="bi bi-check-circle"></i> Confirmar e Adicionar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="js/pdv_logic.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('mostrar-senha-supervisor').addEventListener('change', function () {
            const senhaInput = document.getElementById('senha-supervisor');
            const icon = document.getElementById('icon-senha-supervisor');

            if (this.checked) {
                senhaInput.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                senhaInput.type = 'password';
                icon.className = 'bi bi-eye';
            }
        });
    });
</script>

<?php 
// 4. Inclui o rodapé da página
include 'footer.php'; 
?>