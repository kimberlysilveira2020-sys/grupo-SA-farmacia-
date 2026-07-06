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

// ── Gerador de Payload PIX (padrão EMV / Banco Central do Brasil) ──────────
function gerarPayloadPix(string $chave, string $nome, string $cidade, float $valor, string $txid = ''): string {
    // Limpa e padroniza
    $nome   = mb_strtoupper(substr(preg_replace('/[^A-Za-z0-9 ]/', '', $nome), 0, 25));
    $cidade = mb_strtoupper(substr(preg_replace('/[^A-Za-z0-9 ]/', '', $cidade), 0, 15));
    $txid   = preg_replace('/[^A-Za-z0-9]/', '', $txid ?: 'FARMAVIDA'.time());
    $txid   = substr($txid, 0, 25);

    $valorStr = number_format($valor, 2, '.', '');

    // Merchant Account Information (ID 26)
    $gui     = tlv('00', 'br.gov.bcb.pix');
    $chaveTV = tlv('01', $chave);
    $mai     = tlv('26', $gui . $chaveTV);

    // Additional Data Field (ID 62) — txid
    $txidTV = tlv('05', $txid);
    $adf    = tlv('62', $txidTV);

    // Monta payload sem CRC
    $payload =
        tlv('00', '01')             . // Payload Format Indicator
        tlv('01', '12')             . // Point of Initiation Method (12 = dinâmico, 11 = estático)
        $mai                        . // Merchant Account Information
        tlv('52', '0000')           . // Merchant Category Code
        tlv('53', '986')            . // Transaction Currency (BRL)
        tlv('54', $valorStr)        . // Transaction Amount
        tlv('58', 'BR')             . // Country Code
        tlv('59', $nome)            . // Merchant Name
        tlv('60', $cidade)          . // Merchant City
        $adf                        . // Additional Data Field
        '6304';                       // CRC placeholder

    // CRC16-CCITT
    $crc = crc16($payload);
    return $payload . strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
}

function tlv(string $id, string $value): string {
    return $id . str_pad(strlen($value), 2, '0', STR_PAD_LEFT) . $value;
}

function crc16(string $str): int {
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($str); $i++) {
        $crc ^= ord($str[$i]) << 8;
        for ($j = 0; $j < 8; $j++) {
            if ($crc & 0x8000) { $crc = ($crc << 1) ^ 0x1021; }
            else                { $crc <<= 1; }
            $crc &= 0xFFFF;
        }
    }
    return $crc;
}
// ──────────────────────────────────────────────────────────────────────────────

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

        // ── GERAR PIX ───────────────────────────────────────────────
        // Cria o pedido em status "aguardando_pix" e retorna o payload + txid
        case 'gerar_pix':
            if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Faça login para finalizar.'],401);

            $itens = json_decode($_POST['itens'] ?? '[]', true);
            if (empty($itens)) jsonResp(['success'=>false,'message'=>'Carrinho vazio.']);

            // Garante colunas extras na tabela pedidos (migração automática)
            try {
                $cols = $pdo->query("SHOW COLUMNS FROM pedidos LIKE 'pix_txid'")->fetchAll();
                if (empty($cols)) {
                    $pdo->exec("ALTER TABLE pedidos
                        ADD COLUMN `forma_pagamento` VARCHAR(20) NOT NULL DEFAULT 'pix' AFTER `status`,
                        ADD COLUMN `pix_txid`        VARCHAR(50) NULL AFTER `forma_pagamento`,
                        ADD COLUMN `pix_pago`        TINYINT(1)  NOT NULL DEFAULT 0 AFTER `pix_txid`
                    ");
                }
            } catch (\Exception $e) { /* Colunas já existem */ }

            $pdo->beginTransaction();
            $total = 0;
            foreach ($itens as $i) { $total += $i['quantidade'] * $i['preco']; }

            // Gera txid único
            $txid = 'FV' . strtoupper(substr(uniqid(), -10)) . rand(10,99);

            $s = $pdo->prepare("INSERT INTO pedidos (cliente_id,total,status,forma_pagamento,pix_txid,pix_pago) VALUES (?,?,'pendente','pix',?,0)");
            $s->execute([clienteId(), $total, $txid]);
            $pedidoId = $pdo->lastInsertId();

            $si = $pdo->prepare("INSERT INTO pedido_itens (pedido_id,produto_id,quantidade,preco) VALUES (?,?,?,?)");
            foreach ($itens as $i) {
                $si->execute([$pedidoId, $i['produto_id'], $i['quantidade'], $i['preco']]);
            }
            $pdo->commit();

            // Gera o payload EMV do PIX
            $payload = gerarPayloadPix(
                Config::PIX_CHAVE,
                Config::PIX_NOME,
                Config::PIX_CIDADE,
                (float)$total,
                $txid
            );

            jsonResp([
                'success'    => true,
                'pedido_id'  => $pedidoId,
                'txid'       => $txid,
                'total'      => $total,
                'payload'    => $payload,   // Copia e Cola
                'pix_chave'  => Config::PIX_CHAVE,
                'pix_nome'   => Config::PIX_NOME,
            ]);
            break;

        // ── CONFIRMAR PIX — chamado quando o usuário clica "Já paguei" ─
        // Valida que o pedido existe, pertence ao cliente e ainda está pendente.
        // Marca pix_pago=1 e status='aguardando_confirmacao' — a equipe da
        // farmácia confirma manualmente (ou um webhook real faz isso).
        // NUNCA confirma automaticamente sem alguma evidência.
        case 'confirmar_pix':
            if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Não autenticado.'], 401);

            $pedidoId = (int)($_POST['pedido_id'] ?? 0);
            if (!$pedidoId) jsonResp(['success'=>false,'message'=>'ID de pedido inválido.']);

            // Verifica que o pedido pertence ao cliente logado e ainda está pendente (não pago)
            $chk = $pdo->prepare("
                SELECT id, total, pix_txid
                FROM pedidos
                WHERE id = ? AND cliente_id = ? AND forma_pagamento = 'pix' AND pix_pago = 0
            ");
            $chk->execute([$pedidoId, clienteId()]);
            $pedido = $chk->fetch();

            if (!$pedido) {
                // Pode ser que o pedido já foi pago anteriormente ou não pertence ao cliente
                $jaExiste = $pdo->prepare("SELECT status FROM pedidos WHERE id=? AND cliente_id=?");
                $jaExiste->execute([$pedidoId, clienteId()]);
                $row = $jaExiste->fetch();
                if ($row && $row['status'] === 'aguardando_confirmacao') {
                    jsonResp(['success'=>true,'message'=>'Pedido já registrado como aguardando confirmação.', 'pedido_id'=>$pedidoId]);
                }
                jsonResp(['success'=>false,'message'=>'Pedido não encontrado ou já processado.'], 404);
            }

            // Registra a declaração de pagamento — status intermediário que a farmácia precisa confirmar
            $upd = $pdo->prepare("
                UPDATE pedidos
                SET pix_pago = 1, status = 'aguardando_confirmacao'
                WHERE id = ? AND cliente_id = ? AND pix_pago = 0
            ");
            $upd->execute([$pedidoId, clienteId()]);

            if ($upd->rowCount() === 0) {
                jsonResp(['success'=>false,'message'=>'Não foi possível registrar o pagamento. Tente novamente.']);
            }

            jsonResp([
                'success'    => true,
                'pedido_id'  => $pedidoId,
                'status'     => 'aguardando_confirmacao',
                'message'    => 'Pagamento declarado. Seu pedido será confirmado após a compensação do PIX.',
            ]);
            break;

        // ── CANCELAR PIX (pedido não pago — remove do banco) ────────
        case 'cancelar_pix':
            if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Não autenticado.'],401);
            $pedidoId = (int)($_POST['pedido_id'] ?? 0);
            if (!$pedidoId) jsonResp(['success'=>false,'message'=>'ID inválido.']);

            // Garante que é o dono e que o PIX ainda não foi confirmado
            $chk = $pdo->prepare("SELECT id FROM pedidos WHERE id=? AND cliente_id=? AND pix_pago=0");
            $chk->execute([$pedidoId, clienteId()]);
            if (!$chk->fetch()) jsonResp(['success'=>false,'message'=>'Pedido não encontrado ou já pago.']);

            $pdo->prepare("DELETE FROM pedido_itens WHERE pedido_id=?")->execute([$pedidoId]);
            $pdo->prepare("DELETE FROM pedidos WHERE id=? AND pix_pago=0")->execute([$pedidoId]);
            jsonResp(['success'=>true]);
            break;

        // ══════════════════════════════════════════════════════════════
        // CARTÃO DE CRÉDITO
        // ══════════════════════════════════════════════════════════════

        // ── Listar cartões salvos do cliente ────────────────────────
        case 'cartoes_listar':
            if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Não autenticado.'],401);
            $s = $pdo->prepare("SELECT id,apelido,bandeira,ultimos4,nome_titular,mes_validade,ano_validade,padrao FROM cartoes_cliente WHERE cliente_id=? ORDER BY padrao DESC, criado_em DESC");
            $s->execute([clienteId()]);
            jsonResp(['success'=>true,'cartoes'=>$s->fetchAll()]);
            break;

        // ── Salvar novo cartão ───────────────────────────────────────
        case 'cartao_salvar':
            if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Não autenticado.'],401);
            $numero    = preg_replace('/\D/','',$_POST['numero'] ?? '');
            $nome      = strtoupper(trim($_POST['nome_titular'] ?? ''));
            $mes       = str_pad(preg_replace('/\D/','',$_POST['mes'] ?? ''),2,'0',STR_PAD_LEFT);
            $ano       = preg_replace('/\D/','',$_POST['ano'] ?? '');
            $cvv       = preg_replace('/\D/','',$_POST['cvv'] ?? '');
            $apelido   = trim($_POST['apelido'] ?? '') ?: '';
            $padrao    = (int)($_POST['padrao'] ?? 0);

            // Validações básicas
            if (strlen($numero) < 13 || strlen($numero) > 19)
                jsonResp(['success'=>false,'message'=>'Número de cartão inválido.']);
            if (!$nome) jsonResp(['success'=>false,'message'=>'Nome do titular obrigatório.']);
            if (!preg_match('/^(0[1-9]|1[0-2])$/', $mes))
                jsonResp(['success'=>false,'message'=>'Mês de validade inválido.']);
            if (!preg_match('/^\d{4}$/', $ano) || $ano < date('Y'))
                jsonResp(['success'=>false,'message'=>'Ano de validade inválido.']);
            if (strlen($cvv) < 3) jsonResp(['success'=>false,'message'=>'CVV inválido.']);

            // Detectar bandeira
            $bandeira = 'outro';
            if (preg_match('/^4/', $numero))                          $bandeira = 'visa';
            elseif (preg_match('/^5[1-5]|^2[2-7]/', $numero))        $bandeira = 'mastercard';
            elseif (preg_match('/^3[47]/', $numero))                  $bandeira = 'amex';
            elseif (preg_match('/^6(?:011|5)/', $numero))             $bandeira = 'discover';
            elseif (preg_match('/^(?:606282|3841)/', $numero))        $bandeira = 'hipercard';
            elseif (preg_match('/^(?:4011|4312|4389|4514|4576|5041|5066|5067|509|6277|6362|6363|650|6516|6550)/', $numero)) $bandeira = 'elo';

            $ultimos4  = substr($numero, -4);
            // Token: hash do número completo + cvv + cliente (NUNCA salvar número completo)
            $tokenHash = password_hash($numero . '|' . $cvv . '|' . clienteId(), PASSWORD_BCRYPT);

            // Verificar duplicata (mesmos últimos 4 + bandeira + validade)
            $dup = $pdo->prepare("SELECT id FROM cartoes_cliente WHERE cliente_id=? AND ultimos4=? AND bandeira=? AND mes_validade=? AND ano_validade=?");
            $dup->execute([clienteId(),$ultimos4,$bandeira,$mes,$ano]);
            if ($dup->fetch()) jsonResp(['success'=>false,'message'=>'Este cartão já está cadastrado.']);

            // Se vai ser padrão, tira padrão dos outros
            if ($padrao) $pdo->prepare("UPDATE cartoes_cliente SET padrao=0 WHERE cliente_id=?")->execute([clienteId()]);

            $ins = $pdo->prepare("INSERT INTO cartoes_cliente (cliente_id,apelido,bandeira,ultimos4,nome_titular,mes_validade,ano_validade,token_hash,padrao) VALUES (?,?,?,?,?,?,?,?,?)");
            $ins->execute([clienteId(),$apelido,$bandeira,$ultimos4,$nome,$mes,$ano,$tokenHash,$padrao]);
            jsonResp(['success'=>true,'id'=>$pdo->lastInsertId(),'bandeira'=>$bandeira,'ultimos4'=>$ultimos4]);
            break;

        // ── Excluir cartão ───────────────────────────────────────────
        case 'cartao_excluir':
            if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Não autenticado.'],401);
            $cartaoId = (int)($_POST['cartao_id'] ?? 0);
            $del = $pdo->prepare("DELETE FROM cartoes_cliente WHERE id=? AND cliente_id=?");
            $del->execute([$cartaoId, clienteId()]);
            jsonResp(['success'=>true]);
            break;

        // ── Pagar com cartão ─────────────────────────────────────────
        case 'pagar_cartao':
            if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Faça login para finalizar.'],401);
            $itens     = json_decode($_POST['itens'] ?? '[]', true);
            $cartaoId  = (int)($_POST['cartao_id'] ?? 0);
            $parcelas  = max(1, min(12, (int)($_POST['parcelas'] ?? 1)));
            if (empty($itens)) jsonResp(['success'=>false,'message'=>'Carrinho vazio.']);
            if (!$cartaoId)    jsonResp(['success'=>false,'message'=>'Selecione um cartão.']);

            // Verifica que o cartão pertence ao cliente
            $chkC = $pdo->prepare("SELECT id,bandeira,ultimos4 FROM cartoes_cliente WHERE id=? AND cliente_id=?");
            $chkC->execute([$cartaoId, clienteId()]);
            $cartao = $chkC->fetch();
            if (!$cartao) jsonResp(['success'=>false,'message'=>'Cartão não encontrado.']);

            // Migração automática das colunas extras
            try {
                $cols = $pdo->query("SHOW COLUMNS FROM pedidos LIKE 'forma_pagamento'")->fetchAll();
                if (empty($cols)) {
                    $pdo->exec("ALTER TABLE pedidos
                        ADD COLUMN `forma_pagamento` VARCHAR(20) NOT NULL DEFAULT 'pix' AFTER `status`,
                        ADD COLUMN `pix_txid`        VARCHAR(50) NULL AFTER `forma_pagamento`,
                        ADD COLUMN `pix_pago`        TINYINT(1)  NOT NULL DEFAULT 0 AFTER `pix_txid`,
                        ADD COLUMN `boleto_codigo`   VARCHAR(60) NULL AFTER `pix_pago`,
                        ADD COLUMN `boleto_vencimento` DATE NULL AFTER `boleto_codigo`,
                        ADD COLUMN `paypal_order_id` VARCHAR(80) NULL AFTER `boleto_vencimento`
                    ");
                }
            } catch(\Exception $e){}

            $pdo->beginTransaction();
            $total = 0;
            foreach ($itens as $i) $total += $i['quantidade'] * $i['preco'];

            $s = $pdo->prepare("INSERT INTO pedidos (cliente_id,total,status,forma_pagamento) VALUES (?,?,'confirmado','cartao')");
            $s->execute([clienteId(), $total]);
            $pedidoId = $pdo->lastInsertId();

            $si = $pdo->prepare("INSERT INTO pedido_itens (pedido_id,produto_id,quantidade,preco) VALUES (?,?,?,?)");
            foreach ($itens as $i) $si->execute([$pedidoId,$i['produto_id'],$i['quantidade'],$i['preco']]);
            $pdo->commit();

            // Aqui seria feita a integração real com gateway (Stripe, PagSeguro, etc.)
            // Por ora, simula aprovação com 95% de sucesso
            $aprovado = (rand(1,100) <= 95);
            if (!$aprovado) {
                $pdo->exec("UPDATE pedidos SET status='cancelado' WHERE id=$pedidoId");
                jsonResp(['success'=>false,'message'=>'Pagamento recusado pela operadora. Tente outro cartão.']);
            }

            jsonResp([
                'success'   => true,
                'pedido_id' => $pedidoId,
                'bandeira'  => $cartao['bandeira'],
                'ultimos4'  => $cartao['ultimos4'],
                'parcelas'  => $parcelas,
                'total'     => $total,
            ]);
            break;

        // ══════════════════════════════════════════════════════════════
        // BOLETO BANCÁRIO
        // ══════════════════════════════════════════════════════════════
        case 'gerar_boleto':
            if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Faça login para finalizar.'],401);
            $itens = json_decode($_POST['itens'] ?? '[]', true);
            if (empty($itens)) jsonResp(['success'=>false,'message'=>'Carrinho vazio.']);

            // Migração automática
            try {
                $cols = $pdo->query("SHOW COLUMNS FROM pedidos LIKE 'boleto_codigo'")->fetchAll();
                if (empty($cols)) {
                    $pdo->exec("ALTER TABLE pedidos
                        ADD COLUMN IF NOT EXISTS `forma_pagamento` VARCHAR(20) NOT NULL DEFAULT 'pix' AFTER `status`,
                        ADD COLUMN IF NOT EXISTS `pix_txid`        VARCHAR(50) NULL AFTER `forma_pagamento`,
                        ADD COLUMN IF NOT EXISTS `pix_pago`        TINYINT(1)  NOT NULL DEFAULT 0 AFTER `pix_txid`,
                        ADD COLUMN IF NOT EXISTS `boleto_codigo`   VARCHAR(60) NULL AFTER `pix_pago`,
                        ADD COLUMN IF NOT EXISTS `boleto_vencimento` DATE NULL AFTER `boleto_codigo`,
                        ADD COLUMN IF NOT EXISTS `paypal_order_id` VARCHAR(80) NULL AFTER `boleto_vencimento`
                    ");
                }
            } catch(\Exception $e){}

            $pdo->beginTransaction();
            $total = 0;
            foreach ($itens as $i) $total += $i['quantidade'] * $i['preco'];

            // Gera código de boleto (padrão Febraban simplificado para demonstração)
            $vencimento  = date('Y-m-d', strtotime('+3 days'));
            $nossoNumero = str_pad(rand(1,99999999), 8, '0', STR_PAD_LEFT);
            $cedente     = '0341'; // Código Itaú (exemplo)
            $agencia     = '1234';
            $conta        = '56789';
            $totalCents  = str_pad(round($total * 100), 10, '0', STR_PAD_LEFT);
            // Monta linha digitável fictícia mas bem formada (44 dígitos)
            $campo1 = $cedente . '9' . substr($nossoNumero,0,5);
            $campo2 = substr($nossoNumero,5,3) . $agencia . '1';
            $campo3 = '000' . $conta . '0';
            $fator  = '3921'; // fator de vencimento exemplo
            $codigoBarra = "341" . "9" . $fator . $totalCents . $agencia . $nossoNumero . $conta . "000";
            // Linha digitável formatada: campo1.digito campo2.digito campo3.digito digitoverif fatorvencto valor
            $linhaDigitavel = substr($campo1,0,5).'.'.substr($campo1,5).' '
                            . substr($campo2,0,5).'.'.substr($campo2,5).' '
                            . substr($campo3,0,5).'.'.substr($campo3,5).' '
                            . '1 ' . $fator . $totalCents;

            $s = $pdo->prepare("INSERT INTO pedidos (cliente_id,total,status,forma_pagamento,boleto_codigo,boleto_vencimento) VALUES (?,?,'pendente','boleto',?,?)");
            $s->execute([clienteId(), $total, $linhaDigitavel, $vencimento]);
            $pedidoId = $pdo->lastInsertId();

            $si = $pdo->prepare("INSERT INTO pedido_itens (pedido_id,produto_id,quantidade,preco) VALUES (?,?,?,?)");
            foreach ($itens as $i) $si->execute([$pedidoId,$i['produto_id'],$i['quantidade'],$i['preco']]);
            $pdo->commit();

            jsonResp([
                'success'         => true,
                'pedido_id'       => $pedidoId,
                'total'           => $total,
                'linha_digitavel' => $linhaDigitavel,
                'codigo_barras'   => $codigoBarra,
                'vencimento'      => date('d/m/Y', strtotime($vencimento)),
                'beneficiario'    => Config::PIX_NOME,
            ]);
            break;

        // ══════════════════════════════════════════════════════════════
        // PAYPAL
        // ══════════════════════════════════════════════════════════════

        // ── Cria order PayPal e retorna URL de aprovação ─────────────
        case 'paypal_criar_order':
            if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Faça login para finalizar.'],401);
            $itens = json_decode($_POST['itens'] ?? '[]', true);
            if (empty($itens)) jsonResp(['success'=>false,'message'=>'Carrinho vazio.']);

            $total = 0;
            foreach ($itens as $i) $total += $i['quantidade'] * $i['preco'];

            // Credenciais PayPal (configure em config.php)
            $clientId     = defined('Config::PAYPAL_CLIENT_ID') ? Config::PAYPAL_CLIENT_ID : (Config::PAYPAL_SANDBOX ? 'sb' : '');
            $clientSecret = defined('Config::PAYPAL_SECRET')    ? Config::PAYPAL_SECRET    : '';
            $baseUrl      = Config::PAYPAL_SANDBOX
                ? 'https://api-m.sandbox.paypal.com'
                : 'https://api-m.paypal.com';

            // 1. Obtém token de acesso
            $ctx = stream_context_create(['http'=>[
                'method'  => 'POST',
                'header'  => "Authorization: Basic ".base64_encode("$clientId:$clientSecret")."\r\nContent-Type: application/x-www-form-urlencoded\r\n",
                'content' => 'grant_type=client_credentials',
                'ignore_errors' => true,
            ]]);
            $tokenResp = @file_get_contents("$baseUrl/v1/oauth2/token", false, $ctx);
            $tokenData = json_decode($tokenResp, true);
            if (empty($tokenData['access_token']))
                jsonResp(['success'=>false,'message'=>'Falha ao conectar com PayPal. Verifique as credenciais.']);

            $accessToken = $tokenData['access_token'];

            // URL de retorno
            $returnUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['REQUEST_URI']) . '/index.php?paypal=ok';
            $cancelUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['REQUEST_URI']) . '/index.php?paypal=cancelado';

            // 2. Cria a order
            $orderPayload = json_encode([
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => 'BRL',
                        'value'         => number_format($total, 2, '.', ''),
                    ],
                    'description' => 'Pedido ' . Config::PIX_NOME,
                ]],
                'application_context' => [
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl,
                    'brand_name' => Config::PIX_NOME,
                    'locale'     => 'pt-BR',
                    'user_action'=> 'PAY_NOW',
                ],
            ]);
            $ctx2 = stream_context_create(['http'=>[
                'method'  => 'POST',
                'header'  => "Authorization: Bearer $accessToken\r\nContent-Type: application/json\r\n",
                'content' => $orderPayload,
                'ignore_errors' => true,
            ]]);
            $orderResp = @file_get_contents("$baseUrl/v2/checkout/orders", false, $ctx2);
            $orderData = json_decode($orderResp, true);
            if (empty($orderData['id']))
                jsonResp(['success'=>false,'message'=>'Erro ao criar order PayPal.']);

            $orderId    = $orderData['id'];
            $approveUrl = '';
            foreach ($orderData['links'] as $link) {
                if ($link['rel'] === 'approve') { $approveUrl = $link['href']; break; }
            }

            // Salva o pedido como pendente com o order_id do PayPal
            try {
                $cols = $pdo->query("SHOW COLUMNS FROM pedidos LIKE 'paypal_order_id'")->fetchAll();
                if (empty($cols)) {
                    $pdo->exec("ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS `paypal_order_id` VARCHAR(80) NULL");
                }
            } catch(\Exception $e){}

            $pdo->beginTransaction();
            $sp = $pdo->prepare("INSERT INTO pedidos (cliente_id,total,status,forma_pagamento,paypal_order_id) VALUES (?,?,'pendente','paypal',?)");
            $sp->execute([clienteId(), $total, $orderId]);
            $pedidoId = $pdo->lastInsertId();

            $si = $pdo->prepare("INSERT INTO pedido_itens (pedido_id,produto_id,quantidade,preco) VALUES (?,?,?,?)");
            foreach ($itens as $i) $si->execute([$pedidoId,$i['produto_id'],$i['quantidade'],$i['preco']]);
            $pdo->commit();

            // Armazena pedido_id na sessão para capturar após retorno
            $_SESSION['paypal_pedido_id'] = $pedidoId;
            $_SESSION['paypal_itens']     = $itens;

            jsonResp(['success'=>true,'approve_url'=>$approveUrl,'order_id'=>$orderId,'pedido_id'=>$pedidoId]);
            break;

        // ── Captura pagamento PayPal após retorno ────────────────────
        case 'paypal_capturar':
            if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Não autenticado.'],401);
            $orderId  = trim($_POST['order_id'] ?? '');
            $pedidoId = (int)($_SESSION['paypal_pedido_id'] ?? $_POST['pedido_id'] ?? 0);
            if (!$orderId || !$pedidoId) jsonResp(['success'=>false,'message'=>'Dados inválidos.']);

            $clientId     = Config::PAYPAL_CLIENT_ID;
            $clientSecret = Config::PAYPAL_SECRET;
            $baseUrl      = Config::PAYPAL_SANDBOX
                ? 'https://api-m.sandbox.paypal.com'
                : 'https://api-m.paypal.com';

            // Token
            $ctx = stream_context_create(['http'=>[
                'method'  => 'POST',
                'header'  => "Authorization: Basic ".base64_encode("$clientId:$clientSecret")."\r\nContent-Type: application/x-www-form-urlencoded\r\n",
                'content' => 'grant_type=client_credentials',
                'ignore_errors' => true,
            ]]);
            $tokenData = json_decode(@file_get_contents("$baseUrl/v1/oauth2/token", false, $ctx), true);
            if (empty($tokenData['access_token']))
                jsonResp(['success'=>false,'message'=>'Falha ao autenticar PayPal.']);

            // Captura
            $ctx2 = stream_context_create(['http'=>[
                'method'  => 'POST',
                'header'  => "Authorization: Bearer {$tokenData['access_token']}\r\nContent-Type: application/json\r\n",
                'content' => '{}',
                'ignore_errors' => true,
            ]]);
            $captureData = json_decode(@file_get_contents("$baseUrl/v2/checkout/orders/$orderId/capture", false, $ctx2), true);

            $status = $captureData['status'] ?? '';
            if ($status === 'COMPLETED') {
                $pdo->prepare("UPDATE pedidos SET status='confirmado' WHERE id=? AND cliente_id=?")->execute([$pedidoId, clienteId()]);
                unset($_SESSION['paypal_pedido_id'], $_SESSION['paypal_itens']);
                jsonResp(['success'=>true,'pedido_id'=>$pedidoId]);
            } else {
                $pdo->prepare("UPDATE pedidos SET status='cancelado' WHERE id=?")->execute([$pedidoId]);
                jsonResp(['success'=>false,'message'=>'Pagamento PayPal não concluído. Status: '.$status]);
            }
            break;

        // ── FINALIZAR PEDIDO (legado — mantido para compatibilidade) ─
        case 'pedido_criar':
            if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Faça login para finalizar.'],401);
            $itens = json_decode($_POST['itens'] ?? '[]', true);
            if (empty($itens)) jsonResp(['success'=>false,'message'=>'Carrinho vazio.']);

            $pdo->beginTransaction();
            $total = 0;
            foreach ($itens as $i) { $total += $i['quantidade'] * $i['preco']; }

            $s = $pdo->prepare("INSERT INTO pedidos (cliente_id,total,status) VALUES (?,?,'pendente')");
            $s->execute([clienteId(), $total]);
            $pedidoId = $pdo->lastInsertId();

            $si = $pdo->prepare("INSERT INTO pedido_itens (pedido_id,produto_id,quantidade,preco) VALUES (?,?,?,?)");
            foreach ($itens as $i) {
                $si->execute([$pedidoId, $i['produto_id'], $i['quantidade'], $i['preco']]);
            }
            $pdo->commit();
            jsonResp(['success'=>true,'pedido_id'=>$pedidoId]);
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
