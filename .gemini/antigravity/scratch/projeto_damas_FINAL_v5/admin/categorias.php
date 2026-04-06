<?php
require_once '../functions.php';
session_start();

if (!isset($_SESSION['logado'])) { header("Location: login.php"); exit; }

$msg = "";

// Ação: Adicionar Categoria
if (isset($_POST['add_cat'])) {
    $nome = $_POST['nome_cat'];
    $stmt = $pdo->prepare("INSERT INTO categorias (nome) VALUES (?)");
    $stmt->execute([$nome]);
    $msg = "Categoria '$nome' adicionada!";
}

// Ação: Adicionar Subcategoria
if (isset($_POST['add_sub'])) {
    $nome = $_POST['nome_sub'];
    $cat_id = $_POST['categoria_id'];
    $stmt = $pdo->prepare("INSERT INTO subcategorias (nome, categoria_id) VALUES (?, ?)");
    $stmt->execute([$nome, $cat_id]);
    $msg = "Subcategoria '$nome' adicionada!";
}

// Ação: Excluir
if (isset($_GET['del_cat'])) {
    $id = $_GET['del_cat'];
    $pdo->prepare("DELETE FROM categorias WHERE id = ?")->execute([$id]);
    $msg = "Categoria excluída!";
}

if (isset($_GET['del_sub'])) {
    $id = $_GET['del_sub'];
    $pdo->prepare("DELETE FROM subcategorias WHERE id = ?")->execute([$id]);
    $msg = "Subcategoria excluída!";
}

$categorias = get_categorias($pdo);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Estrutura | Damas Admin</title>
    <script>
        // Suprime o aviso de produção do Tailwind CSS no console
        const originalWarn = console.warn;
        console.warn = (...args) => {
            if (args[0] && typeof args[0] === 'string' && args[0].includes('cdn.tailwindcss.com')) return;
            originalWarn.apply(console, args);
        };
    </script>
    <script src="/static/tailwindcss.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Montserrat', sans-serif; } .font-serif { font-family: 'Playfair Display', serif; }</style>
</head>
<body class="bg-gray-50 p-8 antialiased">

<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-10 border-b border-gray-100 pb-6">
        <h2 class="text-3xl font-serif text-[#2B7A8F]">Gestão de Estrutura</h2>
        <a href="index.php" class="text-xs text-gray-400 hover:text-[#3C9AAE] uppercase font-bold tracking-widest transition-all">Voltar ao Painel</a>
    </div>

    <?php if ($msg): ?><div class="bg-green-50 text-green-700 p-4 rounded-xl border border-green-100 mb-8"><?php echo $msg; ?></div><?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- CATEGORIAS -->
        <section class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-50 pb-4">Categorias Principais</h3>
            <form method="POST" class="flex gap-2 mb-8">
                <input type="text" name="nome_cat" placeholder="Nova Categoria (ex: Joias)" class="flex-1 p-3 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-[#3C9AAE] transition" required>
                <button type="submit" name="add_cat" class="bg-[#3C9AAE] text-white px-6 py-3 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-[#2B7A8F] transition">Add</button>
            </form>
            <div class="space-y-3">
                <?php foreach ($categorias as $cat): ?>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl group">
                    <span class="text-sm font-bold text-[#2B7A8F] uppercase tracking-wider"><?php echo $cat['nome']; ?></span>
                    <a href="categorias.php?del_cat=<?php echo $cat['id']; ?>" onclick="return confirm('Isso excluirá tudo vinculado a esta categoria. Confirmar?')" class="text-red-300 hover:text-red-500 transition">Excluir</a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- SUBCATEGORIAS -->
        <section class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-50 pb-4">Subcategorias</h3>
            <form method="POST" class="flex flex-col gap-3 mb-8">
                <select name="categoria_id" class="w-full p-3 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-[#3C9AAE] transition" required>
                    <option value="">Selecionar Categoria Pai...</option>
                    <?php foreach ($categorias as $cat): ?><option value="<?php echo $cat['id']; ?>"><?php echo $cat['nome']; ?></option><?php endforeach; ?>
                </select>
                <div class="flex gap-2">
                    <input type="text" name="nome_sub" placeholder="Nova Subcategoria (ex: Anéis)" class="flex-1 p-3 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-[#3C9AAE] transition" required>
                    <button type="submit" name="add_sub" class="bg-[#3C9AAE] text-white px-6 py-3 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-[#2B7A8F] transition">Add</button>
                </div>
            </form>
            <div class="max-h-[300px] overflow-y-auto pr-2 space-y-4">
                <?php foreach ($categorias as $cat): 
                $stmt = $pdo->prepare("SELECT * FROM subcategorias WHERE categoria_id = ?");
                $stmt->execute([$cat['id']]);
                $subs = $stmt->fetchAll();
                if ($subs): ?>
                    <div class="mb-4">
                        <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest mb-2 block"><?php echo $cat['nome']; ?></span>
                        <?php foreach ($subs as $sub): ?>
                        <div class="flex items-center justify-between p-3 border border-gray-50 rounded-xl mb-2">
                            <span class="text-xs font-medium text-gray-500"><?php echo $sub['nome']; ?></span>
                            <a href="categorias.php?del_sub=<?php echo $sub['id']; ?>" class="text-red-200 hover:text-red-400 transition">Excluir</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; endforeach; ?>
            </div>
        </section>
    </div>
</div>

</body>
</html>
