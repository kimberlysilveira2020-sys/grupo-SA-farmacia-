<?php
session_start();
require_once 'conexao.php';

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if (empty($email) || empty($senha)) {
    header("Location: login.html?erro=campos");
    exit();
}

$stmt = $conn->prepare("SELECT id, nome, tipo, senha FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: login.html?erro=invalido");
    exit();
}

$usuario = $resultado->fetch_assoc();

if (!password_verify($senha, $usuario['senha'])) {
    header("Location: login.html?erro=invalido");
    exit();
}

// Login OK — salva sessão
$_SESSION['usuario_id']   = $usuario['id'];
$_SESSION['usuario_nome'] = $usuario['nome'];
$_SESSION['usuario_tipo'] = $usuario['tipo'];

$stmt->close();
$conn->close();

header("Location: dashboard.html");
exit();
?>