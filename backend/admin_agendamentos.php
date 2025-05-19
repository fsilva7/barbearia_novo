<?php
// admin_agendamentos.php - Lista todos os agendamentos para o administrador
// Retorna: JSON com dados do agendamento, cliente, profissional, serviço

require_once 'db.php';
header('Content-Type: application/json');

// Se receber profissional_id na query, filtra os agendamentos por esse profissional
$profissional_id = $_GET['profissional_id'] ?? '';
if ($profissional_id) {
    $sql = 'SELECT a.id, a.data, a.hora, a.status, 
                   u.nome AS cliente, s.nome AS servico
            FROM agendamentos a
            INNER JOIN usuarios u ON a.cliente_id = u.id
            INNER JOIN servicos s ON a.servico_id = s.id
            WHERE a.profissional_id = ?
            ORDER BY a.data DESC, a.hora DESC';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $profissional_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $agendamentos = [];
    while ($row = $result->fetch_assoc()) {
        $agendamentos[] = $row;
    }
    $stmt->close();
    echo json_encode($agendamentos);
    $conn->close();
    exit;
}

$sql = 'SELECT a.id, a.data, a.hora, a.status, 
               u.nome AS cliente, p.nome AS profissional, s.nome AS servico
        FROM agendamentos a
        INNER JOIN usuarios u ON a.cliente_id = u.id
        INNER JOIN profissionais p ON a.profissional_id = p.id
        INNER JOIN servicos s ON a.servico_id = s.id
        ORDER BY a.data DESC, a.hora DESC';
$result = $conn->query($sql);

$agendamentos = [];
while ($row = $result->fetch_assoc()) {
    $agendamentos[] = $row;
}

echo json_encode($agendamentos);
$conn->close();
?>
