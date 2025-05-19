<?php
// listar_profissionais.php - Lista profissionais que atendem um serviço
// Recebe: servico_id (GET ou POST), barbeiro_id (opcional)
// Retorna: JSON com id, nome

require_once 'db.php';
header('Content-Type: application/json');

$servico_id = $_GET['servico_id'] ?? $_POST['servico_id'] ?? '';
$barbeiro_id = $_GET['barbeiro_id'] ?? $_POST['barbeiro_id'] ?? '';

if (!$servico_id) {
    echo json_encode([]);
    exit;
}

// Se tiver barbeiro_id, filtra apenas profissionais daquele estabelecimento
if ($barbeiro_id) {
    $nome_barbeiro = 'Barbeiro #' . $barbeiro_id;
    $sql = 'SELECT p.id, p.nome FROM profissionais p
            INNER JOIN profissional_servico ps ON p.id = ps.profissional_id
            WHERE ps.servico_id = ? AND p.nome = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $servico_id, $nome_barbeiro);
} else {
    $sql = 'SELECT p.id, p.nome FROM profissionais p
            INNER JOIN profissional_servico ps ON p.id = ps.profissional_id
            WHERE ps.servico_id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $servico_id);
}

$stmt->execute();
$result = $stmt->get_result();

$profissionais = [];
while ($row = $result->fetch_assoc()) {
    $profissionais[] = $row;
}

echo json_encode($profissionais);
$stmt->close();
$conn->close();
?>
