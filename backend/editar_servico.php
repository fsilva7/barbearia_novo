<?php
// editar_servico.php - Edita um serviço pelo id
require_once 'db.php';
header('Content-Type: application/json');
$id = $_POST['id'] ?? '';
$nome = $_POST['nome'] ?? '';
$duracao = $_POST['duracao_minutos'] ?? '';
$preco = $_POST['preco'] ?? '';
if (!$id || !$nome || !$duracao || !$preco) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos.']);
    exit;
}
$stmt = $conn->prepare('UPDATE servicos SET nome = ?, duracao_minutos = ?, preco = ? WHERE id = ?');
$stmt->bind_param('sidi', $nome, $duracao, $preco, $id);
$stmt->execute();
$stmt->close();
$conn->close();
echo json_encode(['sucesso' => true, 'mensagem' => 'Serviço atualizado!']);
?>
