<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

if (!isset($_SESSION['logado'])) {
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$url = trim($_GET['url'] ?? '');
if (empty($url)) {
    echo json_encode(['error' => 'URL vazia']);
    exit;
}

// Suporta todos os formatos de URL do ML:
// https://produto.mercadolivre.com.br/MLB-4576430879-...
// https://www.mercadolivre.com.br/p/MLB123456789
preg_match('/MLB[-]?(\d+)/i', $url, $matches);
if (empty($matches[1])) {
    echo json_encode(['error' => 'Link inválido. Deve conter MLB seguido do número.']);
    exit;
}

$item_id = 'MLB' . $matches[1];

// Carrega token OAuth se disponível
$config_path  = __DIR__ . '/ml_config.json';
$config       = file_exists($config_path) ? json_decode(file_get_contents($config_path), true) : [];
$access_token = $config['access_token'] ?? '';

// Headers — usa token OAuth se disponível
$headers = ['Accept: application/json'];
if (!empty($access_token)) {
    $headers[] = "Authorization: Bearer $access_token";
}

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER     => $headers,
]);

// Busca os dados do item
curl_setopt($ch, CURLOPT_URL, "https://api.mercadolibre.com/items/$item_id");
$raw  = curl_exec($ch);
$data = json_decode($raw, true);

if (empty($data) || isset($data['error'])) {
    curl_close($ch);
    $detalhe = $data['message'] ?? 'Item não encontrado ou acesso negado.';
    echo json_encode(['error' => "[$item_id] $detalhe"]);
    exit;
}

// === DESCRIÇÃO DETALHADA: texto do anúncio ===
curl_setopt($ch, CURLOPT_URL, "https://api.mercadolibre.com/items/$item_id/description");
$desc_raw  = curl_exec($ch);
$desc_data = json_decode($desc_raw, true);

$desc_longa = '';
if (!empty($desc_data['plain_text'])) {
    $desc_longa = trim($desc_data['plain_text']);
} elseif (!empty($desc_data['text'])) {
    $desc_longa = trim(strip_tags($desc_data['text']));
}

// === DESCRIÇÃO CURTA: características/atributos do produto ===
// Monta tabela de características igual ao Mercado Livre
$atributos = [];
$ignorar   = ['ITEM_CONDITION', 'SELLER_SKU', 'WARRANTY_TYPE', 'WARRANTY_TIME'];
foreach ($data['attributes'] ?? [] as $attr) {
    $id    = $attr['id']         ?? '';
    $nome  = $attr['name']       ?? '';
    $valor = $attr['value_name'] ?? '';
    if ($nome && $valor && !in_array($id, $ignorar)) {
        $atributos[] = "$nome: $valor";
    }
}
$desc_curta = implode("\n", $atributos);

curl_close($ch);

// Fotos: prioriza URL de alta resolução (sufixo -O = original)
$imagens = [];
foreach (array_slice($data['pictures'] ?? [], 0, 4) as $pic) {
    // URL segura: troca qualquer sufixo de tamanho por -O (original)  
    $img = preg_replace('/-[A-Z](\.(jpg|jpeg|png|webp))$/i', '-O.$2', $pic['url'] ?? '');
    if (!$img) $img = $pic['url'] ?? '';
    if ($img) $imagens[] = $img;
}

// Preço como float limpo (JS vai formatar)
$preco = $data['price'] ?? 0;

echo json_encode([
    'status'          => 'ok',
    'item_id'         => $item_id,
    'nome'            => $data['title'] ?? '',
    'preco'           => number_format($preco, 2, ',', ''),
    'descricao_curta' => $desc_curta,   // Características/atributos do produto
    'descricao_longa' => $desc_longa,   // Texto da descrição do anúncio
    'imagens'         => $imagens,
    'video_url'       => !empty($data['video_id']) ? 'https://www.youtube.com/watch?v=' . $data['video_id'] : '',
    'link'            => $data['permalink'] ?? $url,
], JSON_UNESCAPED_UNICODE);
?>
