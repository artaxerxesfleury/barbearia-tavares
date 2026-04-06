<?php
// Força a exibição de erros para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../functions.php';
session_start();

if (!isset($_SESSION['logado'])) { header("Location: login.php"); exit; }

// Configurações da sua API
$ml_config = [
    'client_id' => '8304432912396900',
    'client_secret' => 'BNSEzl4zbEHpAQNBhgCo36kolyNZVQy5',
    'seller_id' => '1148466601'
];

if (isset($_GET['action']) && $_GET['action'] == 'sync_all') {
    try {
        // Verifica se o PDO (conexão com o banco) existe
        if (!isset($pdo)) {
            die("Erro crítico: A conexão com o banco de dados ($pdo) não foi carregada. Verifique o config.php");
        }

        $stmt = $pdo->query("SELECT id, nome, link_mercadolivre FROM produtos WHERE link_mercadolivre LIKE '%MLB%'");
        $produtos = $stmt->fetchAll();
        $sucesso = 0;

        foreach ($produtos as $p) {
            preg_match('/MLB[-]?(\d+)/i', $p['link_mercadolivre'], $matches);
            if ($matches) {
                $ml_id = "MLB" . $matches[1];
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.mercadolibre.com/items/" . $ml_id);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout de 10 segundos
                $response = curl_exec($ch);
                
                if(curl_errno($ch)) {
                    die("Erro no cURL capturado: " . curl_error($ch));
                }
                curl_close($ch);
                
                $data = json_decode($response, true);
                
                if ($data && isset($data['price'])) {
                    $novo_preco = $data['price'];
                    $nome = $data['title'];
                    
                    $imgs = [];
                    if (isset($data['pictures'])) {
                        foreach ($data['pictures'] as $pic) { $imgs[] = $pic['secure_url']; }
                    }
                    $imagens_url = implode(',', $imgs);

                    $upd = $pdo->prepare("UPDATE produtos SET preco = ?, nome = ?, imagens_url = ? WHERE id = ?");
                    $upd->execute([$novo_preco, $nome, $imagens_url, $p['id']]);
                    $sucesso++;
                }
            }
        }
        $_SESSION['msg'] = "Sincronização concluída: $sucesso produtos atualizados!";
        header("Location: index.php");
        exit;
        
    } catch (Exception $e) {
        die("Erro durante a sincronização: " . $e->getMessage());
    }
} else {
    // Se acessar sem a ação, apenas redireciona
    header("Location: index.php");
    exit;
}
?>
