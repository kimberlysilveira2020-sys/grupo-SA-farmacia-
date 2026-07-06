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

$pdo = Config::getDbConnection();
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

            $stmt = $pdo->prepare("INSERT INTO produtos (nome, fabricante, categoria, preco_venda, descricao, foto) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $fabric, $cat, $preco, $desc, $foto]);
            $produtoId = $pdo->lastInsertId();

            // Lote inicial (opcional)
            $loteNum = trim($_POST['lote_numero'] ?? '');
            $loteVal = trim($_POST['lote_validade'] ?? '');
            $loteQtd = intval($_POST['lote_quantidade'] ?? 0);

            if ($loteNum && $loteVal && $loteQtd > 0) {
                $stmtL = $pdo->prepare("INSERT INTO lotes (produto_id, numero_lote, data_validade, qtd_atual, qtd_inicial) VALUES (?, ?, ?, ?, ?)");
                $stmtL->execute([$produtoId, $loteNum, $loteVal, $loteQtd, $loteQtd]);
            }

            json_response(['success' => true, 'produto_id' => $produtoId]);
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

            // Remove foto se existir
            $old = $pdo->prepare("SELECT foto FROM produtos WHERE id = ?");
            $old->execute([$id]);
            $fotoNome = $old->fetchColumn();
            if ($fotoNome && file_exists(__DIR__ . '/uploads/produtos/' . $fotoNome)) {
                @unlink(__DIR__ . '/uploads/produtos/' . $fotoNome);
            }

            $stmt = $pdo->prepare("DELETE FROM produtos WHERE id=?");
            $stmt->execute([$id]);
            json_response(['success' => true]);
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
            if (($_SESSION['cargo'] ?? '') !== 'Gerente') {
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
            if (($_SESSION['cargo'] ?? '') !== 'Gerente') {
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

        default:
            json_response(['success' => false, 'message' => 'Endpoint não encontrado.'], 404);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    json_response(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
