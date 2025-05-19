<?php
// remover_servico.php - Remove um serviço pelo id
require_once 'db.php';
header('Content-Type: application/json');
$id = $_POST['id'] ?? '';
if (!$id) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID não informado.']);
    exit;
}
$stmt = $conn->prepare('DELETE FROM servicos WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();
$conn->close();
echo json_encode(['sucesso' => true]);
?>
