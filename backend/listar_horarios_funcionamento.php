<?php
// listar_horarios_funcionamento.php - Lista horários de funcionamento do barbeiro
// Recebe: barbeiro_id (GET)
// Retorna: JSON com dia_semana, hora_inicio, hora_fim, fechado

require_once 'db.php';
header('Content-Type: application/json');

$barbeiro_id = $_GET['barbeiro_id'] ?? '';
if (!$barbeiro_id) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare('SELECT id, dia_semana, hora_inicio, hora_fim, fechado FROM horarios_funcionamento WHERE barbeiro_id = ?');
$stmt->bind_param('i', $barbeiro_id);
$stmt->execute();
$result = $stmt->get_result();
$horarios = [];
while ($row = $result->fetch_assoc()) {
    $horarios[] = $row;
}
$stmt->close();
$conn->close();
echo json_encode($horarios);
?>
