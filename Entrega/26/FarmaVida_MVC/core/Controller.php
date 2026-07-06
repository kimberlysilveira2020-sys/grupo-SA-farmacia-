<?php
class Controller {
    protected function view(string $view, array $data = []): void {
        extract($data);
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            die("View não encontrada: $view");
        }
        require_once $viewFile;
    }

    protected function redirect(string $url): void {
        header("Location: $url");
        exit;
    }

    protected function requireAuth(): void {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect(BASE_URL . 'login');
        }
    }

    protected function requireGerente(): void {
        $this->requireAuth();
        if (($_SESSION['usuario_cargo'] ?? '') !== 'Gerente') {
            $this->redirect(BASE_URL . 'dashboard');
        }
    }
}
