<?php
// db.php - Conexão simples e comentada com o MySQL
session_start();

// Configurar fuso horário para Brasil/São Paulo
date_default_timezone_set('America/Sao_Paulo');

$host = 'localhost';
$db   = 'barbeiro'; // Nome do banco de dados
$user = 'root'; // Usuário padrão do XAMPP
$pass = ''; // Senha padrão do XAMPP (vazia)

// Cria conexão
$conn = new mysqli($host, $user, $pass, $db);

// Verifica se houve erro na conexão
if ($conn->connect_error) {
    die('Erro na conexão: ' . $conn->connect_error);
}

// Para usar em outros arquivos, inclua com: require_once 'db.php';
?>
