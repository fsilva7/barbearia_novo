<?php
// remover_agendamento.php - Remove (cancela) um agendamento pelo id
require_once 'db.php';
header('Content-Type: application/json');
$id = $_POST['id'] ?? '';
if (!$id) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID não informado.']);
    exit;
}
// Para não perder histórico, apenas marca como cancelado
$stmt = $conn->prepare('UPDATE agendamentos SET status = "cancelado" WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();
$conn->close();
echo json_encode(['sucesso' => true]);
?>
