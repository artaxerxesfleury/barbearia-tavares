<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['logado'])) {
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$seller_id = $_GET['seller_id'] ?? '1148466601'; // Seu Seller ID padrão

// Endpoint para buscar itens de um vendedor no site MLB (Brasil)
$api_url = "https://api.mercadolibre.com/sites/MLB/search?seller_id=$seller_id";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);

$produtos = [];

if (isset($data['results'])) {
    foreach ($data['results'] as $item) {
        $produtos[] = [
            'id' => $item['id'],
            'title' => $item['title'],
            'price' => $item['price'],
            'thumbnail' => $item['thumbnail'],
            'permalink' => $item['permalink']
        ];
    }
}

echo json_encode($produtos);
?>
