<?php
session_start();
if (!isset($_SESSION['logado'])) { header("Location: login.php"); exit; }

// === CREDENCIAIS FIXAS DA API ML ===
define('ML_CLIENT_ID',     '8304432912396900');
define('ML_CLIENT_SECRET', 'BNSEzl4zbEHpAQNBhgCo36kolyNZVQy5');

// Garante que o ml_config.json já tem as credenciais salvas
$config_path = __DIR__ . '/ml_config.json';
$config = file_exists($config_path) ? json_decode(file_get_contents($config_path), true) : [];
$config['client_id']     = ML_CLIENT_ID;
$config['client_secret'] = ML_CLIENT_SECRET;
file_put_contents($config_path, json_encode($config, JSON_PRETTY_PRINT));

// Monta a Redirect URI automaticamente com base no domínio atual
$protocol     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host         = $_SERVER['HTTP_HOST'];
$redirect_uri = "$protocol://$host/admin/ml_oauth_callback.php";

// URL de autorização do Mercado Livre
$auth_url = 'https://auth.mercadolivre.com.br/authorization'
    . '?response_type=code'
    . '&client_id=' . ML_CLIENT_ID
    . '&redirect_uri=' . urlencode($redirect_uri);

header("Location: $auth_url");
exit;
?>
