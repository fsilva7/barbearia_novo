<?php
// criar_agendamento_teste.php - Cria um agendamento de teste para a data atual
// Este script é apenas para fins de teste e depuração

require_once 'db.php';
header('Content-Type: application/json');

// Garantir fuso horário correto
date_default_timezone_set('America/Sao_Paulo');

// Parâmetros
$cliente_id = $_GET['cliente_id'] ?? 1; // Substitua pelo ID de um cliente válido
$profissional_id = $_GET['profissional_id'] ?? 1; // Substitua pelo ID de um profissional válido
$servico_id = $_GET['servico_id'] ?? 1; // Substitua pelo ID de um serviço válido
$data = $_GET['data'] ?? date('Y-m-d'); // Por padrão, usa a data atual
$hora = $_GET['hora'] ?? '14:00:00'; // Por padrão, agenda para as 14h
$status = $_GET['status'] ?? 'pendente';

// Verifica se já existe um agendamento para este profissional na data e hora
$sql_verificar = 'SELECT COUNT(*) as total FROM agendamentos 
                 WHERE profissional_id = ? AND data = ? AND hora = ?';

$stmt_verificar = $conn->prepare($sql_verificar);
$stmt_verificar->bind_param('iss', $profissional_id, $data, $hora);
$stmt_verificar->execute();
$result_verificar = $stmt_verificar->get_result();
$row_verificar = $result_verificar->fetch_assoc();

if ($row_verificar['total'] > 0) {
    echo json_encode([
        'sucesso' => false, 
        'mensagem' => 'Já existe um agendamento para este profissional nesta data e hora.'
    ]);
    exit;
}

// Insere o agendamento
$sql_inserir = 'INSERT INTO agendamentos (cliente_id, profissional_id, servico_id, data, hora, status) 
               VALUES (?, ?, ?, ?, ?, ?)';

$stmt_inserir = $conn->prepare($sql_inserir);
$stmt_inserir->bind_param('iiisss', $cliente_id, $profissional_id, $servico_id, $data, $hora, $status);
$resultado = $stmt_inserir->execute();

if ($resultado) {
    $id = $conn->insert_id;
    echo json_encode([
        'sucesso' => true, 
        'mensagem' => 'Agendamento de teste criado com sucesso!', 
        'id' => $id,
        'data' => [
            'cliente_id' => $cliente_id,
            'profissional_id' => $profissional_id,
            'servico_id' => $servico_id,
            'data' => $data,
            'hora' => $hora,
            'status' => $status
        ]
    ]);
} else {
    echo json_encode([
        'sucesso' => false, 
        'mensagem' => 'Erro ao criar agendamento: ' . $conn->error
    ]);
}

$stmt_verificar->close();
$stmt_inserir->close();
$conn->close();
?>
