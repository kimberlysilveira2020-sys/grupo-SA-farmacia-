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

<div class="container-fluid fade-in">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-box-seam"></i> Gestão de Estoque e Produtos
        </h2>
        <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalNovoProduto">
            <i class="bi bi-plus-circle"></i> Novo Produto
        </button>
    </div>

    <?php if (!empty($produtos)): ?>
    <div class="accordion" id="accordionProdutos">
        <?php foreach ($produtos as $produto): ?>
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading-<?= $produto['id'] ?>">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapse-<?= $produto['id'] ?>">
                    <span class="d-flex justify-content-between w-100 me-3">
                        <span>
                            <strong><?= htmlspecialchars($produto['nome']) ?></strong>
                            <span class="badge bg-secondary ms-2"><?= htmlspecialchars($produto['fabricante']) ?></span>
                            
                            <?php if ($produto['categoria'] === 'Controlado'): ?>
                            <span class="badge badge-controlado ms-2">Controlado</span>
                            <?php endif; ?>
                        </span>
                        <span>
                            <span class="badge bg-primary">Estoque Total: <?= $produto['estoque_total'] ?></span>
                        </span>
                    </span>
                </button>
            </h2>

            <div id="collapse-<?= $produto['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#accordionProdutos">
                <div class="accordion-body">

                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn btn-success btn-sm btn-adicionar-lote"
                            data-produto-id="<?= $produto['id'] ?>" 
                            data-produto-nome="<?= htmlspecialchars($produto['nome']) ?>">
                            <i class="bi bi-plus-circle"></i> Adicionar Lote
                        </button>
                        <button type="button" class="btn btn-warning btn-sm btn-editar-produto"
                            data-id="<?= $produto['id'] ?>" 
                            data-nome="<?= htmlspecialchars($produto['nome']) ?>"
                            data-fabricante="<?= htmlspecialchars($produto['fabricante']) ?>" 
                            data-categoria="<?= htmlspecialchars($produto['categoria']) ?>"
                            data-preco="<?= number_format($produto['preco_venda'], 2, '.', '') ?>"
                            data-descricao="<?= htmlspecialchars($produto['descricao'] ?? '') ?>">
                            <i class="bi bi-pencil"></i> Editar Produto
                        </button>
                        <button type="button" class="btn btn-danger btn-sm btn-deletar-produto"
                            data-id="<?= $produto['id'] ?>">
                            <i class="bi bi-trash"></i> Excluir Produto
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tabela-lotes-<?= $produto['id'] ?>">
                            <thead class="table-primary">
                                <tr>
                                    <th>Nº Lote</th>
                                    <th>Data de Validade</th>
                                    <th>Dias Restantes</th>
                                    <th>Quantidade</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <span class="spinner-border spinner-border-sm"></span>
                                        Carregando lotes...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        Nenhum produto cadastrado. Clique em "Novo Produto" para começar.
    </div>
    <?php endif; ?>

</div>

<div class="modal fade" id="modalNovoProduto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle"></i> Cadastrar Novo Produto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNovoProduto" onsubmit="salvarProduto(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Comercial *</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>

                    <div class="mb-3">
                        <label for="fabricante" class="form-label">Fabricante *</label>
                        <input type="text" class="form-control" id="fabricante" name="fabricante" required>
                    </div>

                    <div class="mb-3">
                        <label for="categoria" class="form-label">Categoria *</label>
                        <select class="form-select" id="categoria" name="categoria" required>
                            <option value="">Selecione...</option>
                            <option value="Comum">Comum</option>
                            <option value="Controlado">Controlado</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="preco_venda" class="form-label">Preço de Venda (R$) *</label>
                        <input type="number" class="form-control" id="preco_venda" name="preco_venda" step="0.01" min="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Salvar Produto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNovoLote" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-box"></i> Adicionar Lote ao Estoque
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNovoLote" onsubmit="salvarLote(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="produto_nome_display">Produto</label>
                        <input type="text" class="form-control" id="produto_nome_display" readonly>
                        <input type="hidden" id="produto_id_lote" name="produto_id">
                    </div>

                    <div class="mb-3">
                        <label for="numero_lote" class="form-label">Número do Lote *</label>
                        <input type="text" class="form-control" id="numero_lote" name="numero_lote" required>
                    </div>

                    <div class="mb-3">
                        <label for="data_validade" class="form-label">Data de Validade *</label>
                        <input type="date" class="form-control" id="data_validade" name="data_validade" required>
                    </div>

                    <div class="mb-3">
                        <label for="qtd_atual" class="form-label">Quantidade *</label>
                        <input type="number" class="form-control" id="qtd_atual" name="qtd_atual" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Adicionar Lote
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarProduto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i> Editar Produto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarProduto" onsubmit="salvarEdicao(event)">
                <div class="modal-body">
                    <input type="hidden" id="edit_produto_id" name="id">

                    <div class="mb-3">
                        <label for="edit_nome" class="form-label">Nome Comercial *</label>
                        <input type="text" class="form-control" id="edit_nome" name="nome" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_fabricante" class="form-label">Fabricante *</label>
                        <input type="text" class="form-control" id="edit_fabricante" name="fabricante" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_categoria" class="form-label">Categoria *</label>
                        <select class="form-select" id="edit_categoria" name="categoria" required>
                            <option value="Comum">Comum</option>
                            <option value="Controlado">Controlado</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_preco_venda" class="form-label">Preço de Venda (R$) *</label>
                        <input type="number" class="form-control" id="edit_preco_venda" name="preco_venda" step="0.01" min="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="edit_descricao" name="descricao" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    async function salvarProduto(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        try {
            const response = await fetch('api.php?endpoint=produtos_criar', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) location.reload(); 
            else alert('Erro: ' + result.message);
        } catch (error) { alert('Erro ao salvar produto'); }
    }

    async function salvarLote(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        try {
            const response = await fetch('api.php?endpoint=lotes_criar', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) location.reload(); 
            else alert('Erro: ' + result.message);
        } catch (error) { alert('Erro ao salvar lote'); }
    }

    async function salvarEdicao(event) {
        event.preventDefault();
        const data = {
            id: document.getElementById('edit_produto_id').value,
            nome: document.getElementById('edit_nome').value,
            fabricante: document.getElementById('edit_fabricante').value,
            categoria: document.getElementById('edit_categoria').value,
            preco_venda: document.getElementById('edit_preco_venda').value,
            descricao: document.getElementById('edit_descricao').value
        };
        try {
            const response = await fetch('api.php?endpoint=produtos_editar', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) location.reload();
            else alert('Erro: ' + result.message);
        } catch (error) { alert('Erro ao atualizar produto'); }
    }

    async function deletarProduto(id) {
        if (confirm('Excluir este produto?')) {
            try {
                const response = await fetch('api.php?endpoint=produtos_deletar', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: id })
                });
                const result = await response.json();
                if (result.success) location.reload();
                else alert('Erro: ' + result.message);
            } catch (error) { alert('Erro ao excluir produto'); }
        }
    }
    
    // (A parte do accordion para expandir os lotes e chamar 'api.php?endpoint=lotes_listar&produto_id=X')
    // Substitua a URL do fetch no evento click do accordion por:
    // const response = await fetch(`api.php?endpoint=lotes_listar&produto_id=${produtoId}`);
</script>
?>