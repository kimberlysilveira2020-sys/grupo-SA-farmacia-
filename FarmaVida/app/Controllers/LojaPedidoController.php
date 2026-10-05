<?php
class LojaPedidoController extends Controller
{
    public function index(): void
    {
        require_once FARMAVIDA_ROOT . '/app/Support/loja.php';
        if (empty($_SESSION['loja_cliente_id'])) {
            header('Location: login.php');
            exit;
        }
        $clienteNome = $_SESSION['loja_cliente_nome'] ?? '';
        $this->render('loja/meus_pedidos', get_defined_vars());
    }
}
