<?php
abstract class BaseController {

    protected function requireAuth(): void {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /login.php");
            exit;
        }
    }

    protected function requireAuthJson(): void {
        if (!isset($_SESSION['usuario_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.'], 401);
        }
    }

    protected function jsonResponse(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function render(string $view, array $data = []): void {
        extract($data);
        include __DIR__ . '/../Views/' . $view . '.php';
    }

    protected function redirect(string $url): void {
        header("Location: $url");
        exit;
    }

    protected function salvarImagem(string $campo, string $pasta): ?string {
        if (empty($_FILES[$campo]['tmp_name'])) return null;
        $file = $_FILES[$campo];
        $maxSize = 2 * 1024 * 1024;
        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > $maxSize) throw new Exception('Imagem muito grande (máx. 2MB).');
        $mime = mime_content_type($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) throw new Exception('Tipo de imagem não permitido. Use JPG, PNG ou WEBP.');
        $ext  = $allowed[$mime];
        $nome = uniqid('img_', true) . '.' . $ext;
        $dir  = __DIR__ . '/../../uploads/' . $pasta . '/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (!move_uploaded_file($file['tmp_name'], $dir . $nome)) {
            throw new Exception('Falha ao mover arquivo para o servidor.');
        }
        return $nome;
    }

    protected function getJsonInput(): array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}
