<?php
// atualizar_tabela_horarios.php - Adiciona coluna "fechado" à tabela horarios_funcionamento
require_once 'db.php';
header('Content-Type: application/json');

// Verifica se a coluna já existe
$result = $conn->query("SHOW COLUMNS FROM `horarios_funcionamento` LIKE 'fechado'");
if ($result->num_rows == 0) {
    // Adiciona a coluna "fechado"
    $conn->query("ALTER TABLE `horarios_funcionamento` ADD COLUMN `fechado` TINYINT(1) DEFAULT 0 AFTER `hora_fim`");
    echo json_encode(['sucesso' => true, 'mensagem' => 'Coluna "fechado" adicionada com sucesso!']);
} else {
    echo json_encode(['sucesso' => true, 'mensagem' => 'Coluna "fechado" já existe.']);
}

$conn->close();
?>
