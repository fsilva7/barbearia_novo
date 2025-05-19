<?php
// verificar_horario_funcionamento.php - Verifica se um estabelecimento está aberto em um dia específico
// Recebe: barbeiro_id, data (GET)
// Retorna: JSON com aberto (true/false), hora_inicio, hora_fim

require_once 'db.php';
header('Content-Type: application/json');

$barbeiro_id = $_GET['barbeiro_id'] ?? '';
$data = $_GET['data'] ?? '';

if (!$barbeiro_id || !$data) {
    echo json_encode(['erro' => 'Parâmetros insuficientes']);
    exit;
}

// Obtém o dia da semana para a data selecionada (0=domingo, 1=segunda, etc)
$dia_semana_num = date('w', strtotime($data));
$dias_semana = ['domingo', 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'];
$dia_semana = $dias_semana[$dia_semana_num];

// Verifica se o estabelecimento tem horário configurado para este dia da semana
$stmt = $conn->prepare('SELECT hora_inicio, hora_fim, fechado FROM horarios_funcionamento 
                        WHERE barbeiro_id = ? AND dia_semana = ?');
$stmt->bind_param('is', $barbeiro_id, $dia_semana);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Se está fechado neste dia
    if ($row['fechado']) {
        echo json_encode(['aberto' => false, 'mensagem' => 'Estabelecimento fechado neste dia.']);
    } else {
        echo json_encode([
            'aberto' => true,
            'hora_inicio' => $row['hora_inicio'],
            'hora_fim' => $row['hora_fim'],
            'dia_semana' => $dia_semana
        ]);
    }
} else {
    // Se não tem configuração, usa horário padrão
    echo json_encode([
        'aberto' => true,
        'hora_inicio' => '08:00',
        'hora_fim' => '20:00',
        'dia_semana' => $dia_semana,
        'padrao' => true
    ]);
}

$stmt->close();
$conn->close();
?>
