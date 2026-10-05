<?php
class LojaController extends Controller
{
    public function index(): void
    {
        require_once FARMAVIDA_ROOT . '/app/Support/loja.php';

        $this->render('loja/index', get_defined_vars());
    }
}
