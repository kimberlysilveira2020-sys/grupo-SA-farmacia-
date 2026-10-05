<?php
class ErroController extends Controller
{
    public function index(): void
    {
        /**
         * Página de Erro 404 - Não Encontrada
         */

        // 1. Inicia a sessão (necessário para o header.php saber se renderiza a navbar ou não)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Força o código de resposta HTTP como 404 (boa prática)
        http_response_code(404);

        // 3. Define o título da página e inclui o cabeçalho
        $page_title = "Página não encontrada";
        $this->render('errors/404', get_defined_vars());
    }
}
