<?php
// debug_agendamentos.php - Um script para depurar os agendamentos no banco de dados
// Retorna: JSON com todos os agendamentos para uma data específica ou todos

require_once 'db.php';
header('Content-Type: application/json');

// Configurar o fuso horário para São Paulo
date_default_timezone_set('America/Sao_Paulo');

// Parâmetros de data (opcional)
$data = $_GET['data'] ?? date('Y-m-d');

// Verificar se é para mostrar todos os agendamentos
$mostrar_todos = isset($_GET['todos']) && $_GET['todos'] === 'true';

// Se for para mostrar todos os agendamentos
if ($mostrar_todos) {
    $sql = 'SELECT a.id, a.data, a.hora, a.status, 
                 u.nome AS cliente, p.nome AS profissional, s.nome AS servico, s.duracao_minutos
          FROM agendamentos a
          INNER JOIN usuarios u ON a.cliente_id = u.id
          INNER JOIN profissionais p ON a.profissional_id = p.id
          INNER JOIN servicos s ON a.servico_id = s.id
          ORDER BY a.data DESC, a.hora ASC';
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
} else {
    // Mostrar agendamentos para uma data específica
    $sql = 'SELECT a.id, a.data, a.hora, a.status, 
                 u.nome AS cliente, p.nome AS profissional, s.nome AS servico, s.duracao_minutos
          FROM agendamentos a
          INNER JOIN usuarios u ON a.cliente_id = u.id
          INNER JOIN profissionais p ON a.profissional_id = p.id
          INNER JOIN servicos s ON a.servico_id = s.id
          WHERE a.data = ?
          ORDER BY a.hora ASC';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $data);
    $stmt->execute();
}

$result = $stmt->get_result();

$agendamentos = [];
while ($row = $result->fetch_assoc()) {
    // Informações adicionais para depuração
    $row['data_formatada'] = date('d/m/Y', strtotime($row['data']));
    $row['hora_formatada'] = substr($row['hora'], 0, 5); // Remover segundos
    
    $agendamentos[] = $row;
}

// Informações de contexto
$resposta = [
    'data_consulta' => $data,
    'data_atual_php' => date('Y-m-d'),
    'data_hora_atual_php' => date('Y-m-d H:i:s'),
    'timezone' => date_default_timezone_get(),
    'total_agendamentos' => count($agendamentos),
    'agendamentos' => $agendamentos
];

echo json_encode($resposta, JSON_PRETTY_PRINT);
$stmt->close();
$conn->close();
?>
