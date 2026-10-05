<?php
class LojaApiController extends Controller
{
    public function index(): void
    {
        require_once FARMAVIDA_ROOT . '/app/Support/loja.php';
        require_once FARMAVIDA_ROOT . '/app/Support/loja_api.php';
        header('Content-Type: application/json');

        $model      = new LojaModel();
        $endpoint = $_GET['endpoint'] ?? '';



        // ── Gerador de Payload PIX (padrão EMV / Banco Central do Brasil) ──────────





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
                        $s = $model->registrarInserirClientesLoja();
                        $s->execute([$nome,$email,$hash,$cpfFmt,$tel]);
                        $id = $model->lastInsertId();
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
                    $s = $model->loginBuscarClientesLoja();
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

                    $stmtCount = $model->produtosBuscarProdutos($whereSQL);
                    $stmtCount->execute($params);
                    $total = (int)$stmtCount->fetchColumn();

                    $stmtP = $model->produtosBuscarProdutos2($whereSQL, $porPagina, $offset);
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
                        $cats = $model->categoriasBuscarCategorias()->fetchAll();
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
                    $s = $model->bannersBuscarBanners();
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
                        $cols = $model->gerarPixInspecionarPedidos()->fetchAll();
                        if (empty($cols)) {
                            $model->gerarPixAlterarTabelaPedidos();
                        }
                    } catch (\Exception $e) { /* Colunas já existem */ }

                    $model->beginTransaction();
                    $total = 0;
                    foreach ($itens as $i) { $total += $i['quantidade'] * $i['preco']; }

                    // Gera txid único
                    $txid = 'FV' . strtoupper(substr(uniqid(), -10)) . rand(10,99);

                    $s = $model->gerarPixInserirPedidos();
                    $s->execute([clienteId(), $total, $txid]);
                    $pedidoId = $model->lastInsertId();

                    $si = $model->gerarPixInserirPedidoItens();
                    foreach ($itens as $i) {
                        $si->execute([$pedidoId, $i['produto_id'], $i['quantidade'], $i['preco']]);
                    }
                    $model->commit();

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
                    $chk = $model->confirmarPixBuscarPedidos();
                    $chk->execute([$pedidoId, clienteId()]);
                    $pedido = $chk->fetch();

                    if (!$pedido) {
                        // Pode ser que o pedido já foi pago anteriormente ou não pertence ao cliente
                        $jaExiste = $model->confirmarPixBuscarPedidos2();
                        $jaExiste->execute([$pedidoId, clienteId()]);
                        $row = $jaExiste->fetch();
                        if ($row && $row['status'] === 'aguardando_confirmacao') {
                            jsonResp(['success'=>true,'message'=>'Pedido já registrado como aguardando confirmação.', 'pedido_id'=>$pedidoId]);
                        }
                        jsonResp(['success'=>false,'message'=>'Pedido não encontrado ou já processado.'], 404);
                    }

                    // Registra a declaração de pagamento — status intermediário que a farmácia precisa confirmar
                    $upd = $model->confirmarPixAtualizarPedidos();
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
                    $chk = $model->cancelarPixBuscarPedidos();
                    $chk->execute([$pedidoId, clienteId()]);
                    if (!$chk->fetch()) jsonResp(['success'=>false,'message'=>'Pedido não encontrado ou já pago.']);

                    $model->cancelarPixExcluirPedidoItens()->execute([$pedidoId]);
                    $model->cancelarPixExcluirPedidos()->execute([$pedidoId]);
                    jsonResp(['success'=>true]);
                    break;

                // ══════════════════════════════════════════════════════════════
                // CARTÃO DE CRÉDITO
                // ══════════════════════════════════════════════════════════════

                // ── Listar cartões salvos do cliente ────────────────────────
                case 'cartoes_listar':
                    if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Não autenticado.'],401);
                    $s = $model->cartoesListarBuscarCartoesCliente();
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
                    $dup = $model->cartaoSalvarBuscarCartoesCliente();
                    $dup->execute([clienteId(),$ultimos4,$bandeira,$mes,$ano]);
                    if ($dup->fetch()) jsonResp(['success'=>false,'message'=>'Este cartão já está cadastrado.']);

                    // Se vai ser padrão, tira padrão dos outros
                    if ($padrao) $model->cartaoSalvarAtualizarCartoesCliente()->execute([clienteId()]);

                    $ins = $model->cartaoSalvarInserirCartoesCliente();
                    $ins->execute([clienteId(),$apelido,$bandeira,$ultimos4,$nome,$mes,$ano,$tokenHash,$padrao]);
                    jsonResp(['success'=>true,'id'=>$model->lastInsertId(),'bandeira'=>$bandeira,'ultimos4'=>$ultimos4]);
                    break;

                // ── Excluir cartão ───────────────────────────────────────────
                case 'cartao_excluir':
                    if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Não autenticado.'],401);
                    $cartaoId = (int)($_POST['cartao_id'] ?? 0);
                    $del = $model->cartaoExcluirExcluirCartoesCliente();
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
                    $chkC = $model->pagarCartaoBuscarCartoesCliente();
                    $chkC->execute([$cartaoId, clienteId()]);
                    $cartao = $chkC->fetch();
                    if (!$cartao) jsonResp(['success'=>false,'message'=>'Cartão não encontrado.']);

                    // Migração automática das colunas extras
                    try {
                        $cols = $model->pagarCartaoInspecionarPedidos()->fetchAll();
                        if (empty($cols)) {
                            $model->pagarCartaoAlterarTabelaPedidos();
                        }
                    } catch(\Exception $e){}

                    $model->beginTransaction();
                    $total = 0;
                    foreach ($itens as $i) $total += $i['quantidade'] * $i['preco'];

                    $s = $model->pagarCartaoInserirPedidos();
                    $s->execute([clienteId(), $total]);
                    $pedidoId = $model->lastInsertId();

                    $si = $model->pagarCartaoInserirPedidoItens();
                    foreach ($itens as $i) $si->execute([$pedidoId,$i['produto_id'],$i['quantidade'],$i['preco']]);
                    $model->commit();

                    // Aqui seria feita a integração real com gateway (Stripe, PagSeguro, etc.)
                    // Por ora, simula aprovação com 95% de sucesso
                    $aprovado = (rand(1,100) <= 95);
                    if (!$aprovado) {
                        $model->pagarCartaoAtualizarPedidos($pedidoId);
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
                        $cols = $model->gerarBoletoInspecionarPedidos()->fetchAll();
                        if (empty($cols)) {
                            $model->gerarBoletoAlterarTabelaPedidos();
                        }
                    } catch(\Exception $e){}

                    $model->beginTransaction();
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

                    $s = $model->gerarBoletoInserirPedidos();
                    $s->execute([clienteId(), $total, $linhaDigitavel, $vencimento]);
                    $pedidoId = $model->lastInsertId();

                    $si = $model->gerarBoletoInserirPedidoItens();
                    foreach ($itens as $i) $si->execute([$pedidoId,$i['produto_id'],$i['quantidade'],$i['preco']]);
                    $model->commit();

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
                        $cols = $model->paypalCriarOrderInspecionarPedidos()->fetchAll();
                        if (empty($cols)) {
                            $model->paypalCriarOrderAlterarTabelaPedidos();
                        }
                    } catch(\Exception $e){}

                    $model->beginTransaction();
                    $sp = $model->paypalCriarOrderInserirPedidos();
                    $sp->execute([clienteId(), $total, $orderId]);
                    $pedidoId = $model->lastInsertId();

                    $si = $model->paypalCriarOrderInserirPedidoItens();
                    foreach ($itens as $i) $si->execute([$pedidoId,$i['produto_id'],$i['quantidade'],$i['preco']]);
                    $model->commit();

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
                        $model->paypalCapturarAtualizarPedidos()->execute([$pedidoId, clienteId()]);
                        unset($_SESSION['paypal_pedido_id'], $_SESSION['paypal_itens']);
                        jsonResp(['success'=>true,'pedido_id'=>$pedidoId]);
                    } else {
                        $model->paypalCapturarAtualizarPedidos2()->execute([$pedidoId]);
                        jsonResp(['success'=>false,'message'=>'Pagamento PayPal não concluído. Status: '.$status]);
                    }
                    break;

                // ── FINALIZAR PEDIDO (legado — mantido para compatibilidade) ─
                case 'pedido_criar':
                    if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Faça login para finalizar.'],401);
                    $itens = json_decode($_POST['itens'] ?? '[]', true);
                    if (empty($itens)) jsonResp(['success'=>false,'message'=>'Carrinho vazio.']);

                    $model->beginTransaction();
                    $total = 0;
                    foreach ($itens as $i) { $total += $i['quantidade'] * $i['preco']; }

                    $s = $model->pedidoCriarInserirPedidos();
                    $s->execute([clienteId(), $total]);
                    $pedidoId = $model->lastInsertId();

                    $si = $model->pedidoCriarInserirPedidoItens();
                    foreach ($itens as $i) {
                        $si->execute([$pedidoId, $i['produto_id'], $i['quantidade'], $i['preco']]);
                    }
                    $model->commit();
                    jsonResp(['success'=>true,'pedido_id'=>$pedidoId]);
                    break;

                // ── ITENS DE UM PEDIDO ──────────────────────────────────────
                case 'pedido_itens':
                    if (!clienteLogado()) jsonResp(['success'=>false,'message'=>'Não autenticado.'],401);
                    $pedidoId = (int)($_GET['id'] ?? 0);
                    if (!$pedidoId) jsonResp(['success'=>false,'message'=>'ID inválido.']);
                    $check = $model->pedidoItensBuscarPedidos();
                    $check->execute([$pedidoId, clienteId()]);
                    if (!$check->fetch()) jsonResp(['success'=>false,'message'=>'Pedido não encontrado.'],404);
                    $s = $model->pedidoItensBuscarPedidoItens();
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
                    $s = $model->meusPedidosBuscarPedidos();
                    $s->execute([clienteId()]);
                    jsonResp(['success'=>true,'pedidos'=>$s->fetchAll()]);
                    break;

                default:
                    jsonResp(['success'=>false,'message'=>'Endpoint não encontrado.'],404);
            }
        } catch (Exception $e) {
            if (isset($model) && $model->inTransaction()) $model->rollBack();
            jsonResp(['success'=>false,'message'=>$e->getMessage()],500);
        }
    }
}
