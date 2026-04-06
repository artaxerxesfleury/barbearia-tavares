<?php
/**
 * Gerenciador de Tokens do Mercado Livre
 * Responsável por obter o token atual e renová-lo (Refresh Token) automaticamente se estiver expirado.
 */

// Credenciais caso não estejam no JSON
define('ML_CLIENT_ID_GLOBAL', '8304432912396900');
define('ML_CLIENT_SECRET_GLOBAL', 'BNSEzl4zbEHpAQNBhgCo36kolyNZVQy5');

function get_valid_ml_token() {
    $config_path = __DIR__ . '/ml_config.json';
    if (!file_exists($config_path)) {
        throw new Exception("Arquivo de configuração ml_config.json não encontrado. Faça a autorização via painel Admin.");
    }

    $config = json_decode(file_get_contents($config_path), true);
    
    $access_token  = $config['access_token'] ?? '';
    $refresh_token = $config['refresh_token'] ?? '';
    $expires       = (int)($config['token_expires'] ?? 0);
    $client_id     = $config['client_id'] ?? ML_CLIENT_ID_GLOBAL;
    $client_secret = $config['client_secret'] ?? ML_CLIENT_SECRET_GLOBAL;

    if (empty($access_token)) {
        throw new Exception("Token de acesso não encontrado. Faça a autorização via painel Admin.");
    }

    // Se o token expira em menos de 10 minutos (600 segundos), renovar
    if (time() >= ($expires - 600)) {
        if (empty($refresh_token)) {
            throw new Exception("Token expirado e sem refresh_token disponível. Refaça a autorização geral Meli.");
        }

        $ch = curl_init('https://api.mercadolibre.com/oauth/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ],
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type'    => 'refresh_token',
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'refresh_token' => $refresh_token,
            ]),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300 && !empty($data['access_token'])) {
            // Renovou com sucesso
            $config['access_token']  = $data['access_token'];
            $config['refresh_token'] = $data['refresh_token'] ?? $refresh_token; 
            $config['token_expires'] = time() + (int)($data['expires_in'] ?? 21600);
            
            file_put_contents($config_path, json_encode($config, JSON_PRETTY_PRINT));
            
            return $data['access_token'];
        } else {
            // Falha ao renovar
            $msg = $data['message'] ?? $data['error_description'] ?? 'Erro desconhecido';
            throw new Exception("Falha ao renovar o Token ML: $msg (HTTP $httpCode)");
        }
    }

    // Ainda é válido
    return $access_token;
}
?>
