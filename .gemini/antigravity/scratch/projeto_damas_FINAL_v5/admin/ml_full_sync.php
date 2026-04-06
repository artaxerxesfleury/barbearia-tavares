<?php
header('Content-Type: application/json; charset=utf-8');
set_time_limit(300);
require_once '../functions.php';
session_start();

if (!isset($_SESSION['logado'])) {
    die(json_encode(['error' => 'Não autorizado']));
}

try {
    // === TOKEN OAUTH (Com Refresh Automático) ===
    require_once __DIR__ . '/ml_token_manager.php';
    $access_token = get_valid_ml_token();
    
    // Ler o user_id (atualizado no get_valid_ml_token se houver o arquivo, ou lemos do arquivo novamente)
    $config_path  = __DIR__ . '/ml_config.json';
    $config       = file_exists($config_path) ? json_decode(file_get_contents($config_path), true) : [];
    $user_id      = $config['user_id'] ?? '';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer $access_token",
            'Accept: application/json',
        ],
    ]);

    // Busca user_id se necessário
    if (empty($user_id)) {
        curl_setopt($ch, CURLOPT_URL, 'https://api.mercadolibre.com/users/me');
        $me = json_decode(curl_exec($ch), true);
        $user_id = $me['id'] ?? '';
        if (empty($user_id)) throw new Exception('Token ML inválido ou expirado.');
        $config['user_id'] = $user_id;
        file_put_contents($config_path, json_encode($config, JSON_PRETTY_PRINT));
    }

    // Busca IDs dos anúncios ativos
    $todos_ids = [];
    $offset    = 0;
    $limit     = 50;

    do {
        curl_setopt($ch, CURLOPT_URL, "https://api.mercadolibre.com/users/$user_id/items/search?limit=$limit&offset=$offset&status=active");
        $res  = json_decode(curl_exec($ch), true);
        $ids  = $res['results'] ?? [];
        if (empty($ids)) break;
        $todos_ids = array_merge($todos_ids, $ids);
        $total_disponivel = $res['paging']['total'] ?? 0;
        $offset += $limit;
    } while ($offset < $total_disponivel && count($todos_ids) < 200); // Limitamos a 200 para evitar timeout em servers lentos

    if (empty($todos_ids)) {
        throw new Exception('Nenhum anúncio ativo encontrado na sua conta ML.');
    }

    $import_count = 0;
    $skip_count   = 0;
    $errors       = [];

    foreach ($todos_ids as $item_id) {
        try {
            curl_setopt($ch, CURLOPT_URL, "https://api.mercadolibre.com/items/$item_id");
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                $errors[] = "Erro cURL no item $item_id: " . curl_error($ch);
                continue;
            }

            $item_data = json_decode($response, true);
            if (empty($item_data) || isset($item_data['error'])) continue;

            // Descrição
            curl_setopt($ch, CURLOPT_URL, "https://api.mercadolibre.com/items/$item_id/description");
            $desc_response = curl_exec($ch);
            $desc_data = json_decode($desc_response, true);
            $desc_longa = trim(strip_tags($desc_data['plain_text'] ?? $desc_data['text'] ?? ''));

            // Categorias (Hierarquia Automática - Achatamento para 2 Níveis)
            $cat_root_id  = null; // Categoria Principal (Raiz)
            $cat_final_id = null; // Subcategoria (Direto da Raiz)
            $ml_cat_id    = $item_data['category_id'] ?? '';

            if ($ml_cat_id) {
                curl_setopt($ch, CURLOPT_URL, "https://api.mercadolibre.com/categories/$ml_cat_id");
                $cat_response = curl_exec($ch);
                $cat_data = json_decode($cat_response, true);
                $path     = $cat_data['path_from_root'] ?? [];
                
                if (!empty($path)) {
                    // 1. Pega a PRIMEIRA (Raiz)
                    $root_nome = trim($path[0]['name'] ?? '');
                    if ($root_nome) {
                        $st = $pdo->prepare("SELECT id FROM categorias WHERE nome = ? AND pai_id IS NULL");
                        $st->execute([$root_nome]);
                        $root_row = $st->fetch();
                        if ($root_row) {
                            $cat_root_id = $root_row['id'];
                        } else {
                            $ins = $pdo->prepare("INSERT INTO categorias (nome, pai_id) VALUES (?, NULL)");
                            $ins->execute([$root_nome]);
                            $cat_root_id = $pdo->lastInsertId();
                        }
                    }

                    // 2. Pega a ÚLTIMA (Folha - Se houver mais de um nível)
                    if (count($path) > 1) {
                        $leaf_nome = trim(end($path)['name'] ?? '');
                        if ($leaf_nome && $leaf_nome != $root_nome) {
                            $st = $pdo->prepare("SELECT id, pai_id FROM categorias WHERE nome = ?");
                            $st->execute([$leaf_nome]);
                            $leaf_row = $st->fetch();

                            if ($leaf_row) {
                                // Se já existe mas em outra hierarquia, ou sem pai, forçamos o vínculo para a Raiz
                                if ($leaf_row['pai_id'] != $cat_root_id && $leaf_row['id'] != $cat_root_id) {
                                    $pdo->prepare("UPDATE categorias SET pai_id = ? WHERE id = ?")->execute([$cat_root_id, $leaf_row['id']]);
                                }
                                $cat_final_id = $leaf_row['id'];
                            } else {
                                $ins = $pdo->prepare("INSERT INTO categorias (nome, pai_id) VALUES (?, ?)");
                                $ins->execute([$leaf_nome, $cat_root_id]);
                                $cat_final_id = $pdo->lastInsertId();
                            }
                        }
                    }

                    // Se não houver subcategoria (ou for igual), ambos usam o Root
                    if ($cat_final_id === null) $cat_final_id = $cat_root_id;
                }
            }

            // Fotos
            $fotos = [];
            foreach (array_slice($item_data['pictures'] ?? [], 0, 4) as $pic) {
                $fotos[] = preg_replace('/-[A-Z](\.(jpg|jpeg|png|webp))$/i', '-O.$1', $pic['url'] ?? '');
            }

            // Atributos
            $attrs = [];
            foreach ($item_data['attributes'] ?? [] as $at) {
                if (($at['name'] ?? null) && ($at['value_name'] ?? null)) $attrs[] = "{$at['name']}: {$at['value_name']}";
            }
            $desc_curta = implode("\n", $attrs);

            // Upsert
            preg_match('/(\d{5,})/', $item_id, $m);
            $nid = $m[1] ?? $item_id;
            
            $chk = $pdo->prepare("SELECT id FROM produtos WHERE link_mercadolivre LIKE ?");
            $chk->execute(["%$nid%"]);
            $exist = $chk->fetch();

            $sql_data = [
                $item_data['title'], (float)$item_data['price'], $cat_root_id, $cat_final_id,
                $desc_curta, $desc_longa, implode(',', $fotos), 
                $item_data['permalink'], (isset($item_data['video_id']) && $item_data['video_id'] ? "https://youtube.com/watch?v={$item_data['video_id']}" : '')
            ];

            if ($exist) {
                $sql_data[] = $exist['id'];
                $pdo->prepare("UPDATE produtos SET nome=?, preco=?, categoria_id=?, sub_categoria_id=?, descricao_curta=?, descricao_longa=?, imagens_url=?, link_mercadolivre=?, video_url=? WHERE id=?")->execute($sql_data);
                $skip_count++;
            } else {
                $pdo->prepare("INSERT INTO produtos (nome, preco, categoria_id, sub_categoria_id, descricao_curta, descricao_longa, imagens_url, link_mercadolivre, video_url, ativo) VALUES (?,?,?,?,?,?,?,?,?,1)")->execute($sql_data);
                $import_count++;
            }

        } catch (Throwable $e) {
            $errors[] = "Item $item_id: " . $e->getMessage();
        }
    }

    curl_close($ch);
    echo json_encode([
        'status'     => 'ok',
        'total_ml'   => count($todos_ids),
        'importados' => $import_count,
        'ignorados'  => $skip_count,
        'erros'      => $errors
    ]);

} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
?>
