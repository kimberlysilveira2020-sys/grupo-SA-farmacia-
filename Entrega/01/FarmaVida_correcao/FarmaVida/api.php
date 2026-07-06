<?php

require_once 'config.php';

header('Content-Type: application/json');

function json_response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if (!isset($_SESSION['usuario_id'])) {
    json_response(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.'], 401);
}

// Conecta diretamente ao banco (eliminando necessidade do db.php)
$pdo = Config::getDbConnection();
$endpoint = $_GET['endpoint'] ?? '';

try {
    switch ($endpoint) {
        
        // SALVA PRODUTO
        case 'produtos_criar':
            $stmt = $pdo->prepare("INSERT INTO produtos (nome, fabricante, categoria, preco_venda, descricao) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['nome'] ?? '', 
                $_POST['fabricante'] ?? '', 
                $_POST['categoria'] ?? '', 
                $_POST['preco_venda'] ?? 0, 
                $_POST['descricao'] ?? ''
            ]);
            json_response(['success' => true]);
            break;

        // EDITA PRODUTO
        case 'produtos_editar':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE produtos SET nome=?, fabricante=?, categoria=?, preco_venda=?, descricao=? WHERE id=?");
            $stmt->execute([
                $data['nome'], $data['fabricante'], $data['categoria'], 
                $data['preco_venda'], $data['descricao'], $data['id']
            ]);
            json_response(['success' => true]);
            break;

        // DELETA PRODUTO
        case 'produtos_deletar':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("DELETE FROM produtos WHERE id=?");
            $stmt->execute([$data['id']]);
            json_response(['success' => true]);
            break;

        // LISTA LOTES DO PRODUTO (Accordion)
        case 'lotes_listar':
            $stmt = $pdo->prepare("SELECT id, numero_lote, data_validade, qtd_atual, DATEDIFF(data_validade, CURDATE()) AS dias_para_vencer, CASE WHEN data_validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END AS vencendo FROM lotes WHERE produto_id = ? ORDER BY data_validade ASC");
            $stmt->execute([(int)$_GET['produto_id']]);
            json_response(['success' => true, 'lotes' => $stmt->fetchAll()]);
            break;

        // SALVA LOTE
        case 'lotes_criar':
            $stmt = $pdo->prepare("INSERT INTO lotes (produto_id, numero_lote, data_validade, qtd_atual, qtd_inicial) VALUES (?, ?, ?, ?, ?)");
            $qtd = $_POST['qtd_atual'] ?? 0;
            $stmt->execute([
                $_POST['produto_id'], 
                $_POST['numero_lote'], 
                $_POST['data_validade'], 
                $qtd, 
                $qtd
            ]);
            json_response(['success' => true]);
            break;

        // SALVA VENDA E DÁ BAIXA NO ESTOQUE
        case 'venda_finalizar':
            $itens = json_decode($_POST['itens'] ?? '[]', true);
            $supervisor = $_POST['supervisor'] ?? null;

            if ($supervisor && $supervisor !== Config::SENHA_SUPERVISOR_MESTRA) {
                json_response(['success' => false, 'message' => 'Senha do supervisor incorreta!'], 403);
            }

            // Inicia Transação Segura para Venda
            $pdo->beginTransaction();

            $total = array_sum(array_map(function($i) { return $i['quantidade'] * $i['preco']; }, $itens));

            // Insere cabeçalho
            $stmtVenda = $pdo->prepare("INSERT INTO vendas (total, usuario_id, supervisor_liberacao) VALUES (?, ?, ?)");
            $stmtVenda->execute([$total, $_SESSION['usuario_id'], $supervisor]);
            $venda_id = $pdo->lastInsertId();

            $stmtItem = $pdo->prepare("INSERT INTO itens_venda (venda_id, produto_id, lote_id, quantidade, preco) VALUES (?, ?, ?, ?, ?)");
            $stmtEstoque = $pdo->prepare("UPDATE lotes SET qtd_atual = qtd_atual - ? WHERE id = ?");
            $stmtBuscaLote = $pdo->prepare("SELECT id, qtd_atual FROM lotes WHERE produto_id = ? AND qtd_atual >= ? ORDER BY data_validade ASC LIMIT 1");

            foreach ($itens as $item) {
                // Busca lote mais antigo (FEFO) que tenha a quantidade necessária
                $stmtBuscaLote->execute([$item['produto_id'], $item['quantidade']]);
                $lote = $stmtBuscaLote->fetch();
                
                if (!$lote) {
                    throw new Exception("Estoque insuficiente ou lote fragmentado para {$item['nome']}.");
                }

                $stmtItem->execute([$venda_id, $item['produto_id'], $lote['id'], $item['quantidade'], $item['preco']]);
                $stmtEstoque->execute([$item['quantidade'], $lote['id']]);
            }

            $pdo->commit();
            json_response(['success' => true, 'venda_id' => $venda_id]);
            break;

        default:
            json_response(['success' => false, 'message' => 'Endpoint da API não encontrado.'], 404);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(['success' => false, 'message' => $e->getMessage()], 500);
}
?>