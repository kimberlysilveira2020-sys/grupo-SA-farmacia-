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

// Garante que coluna 'ativo' existe
$pdo = Config::getDbConnection();
$colAtivo = $pdo->query("SHOW COLUMNS FROM produtos LIKE 'ativo'")->fetchAll();
if (empty($colAtivo)) {
    $pdo->exec("ALTER TABLE produtos ADD COLUMN `ativo` TINYINT(1) NOT NULL DEFAULT 1");
    $pdo->exec("UPDATE produtos SET ativo = 1 WHERE ativo IS NULL");
}

// Remove soft-deleted sem histórico (sem venda interna E sem pedido de loja)
// Mantém apenas os que têm referências históricas
try {
    $pdo->exec("
        DELETE FROM produtos
        WHERE COALESCE(ativo,1) = 0
          AND id NOT IN (SELECT DISTINCT produto_id FROM itens_venda)
          AND id NOT IN (SELECT DISTINCT produto_id FROM pedido_itens)
    ");
} catch (\Exception $e) { /* tabelas ainda podem não existir */ }

// Garantir índice UNIQUE na criação de produtos (evita race condition)
try {
    $indexExists = $pdo->query("
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_NAME='produtos' AND COLUMN_NAME='nome' AND SEQ_IN_INDEX=1
        AND INDEX_NAME LIKE 'idx_%' AND NON_UNIQUE=0
    ")->fetch();
    if (!$indexExists) {
        $pdo->exec("ALTER TABLE produtos ADD UNIQUE INDEX idx_nome_fabricante_ativo (nome, fabricante, ativo)");
    }
} catch (\Exception $e) { /* já existe ou não consegue */ }

$endpoint = $_GET['endpoint'] ?? '';

// ─── Helper: salvar imagem enviada via upload ───────────────────────────────
function salvarImagem(string $campo, string $pasta): ?string {
    if (empty($_FILES[$campo]['tmp_name'])) return null;

    $file = $_FILES[$campo];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > $maxSize) throw new Exception('Imagem muito grande (máx. 2MB).');

    $mime = mime_content_type($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) throw new Exception('Tipo de imagem não permitido. Use JPG, PNG ou WEBP.');

    $ext  = $allowed[$mime];
    $nome = uniqid('img_', true) . '.' . $ext;
    $dir  = __DIR__ . '/uploads/' . $pasta . '/';

    if (!is_dir($dir)) mkdir($dir, 0755, true);

    if (!move_uploaded_file($file['tmp_name'], $dir . $nome)) {
        throw new Exception('Falha ao mover arquivo para o servidor.');
    }

    return $nome;
}

try {
    switch ($endpoint) {

        // ── CRIAR PRODUTO (com foto e lote inicial opcional) ────────────────
        case 'produtos_criar':
            $nome      = trim($_POST['nome'] ?? '');
            $fabric    = trim($_POST['fabricante'] ?? '');
            $cat       = trim($_POST['categoria'] ?? '');
            $preco     = $_POST['preco_venda'] ?? 0;
            $desc      = trim($_POST['descricao'] ?? '');

            if (!$nome || !$fabric || !$cat || !$preco) {
                json_response(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
            }

            $foto = salvarImagem('foto', 'produtos');

            $pdo->beginTransaction();
            try {
                // Busca produto com mesmo nome+fabricante independente do status ativo
                $chk = $pdo->prepare("SELECT id, ativo FROM produtos WHERE nome = ? AND fabricante = ? LIMIT 1 FOR UPDATE");
                $chk->execute([$nome, $fabric]);
                $existente = $chk->fetch();

                if ($existente) {
                    if ((int)$existente['ativo'] === 1) {
                        // Produto já ativo — não duplicar
                        $pdo->rollBack();
                        json_response(['success' => false, 'message' => 'Já existe um produto com este nome e fabricante.']);
                    }

                    // Produto estava desativado (soft-delete) — reativar com novos dados
                    $produtoId = (int)$existente['id'];
                    $stmtReativa = $pdo->prepare(
                        "UPDATE produtos SET categoria=?, preco_venda=?, descricao=?, ativo=1" .
                        ($foto ? ", foto=?" : "") .
                        " WHERE id=?"
                    );
                    $params = [$cat, $preco, $desc];
                    if ($foto) $params[] = $foto;
                    $params[] = $produtoId;
                    $stmtReativa->execute($params);
                } else {
                    // Produto novo — inserir
                    $stmt = $pdo->prepare("INSERT INTO produtos (nome, fabricante, categoria, preco_venda, descricao, foto, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)");
                    $stmt->execute([$nome, $fabric, $cat, $preco, $desc, $foto]);
                    $produtoId = (int)$pdo->lastInsertId();
                }

                // Lote inicial (opcional)
                $loteNum = trim($_POST['lote_numero'] ?? '');
                $loteVal = trim($_POST['lote_validade'] ?? '');
                $loteQtd = intval($_POST['lote_quantidade'] ?? 0);

                if ($loteNum && $loteVal && $loteQtd > 0) {
                    $stmtL = $pdo->prepare("INSERT INTO lotes (produto_id, numero_lote, data_validade, qtd_atual, qtd_inicial) VALUES (?, ?, ?, ?, ?)");
                    $stmtL->execute([$produtoId, $loteNum, $loteVal, $loteQtd, $loteQtd]);
                }

                $pdo->commit();
                json_response(['success' => true, 'produto_id' => $produtoId]);
            } catch (\PDOException $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        // ── EDITAR PRODUTO via FormData (suporta upload de foto) ───────────
        case 'produtos_editar_form':
            $id    = intval($_POST['id'] ?? 0);
            $nome  = trim($_POST['nome'] ?? '');
            $fab   = trim($_POST['fabricante'] ?? '');
            $cat   = trim($_POST['categoria'] ?? '');
            $preco = $_POST['preco_venda'] ?? 0;
            $desc  = trim($_POST['descricao'] ?? '');

            if (!$id || !$nome || !$fab || !$cat || !$preco) {
                json_response(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
            }

            $foto = salvarImagem('foto', 'produtos');

            if ($foto) {
                // Apaga foto antiga
                $old = $pdo->prepare("SELECT foto FROM produtos WHERE id = ?");
                $old->execute([$id]);
                $oldFoto = $old->fetchColumn();
                if ($oldFoto && file_exists(__DIR__ . '/uploads/produtos/' . $oldFoto)) {
                    @unlink(__DIR__ . '/uploads/produtos/' . $oldFoto);
                }
                $stmt = $pdo->prepare("UPDATE produtos SET nome=?, fabricante=?, categoria=?, preco_venda=?, descricao=?, foto=? WHERE id=?");
                $stmt->execute([$nome, $fab, $cat, $preco, $desc, $foto, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE produtos SET nome=?, fabricante=?, categoria=?, preco_venda=?, descricao=? WHERE id=?");
                $stmt->execute([$nome, $fab, $cat, $preco, $desc, $id]);
            }

            json_response(['success' => true]);
            break;

        // ── EDITAR PRODUTO via JSON (mantém compatibilidade) ───────────────
        case 'produtos_editar':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE produtos SET nome=?, fabricante=?, categoria=?, preco_venda=?, descricao=? WHERE id=?");
            $stmt->execute([
                $data['nome'], $data['fabricante'], $data['categoria'],
                $data['preco_venda'], $data['descricao'], $data['id']
            ]);
            json_response(['success' => true]);
            break;

        // ── DELETAR PRODUTO ────────────────────────────────────────────────
        case 'produtos_deletar':
            $data = json_decode(file_get_contents('php://input'), true);
            $id   = intval($data['id'] ?? 0);
            if (!$id) json_response(['success' => false, 'message' => 'ID inválido.']);

            // Verifica se o produto tem vendas internas vinculadas
            $chkVenda = $pdo->prepare("SELECT COUNT(*) FROM itens_venda WHERE produto_id = ?");
            $chkVenda->execute([$id]);
            $temVendas = (int)$chkVenda->fetchColumn() > 0;

            // Verifica se o produto tem pedidos da loja vinculados
            $chkPedido = $pdo->prepare("SELECT COUNT(*) FROM pedido_itens WHERE produto_id = ?");
            $chkPedido->execute([$id]);
            $temPedidos = (int)$chkPedido->fetchColumn() > 0;

            if ($temVendas || $temPedidos) {
                // Produto tem histórico — apenas desativa (soft delete)
                $pdo->prepare("UPDATE produtos SET ativo = 0 WHERE id = ?")->execute([$id]);
                $origens = [];
                if ($temVendas)  $origens[] = 'vendas internas';
                if ($temPedidos) $origens[] = 'pedidos da loja';
                json_response(['success' => true, 'aviso' => 'Produto desativado pois possui ' . implode(' e ', $origens) . ' vinculadas. Ele não aparecerá mais na lista.']);
            } else {
                // Sem vendas — pode deletar fisicamente
                // Remove lotes primeiro (FK)
                $pdo->prepare("DELETE FROM lotes WHERE produto_id = ?")->execute([$id]);

                // Remove foto se existir
                $old = $pdo->prepare("SELECT foto FROM produtos WHERE id = ?");
                $old->execute([$id]);
                $fotoNome = $old->fetchColumn();
                if ($fotoNome && file_exists(__DIR__ . '/uploads/produtos/' . $fotoNome)) {
                    @unlink(__DIR__ . '/uploads/produtos/' . $fotoNome);
                }

                $pdo->prepare("DELETE FROM produtos WHERE id = ?")->execute([$id]);
                json_response(['success' => true]);
            }
            break;

        // ── LISTAR LOTES ───────────────────────────────────────────────────
        case 'lotes_listar':
            $stmt = $pdo->prepare("SELECT id, numero_lote, data_validade, qtd_atual, DATEDIFF(data_validade, CURDATE()) AS dias_para_vencer, CASE WHEN data_validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END AS vencendo FROM lotes WHERE produto_id = ? ORDER BY data_validade ASC");
            $stmt->execute([(int)$_GET['produto_id']]);
            json_response(['success' => true, 'lotes' => $stmt->fetchAll()]);
            break;

        // ── CRIAR LOTE ─────────────────────────────────────────────────────
        case 'lotes_criar':
            $stmt = $pdo->prepare("INSERT INTO lotes (produto_id, numero_lote, data_validade, qtd_atual, qtd_inicial) VALUES (?, ?, ?, ?, ?)");
            $qtd  = intval($_POST['qtd_atual'] ?? 0);
            $stmt->execute([
                intval($_POST['produto_id']),
                trim($_POST['numero_lote']),
                $_POST['data_validade'],
                $qtd, $qtd
            ]);
            json_response(['success' => true]);
            break;

        // ── FINALIZAR VENDA ────────────────────────────────────────────────
        case 'venda_finalizar':
            $itens      = json_decode($_POST['itens'] ?? '[]', true);
            $supervisor = $_POST['supervisor'] ?? null;

            if ($supervisor && $supervisor !== Config::SENHA_SUPERVISOR_MESTRA) {
                json_response(['success' => false, 'message' => 'Senha do supervisor incorreta!'], 403);
            }

            $pdo->beginTransaction();

            $total = array_sum(array_map(function($i) { return $i['quantidade'] * $i['preco']; }, $itens));

            $stmtVenda = $pdo->prepare("INSERT INTO vendas (total, usuario_id, supervisor_liberacao) VALUES (?, ?, ?)");
            $stmtVenda->execute([$total, $_SESSION['usuario_id'], $supervisor]);
            $venda_id = $pdo->lastInsertId();

            $stmtItem     = $pdo->prepare("INSERT INTO itens_venda (venda_id, produto_id, lote_id, quantidade, preco) VALUES (?, ?, ?, ?, ?)");
            $stmtEstoque  = $pdo->prepare("UPDATE lotes SET qtd_atual = qtd_atual - ? WHERE id = ?");
            $stmtBuscaLote = $pdo->prepare("SELECT id, qtd_atual FROM lotes WHERE produto_id = ? AND qtd_atual >= ? ORDER BY data_validade ASC LIMIT 1");

            foreach ($itens as $item) {
                $stmtBuscaLote->execute([$item['produto_id'], $item['quantidade']]);
                $lote = $stmtBuscaLote->fetch();
                if (!$lote) throw new Exception("Estoque insuficiente para {$item['nome']}.");
                $stmtItem->execute([$venda_id, $item['produto_id'], $lote['id'], $item['quantidade'], $item['preco']]);
                $stmtEstoque->execute([$item['quantidade'], $lote['id']]);
            }

            $pdo->commit();
            json_response(['success' => true, 'venda_id' => $venda_id]);
            break;

        // ── CRIAR BANNER ───────────────────────────────────────────────────
        case 'banner_criar':
            if (($_SESSION['usuario_cargo'] ?? '') !== 'Gerente') {
                json_response(['success' => false, 'message' => 'Acesso restrito a Gerentes.'], 403);
            }

            $titulo = trim($_POST['titulo'] ?? '');
            if (!$titulo) json_response(['success' => false, 'message' => 'Título obrigatório.']);

            $imagem    = salvarImagem('imagem', 'banners');
            $cor       = $_POST['cor_fundo'] ?? '#1976D2';
            $desc      = trim($_POST['descricao'] ?? '');
            $dtInicio  = $_POST['data_inicio'] ?: null;
            $dtFim     = $_POST['data_fim'] ?: null;

            $stmt = $pdo->prepare("INSERT INTO banners (titulo, descricao, imagem, cor_fundo, data_inicio, data_fim, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$titulo, $desc, $imagem, $cor, $dtInicio, $dtFim]);
            json_response(['success' => true]);
            break;

        // ── DELETAR BANNER ─────────────────────────────────────────────────
        case 'banner_deletar':
            if (($_SESSION['usuario_cargo'] ?? '') !== 'Gerente') {
                json_response(['success' => false, 'message' => 'Acesso restrito a Gerentes.'], 403);
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $id   = intval($data['id'] ?? 0);

            $old = $pdo->prepare("SELECT imagem FROM banners WHERE id = ?");
            $old->execute([$id]);
            $img = $old->fetchColumn();
            if ($img && file_exists(__DIR__ . '/uploads/banners/' . $img)) {
                @unlink(__DIR__ . '/uploads/banners/' . $img);
            }

            $pdo->prepare("DELETE FROM banners WHERE id=?")->execute([$id]);
            json_response(['success' => true]);
            break;

        // ── CLIENTES ───────────────────────────────────────────────────
        case 'cliente_criar':
            $nome     = trim($_POST['nome'] ?? '');
            $cpf      = preg_replace('/\D/','',$_POST['cpf'] ?? '') ?: null;
            $telefone = trim($_POST['telefone'] ?? '') ?: null;
            if (!$nome) json_response(['success'=>false,'message'=>'Nome obrigatório.']);
            // CPF formatado para armazenamento
            $cpfFmt = $cpf ? preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/','$1.$2.$3-$4',$cpf) : null;
            try {
                $s = $pdo->prepare("INSERT INTO clientes (nome,cpf,telefone) VALUES (?,?,?)");
                $s->execute([$nome,$cpfFmt,$telefone]);
                json_response(['success'=>true]);
            } catch (\PDOException $e) {
                json_response(['success'=>false,'message'=>'CPF já cadastrado.']);
            }
            break;

        case 'cliente_editar':
            $id       = intval($_POST['id'] ?? 0);
            $nome     = trim($_POST['nome'] ?? '');
            $cpf      = preg_replace('/\D/','',$_POST['cpf'] ?? '') ?: null;
            $telefone = trim($_POST['telefone'] ?? '') ?: null;
            if (!$id || !$nome) json_response(['success'=>false,'message'=>'Dados inválidos.']);
            $cpfFmt = $cpf ? preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/','$1.$2.$3-$4',$cpf) : null;
            try {
                $s = $pdo->prepare("UPDATE clientes SET nome=?,cpf=?,telefone=? WHERE id=?");
                $s->execute([$nome,$cpfFmt,$telefone,$id]);
                json_response(['success'=>true]);
            } catch (\PDOException $e) {
                json_response(['success'=>false,'message'=>'CPF já cadastrado para outro cliente.']);
            }
            break;

        case 'cliente_deletar':
            $data = json_decode(file_get_contents('php://input'),true);
            $id   = intval($data['id'] ?? 0);
            $pdo->prepare("DELETE FROM clientes WHERE id=?")->execute([$id]);
            json_response(['success'=>true]);
            break;

        // ── CAIXA ──────────────────────────────────────────────────────
        case 'caixa_abrir':
            // Verifica se já tem caixa aberto
            $chk = $pdo->prepare("SELECT id FROM caixa WHERE usuario_id=? AND status='aberto'");
            $chk->execute([$_SESSION['usuario_id']]);
            if ($chk->fetch()) json_response(['success'=>false,'message'=>'Você já possui um caixa aberto.']);
            $valor = floatval($_POST['valor_abertura'] ?? 0);
            $obs   = trim($_POST['observacao'] ?? '') ?: null;
            $s = $pdo->prepare("INSERT INTO caixa (usuario_id,valor_abertura,observacao,status) VALUES (?,?,?,'aberto')");
            $s->execute([$_SESSION['usuario_id'],$valor,$obs]);
            json_response(['success'=>true,'caixa_id'=>$pdo->lastInsertId()]);
            break;

        case 'caixa_fechar':
            $id    = intval($_POST['id'] ?? 0);
            $valor = floatval($_POST['valor_fechamento'] ?? 0);
            $obs   = trim($_POST['observacao'] ?? '') ?: null;
            if (!$id) json_response(['success'=>false,'message'=>'ID inválido.']);
            $s = $pdo->prepare("UPDATE caixa SET status='fechado', valor_fechamento=?, fechado_em=NOW(), observacao=CONCAT(COALESCE(observacao,''),' | Fechamento: ',?) WHERE id=? AND usuario_id=?");
            $s->execute([$valor,$obs??'',$id,$_SESSION['usuario_id']]);
            json_response(['success'=>true]);
            break;

        // ── ITENS DE UMA VENDA ─────────────────────────────────────────
        case 'venda_itens':
            $id = intval($_GET['id'] ?? 0);
            $s  = $pdo->prepare("
                SELECT iv.quantidade, iv.preco, p.nome AS produto_nome
                FROM itens_venda iv
                INNER JOIN produtos p ON iv.produto_id = p.id
                WHERE iv.venda_id = ?
            ");
            $s->execute([$id]);
            $itens = $s->fetchAll();
            $total = array_sum(array_map(fn($i) => $i['quantidade']*$i['preco'], $itens));
            json_response(['success'=>true,'itens'=>$itens,'total'=>$total]);
            break;

        // ── CRIAR CATEGORIA ────────────────────────────────────────────────
        case 'categoria_criar':
            $nome  = trim($_POST['nome'] ?? '');
            $icone = trim($_POST['icone'] ?? 'bi-tag');
            if (!$nome) json_response(['success'=>false,'message'=>'Nome da categoria obrigatório.']);
            // Garante que a tabela existe
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `categorias` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `nome` varchar(100) NOT NULL,
                  `icone` varchar(50) DEFAULT 'bi-tag',
                  `ativo` tinyint(1) DEFAULT 1,
                  `ordem` int(11) DEFAULT 0,
                  `criado_em` datetime DEFAULT current_timestamp(),
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `nome` (`nome`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ");
            try {
                $s = $pdo->prepare("INSERT INTO categorias (nome, icone) VALUES (?, ?)");
                $s->execute([$nome, $icone]);
                json_response(['success'=>true,'id'=>(int)$pdo->lastInsertId()]);
            } catch (\PDOException $e) {
                json_response(['success'=>false,'message'=>'Categoria já existe com este nome.']);
            }
            break;

        // ── REMOVER CATEGORIA ──────────────────────────────────────────────
        case 'categoria_remover':
            $data = json_decode(file_get_contents('php://input'), true);
            $id   = intval($data['id'] ?? 0);
            if (!$id) json_response(['success'=>false,'message'=>'ID inválido.']);
            $pdo->prepare("DELETE FROM categorias WHERE id=?")->execute([$id]);
            json_response(['success'=>true]);
            break;

        // ── LISTAR CATEGORIAS ──────────────────────────────────────────────
        case 'categorias_listar':
            // Garante tabela existe
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `categorias` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `nome` varchar(100) NOT NULL,
                  `icone` varchar(50) DEFAULT 'bi-tag',
                  `ativo` tinyint(1) DEFAULT 1,
                  `ordem` int(11) DEFAULT 0,
                  `criado_em` datetime DEFAULT current_timestamp(),
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `nome` (`nome`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ");
            $cats = $pdo->query("SELECT * FROM categorias WHERE ativo=1 ORDER BY ordem ASC, nome ASC")->fetchAll();
            json_response(['success'=>true,'categorias'=>$cats]);
            break;

        // ══════════════════════════════════════════════════════════════
        // PEDIDOS DA LOJA (painel admin)
        // ══════════════════════════════════════════════════════════════

        case 'pedidos_loja_listar':
            $status  = $_GET['status']  ?? '';
            $busca   = trim($_GET['busca'] ?? '');
            $where   = ['1=1'];
            $params  = [];

            if ($status && $status !== 'todos') {
                $where[]          = 'p.status = :status';
                $params[':status'] = $status;
            }
            if ($busca) {
                $where[]        = '(cl.nome LIKE :busca OR p.id = :pid OR cl.email LIKE :busca2)';
                $params[':busca']  = "%$busca%";
                $params[':busca2'] = "%$busca%";
                $params[':pid']    = is_numeric($busca) ? (int)$busca : 0;
            }

            $whereSQL = implode(' AND ', $where);
            $stmt = $pdo->prepare("
                SELECT p.id, p.status, p.total, p.forma_pagamento, p.pix_pago,
                       p.boleto_codigo, p.boleto_vencimento, p.pix_txid,
                       p.criado_em,
                       cl.nome AS cliente_nome, cl.email AS cliente_email, cl.telefone AS cliente_tel,
                       COUNT(pi.id) AS qtd_itens
                FROM pedidos p
                INNER JOIN clientes_loja cl ON cl.id = p.cliente_id
                LEFT  JOIN pedido_itens pi  ON pi.pedido_id = p.id
                WHERE $whereSQL
                GROUP BY p.id
                ORDER BY p.criado_em DESC
            ");
            $stmt->execute($params);
            $pedidos = $stmt->fetchAll();

            // Totalizadores
            $tots = $pdo->query("
                SELECT status, COUNT(*) AS qtd, COALESCE(SUM(total),0) AS soma
                FROM pedidos GROUP BY status
            ")->fetchAll(PDO::FETCH_ASSOC);
            $resumo = [];
            foreach ($tots as $t) $resumo[$t['status']] = $t;

            json_response(['success'=>true,'pedidos'=>$pedidos,'resumo'=>$resumo]);
            break;

        case 'pedido_loja_detalhes':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) json_response(['success'=>false,'message'=>'ID inválido.']);

            $p = $pdo->prepare("
                SELECT p.*, cl.nome AS cliente_nome, cl.email AS cliente_email,
                       cl.telefone AS cliente_tel, cl.cpf AS cliente_cpf
                FROM pedidos p
                INNER JOIN clientes_loja cl ON cl.id = p.cliente_id
                WHERE p.id = ?
            ");
            $p->execute([$id]);
            $pedido = $p->fetch();
            if (!$pedido) json_response(['success'=>false,'message'=>'Pedido não encontrado.']);

            $itens = $pdo->prepare("
                SELECT pi.quantidade, pi.preco,
                       pr.nome AS produto_nome, pr.fabricante, pr.categoria
                FROM pedido_itens pi
                INNER JOIN produtos pr ON pr.id = pi.produto_id
                WHERE pi.pedido_id = ?
            ");
            $itens->execute([$id]);
            $pedido['itens'] = $itens->fetchAll();

            json_response(['success'=>true,'pedido'=>$pedido]);
            break;

        case 'pedido_loja_status':
            $id     = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $validos = ['pendente','confirmado','cancelado'];
            if (!$id || !in_array($status, $validos))
                json_response(['success'=>false,'message'=>'Dados inválidos.']);

            $pdo->prepare("UPDATE pedidos SET status=? WHERE id=?")
                ->execute([$status, $id]);
            json_response(['success'=>true,'message'=>'Status atualizado.']);
            break;

        case 'pedido_loja_pix_confirmar':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) json_response(['success'=>false,'message'=>'ID inválido.']);
            $pdo->prepare("UPDATE pedidos SET pix_pago=1, status='confirmado' WHERE id=?")
                ->execute([$id]);
            json_response(['success'=>true,'message'=>'Pagamento PIX confirmado.']);
            break;

        case 'pedido_loja_deletar':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) json_response(['success'=>false,'message'=>'ID inválido.']);
            $pdo->prepare("DELETE FROM pedido_itens WHERE pedido_id=?")->execute([$id]);
            $pdo->prepare("DELETE FROM pedidos WHERE id=?")->execute([$id]);
            json_response(['success'=>true,'message'=>'Pedido removido.']);
            break;

        default:
            json_response(['success' => false, 'message' => 'Endpoint não encontrado.'], 404);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    json_response(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
