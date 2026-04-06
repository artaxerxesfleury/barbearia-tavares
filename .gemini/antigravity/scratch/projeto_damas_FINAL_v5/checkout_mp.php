<?php
/**
 * Damas Acessórios - Checkout Unificado via Mercado Pago API
 * Recebe os itens do carrinho e cria uma Preferência de Pagamento.
 */
header('Content-Type: application/json; charset=utf-8');

// Permite requisições do próprio domínio
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['error' => 'Método não permitido']));
}

require_once __DIR__ . '/admin/ml_token_manager.php';

try {
    // 1. Obtém os dados do carrinho enviados via AJAX
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (empty($data['cart'])) {
        throw new Exception("Carrinho vazio.");
    }
    
    // 2. Obtém o Token válido (re-autenticando se necessário via ml_token_manager)
    $access_token = get_valid_ml_token();
    
    // 3. Constrói o Payload da Preferência do Mercado Pago
    $mp_items = [];
    foreach ($data['cart'] as $item) {
        $preco = (float)($item['preco'] ?? 0);
        if ($preco <= 0) continue; // Pula itens sem preço ou inválidos
        
        $mp_item = [
            "title"       => substr(trim($item['nome']), 0, 250), // Limite MP
            "quantity"    => 1, // Assumindo qtde 1 para cada item inserido
            "unit_price"  => $preco,
            "currency_id" => "BRL",
        ];
        
        if (!empty($item['img'])) {
            $mp_item["picture_url"] = $item['img'];
        }
        
        if (!empty($item['id'])) {
            $mp_item["id"] = (string)$item['id'];
        }
        
        $mp_items[] = $mp_item;
    }
    
    if (empty($mp_items)) {
        throw new Exception("Nenhum item válido para checkout.");
    }
    
    // Configura a URL de retorno dependendo do ambiente
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'];
    $base_url = "$protocol://$host";
    
    $preference_data = [
        "items" => $mp_items,
        "back_urls" => [
            "success" => "$base_url/index.php?pagamento=sucesso",
            "failure" => "$base_url/index.php?pagamento=falha",
            "pending" => "$base_url/index.php?pagamento=pendente"
        ],
        "auto_return" => "approved",
        // Statement Descriptor: nome que aparece na fatura do cartão
        "statement_descriptor" => "DAMAS ACESS",
        // Revertendo o frete para não especificado já que a conta não tem me2 ativo fora do ML
        "shipments" => [
            "mode" => "not_specified" 
        ]
    ];
    
    // 4. Faz a requisição POST para a API do Mercado Pago
    $ch = curl_init('https://api.mercadopago.com/checkout/preferences');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer $access_token",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode($preference_data)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $mp_res = json_decode($response, true);
    
    if ($httpCode >= 200 && $httpCode < 300 && !empty($mp_res['init_point'])) {
        // Sucesso: Retorna o link de checkout do Mercado Pago
        echo json_encode([
            'status' => 'success',
            'init_point' => $mp_res['init_point'],
            'sandbox_init_point' => $mp_res['sandbox_init_point'] ?? ''
        ]);
    } else {
        $error_msg = $mp_res['message'] ?? 'Erro desconhecido ao gerar link.';
        throw new Exception($error_msg);
    }
    
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
