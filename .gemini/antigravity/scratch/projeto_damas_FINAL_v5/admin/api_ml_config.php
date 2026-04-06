<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['logado'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data['client_id']) && !empty($data['client_secret'])) {
    $config = [
        'client_id'     => trim($data['client_id']),
        'client_secret' => trim($data['client_secret']),
    ];

    // Caminho absoluto — garante que salva na pasta correta
    $path = __DIR__ . '/ml_config.json';
    file_put_contents($path, json_encode($config, JSON_PRETTY_PRINT));

    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Preencha Client ID e Client Secret']);
}
?>
