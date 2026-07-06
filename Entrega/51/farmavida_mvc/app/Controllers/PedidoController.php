<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/PedidoModel.php';

class PedidoController extends BaseController {

    public function index(): void {
        $this->requireAuth();
        $page_title = "Pedidos Online";
        $extra_css  = $this->getPedidosCss();
        include __DIR__ . '/../Views/layouts/header.php';
        include __DIR__ . '/../Views/pedidos/index.php';
        include __DIR__ . '/../Views/layouts/footer.php';
    }

    public function apiListar(): void {
        $this->requireAuthJson();
        $model   = new PedidoModel();
        $pedidos = $model->listar($_GET['status'] ?? '', trim($_GET['busca'] ?? ''));
        $resumo  = $model->getResumo();
        $this->jsonResponse(['success' => true, 'pedidos' => $pedidos, 'resumo' => $resumo]);
    }

    public function apiDetalhes(): void {
        $this->requireAuthJson();
        $id     = (int)($_GET['id'] ?? 0);
        if (!$id) $this->jsonResponse(['success' => false, 'message' => 'ID inválido.']);
        $model  = new PedidoModel();
        $pedido = $model->getDetalhes($id);
        if (!$pedido) $this->jsonResponse(['success' => false, 'message' => 'Pedido não encontrado.']);
        $this->jsonResponse(['success' => true, 'pedido' => $pedido]);
    }

    public function apiStatus(): void {
        $this->requireAuthJson();
        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $validos = ['pendente', 'confirmado', 'cancelado'];
        if (!$id || !in_array($status, $validos)) {
            $this->jsonResponse(['success' => false, 'message' => 'Dados inválidos.']);
        }
        $model = new PedidoModel();
        $model->atualizarStatus($id, $status);
        $this->jsonResponse(['success' => true, 'message' => 'Status atualizado.']);
    }

    public function apiConfirmarPix(): void {
        $this->requireAuthJson();
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) $this->jsonResponse(['success' => false, 'message' => 'ID inválido.']);
        $model = new PedidoModel();
        $model->confirmarPix($id);
        $this->jsonResponse(['success' => true, 'message' => 'Pagamento PIX confirmado.']);
    }

    public function apiDeletar(): void {
        $this->requireAuthJson();
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) $this->jsonResponse(['success' => false, 'message' => 'ID inválido.']);
        $model = new PedidoModel();
        $model->deletar($id);
        $this->jsonResponse(['success' => true, 'message' => 'Pedido removido.']);
    }

    private function getPedidosCss(): string {
        return <<<CSS
<style>
.resumo-cards{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:22px;}
.resumo-card{flex:1;min-width:140px;border-radius:12px;padding:16px 20px;display:flex;align-items:center;gap:14px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.08);transition:box-shadow .15s,transform .15s;}
.resumo-card:hover{box-shadow:0 4px 16px rgba(25,135,84,.18);transform:translateY(-2px);}
.resumo-card.ativo{box-shadow:0 0 0 2.5px #198754;}
.resumo-card .icon{font-size:2rem;width:44px;text-align:center;}
.resumo-card .info .num{font-size:1.5rem;font-weight:800;line-height:1;}
.resumo-card .info .label{font-size:.75rem;color:#666;margin-top:2px;}
.tbl-pedidos{width:100%;border-collapse:collapse;font-size:.875rem;}
.tbl-pedidos thead th{background:#f8f9fa;padding:10px 14px;text-align:left;font-weight:700;color:#555;border-bottom:2px solid #dee2e6;white-space:nowrap;}
.tbl-pedidos tbody tr{border-bottom:1px solid #eee;transition:background .12s;}
.tbl-pedidos tbody tr:hover{background:#f5f9f5;}
.tbl-pedidos td{padding:10px 14px;vertical-align:middle;}
.badge-status{display:inline-block;padding:4px 10px;border-radius:20px;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
.bs-pendente{background:#fff3cd;color:#856404;}
.bs-confirmado{background:#d1e7dd;color:#0a3622;}
.bs-cancelado{background:#f8d7da;color:#58151c;}
.badge-pgto{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:.72rem;font-weight:600;}
.bp-pix{background:#e8f5e9;color:#1b5e20;}
.bp-credito{background:#e3f2fd;color:#0d47a1;}
.bp-boleto{background:#fff8e1;color:#e65100;}
.bp-paypal{background:#e8eaf6;color:#1a237e;}
</style>
CSS;
    }
}
