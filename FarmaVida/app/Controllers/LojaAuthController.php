<?php
class LojaAuthController extends Controller
{
    public function index(): void
    {
        require_once FARMAVIDA_ROOT . '/app/Support/loja.php';
        // Redireciona se já logado
        if (!empty($_SESSION['loja_cliente_id'])) {
            header('Location: index.php');
            exit;
        }
        $modo = $_GET['modo'] ?? 'login'; // login | cadastro
        $this->render('loja/login', get_defined_vars());
    }
}
