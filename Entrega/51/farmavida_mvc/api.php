<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

function json_response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if (!isset($_SESSION['usuario_id'])) {
    json_response(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.'], 401);
}

require_once __DIR__ . '/app/Controllers/ProdutoController.php';
require_once __DIR__ . '/app/Controllers/ClienteController.php';
require_once __DIR__ . '/app/Controllers/VendaController.php';
require_once __DIR__ . '/app/Controllers/PedidoController.php';
require_once __DIR__ . '/app/Controllers/DashboardController.php';

$endpoint = $_GET['endpoint'] ?? '';

try {
    switch ($endpoint) {
        // ── Produtos ───────────────────────────────────────────────
        case 'produtos_criar':
            (new ProdutoController())->apiCriar();
            break;
        case 'produtos_editar_form':
            (new ProdutoController())->apiEditarForm();
            break;
        case 'produtos_editar':
            (new ProdutoController())->apiEditar();
            break;
        case 'produtos_deletar':
            (new ProdutoController())->apiDeletar();
            break;

        // ── Lotes ──────────────────────────────────────────────────
        case 'lotes_listar':
            (new ProdutoController())->apiLotesListar();
            break;
        case 'lotes_criar':
            (new ProdutoController())->apiLotesCriar();
            break;

        // ── Categorias ─────────────────────────────────────────────
        case 'categoria_criar':
            (new ProdutoController())->apiCategoriaCriar();
            break;
        case 'categoria_remover':
            (new ProdutoController())->apiCategoriaRemover();
            break;
        case 'categorias_listar':
            (new ProdutoController())->apiCategoriasListar();
            break;

        // ── Vendas ─────────────────────────────────────────────────
        case 'venda_finalizar':
            (new VendaController())->apiFinalizar();
            break;
        case 'venda_itens':
            (new VendaController())->apiItens();
            break;

        // ── Caixa ──────────────────────────────────────────────────
        case 'caixa_abrir':
            (new VendaController())->apiAbrirCaixa();
            break;
        case 'caixa_fechar':
            (new VendaController())->apiFecharCaixa();
            break;

        // ── Clientes ───────────────────────────────────────────────
        case 'cliente_criar':
            (new ClienteController())->apiCriar();
            break;
        case 'cliente_editar':
            (new ClienteController())->apiEditar();
            break;
        case 'cliente_deletar':
            (new ClienteController())->apiDeletar();
            break;

        // ── Banners ────────────────────────────────────────────────
        case 'banner_criar':
            if (($_SESSION['usuario_cargo'] ?? '') !== 'Gerente') {
                json_response(['success' => false, 'message' => 'Acesso restrito a Gerentes.'], 403);
            }
            require_once __DIR__ . '/app/Models/DashboardModel.php';
            $titulo = trim($_POST['titulo'] ?? '');
            if (!$titulo) json_response(['success' => false, 'message' => 'Título obrigatório.']);
            $ctrl  = new DashboardController();
            // DashboardController não expõe salvarImagem diretamente, usamos BaseController via herança
            // Criamos inline para manter compatibilidade:
            $imagem = null;
            if (!empty($_FILES['imagem']['tmp_name'])) {
                $file = $_FILES['imagem'];
                if ($file['error'] === UPLOAD_ERR_OK && $file['size'] <= 2*1024*1024) {
                    $mime = mime_content_type($file['tmp_name']);
                    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
                    if (isset($allowed[$mime])) {
                        $ext   = $allowed[$mime];
                        $nome  = uniqid('img_',true).'.'.$ext;
                        $dir   = __DIR__.'/uploads/banners/';
                        if (!is_dir($dir)) mkdir($dir, 0755, true);
                        if (move_uploaded_file($file['tmp_name'], $dir.$nome)) $imagem = $nome;
                    }
                }
            }
            require_once __DIR__ . '/app/Models/DashboardModel.php';
            $model = new DashboardModel();
            $model->criarBanner($_POST, $imagem);
            json_response(['success' => true]);
            break;

        case 'banner_deletar':
            if (($_SESSION['usuario_cargo'] ?? '') !== 'Gerente') {
                json_response(['success' => false, 'message' => 'Acesso restrito a Gerentes.'], 403);
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $id   = intval($data['id'] ?? 0);
            require_once __DIR__ . '/app/Models/DashboardModel.php';
            (new DashboardModel())->deletarBanner($id);
            json_response(['success' => true]);
            break;

        // ── Pedidos da Loja ────────────────────────────────────────
        case 'pedidos_loja_listar':
            (new PedidoController())->apiListar();
            break;
        case 'pedido_loja_detalhes':
            (new PedidoController())->apiDetalhes();
            break;
        case 'pedido_loja_status':
            (new PedidoController())->apiStatus();
            break;
        case 'pedido_loja_pix_confirmar':
            (new PedidoController())->apiConfirmarPix();
            break;
        case 'pedido_loja_deletar':
            (new PedidoController())->apiDeletar();
            break;

        default:
            json_response(['success' => false, 'message' => 'Endpoint não encontrado.'], 404);
    }
} catch (Exception $e) {
    json_response(['success' => false, 'message' => $e->getMessage()], 500);
}
