<?php
require_once '../functions.php';
session_start();

if (!isset($_SESSION['logado'])) {
    header("Location: login.php");
    exit;
}

$action = $_GET['action'] ?? '';

// === PROTEÇÃO CSRF ===
// Todas as ações de admin exigem validação do token
$token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
if (!validate_csrf_token($token)) {
    die("Acesso negado: Token CSRF inválido ou expirado.");
}

// ==========================================
// CATEGORIAS
// ==========================================
if ($action === 'add_cat') {
    $nome = trim($_POST['nome'] ?? '');
    if ($nome) {
        try {
            $stmt = $pdo->prepare("INSERT INTO categorias (nome) VALUES (?)");
            $stmt->execute([$nome]);
        } catch (Exception $e) {
            // Ignora duplicata silenciosamente
        }
    }

} elseif ($action === 'del_cat') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) {
        try {
            $pdo->prepare("DELETE FROM categorias WHERE id = ?")->execute([$id]);
        } catch (Exception $e) {}
    }

// ==========================================
// PRODUTOS
// ==========================================
} elseif ($action === 'add_produto') {
    try {
        $nome       = trim($_POST['nome'] ?? '');
        $preco      = (float)str_replace(['.', ','], ['', '.'], $_POST['preco'] ?? '0');
        $cat_id     = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
        $desc_curta = trim($_POST['descricao_curta'] ?? '');
        $desc_longa = trim($_POST['descricao_longa'] ?? '');
        $link_ml    = trim($_POST['link_mercadolivre'] ?? '');
        $video_url  = trim($_POST['video_url'] ?? '');

        // Junta as 4 fotos em uma string separada por vírgula
        $fotos = [];
        for ($i = 1; $i <= 4; $i++) {
            $foto = trim($_POST["imagem_$i"] ?? '');
            if ($foto !== '') $fotos[] = $foto;
        }
        $imagens_url = implode(',', $fotos);

        if (empty($nome)) {
            throw new Exception("Nome do produto é obrigatório.");
        }

        $stmt = $pdo->prepare("
            INSERT INTO produtos 
                (nome, preco, categoria_id, descricao_curta, descricao_longa, imagens_url, link_mercadolivre, video_url, ativo)
            VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $nome,
            $preco,
            $cat_id,      // null se não selecionou categoria
            $desc_curta,
            $desc_longa,
            $imagens_url,
            $link_ml,
            $video_url,
        ]);

    } catch (Exception $e) {
        // Redireciona com mensagem de erro visível no painel
        header("Location: index.php?erro=" . urlencode("Erro ao cadastrar: " . $e->getMessage()));
        exit;
    }

} elseif ($action === 'toggle') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) {
        try {
            $pdo->prepare("UPDATE produtos SET ativo = 1 - ativo WHERE id = ?")->execute([$id]);
        } catch (Exception $e) {}
    }

} elseif ($action === 'del_produto') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) {
        try {
            $pdo->prepare("DELETE FROM produtos WHERE id = ?")->execute([$id]);
        } catch (Exception $e) {}
    }

} elseif ($action === 'bulk_del') {
    $ids = $_POST['ids'] ?? [];
    if (!empty($ids) && is_array($ids)) {
        // Sanitiza os IDs para garantir que são inteiros
        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        try {
            $stmt = $pdo->prepare("DELETE FROM produtos WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            header("Location: index.php?sucesso=" . urlencode(count($ids) . " produtos excluídos com sucesso."));
            exit;
        } catch (Exception $e) {
            header("Location: index.php?erro=" . urlencode("Erro na exclusão em massa: " . $e->getMessage()));
            exit;
        }
    }
}

// Volta para o Painel Admin
header("Location: index.php");
exit;
?>
