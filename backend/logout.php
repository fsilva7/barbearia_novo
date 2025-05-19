<?php
// logout.php - Faz logout do usuário destruindo a sessão
session_start();
session_unset();
session_destroy();
header('Content-Type: application/json');
echo json_encode(['sucesso' => true, 'mensagem' => 'Logout realizado!']);
?>
