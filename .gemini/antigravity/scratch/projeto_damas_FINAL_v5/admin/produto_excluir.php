<?php
require_once '../functions.php';
session_start();

if (!isset($_SESSION['logado'])) { header("Location: login.php"); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['msg'] = "Produto excluído com sucesso!";
    } catch (Exception $e) {
        $_SESSION['msg'] = "Erro ao excluir: " . $e->getMessage();
    }
}

header("Location: index.php");
exit;
?>
