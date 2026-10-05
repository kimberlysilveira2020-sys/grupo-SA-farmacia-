<?php
function json_response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function salvarImagem(string $campo, string $pasta): ?string {
    if (empty($_FILES[$campo]['tmp_name'])) return null;

    $file = $_FILES[$campo];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > $maxSize) throw new Exception('Imagem muito grande (máx. 2MB).');

    $mime = mime_content_type($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) throw new Exception('Tipo de imagem não permitido. Use JPG, PNG ou WEBP.');

    $ext  = $allowed[$mime];
    $nome = uniqid('img_', true) . '.' . $ext;
    $dir  = FARMAVIDA_ROOT . '/uploads/' . $pasta . '/';

    if (!is_dir($dir)) mkdir($dir, 0755, true);

    if (!move_uploaded_file($file['tmp_name'], $dir . $nome)) {
        throw new Exception('Falha ao mover arquivo para o servidor.');
    }

    return $nome;
}
