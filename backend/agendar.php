<?php
// agendar.php - Realiza o agendamento
// Recebe: cliente_id, profissional_id, servico_id, data, hora (POST)
// Retorna: JSON com sucesso ou erro

require_once 'db.php';
header('Content-Type: application/json');

$cliente_id = $_POST['cliente_id'] ?? '';
$profissional_id = $_POST['profissional_id'] ?? '';
$servico_id = $_POST['servico_id'] ?? '';
$data = $_POST['data'] ?? '';
$hora = $_POST['hora'] ?? '';

if (!$cliente_id || !$profissional_id || !$servico_id || !$data || !$hora) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos.']);
    exit;
}

// Verifica se já existe agendamento nesse horário
$stmt = $conn->prepare('SELECT COUNT(*) FROM agendamentos WHERE profissional_id = ? AND data = ? AND hora = ? AND status != "cancelado"');
$stmt->bind_param('iss', $profissional_id, $data, $hora);
$stmt->execute();
$stmt->bind_result($existe);
$stmt->fetch();
$stmt->close();

if ($existe > 0) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Horário já ocupado!']);
    exit;
}

// Insere agendamento
$stmt = $conn->prepare('INSERT INTO agendamentos (cliente_id, profissional_id, servico_id, data, hora, status) VALUES (?, ?, ?, ?, ?, "pendente")');
$stmt->bind_param('iiiss', $cliente_id, $profissional_id, $servico_id, $data, $hora);
if ($stmt->execute()) {
    echo json_encode(['sucesso' => true, 'mensagem' => 'Agendamento realizado com sucesso!']);
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao agendar: ' . $conn->error]);
}
$stmt->close();
$conn->close();
?>
