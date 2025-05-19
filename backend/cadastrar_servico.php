<?php
// cadastrar_servico.php - CRUD simples: salva serviço para o barbeiro
require_once 'db.php';
header('Content-Type: application/json');

$nome = $_POST['nome'] ?? '';
$duracao = $_POST['duracao_minutos'] ?? '';
$preco = $_POST['preco'] ?? '';
$barbeiro_id = $_POST['barbeiro_id'] ?? '';

if (!$nome || !$duracao || !$preco || !$barbeiro_id) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos.']);
    exit;
}

// Cria profissional se não existir
$stmt = $conn->prepare('SELECT id FROM profissionais WHERE nome = ?');
$nome_barbeiro = 'Barbeiro #' . $barbeiro_id;
$stmt->bind_param('s', $nome_barbeiro);
$stmt->execute();
$stmt->bind_result($profissional_id);
if ($stmt->fetch()) {
    $stmt->close();
} else {
    $stmt->close();
    $stmt2 = $conn->prepare('INSERT INTO profissionais (nome) VALUES (?)');
    $stmt2->bind_param('s', $nome_barbeiro);
    $stmt2->execute();
    $profissional_id = $conn->insert_id;
    $stmt2->close();
}
// Salva serviço
$stmt = $conn->prepare('INSERT INTO servicos (nome, duracao_minutos, preco) VALUES (?, ?, ?)');
$stmt->bind_param('sid', $nome, $duracao, $preco);
if ($stmt->execute()) {
    $servico_id = $conn->insert_id;
    $stmt3 = $conn->prepare('INSERT INTO profissional_servico (profissional_id, servico_id) VALUES (?, ?)');
    $stmt3->bind_param('ii', $profissional_id, $servico_id);
    $stmt3->execute();
    $stmt3->close();
    echo json_encode(['sucesso' => true, 'mensagem' => 'Serviço cadastrado!']);
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar: ' . $conn->error]);
}
$stmt->close();
$conn->close();
?>
