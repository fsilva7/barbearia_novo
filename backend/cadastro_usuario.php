<?php
// cadastro_usuario.php - Cadastro simples de usuário (cliente, barbeiro ou admin)
// Recebe: nome, email, senha, tipo (POST)
// Retorna: JSON com sucesso ou erro

require_once 'db.php';

header('Content-Type: application/json');

// Recebe dados do POST
$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';
$tipo = $_POST['tipo'] ?? '';

// Validação simples
if (!$nome || !$email || !$senha || !$tipo) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos.']);
    exit;
}

// Criptografa a senha
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Prepara e executa o insert
$stmt = $conn->prepare('INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $nome, $email, $senha_hash, $tipo);

if ($stmt->execute()) {
    echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário cadastrado com sucesso!']);
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
