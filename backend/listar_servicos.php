<?php
// listar_servicos.php - Lista todos os serviços cadastrados
// Se barbeiro_id for passado, lista só os serviços do barbeiro
require_once 'db.php';
header('Content-Type: application/json');

$barbeiro_id = $_GET['barbeiro_id'] ?? '';
if ($barbeiro_id) {
    $sql = 'SELECT s.id, s.nome, s.duracao_minutos, s.preco FROM servicos s
            INNER JOIN profissional_servico ps ON s.id = ps.servico_id
            INNER JOIN profissionais p ON ps.profissional_id = p.id
            WHERE p.nome = ?';
    $nome_barbeiro = 'Barbeiro #' . $barbeiro_id;
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $nome_barbeiro);
    $stmt->execute();
    $result = $stmt->get_result();
    $servicos = [];
    while ($row = $result->fetch_assoc()) {
        $servicos[] = $row;
    }
    $stmt->close();
    echo json_encode($servicos);
    $conn->close();
    exit;
}

$sql = 'SELECT id, nome, duracao_minutos, preco FROM servicos';
$result = $conn->query($sql);

$servicos = [];
while ($row = $result->fetch_assoc()) {
    $servicos[] = $row;
}

echo json_encode($servicos);
$conn->close();
?>
