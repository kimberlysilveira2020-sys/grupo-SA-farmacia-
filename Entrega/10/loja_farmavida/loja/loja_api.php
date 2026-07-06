<?php
require_once '../config.php';

header('Content-Type: application/json');

function json_r($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

$pdo      = Config::getDbConnection();
$endpoint = $_GET['endpoint'] ?? '';

// Helper senha
function hashSenha($s) { return password_hash($s, PASSWORD_DEFAULT); }
function verificaSenha($s, $h) { return password_verify($s, $h); }

try {
    switch ($endpoint) {

        // ── PRODUTOS PÚBLICOS (vitrine) ─────────────────────────────────
        case 'vitrine_produtos':
            $busca    = trim($_GET['busca'] ?? '');
            $categoria = trim($_GET['categoria'] ?? '');
            $pagina   = max(1, intval($_GET['pagina'] ?? 1));
            $por_pag  = 16;
            $offset   = ($pagina - 1) * $por_pag;

            $where = ["p.receita_obrigatoria = 0"];
            $params = [];

            // Só exibe produtos com estoque disponível
            $where[] = "(SELECT COALESCE(SUM(l.qtd_atual),0) FROM lotes l WHERE l.produto_id = p.id AND l.data_validade >= CURDATE()) > 0";

            if ($busca) {
                $where[] = "(p.nome LIKE ? OR p.fabricante LIKE ? OR p.descricao LIKE ? OR p.categoria LIKE ?)";
                $params = array_merge($params, ["%$busca%", "%$busca%", "%$busca%", "%$busca%"]);
            }
            if ($categoria) {
                $where[] = "p.categoria = ?";
                $params[] = $categoria;
            }

            $whereSQL = implode(' AND ', $where);

            // Total para paginação
            $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM produtos p WHERE $whereSQL");
            $stmtTotal->execute($params);
            $total = $stmtTotal->fetchColumn();

            $stmt = $pdo->prepare("
                SELECT p.id, p.nome, p.fabricante, p.categoria, p.preco_venda, p.descricao, p.foto,
                       (SELECT COALESCE(SUM(l.qtd_atual),0) FROM lotes l WHERE l.produto_id = p.id AND l.data_validade >= CURDATE()) AS estoque
                FROM produtos p
                WHERE $whereSQL
                ORDER BY p.nome ASC
                LIMIT $por_pag OFFSET $offset
            ");
            $stmt->execute($params);
            $produtos = $stmt->fetchAll();

            json_r([
                'success'   => true,
                'produtos'  => $produtos,
                'total'     => (int)$total,
                'paginas'   => ceil($total / $por_pag),
                'pagina'    => $pagina,
            ]);
            break;

        // ── CATEGORIAS DISPONÍVEIS ──────────────────────────────────────
        case 'vitrine_categorias':
            $stmt = $pdo->query("
                SELECT DISTINCT p.categoria, COUNT(*) AS qtd
                FROM produtos p
                WHERE p.categoria IS NOT NULL AND p.categoria != ''
                  AND p.receita_obrigatoria = 0
                  AND (SELECT COALESCE(SUM(l.qtd_atual),0) FROM lotes l WHERE l.produto_id = p.id AND l.data_validade >= CURDATE()) > 0
                GROUP BY p.categoria
                ORDER BY p.categoria ASC
            ");
            json_r(['success' => true, 'categorias' => $stmt->fetchAll()]);
            break;

        // ── BANNERS ATIVOS ──────────────────────────────────────────────
        case 'vitrine_banners':
            $stmt = $pdo->query("
                SELECT id, titulo, descricao, imagem, cor_fundo
                FROM banners
                WHERE ativo = 1
                  AND (data_inicio IS NULL OR data_inicio <= CURDATE())
                  AND (data_fim IS NULL OR data_fim >= CURDATE())
                ORDER BY ordem ASC, id ASC
                LIMIT 6
            ");
            json_r(['success' => true, 'banners' => $stmt->fetchAll()]);
            break;

        // ── PRODUTO DETALHE ─────────────────────────────────────────────
        case 'vitrine_produto':
            $id   = intval($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("
                SELECT p.*,
                       (SELECT COALESCE(SUM(l.qtd_atual),0) FROM lotes l WHERE l.produto_id = p.id AND l.data_validade >= CURDATE()) AS estoque
                FROM produtos p WHERE p.id = ? AND p.receita_obrigatoria = 0
            ");
            $stmt->execute([$id]);
            $produto = $stmt->fetch();
            if (!$produto) json_r(['success' => false, 'message' => 'Produto não encontrado.'], 404);
            json_r(['success' => true, 'produto' => $produto]);
            break;

        // ── CADASTRO CLIENTE ────────────────────────────────────────────
        case 'cliente_registrar':
            $nome     = trim($_POST['nome'] ?? '');
            $email    = strtolower(trim($_POST['email'] ?? ''));
            $senha    = $_POST['senha'] ?? '';
            $cpf      = preg_replace('/\D/', '', $_POST['cpf'] ?? '') ?: null;
            $telefone = trim($_POST['telefone'] ?? '') ?: null;

            if (!$nome || !$email || !$senha) {
                json_r(['success' => false, 'message' => 'Nome, e-mail e senha são obrigatórios.']);
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                json_r(['success' => false, 'message' => 'E-mail inválido.']);
            }
            if (strlen($senha) < 6) {
                json_r(['success' => false, 'message' => 'Senha deve ter ao menos 6 caracteres.']);
            }

            $cpfFmt = $cpf ? preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $cpf) : null;

            try {
                $s = $pdo->prepare("INSERT INTO clientes (nome, email, senha_hash, cpf, telefone) VALUES (?, ?, ?, ?, ?)");
                $s->execute([$nome, $email, hashSenha($senha), $cpfFmt, $telefone]);
                $clienteId = $pdo->lastInsertId();

                $_SESSION['loja_cliente_id']   = $clienteId;
                $_SESSION['loja_cliente_nome']  = $nome;
                $_SESSION['loja_cliente_email'] = $email;

                json_r(['success' => true, 'nome' => $nome]);
            } catch (\PDOException $e) {
                $msg = str_contains($e->getMessage(), 'email') ? 'E-mail já cadastrado.' : 'CPF já cadastrado.';
                json_r(['success' => false, 'message' => $msg]);
            }
            break;

        // ── LOGIN CLIENTE ───────────────────────────────────────────────
        case 'cliente_login':
            $email = strtolower(trim($_POST['email'] ?? ''));
            $senha = $_POST['senha'] ?? '';

            if (!$email || !$senha) json_r(['success' => false, 'message' => 'Preencha e-mail e senha.']);

            $s = $pdo->prepare("SELECT id, nome, email, senha_hash FROM clientes WHERE email = ?");
            $s->execute([$email]);
            $cliente = $s->fetch();

            if (!$cliente || !verificaSenha($senha, $cliente['senha_hash'])) {
                json_r(['success' => false, 'message' => 'E-mail ou senha incorretos.']);
            }

            $_SESSION['loja_cliente_id']   = $cliente['id'];
            $_SESSION['loja_cliente_nome']  = $cliente['nome'];
            $_SESSION['loja_cliente_email'] = $cliente['email'];

            json_r(['success' => true, 'nome' => $cliente['nome']]);
            break;

        // ── LOGOUT CLIENTE ──────────────────────────────────────────────
        case 'cliente_logout':
            unset($_SESSION['loja_cliente_id'], $_SESSION['loja_cliente_nome'], $_SESSION['loja_cliente_email']);
            json_r(['success' => true]);
            break;

        // ── FAZER PEDIDO ────────────────────────────────────────────────
        case 'pedido_criar':
            if (empty($_SESSION['loja_cliente_id'])) {
                json_r(['success' => false, 'message' => 'Faça login para finalizar o pedido.'], 401);
            }
            $itens = json_decode($_POST['itens'] ?? '[]', true);
            $obs   = trim($_POST['observacao'] ?? '') ?: null;

            if (empty($itens)) json_r(['success' => false, 'message' => 'Carrinho vazio.']);

            $pdo->beginTransaction();

            $total = 0;
            foreach ($itens as $item) {
                $total += floatval($item['preco']) * intval($item['quantidade']);
            }

            $s = $pdo->prepare("INSERT INTO pedidos_loja (cliente_id, total, observacao, status) VALUES (?, ?, ?, 'pendente')");
            $s->execute([$_SESSION['loja_cliente_id'], $total, $obs]);
            $pedidoId = $pdo->lastInsertId();

            $si = $pdo->prepare("INSERT INTO itens_pedido_loja (pedido_id, produto_id, quantidade, preco) VALUES (?, ?, ?, ?)");
            foreach ($itens as $item) {
                $si->execute([$pedidoId, intval($item['produto_id']), intval($item['quantidade']), floatval($item['preco'])]);
            }

            $pdo->commit();
            json_r(['success' => true, 'pedido_id' => $pedidoId]);
            break;

        // ── MEUS PEDIDOS ────────────────────────────────────────────────
        case 'meus_pedidos':
            if (empty($_SESSION['loja_cliente_id'])) {
                json_r(['success' => false, 'message' => 'Não autenticado.'], 401);
            }
            $s = $pdo->prepare("
                SELECT p.id, p.status, p.total, p.observacao, p.criado_em,
                       COUNT(i.id) AS qtd_itens
                FROM pedidos_loja p
                LEFT JOIN itens_pedido_loja i ON i.pedido_id = p.id
                WHERE p.cliente_id = ?
                GROUP BY p.id
                ORDER BY p.criado_em DESC
                LIMIT 20
            ");
            $s->execute([$_SESSION['loja_cliente_id']]);
            json_r(['success' => true, 'pedidos' => $s->fetchAll()]);
            break;

        // ── VERIFICAR SESSÃO ────────────────────────────────────────────
        case 'cliente_sessao':
            if (!empty($_SESSION['loja_cliente_id'])) {
                json_r([
                    'success'    => true,
                    'logado'     => true,
                    'nome'       => $_SESSION['loja_cliente_nome'],
                    'cliente_id' => $_SESSION['loja_cliente_id'],
                ]);
            } else {
                json_r(['success' => true, 'logado' => false]);
            }
            break;

        default:
            json_r(['success' => false, 'message' => 'Endpoint não encontrado.'], 404);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    json_r(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
