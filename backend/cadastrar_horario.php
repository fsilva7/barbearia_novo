<?php
// cadastrar_horario.php - CRUD simples: salva ou atualiza horários de funcionamento do barbeiro
require_once 'db.php';
header('Content-Type: application/json');

// Recebe dados via POST simples
$barbeiro_id = $_POST['barbeiro_id'] ?? '';
$dia_semana = $_POST['dia_semana'] ?? '';
$hora_inicio = $_POST['hora_inicio'] ?? '';
$hora_fim = $_POST['hora_fim'] ?? '';
$fechado = isset($_POST['fechado']) ? 1 : 0;

if (!$barbeiro_id || !$dia_semana) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados obrigatórios não informados.']);
    exit;
}

// Se estiver fechado, podemos permitir horários vazios
if ($fechado) {
    // Define valores padrão para horários quando fechado
    $hora_inicio = $hora_inicio ?: '00:00:00';
    $hora_fim = $hora_fim ?: '00:00:00';
} else if (!$hora_inicio || !$hora_fim) {
    // Se não estiver fechado, precisamos de horários válidos
    echo json_encode(['sucesso' => false, 'mensagem' => 'Horários de início e fim são obrigatórios quando não está fechado.']);
    exit;
}

// Verifica se já existe horário para esse barbeiro e dia
$stmt = $conn->prepare('SELECT id FROM horarios_funcionamento WHERE barbeiro_id = ? AND dia_semana = ?');
$stmt->bind_param('is', $barbeiro_id, $dia_semana);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    // Atualiza
    $stmt->close();
    $stmt = $conn->prepare('UPDATE horarios_funcionamento SET hora_inicio = ?, hora_fim = ?, fechado = ? WHERE barbeiro_id = ? AND dia_semana = ?');
    $stmt->bind_param('ssis', $hora_inicio, $hora_fim, $fechado, $barbeiro_id, $dia_semana);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    echo json_encode(['sucesso' => true, 'mensagem' => 'Horário atualizado!']);
    exit;
} else {
    $stmt->close();
    // Insere
    $stmt = $conn->prepare('INSERT INTO horarios_funcionamento (barbeiro_id, dia_semana, hora_inicio, hora_fim, fechado) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('isssi', $barbeiro_id, $dia_semana, $hora_inicio, $hora_fim, $fechado);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    echo json_encode(['sucesso' => true, 'mensagem' => 'Horário cadastrado!']);
    exit;
}
?>
?>
