<?php
require_once 'loja_config.php';
header('Content-Type: application/json');

$pdo      = Config::getDbConnection();
$endpoint = $_GET['endpoint'] ?? '';

function salvarImagemLoja(string $campo, string $pasta): ?string {
    if (empty($_FILES[$campo]['tmp_name'])) return null;
    $file    = $_FILES[$campo];
    $mime    = mime_content_type($file['tmp_name']);
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if (!isset($allowed[$mime]) || $file['size'] > 2*1024*1024) return null;
    $nome = uniqid('img_',true).'.'.$allowed[$mime];
    $dir  = __DIR__.'/../uploads/'.$pasta.'/';
    if (!is_dir($dir)) mkdir($dir,0755,true);
    move_uploaded_file($file['tmp_name'], $dir.$nome);
    return $nome;
}

// ════════════════════════════════════════════════════
// GERADOR DE PAYLOAD PIX (BR Code / EMV)
// ════════════════════════════════════════════════════
function pixTLV(string $id, string $value): string {
    $len = str_pad(strlen($value), 2, '0', STR_PAD_LEFT);
    return $id . $len . $value;
}

function gerarPayloadPix(string $chave, string $nome, string $cidade, float $valor, string $txid = 'FARMAVIDA'): string {
    // Sanitiza
    $nome   = substr(preg_replace('/[^A-Za-z0-9 ]/', '', $nome), 0, 25);
    $cidade = substr(preg_replace('/[^A-Za-z0-9 ]/', '', $cidade), 0, 15);
    $txid   = substr(preg_replace('/[^A-Za-z0-9]/', '', $txid), 0, 25);
    $valor  = number_format($valor, 2, '.', '');

    // Merchant Account Information (GUI + chave)
    $mai = pixTLV('00', 'BR.GOV.BCB.PIX') . pixTLV('01', $chave);

    $payload =
        pixTLV('00', '01') .                          // Payload Format Indicator
        pixTLV('26', $mai) .                           // Merchant Account Information
        pixTLV('52', '0000') .                         // Merchant Category Code
        pixTLV('53', '986') .                          // Transaction Currency (BRL)
        pixTLV('54', $valor) .                         // Transaction Amount
        pixTLV('58', 'BR') .                           // Country Code
        pixTLV('59', $nome) .                          // Merchant Name
        pixTLV('60', $cidade) .                        // Merchant City
        pixTLV('62', pixTLV('05', $txid));             // Additional Data (TxID)

    // CRC16/CCITT
    $payload .= '6304';
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($payload); $i++) {
        $crc ^= (ord($payload[$i]) << 8);
        for ($j = 0; $j < 8; $j++) {
            $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
        }
    }
    return $payload . strtoupper(str_pad(dechex($crc & 0xFFFF), 4, '0', STR_PAD_LEFT));
}

try {
    switch ($endpoint) {

        // ── REGISTRO DE CLIENTE ─────────────────────────────────────
        case 'registrar':
            $nome  = trim($_POST['nome']  ?? '');
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $cpf   = preg_replace('/\D/','',$_POST['cpf'] ?? '') ?: null;
            $tel   = trim($_POST['telefone'] ?? '') ?: null;

            if (!$nome || !$email || !$senha)
                jsonResp(['success'=>false,'message'=>'Nome, e-mail e senha são obrigatórios.']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                jsonResp(['success'=>false,'message'=>'E-mail inválido.']);
            if (strlen($senha) < 6)
                jsonResp(['success'=>false,'message'=>'Senha deve ter pelo menos 6 caracteres.']);

            $cpfFmt = $cpf ? preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/','$1.$2.$3-$4',$cpf) : null;
            $hash   = password_hash($senha, PASSWORD_BCRYPT);
            try {
                $s = $pdo->prepare("INSERT INTO clientes_loja (nome,email,senha_hash,cpf,telefone) VALUES (?,?,?,?,?)");
                $s->execute([$nome,$email,$hash,$cpfFmt,$tel]);
                $id = $pdo->lastInsertId();
                $_SESSION[LOJA_SESSION_PREFIX.'id']   = $id;
                $_SESSION[LOJA_SESSION_PREFIX.'nome']  = $nome;
                $_SESSION[LOJA_SESSION_PREFIX.'email'] = $email;
                jsonResp(['success'=>true]);
            } catch (\PDOException $e) {
                jsonResp(['success'=>false,'message'=>'E-mail ou CPF já cadastrado.']);
            }
            break;

        // ── LOGIN ───────────────────────────────────────────────────
        case 'login':
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $s = $pdo->prepare("SELECT id,nome,email,senha_hash FROM clientes_loja WHERE email=?");
            $s->execute([$email]);
            $c = $s->fetch();
            if (!$c || !password_verify($senha, $c['senha_hash']))
                jsonResp(['success'=>false,'message'=>'E-mail ou senha incorretos.']);
            $_SESSION[LOJA_SESSION_PREFIX.'id']    = $c['id'];
            $_SESSION[LOJA_SESSION_PREFIX.'nome']  = $c['nome'];
            $_SESSION[LOJA_SESSION_PREFIX.'email'] = $c['email'];
            jsonResp(['success'=>true]);
            break;

        // ── LOGOUT ──────────────────────────────────────────────────
        case 'logout':
            unset(
                $_SESSION[LOJA_SESSION_PREFIX.'id'],
                $_SESSION[LOJA_SESSION_PREFIX.'nome'],
                $_SESSION[LOJA_SESSION_PREFIX.'email']
            );
            jsonResp(['success'=>true]);
            break;

        // ── PRODUTOS (busca + listagem) ─────────────────────────────
        case 'produtos':
            $busca    = trim($_GET['q'] ?? '');
            $categoria= trim($_GET['categoria'] ?? '');
            $pagina   = max(1, (int)($_GET['pagina'] ?? 1));
            $porPagina= 12;
            $offset   = ($pagina - 1) * $porPagina;

            $where  = ["p.receita_obrigatoria = 0", "p.ativo = 1"];
            $params = [];

            if ($busca) {
                $where[]  = "(p.nome LIKE ? OR p.fabricante LIKE ? OR p.descricao LIKE ?)";
                $like     = "%$busca%";
                $params   = array_merge($params, [$like,$like,$like]);
            }
            if ($categoria) {
                $where[]  = "p.categoria = ?";
                $params[] = $categoria;
            }

            $whereSQL = 'WHERE '.implode(' AND ',$where);

            // Total para paginação
            $stmtCount = $pdo->prepare("
                SELECT COUNT(*) FROM produtos p
                LEFT JOIN (SELECT produto_id, SUM(qtd_atual) AS total FROM lotes WHERE qtd_atual>0 GROUP BY produto_id) est ON est.produto_id=p.id
                $whereSQL
            ");
            $stmtCount->execute($params);
            $total = (int)$stmtCount->fetchColumn();

            $stmtP = $pdo->prepare("
                SELECT p.id, p.nome, p.fabricante, p.categoria, p.preco_venda, p.descricao, p.foto,
                       COALESCE(est.total,0) AS estoque,
                       DATEDIFF(MIN(l.data_validade), CURDATE()) AS dias_vencer
                FROM produtos p
                LEFT JOIN (SELECT produto_id, SUM(qtd_atual) AS total FROM lotes WHERE qtd_atual>0 GROUP BY produto_id) est ON est.produto_id=p.id
                LEFT JOIN lotes l ON l.produto_id=p.id AND l.qtd_atual>0
                $whereSQL
                GROUP BY p.id
                ORDER BY p.nome ASC
                LIMIT $porPagina OFFSET $offset
            ");
            $stmtP->execute($params);
            $produtos = $stmtP->fetchAll();

            // Aplica desconto para lotes vencendo
            foreach ($produtos as &$p) {
                $p['desconto']      = 0;
                $p['preco_original']= null;
                if ($p['dias_vencer'] !== null && $p['dias_vencer'] <= 30 && $p['dias_vencer'] >= 0) {
                    $p['preco_original'] = $p['preco_venda'];
                    $p['preco_venda']    = round($p['preco_venda'] * 0.80, 2);
                    $p['desconto']       = 20;
                }
                $p['foto_url'] = !empty($p['foto']) ? '../uploads/produtos/'.$p['foto'] : null;
            }

            jsonResp(['success'=>true,'produtos'=>$produtos,'total'=>$total,'paginas'=>ceil($total/$porPagina)]);
            break;

        // ── CATEGORIAS ─────────────────────────────────────────────
        case 'categorias':
            try {
                $cats = $pdo->query("
                    SELECT nome, icone FROM categorias WHERE ativo=1 ORDER BY ordem ASC, nome ASC
                ")->fetchAll();
                jsonResp(['success'=>true,'categorias'=>$cats]);
            } catch (\Exception $e) {
                // Tabela pode não existir ainda; retorna lista padrão
                jsonResp(['success'=>true,'categorias'=>[
                    ['nome'=>'Comum',          'icone'=>'bi-capsule'],
                    ['nome'=>'Genérico',       'icone'=>'bi-capsule-pill'],
                    ['nome'=>'Vitaminas',      'icone'=>'bi-heart'],
                    ['nome'=>'Dermocosméticos','icone'=>'bi-stars'],
                    ['nome'=>'Higiene',        'icone'=>'bi-droplet'],
                    ['nome'=>'Beleza',         'icone'=>'bi-flower1'],
                ]]);
            }
            break;

        // ── BANNERS ─────────────────────────────────────────────────
        case 'banners':
            $s = $pdo->query("
                SELECT id, titulo, descricao, imagem, cor_fundo FROM banners
                WHERE ativo=1
                  AND (data_inicio IS NULL OR data_inicio <= CURDATE())
                  AND (data_fim   IS NULL OR data_fim   >= CURDATE())
                ORDER BY ordem ASC
            ");
            $banners = $s->fetchAll();
            foreach ($banners as &$b) {
                $b['imagem_url'] = !empty($b['imagem']) ? '../uploads/banners/'.$b['imagem'] : null;
            }
            jsonResp(['success'=>true,'banners'=>$banners]);
            break;

        // ── PRÉVIA PIX (QR Code antes de confirmar) ─────────────────
        case 'pix_preview':
            $valor = (float)($_POST['total'] ?? 0);
            if ($valor <= 0) jsonResp(['success'=>false,'message'=>'Valor inválido.']);
            $payload = gerarPayloadPix(
                Config::PIX_CHAVE,
                Config::PIX_NOME,
                Config::PIX_CIDADE,
                $valor,
                Config::PIX_TXID . 'PREV'
            );
            jsonResp(['success'=>true,'pix_payload'=>$payload,'chave'=>Config::PIX_CHAVE]);
            break;

        // ── FINALIZAR PEDIDO ────────────────────────────────────────
        case 'pedido_criar':
            if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Faça login para finalizar.'],401);
            $itens = json_decode($_POST['itens'] ?? '[]', true);
            if (empty($itens)) jsonResp(['success'=>false,'message'=>'Carrinho vazio.']);

            $formasValidas   = ['credito','pix','paypal','boleto'];
            $formaPagamento  = $_POST['forma_pagamento'] ?? 'pix';
            if (!in_array($formaPagamento, $formasValidas))
                jsonResp(['success'=>false,'message'=>'Forma de pagamento inválida.']);

            $pdo->beginTransaction();
            $total = 0;
            foreach ($itens as $i) { $total += $i['quantidade'] * $i['preco']; }

            // Tenta inserir com forma_pagamento; se a coluna não existir ainda, insere sem ela
            try {
                $s = $pdo->prepare("INSERT INTO pedidos (cliente_id,total,forma_pagamento,status) VALUES (?,?,?,'pendente')");
                $s->execute([clienteId(), $total, $formaPagamento]);
            } catch (\PDOException $e) {
                // Coluna ainda não existe — insere sem ela (compatibilidade retroativa)
                $s = $pdo->prepare("INSERT INTO pedidos (cliente_id,total,status) VALUES (?,?,'pendente')");
                $s->execute([clienteId(), $total]);
            }
            $pedidoId = $pdo->lastInsertId();

            $si = $pdo->prepare("INSERT INTO pedido_itens (pedido_id,produto_id,quantidade,preco) VALUES (?,?,?,?)");
            foreach ($itens as $i) {
                $si->execute([$pedidoId, $i['produto_id'], $i['quantidade'], $i['preco']]);
            }
            $pdo->commit();

            // Para PIX: gera payload e retorna dados para exibir QR Code no frontend
            $extra = [];
            if ($formaPagamento === 'pix') {
                $extra['pix_payload'] = gerarPayloadPix(
                    Config::PIX_CHAVE,
                    Config::PIX_NOME,
                    Config::PIX_CIDADE,
                    (float)$total,
                    Config::PIX_TXID . $pedidoId
                );
            }

            jsonResp(array_merge(['success'=>true,'pedido_id'=>$pedidoId,'forma_pagamento'=>$formaPagamento], $extra));
            break;

        // ── ITENS DE UM PEDIDO ──────────────────────────────────────
        case 'pedido_itens':
            if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Não autenticado.'],401);
            $pedidoId = (int)($_GET['id'] ?? 0);
            if (!$pedidoId) jsonResp(['success'=>false,'message'=>'ID inválido.']);
            $check = $pdo->prepare("SELECT id FROM pedidos WHERE id=? AND cliente_id=?");
            $check->execute([$pedidoId, clienteId()]);
            if (!$check->fetch()) jsonResp(['success'=>false,'message'=>'Pedido não encontrado.'],404);
            $s = $pdo->prepare("
                SELECT pi.quantidade, pi.preco,
                       pr.nome AS produto_nome, pr.foto
                FROM pedido_itens pi
                INNER JOIN produtos pr ON pr.id = pi.produto_id
                WHERE pi.pedido_id = ?
            ");
            $s->execute([$pedidoId]);
            $itens = $s->fetchAll();
            foreach ($itens as &$item) {
                $item['foto_url'] = !empty($item['foto']) ? '../uploads/produtos/'.$item['foto'] : null;
            }
            jsonResp(['success'=>true,'itens'=>$itens]);
            break;

        // ── MEUS PEDIDOS ────────────────────────────────────────────
        case 'meus_pedidos':
            if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Não autenticado.'],401);
            $s = $pdo->prepare("
                SELECT p.id, p.status, p.total, p.criado_em,
                       GROUP_CONCAT(pr.nome SEPARATOR ', ') AS produtos_nomes
                FROM pedidos p
                INNER JOIN pedido_itens itv ON itv.pedido_id = p.id
                INNER JOIN produtos pr ON pr.id = itv.produto_id
                WHERE p.cliente_id = ?
                GROUP BY p.id ORDER BY p.criado_em DESC
            ");
            $s->execute([clienteId()]);
            jsonResp(['success'=>true,'pedidos'=>$s->fetchAll()]);
            break;

        default:
            jsonResp(['success'=>false,'message'=>'Endpoint não encontrado.'],404);
    }
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    jsonResp(['success'=>false,'message'=>$e->getMessage()],500);
}
?>
