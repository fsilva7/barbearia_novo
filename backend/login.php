<?php
// login.php - Login simples de usuário
// Recebe: email, senha (POST)
// Retorna: JSON com sucesso, tipo de usuário e id, ou erro

require_once 'db.php';

header('Content-Type: application/json');

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

if (!$email || !$senha) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos.']);
    exit;
}

$stmt = $conn->prepare('SELECT id, nome, senha, tipo FROM usuarios WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($senha, $row['senha'])) {
        // Salva dados do usuário na sessão
        $_SESSION['usuario_id'] = $row['id'];
        $_SESSION['usuario_nome'] = $row['nome'];
        $_SESSION['usuario_tipo'] = $row['tipo'];
        echo json_encode([
            'sucesso' => true,
            'id' => $row['id'],
            'nome' => $row['nome'],
            'tipo' => $row['tipo']
        ]);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Senha incorreta.']);
    }
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não encontrado.']);
}

$stmt->close();
$conn->close();
?>
