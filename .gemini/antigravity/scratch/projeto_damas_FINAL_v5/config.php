<?php
require_once __DIR__ . '/env_loader.php';

// CONFIGURAÇÃO DINÂMICA
$host    = $_ENV['DB_HOST'] ?? '127.0.0.1';
$db      = $_ENV['DB_NAME'] ?? '';
$user    = $_ENV['DB_USER'] ?? '';
$pass    = $_ENV['DB_PASS'] ?? '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Log do erro real (apenas para o servidor)
    error_log("Erro de Conexão com o Banco: " . $e->getMessage());
    // Mensagem amigável para o usuário
    die("Desculpe, estamos passando por uma manutenção técnica. Por favor, tente novamente em alguns instantes.");
}
?>