<?php
// listar_agendamentos_por_data.php - Lista agendamentos por data para um profissional
// Recebe: profissional_id, data (opcional)
// Retorna: JSON com dados dos agendamentos

require_once 'db.php';
header('Content-Type: application/json');

// Garantir fuso horário correto
date_default_timezone_set('America/Sao_Paulo');

$profissional_id = $_GET['profissional_id'] ?? '';
$data = $_GET['data'] ?? date('Y-m-d'); // Se não receber data, usa a data atual

if (!$profissional_id) {
    echo json_encode(['erro' => 'Profissional não informado']);
    exit;
}

// Garantir que a data está no formato correto (YYYY-MM-DD)
$data_formatada = date('Y-m-d', strtotime($data));

// Registro para depuração
error_log("Buscando agendamentos para profissional ID: $profissional_id e data: $data_formatada");

$sql = 'SELECT a.id, a.data, a.hora, a.status, 
               u.nome AS cliente, s.nome AS servico, s.duracao_minutos
        FROM agendamentos a
        INNER JOIN usuarios u ON a.cliente_id = u.id
        INNER JOIN servicos s ON a.servico_id = s.id
        WHERE a.profissional_id = ? AND a.data = ?
        ORDER BY a.hora ASC';

$stmt = $conn->prepare($sql);
$stmt->bind_param('is', $profissional_id, $data_formatada);
$stmt->execute();
$result = $stmt->get_result();

$agendamentos = [];
while ($row = $result->fetch_assoc()) {
    // Certifique-se de que a data está no formato correto
    $row['data'] = date('Y-m-d', strtotime($row['data']));
    $agendamentos[] = $row;
}

// Registro para depuração
error_log("Total de agendamentos encontrados: " . count($agendamentos));

// Retornar como JSON
echo json_encode($agendamentos);
$stmt->close();
$conn->close();
?>
