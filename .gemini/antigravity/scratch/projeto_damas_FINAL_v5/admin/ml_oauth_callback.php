<?php
session_start();
if (!isset($_SESSION['logado'])) { header("Location: login.php"); exit; }

// === CREDENCIAIS FIXAS DA API ML ===
define('ML_CLIENT_ID',     '8304432912396900');
define('ML_CLIENT_SECRET', 'BNSEzl4zbEHpAQNBhgCo36kolyNZVQy5');

$config_path = __DIR__ . '/ml_config.json';
$config = file_exists($config_path) ? json_decode(file_get_contents($config_path), true) : [];

$code  = $_GET['code']  ?? '';
$error = $_GET['error'] ?? '';

// ML retornou erro de autorização
if ($error || empty($code)) {
    $msg = $_GET['error_description'] ?? 'Autorização negada ou cancelada no Mercado Livre.';
    header("Location: index.php?erro=" . urlencode($msg));
    exit;
}

// Monta a MESMA Redirect URI usada em ml_oauth.php
$protocol     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host         = $_SERVER['HTTP_HOST'];
$redirect_uri = "$protocol://$host/admin/ml_oauth_callback.php";

// Troca o code pelo Access Token
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
        'grant_type'    => 'authorization_code',
        'client_id'     => ML_CLIENT_ID,
        'client_secret' => ML_CLIENT_SECRET,
        'code'          => $code,
        'redirect_uri'  => $redirect_uri,
    ]),
]);
$token_res  = curl_exec($ch);
$token_data = json_decode($token_res, true);
curl_close($ch);

if (empty($token_data['access_token'])) {
    $msg = $token_data['message'] ?? $token_data['error_description'] ?? 'Falha ao obter token.';
    header("Location: index.php?erro=" . urlencode("Erro ML: $msg"));
    exit;
}

// Salva token + credenciais no ml_config.json
$config['client_id']     = ML_CLIENT_ID;
$config['client_secret'] = ML_CLIENT_SECRET;
$config['access_token']  = $token_data['access_token'];
$config['refresh_token'] = $token_data['refresh_token'] ?? '';
$config['user_id']       = (string)($token_data['user_id'] ?? '');
$config['token_expires'] = time() + (int)($token_data['expires_in'] ?? 21600);

if (file_exists($config_path)) {
    @chmod($config_path, 0666);
}

$written = @file_put_contents($config_path, json_encode($config, JSON_PRETTY_PRINT));

if ($written === false) {
    $msg = "ERRO FATAL: A Hostinger bloqueou a gravação do token. Dê permissão de escrita (CHMOD 666 ou 777) no arquivo admin/ml_config.json";
    header("Location: index.php?erro=" . urlencode($msg));
    exit;
}

header("Location: index.php?sucesso=" . urlencode("✅ Mercado Livre conectado com sucesso!"));
exit;
?>
