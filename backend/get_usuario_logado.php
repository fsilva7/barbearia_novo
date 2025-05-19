<?php
// get_usuario_logado.php - Retorna dados do usuário logado pela sessão ou pelo ID
require_once 'db.php';
header('Content-Type: application/json');

// Se for passado um ID específico, busca as informações desse usuário
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare('SELECT id, nome, tipo FROM usuarios WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'id' => $row['id'],
            'nome' => $row['nome'],
            'tipo' => $row['tipo']
        ]);
    } else {
        echo json_encode(['error' => 'Usuário não encontrado']);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Se não, busca o usuário da sessão atual
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['logado' => false]);
    exit;
}

echo json_encode([
    'logado' => true,
    'id' => $_SESSION['usuario_id'],
    'nome' => $_SESSION['usuario_nome'],
    'tipo' => $_SESSION['usuario_tipo']
]);
?>
