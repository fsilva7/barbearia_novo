<?php
// listar_horarios.php - Lista horários disponíveis para um profissional, serviço e data
// Recebe: profissional_id, servico_id, data (GET ou POST), barbeiro_id (opcional)
// Retorna: JSON com horários disponíveis (ex: ["08:00", "08:10", ...])

require_once 'db.php';
header('Content-Type: application/json');

$profissional_id = $_GET['profissional_id'] ?? $_POST['profissional_id'] ?? '';
$servico_id = $_GET['servico_id'] ?? $_POST['servico_id'] ?? '';
$data = $_GET['data'] ?? $_POST['data'] ?? '';
$barbeiro_id = $_GET['barbeiro_id'] ?? $_POST['barbeiro_id'] ?? '';

if (!$profissional_id || !$servico_id || !$data) {
    echo json_encode([]);
    exit;
}

// Busca duração do serviço
$stmt = $conn->prepare('SELECT duracao_minutos FROM servicos WHERE id = ?');
$stmt->bind_param('i', $servico_id);
$stmt->execute();
$stmt->bind_result($duracao);
$stmt->fetch();
$stmt->close();

if (!$duracao) {
    echo json_encode([]);
    exit;
}

// Obtém o dia da semana para a data selecionada (0=domingo, 1=segunda, etc)
$dia_semana_num = date('w', strtotime($data));
$dias_semana = ['domingo', 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'];
$dia_semana = $dias_semana[$dia_semana_num];
// Busca horários de funcionamento para o dia da semana
$hora_inicio = '08:00'; // Padrão se não houver configuração
$hora_fim = '20:00';    // Padrão se não houver configuração
$fechado = false;

if ($barbeiro_id) {
    // Verifica se o estabelecimento tem horário configurado para este dia da semana
    $stmt = $conn->prepare('SELECT hora_inicio, hora_fim, fechado FROM horarios_funcionamento 
                            WHERE barbeiro_id = ? AND dia_semana = ?');
    $stmt->bind_param('is', $barbeiro_id, $dia_semana);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Se está fechado neste dia, retorna array vazio
        if ($row['fechado']) {
            echo json_encode([]);
            exit;
        }
        
        // Usa os horários configurados pelo estabelecimento
        $hora_inicio = $row['hora_inicio'];
        $hora_fim = $row['hora_fim'];
    }
    $stmt->close();
}

// Busca agendamentos já feitos
$stmt = $conn->prepare('SELECT hora, s.duracao_minutos 
                        FROM agendamentos a
                        INNER JOIN servicos s ON a.servico_id = s.id
                        WHERE a.profissional_id = ? AND a.data = ? AND a.status != "cancelado"');
$stmt->bind_param('is', $profissional_id, $data);
$stmt->execute();
$result = $stmt->get_result();
$agendamentos_ocupados = [];

// Armazena não apenas o horário, mas também a duração do serviço para calcular conflitos
while ($row = $result->fetch_assoc()) {
    $agendamentos_ocupados[] = [
        'hora' => $row['hora'],
        'duracao' => $row['duracao_minutos']
    ];
}
$stmt->close();

// Gera horários de 10 em 10 minutos considerando duração do serviço e conflitos
function horarios_disponiveis($inicio, $fim, $duracao_servico, $agendamentos_ocupados) {
    $disponiveis = [];
    $h = strtotime($inicio);
    $f = strtotime($fim);
    
    // Horários a cada 10 minutos
    while ($h + $duracao_servico*60 <= $f) {
        $hora_str = date('H:i', $h);
        $conflito = false;
        
        // Verifica se este horário conflita com algum agendamento existente
        foreach ($agendamentos_ocupados as $ag) {
            $hora_agendada = strtotime($ag['hora']);
            $duracao_agendada = $ag['duracao'];
            
            // Verifica se há sobreposição de horários
            // Um horário está ocupado se o início do novo horário estiver dentro de um agendamento existente
            // OU se o final do novo horário estiver dentro de um agendamento existente
            // OU se o novo horário englobar completamente um agendamento existente
            if (
                // Novo agendamento começa durante um existente
                ($h >= $hora_agendada && $h < $hora_agendada + $duracao_agendada*60) ||
                // Novo agendamento termina durante um existente
                ($h + $duracao_servico*60 > $hora_agendada && $h + $duracao_servico*60 <= $hora_agendada + $duracao_agendada*60) ||
                // Novo agendamento engloba completamente um existente
                ($h <= $hora_agendada && $h + $duracao_servico*60 >= $hora_agendada + $duracao_agendada*60)
            ) {
                $conflito = true;
                break;
            }
        }
        
        if (!$conflito) {
            $disponiveis[] = $hora_str;
        }
        
        // Avança 10 minutos
        $h += 10*60;
    }
    
    return $disponiveis;
}

$horarios = horarios_disponiveis($hora_inicio, $hora_fim, $duracao, $agendamentos_ocupados);
echo json_encode($horarios);
$conn->close();
?>
