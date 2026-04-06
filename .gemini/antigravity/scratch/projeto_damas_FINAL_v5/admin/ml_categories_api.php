<?php
/**
 * API Auxiliar para buscar categorias do Mercado Livre
 */
require_once 'ml_token_manager.php';
header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
$access_token = get_valid_ml_token();

if (!$id) {
    // Se não tiver ID, busca as categorias principais do Brasil (MLB)
    $url = "https://api.mercadolivre.com/sites/MLB/categories";
} else {
    // Se tiver ID, busca as subcategorias daquele ID
    $url = "https://api.mercadolivre.com/categories/$id";
}

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $access_token"]);
$res = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo $res;
exit;
