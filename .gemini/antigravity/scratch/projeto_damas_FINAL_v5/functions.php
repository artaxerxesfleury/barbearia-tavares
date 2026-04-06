<?php
require_once __DIR__ . '/config.php';

// Inicia sessão com configurações de segurança
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure'   => isset($_SERVER['HTTPS']),
        'cookie_samesite' => 'Lax',
    ]);
}

/**
 * Gera um token CSRF e armazena na sessão
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida um token CSRF
 */
function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * Busca todas as categorias (sem depender da tabela subcategorias)
 */
function get_categorias($pdo, $pai_id = null, $apenas_com_produtos = false) {
    try {
        if ($apenas_com_produtos) {
            if ($pai_id) {
                // Subcategorias que possuem produtos vinculados (em sub_categoria_id)
                $sql = "SELECT DISTINCT c.* FROM categorias c 
                        JOIN produtos p ON p.sub_categoria_id = c.id 
                        WHERE c.pai_id = ? AND p.ativo = 1 ORDER BY c.nome ASC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$pai_id]);
            } else {
                // Categorias principais que possuem produtos vinculados (em categoria_id)
                $sql = "SELECT DISTINCT c.* FROM categorias c 
                        JOIN produtos p ON p.categoria_id = c.id 
                        WHERE c.pai_id IS NULL AND p.ativo = 1 ORDER BY c.nome ASC";
                $stmt = $pdo->query($sql);
            }
        } else {
            // Comportamento original para o Admin (mostra tudo)
            if ($pai_id) {
                $stmt = $pdo->prepare("SELECT * FROM categorias WHERE pai_id = ? ORDER BY nome ASC");
                $stmt->execute([$pai_id]);
            } else {
                $stmt = $pdo->query("SELECT * FROM categorias WHERE pai_id IS NULL ORDER BY nome ASC");
            }
        }
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Busca produtos para a VITRINE (apenas ativos, com filtros opcionais)
 */
function get_produtos_filtrados($pdo, $cat_id = null, $sub_id = null, $busca = null) {
    $sql    = "SELECT p.*, c.nome as categoria_nome FROM produtos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.ativo = 1";
    $params = [];

    if ($busca) {
        $sql   .= " AND (p.nome LIKE ? OR p.descricao_curta LIKE ? OR p.descricao_longa LIKE ?)";
        $termo  = "%$busca%";
        $params = array_merge($params, [$termo, $termo, $termo]);
    }

    if ($sub_id) {
        $sql     .= " AND p.sub_categoria_id = ?";
        $params[] = $sub_id;
    } elseif ($cat_id) {
        $sql     .= " AND p.categoria_id = ?";
        $params[] = $cat_id;
    }

    $sql .= " ORDER BY p.id DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Busca TODOS os produtos para o Admin (visíveis e ocultos)
 */
function get_todos_produtos($pdo) {
    try {
        $stmt = $pdo->query("SELECT p.*, c.nome as categoria_nome FROM produtos p LEFT JOIN categorias c ON p.categoria_id = c.id ORDER BY p.id DESC");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Atalho para vitrine (apenas ativos)
 */
function get_produtos($pdo) {
    // Vitrine deve listar apenas ativos, mas aqui parece que o código original usava get_todos_produtos.
    // Vou corrigir para filtrar ativos se for para a vitrine.
    $stmt = $pdo->query("SELECT p.*, c.nome as categoria_nome FROM produtos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.ativo = 1 ORDER BY p.id DESC");
    return $stmt->fetchAll();
}

/**
 * Busca um único produto por ID
 */
function get_produto_por_id($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Retorna a primeira imagem de uma string separada por vírgulas
 */
function get_imagem_principal($imagens_url) {
    if (!$imagens_url) return 'https://placehold.co/400x500?text=Sem+Foto';
    $imgs = explode(',', $imagens_url);
    return trim($imgs[0]);
}

/**
 * Retorna todas as imagens como array limpo
 */
function get_todas_imagens($imagens_url) {
    if (!$imagens_url) return [];
    return array_values(array_filter(array_map('trim', explode(',', $imagens_url))));
}

// Configurações Globais
$WHATSAPP_GLOBAL = $_ENV['WHATSAPP_GLOBAL'] ?? '5515996710838';
