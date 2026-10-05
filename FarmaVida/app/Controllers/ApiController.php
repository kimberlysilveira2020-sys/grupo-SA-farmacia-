<?php
class ApiController extends Controller
{
    public function index(): void
    {
        require_once FARMAVIDA_ROOT . '/app/Support/api.php';
        header('Content-Type: application/json');



        if (!isset($_SESSION['usuario_id'])) {
            json_response(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.'], 401);
        }

        // Garante que coluna 'ativo' existe
        $model = new ApiModel();
        $colAtivo = $model->apiInspecionarProdutos()->fetchAll();
        if (empty($colAtivo)) {
            $model->apiAlterarTabelaProdutos();
            $model->apiAtualizarProdutos();
        }

        // Remove soft-deleted sem histórico (sem venda interna E sem pedido de loja)
        // Mantém apenas os que têm referências históricas
        try {
            $model->apiExcluirProdutos();
        } catch (\Exception $e) { /* tabelas ainda podem não existir */ }

        // Garantir índice UNIQUE na criação de produtos (evita race condition)
        try {
            $indexExists = $model->apiBuscarInformationSchema()->fetch();
            if (!$indexExists) {
                $model->apiAlterarTabelaProdutos2();
            }
        } catch (\Exception $e) { /* já existe ou não consegue */ }

        $endpoint = $_GET['endpoint'] ?? '';

        // ─── Helper: salvar imagem enviada via upload ───────────────────────────────


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

                    $model->beginTransaction();
                    try {
                        // Busca produto com mesmo nome+fabricante independente do status ativo
                        $chk = $model->produtosCriarBuscarProdutos();
                        $chk->execute([$nome, $fabric]);
                        $existente = $chk->fetch();

                        if ($existente) {
                            if ((int)$existente['ativo'] === 1) {
                                // Produto já ativo — não duplicar
                                $model->rollBack();
                                json_response(['success' => false, 'message' => 'Já existe um produto com este nome e fabricante.']);
                            }

                            // Produto estava desativado (soft-delete) — reativar com novos dados
                            $produtoId = (int)$existente['id'];
                            $stmtReativa = $model->produtosCriarAtualizarProdutos($foto);
                            $params = [$cat, $preco, $desc];
                            if ($foto) $params[] = $foto;
                            $params[] = $produtoId;
                            $stmtReativa->execute($params);
                        } else {
                            // Produto novo — inserir
                            $stmt = $model->produtosCriarInserirProdutos();
                            $stmt->execute([$nome, $fabric, $cat, $preco, $desc, $foto]);
                            $produtoId = (int)$model->lastInsertId();
                        }

                        // Lote inicial (opcional)
                        $loteNum = trim($_POST['lote_numero'] ?? '');
                        $loteVal = trim($_POST['lote_validade'] ?? '');
                        $loteQtd = intval($_POST['lote_quantidade'] ?? 0);

                        if ($loteNum && $loteVal && $loteQtd > 0) {
                            $stmtL = $model->produtosCriarInserirLotes();
                            $stmtL->execute([$produtoId, $loteNum, $loteVal, $loteQtd, $loteQtd]);
                        }

                        $model->commit();
                        json_response(['success' => true, 'produto_id' => $produtoId]);
                    } catch (\PDOException $e) {
                        $model->rollBack();
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
                        $old = $model->produtosEditarFormBuscarProdutos();
                        $old->execute([$id]);
                        $oldFoto = $old->fetchColumn();
                        if ($oldFoto && file_exists(FARMAVIDA_ROOT . '/uploads/produtos/' . $oldFoto)) {
                            @unlink(FARMAVIDA_ROOT . '/uploads/produtos/' . $oldFoto);
                        }
                        $stmt = $model->produtosEditarFormAtualizarProdutos();
                        $stmt->execute([$nome, $fab, $cat, $preco, $desc, $foto, $id]);
                    } else {
                        $stmt = $model->produtosEditarFormAtualizarProdutos2();
                        $stmt->execute([$nome, $fab, $cat, $preco, $desc, $id]);
                    }

                    json_response(['success' => true]);
                    break;

                // ── EDITAR PRODUTO via JSON (mantém compatibilidade) ───────────────
                case 'produtos_editar':
                    $data = json_decode(file_get_contents('php://input'), true);
                    $stmt = $model->produtosEditarAtualizarProdutos();
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
                    $chkVenda = $model->produtosDeletarBuscarItensVenda();
                    $chkVenda->execute([$id]);
                    $temVendas = (int)$chkVenda->fetchColumn() > 0;

                    // Verifica se o produto tem pedidos da loja vinculados
                    $chkPedido = $model->produtosDeletarBuscarPedidoItens();
                    $chkPedido->execute([$id]);
                    $temPedidos = (int)$chkPedido->fetchColumn() > 0;

                    if ($temVendas || $temPedidos) {
                        // Produto tem histórico — apenas desativa (soft delete)
                        $model->produtosDeletarAtualizarProdutos()->execute([$id]);
                        $origens = [];
                        if ($temVendas)  $origens[] = 'vendas internas';
                        if ($temPedidos) $origens[] = 'pedidos da loja';
                        json_response(['success' => true, 'aviso' => 'Produto desativado pois possui ' . implode(' e ', $origens) . ' vinculadas. Ele não aparecerá mais na lista.']);
                    } else {
                        // Sem vendas — pode deletar fisicamente
                        // Remove lotes primeiro (FK)
                        $model->produtosDeletarExcluirLotes()->execute([$id]);

                        // Remove foto se existir
                        $old = $model->produtosDeletarBuscarProdutos();
                        $old->execute([$id]);
                        $fotoNome = $old->fetchColumn();
                        if ($fotoNome && file_exists(FARMAVIDA_ROOT . '/uploads/produtos/' . $fotoNome)) {
                            @unlink(FARMAVIDA_ROOT . '/uploads/produtos/' . $fotoNome);
                        }

                        $model->produtosDeletarExcluirProdutos()->execute([$id]);
                        json_response(['success' => true]);
                    }
                    break;

                // ── LISTAR LOTES ───────────────────────────────────────────────────
                case 'lotes_listar':
                    $stmt = $model->lotesListarBuscarLotes();
                    $stmt->execute([(int)$_GET['produto_id']]);
                    json_response(['success' => true, 'lotes' => $stmt->fetchAll()]);
                    break;

                // ── CRIAR LOTE ─────────────────────────────────────────────────────
                case 'lotes_criar':
                    $stmt = $model->lotesCriarInserirLotes();
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

                    $model->beginTransaction();

                    $total = array_sum(array_map(function($i) { return $i['quantidade'] * $i['preco']; }, $itens));

                    $stmtVenda = $model->vendaFinalizarInserirVendas();
                    $stmtVenda->execute([$total, $_SESSION['usuario_id'], $supervisor]);
                    $venda_id = $model->lastInsertId();

                    $stmtItem     = $model->vendaFinalizarInserirItensVenda();
                    $stmtEstoque  = $model->vendaFinalizarAtualizarLotes();
                    $stmtBuscaLote = $model->vendaFinalizarBuscarLotes();

                    foreach ($itens as $item) {
                        $stmtBuscaLote->execute([$item['produto_id'], $item['quantidade']]);
                        $lote = $stmtBuscaLote->fetch();
                        if (!$lote) throw new Exception("Estoque insuficiente para {$item['nome']}.");
                        $stmtItem->execute([$venda_id, $item['produto_id'], $lote['id'], $item['quantidade'], $item['preco']]);
                        $stmtEstoque->execute([$item['quantidade'], $lote['id']]);
                    }

                    $model->commit();
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

                    $stmt = $model->bannerCriarInserirBanners();
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

                    $old = $model->bannerDeletarBuscarBanners();
                    $old->execute([$id]);
                    $img = $old->fetchColumn();
                    if ($img && file_exists(FARMAVIDA_ROOT . '/uploads/banners/' . $img)) {
                        @unlink(FARMAVIDA_ROOT . '/uploads/banners/' . $img);
                    }

                    $model->bannerDeletarExcluirBanners()->execute([$id]);
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
                        $s = $model->clienteCriarInserirClientes();
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
                        $s = $model->clienteEditarAtualizarClientes();
                        $s->execute([$nome,$cpfFmt,$telefone,$id]);
                        json_response(['success'=>true]);
                    } catch (\PDOException $e) {
                        json_response(['success'=>false,'message'=>'CPF já cadastrado para outro cliente.']);
                    }
                    break;

                case 'cliente_deletar':
                    $data = json_decode(file_get_contents('php://input'),true);
                    $id   = intval($data['id'] ?? 0);
                    $model->clienteDeletarExcluirClientes()->execute([$id]);
                    json_response(['success'=>true]);
                    break;

                // ── CAIXA ──────────────────────────────────────────────────────
                case 'caixa_abrir':
                    // Verifica se já tem caixa aberto
                    $chk = $model->caixaAbrirBuscarCaixa();
                    $chk->execute([$_SESSION['usuario_id']]);
                    if ($chk->fetch()) json_response(['success'=>false,'message'=>'Você já possui um caixa aberto.']);
                    $valor = floatval($_POST['valor_abertura'] ?? 0);
                    $obs   = trim($_POST['observacao'] ?? '') ?: null;
                    $s = $model->caixaAbrirInserirCaixa();
                    $s->execute([$_SESSION['usuario_id'],$valor,$obs]);
                    json_response(['success'=>true,'caixa_id'=>$model->lastInsertId()]);
                    break;

                case 'caixa_fechar':
                    $id    = intval($_POST['id'] ?? 0);
                    $valor = floatval($_POST['valor_fechamento'] ?? 0);
                    $obs   = trim($_POST['observacao'] ?? '') ?: null;
                    if (!$id) json_response(['success'=>false,'message'=>'ID inválido.']);
                    $s = $model->caixaFecharAtualizarCaixa();
                    $s->execute([$valor,$obs??'',$id,$_SESSION['usuario_id']]);
                    json_response(['success'=>true]);
                    break;

                // ── ITENS DE UMA VENDA ─────────────────────────────────────────
                case 'venda_itens':
                    $id = intval($_GET['id'] ?? 0);
                    $s  = $model->vendaItensBuscarItensVenda();
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
                    $model->categoriaCriarCriarTabelaCategorias();
                    try {
                        $s = $model->categoriaCriarInserirCategorias();
                        $s->execute([$nome, $icone]);
                        json_response(['success'=>true,'id'=>(int)$model->lastInsertId()]);
                    } catch (\PDOException $e) {
                        json_response(['success'=>false,'message'=>'Categoria já existe com este nome.']);
                    }
                    break;

                // ── REMOVER CATEGORIA ──────────────────────────────────────────────
                case 'categoria_remover':
                    $data = json_decode(file_get_contents('php://input'), true);
                    $id   = intval($data['id'] ?? 0);
                    if (!$id) json_response(['success'=>false,'message'=>'ID inválido.']);
                    $model->categoriaRemoverExcluirCategorias()->execute([$id]);
                    json_response(['success'=>true]);
                    break;

                // ── LISTAR CATEGORIAS ──────────────────────────────────────────────
                case 'categorias_listar':
                    // Garante tabela existe
                    $model->categoriasListarCriarTabelaCategorias();
                    $cats = $model->categoriasListarBuscarCategorias()->fetchAll();
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
                    $stmt = $model->pedidosLojaListarBuscarPedidos($whereSQL);
                    $stmt->execute($params);
                    $pedidos = $stmt->fetchAll();

                    // Totalizadores — query independente sem JOIN para sempre retornar todos os status
                    $tots = $model->pedidosLojaListarBuscarPedidos2()->fetchAll(PDO::FETCH_ASSOC);
                    $resumo = [];
                    foreach ($tots as $t) $resumo[$t['status']] = $t;

                    json_response(['success'=>true,'pedidos'=>$pedidos,'resumo'=>$resumo]);
                    break;

                case 'pedido_loja_detalhes':
                    $id = (int)($_GET['id'] ?? 0);
                    if (!$id) json_response(['success'=>false,'message'=>'ID inválido.']);

                    $p = $model->pedidoLojaDetalhesBuscarPedidos();
                    $p->execute([$id]);
                    $pedido = $p->fetch();
                    if (!$pedido) json_response(['success'=>false,'message'=>'Pedido não encontrado.']);

                    $itens = $model->pedidoLojaDetalhesBuscarPedidoItens();
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

                    $model->pedidoLojaStatusAtualizarPedidos()
                        ->execute([$status, $id]);
                    json_response(['success'=>true,'message'=>'Status atualizado.']);
                    break;

                case 'pedido_loja_pix_confirmar':
                    $id = (int)($_POST['id'] ?? 0);
                    if (!$id) json_response(['success'=>false,'message'=>'ID inválido.']);
                    $model->pedidoLojaPixConfirmarAtualizarPedidos()
                        ->execute([$id]);
                    json_response(['success'=>true,'message'=>'Pagamento PIX confirmado.']);
                    break;

                case 'pedido_loja_deletar':
                    $id = (int)($_POST['id'] ?? 0);
                    if (!$id) json_response(['success'=>false,'message'=>'ID inválido.']);
                    $model->pedidoLojaDeletarExcluirPedidoItens()->execute([$id]);
                    $model->pedidoLojaDeletarExcluirPedidos()->execute([$id]);
                    json_response(['success'=>true,'message'=>'Pedido removido.']);
                    break;

                default:
                    json_response(['success' => false, 'message' => 'Endpoint não encontrado.'], 404);
            }

        } catch (Exception $e) {
            if ($model->inTransaction()) $model->rollBack();
            json_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
