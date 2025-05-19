<?php
// editar_horario.php - Edita um horário de funcionamento pelo id
require_once 'db.php';
header('Content-Type: application/json');
$id = $_POST['id'] ?? '';
$dia_semana = $_POST['dia_semana'] ?? '';
$hora_inicio = $_POST['hora_inicio'] ?? '';
$hora_fim = $_POST['hora_fim'] ?? '';
$fechado = isset($_POST['fechado']) ? 1 : 0;

if (!$id || !$dia_semana) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos obrigatórios.']);
    exit;
}

$stmt = $conn->prepare('UPDATE horarios_funcionamento SET dia_semana = ?, hora_inicio = ?, hora_fim = ?, fechado = ? WHERE id = ?');
$stmt->bind_param('sssii', $dia_semana, $hora_inicio, $hora_fim, $fechado, $id);
$stmt->execute();
$stmt->close();
$conn->close();
echo json_encode(['sucesso' => true, 'mensagem' => 'Horário atualizado!']);
?>
