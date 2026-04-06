<?php
require_once '../functions.php';
session_start();

$erro = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Check
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Token CSRF inválido.");
    }

    $usuario = $_POST['usuario'];
    $senha   = $_POST['senha'];

    $env_user = $_ENV['ADMIN_USER'] ?? 'admin';
    $env_pass = $_ENV['ADMIN_PASS'] ?? 'admin';

    if ($usuario === $env_user && $senha === $env_pass) {
        $_SESSION['logado'] = true;
        header("Location: index.php");
        exit;
    } else {
        $erro = "Acesso negado. Verifique as credenciais.";
    }
}
$csrf_token = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login Administrativo | Damas</title>
    <script>
        // Suprime o aviso de produção do Tailwind CSS no console
        const originalWarn = console.warn;
        console.warn = (...args) => {
            if (args[0] && typeof args[0] === 'string' && args[0].includes('cdn.tailwindcss.com')) return;
            originalWarn.apply(console, args);
        };
    </script>
    <script src="/static/tailwindcss.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Montserrat:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .jewel-shine { position: relative; overflow: hidden; }
        .jewel-shine::after { content: ''; position: absolute; top: -50%; left: -75%; width: 50%; height: 200%; background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%); transform: skewX(-25deg); transition: left 0.6s ease; }
        .jewel-shine:hover::after { left: 125%; }
    </style>
</head>
<body class="bg-[#fafafa] min-h-screen flex items-center justify-center p-6 antialiased">
    <div class="bg-white p-10 md:p-14 rounded-sm border border-gray-100 shadow-2xl w-full max-w-md animate-on-scroll fade-in visible">
        <div class="text-center mb-12">
            <h1 class="text-3xl font-serif font-black text-[#2A6B7A] italic mb-3">Painel Administrativo</h1>
            <p class="text-[10px] text-gray-300 uppercase tracking-[0.4em] font-black">Área Restrita Administrativa</p>
        </div>
        
        <?php if ($erro): ?>
            <div class="bg-red-50 text-red-300 p-4 rounded-sm border border-red-50 mb-8 text-[9px] font-black uppercase tracking-widest text-center">
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="relative group">
                <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3 group-focus-within:text-[#3C9AAE] transition-colors">Usuário</label>
                <input type="text" name="usuario" 
                    class="w-full px-5 py-4 bg-gray-50/50 border border-gray-100 rounded-sm focus:outline-none focus:border-[#3C9AAE] transition-all font-bold text-[#2A6B7A]" required>
            </div>
            
            <div class="relative group">
                <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3 group-focus-within:text-[#3C9AAE] transition-colors">Senha</label>
                <input type="password" name="senha" 
                    class="w-full px-5 py-4 bg-gray-50/50 border border-gray-100 rounded-sm focus:outline-none focus:border-[#3C9AAE] transition-all font-bold text-[#2A6B7A]" required>
            </div>
            
            <button type="submit" class="jewel-shine w-full bg-[#3C9AAE] text-white py-5 rounded-sm text-[10px] font-black uppercase tracking-[0.4em] hover:bg-[#2A6B7A] transition-all shadow-xl shadow-[#3C9AAE]/20">
                Entrar
            </button>
        </form>

        <div class="mt-12 text-center">
            <a href="../index.php" class="text-[9px] text-gray-300 font-black uppercase tracking-widest hover:text-[#3C9AAE] transition-all">
                &larr; Voltar para Boutique
            </a>
        </div>
    </div>
</body>
</html>
