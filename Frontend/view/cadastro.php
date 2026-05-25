<?php
require_once 'conexao.php';

// Pega os dados do formulário
$nome      = trim($_POST['nome'] ?? '');
$sobrenome = trim($_POST['sobrenome'] ?? '');
$cpf       = trim($_POST['cpf'] ?? '');
$nascimento = $_POST['nascimento'] ?? '';
$telefone  = trim($_POST['telefone'] ?? '');
$tipo      = $_POST['tipo'] ?? 'cliente';
$email     = trim($_POST['email'] ?? '');
$senha     = $_POST['senha'] ?? '';
$confirmar = $_POST['confirmar_senha'] ?? '';

// Validações básicas
if (empty($nome) || empty($email) || empty($senha)) {
    die("Preencha todos os campos obrigatórios.");
}

if ($senha !== $confirmar) {
    die("As senhas não coincidem.");
}

// Criptografa a senha (NUNCA salvar senha pura)
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Verifica se e-mail já existe
$check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    die("E-mail já cadastrado.");
}
$check->close();

// Insere no banco
$stmt = $conn->prepare("
    INSERT INTO usuarios (nome, sobrenome, cpf, nascimento, telefone, tipo, email, senha)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("ssssssss",
    $nome, $sobrenome, $cpf, $nascimento,
    $telefone, $tipo, $email, $senha_hash
);

if ($stmt->execute()) {
    header("Location: login.html");
    exit();
} else {
    echo "Erro ao cadastrar: " . $conn->error;
}

$stmt->close();
$conn->close();
?>