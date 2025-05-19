<?php
// atualizar_status_agendamento.php - Atualiza o status de um agendamento
// Recebe: id, status
// Retorna: JSON com sucesso ou erro

require_once 'db.php';
header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$status = $_POST['status'] ?? '';

if (!$id || !$status) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Parâmetros insuficientes']);
    exit;
}

// Verificar se o status é válido
$statusPermitidos = ['pendente', 'confirmado', 'cancelado'];
if (!in_array($status, $statusPermitidos)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Status inválido']);
    exit;
}

$stmt = $conn->prepare('UPDATE agendamentos SET status = ? WHERE id = ?');
$stmt->bind_param('si', $status, $id);
$sucesso = $stmt->execute();

if ($sucesso) {
    echo json_encode(['sucesso' => true, 'mensagem' => 'Status atualizado com sucesso']);
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar status: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
