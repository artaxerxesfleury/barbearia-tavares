<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Damas Acessórios | Elegância em Detalhes'; ?></title>
    <meta name="description" content="Curadoria exclusiva de Semijoias Rommanel, Vestuário Feminino e Chocolates Cacau Show.">

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
    <link rel="stylesheet" href="/static/css/main.css">

    <style>
        :root {
            --brand: #3C9AAE;
            --brand-light: #5AB4C5;
            --brand-dark: #2A6B7A;
            --gold: #D4AF37;
            --bg-glass: rgba(255, 255, 255, 0.85);
            --border-glass: rgba(255, 255, 255, 0.3);
            --shadow-premium: 0 20px 40px -15px rgba(42, 107, 122, 0.15);
        }

        * { box-sizing: border-box; }

        body { font-family: 'Montserrat', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }

        /* Glass Navbar */
        .glass-nav {
            background: var(--bg-glass);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid var(--border-glass);
        }

        /* Hover underline nav */
        .hover-line::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 1.5px;
            background: var(--brand);
            transition: width 0.3s ease;
        }
        .hover-line:hover::after { width: 100%; }

        /* Card hover */
        .luxury-card {
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 4px;
        }
        .luxury-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-premium);
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-on-scroll { opacity: 0; transform: translateY(24px); transition: opacity 0.7s ease, transform 0.7s ease; }
        .animate-on-scroll.visible { opacity: 1; transform: translateY(0); }

        /* Cart badge */
        .cart-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            min-width: 17px;
            height: 17px;
            border-radius: 50%;
            font-size: 9px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 3px;
            line-height: 1;
        }

        /* Cart drawer */
        .cart-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 100;
            opacity: 0; pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .cart-overlay.open { opacity: 1; pointer-events: all; }
        .cart-drawer {
            position: fixed; top: 0; right: -100%;
            width: min(420px, 100vw);
            height: 100vh;
            background: #fff;
            z-index: 101;
            box-shadow: -12px 0 40px rgba(0,0,0,0.1);
            transition: right 0.4s cubic-bezier(0.16,1,0.3,1);
        }
        .cart-drawer.open { right: 0; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #f8fafc; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--brand); }

        /* No scrollbar (filter bar) */
        .no-scrollbar { scrollbar-width: none; -ms-overflow-style: none; }
        .no-scrollbar::-webkit-scrollbar { display: none; }

        /* Shine effect */
        .jewel-shine {
            position: relative; overflow: hidden;
        }
        .jewel-shine::after {
            content: '';
            position: absolute; top: -50%; left: -75%;
            width: 50%; height: 200%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg);
            transition: left 0.6s ease;
        }
        .jewel-shine:hover::after { left: 125%; }

        /* Hero glow text */
        .text-glow { text-shadow: 0 2px 20px rgba(0,0,0,0.3); }

        /* Diamond header overlay */
        .diamond-header::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(to bottom, rgba(43,122,143,0.5) 0%, rgba(0,0,0,0.6) 100%);
            z-index: 10;
        }

        /* Logo shape */
        .logo-shape { border-radius: 50%; }

        /* Mobile search drop */
        /* Skeleton Loading */
        .skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite linear;
        }
        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Better mobile select */
        select { -webkit-appearance: none; -moz-appearance: none; appearance: none; }
    </style>
</head>

<body class="antialiased text-gray-900 bg-white">

    <!-- NAVBAR -->
    <nav class="glass-nav border-b border-gray-100/80 sticky top-0 z-50">
        <div class="px-4 md:px-8 py-3 flex items-center justify-between gap-3">

            <!-- Logo -->
            <a href="/" class="flex items-center gap-2 hover:opacity-80 transition flex-shrink-0">
                <img src="/static/logo_damas_2026.jpg" alt="Damas Acessórios" class="h-9 md:h-11 rounded-xl">
            </a>

            <!-- Nav Desktop -->
            <div class="hidden md:flex items-center gap-10 text-[10px] font-extrabold tracking-[0.25em] uppercase text-[#2A6B7A] flex-1 justify-center">
                <a href="/#vitrine" class="nav-link px-2 py-1 relative hover-line transition-all hover:text-[#3C9AAE] opacity-80 hover:opacity-100">Início</a>
                <?php
                $nav_cats = get_categorias($pdo, null, true);
                foreach ($nav_cats as $cat): ?>
                <a href="/index.php?cat=<?php echo $cat['id']; ?>#vitrine"
                    class="nav-link px-2 py-1 relative hover-line transition-all hover:text-[#3C9AAE] opacity-80 hover:opacity-100"><?php echo htmlspecialchars($cat['nome']); ?></a>
                <?php endforeach; ?>
            </div>

            <!-- Ações -->
            <div class="flex items-center gap-1 flex-shrink-0">
                <!-- Busca Desktop -->
                <form action="/busca.php" method="GET" class="relative group hidden md:block">
                    <input type="text" name="q" placeholder="Buscar peça..."
                        class="pl-9 pr-3 py-2 bg-gray-50 border border-transparent rounded-full text-[10px] font-bold tracking-widest text-[#2B7A8F] focus:outline-none focus:border-[#3C9AAE] focus:bg-white w-36 focus:w-48 transition-all duration-300 placeholder-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5"
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-[#3C9AAE] transition-colors">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </form>

                <!-- Busca Mobile -->
                <button id="mobile-search-btn" onclick="toggleMobileSearch()"
                    class="md:hidden p-2.5 hover:bg-gray-100 rounded-full transition-all text-[#2B7A8F]" aria-label="Buscar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>

                <!-- Carrinho -->
                <button onclick="toggleCart()"
                    class="relative p-2.5 hover:bg-gray-100 rounded-full transition-all duration-200 text-[#2B7A8F]" aria-label="Carrinho">
                    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                        <path d="M3 6h18"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                    <span id="cart-count-badge" class="cart-badge bg-[#3C9AAE] text-white">0</span>
                </button>

                <!-- Hamburger Mobile -->
                <button id="hamburger-btn" onclick="toggleMobileMenu()"
                    class="md:hidden p-2.5 hover:bg-gray-100 rounded-full transition-all text-[#2B7A8F]" aria-label="Menu">
                    <svg id="hamburger-icon" xmlns="http://www.w3.org/2000/svg" width="19" height="19"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="4" x2="20" y1="6" y2="6"/>
                        <line x1="4" x2="20" y1="12" y2="12"/>
                        <line x1="4" x2="20" y1="18" y2="18"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Campo de Busca Mobile (slide down) -->
        <div id="mobile-search-bar" class="px-4 pb-0 md:hidden">
            <form action="/busca.php" method="GET" class="relative pb-3">
                <input type="text" name="q" id="mobile-search-input" placeholder="Buscar produto..."
                    class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl text-sm text-[#2B7A8F] focus:outline-none focus:border-[#3C9AAE] focus:ring-2 focus:ring-[#3C9AAE]/20 transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2"
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 mt-[-6px]">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </form>
        </div>

        <!-- Menu Mobile Dropdown -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-gray-100 bg-white/98 backdrop-blur-xl">
            <div class="px-4 py-3 flex flex-col gap-1">
                <a href="/#vitrine" onclick="toggleMobileMenu()"
                    class="px-4 py-3.5 text-[11px] font-bold tracking-[0.22em] uppercase text-[#2B7A8F] rounded-xl hover:bg-[#3C9AAE]/5 transition flex items-center gap-3">
                    <span class="w-1.5 h-1.5 bg-[#3C9AAE] rounded-full"></span> Início
                </a>
                <?php foreach ($nav_cats as $cat): ?>
                <a href="/index.php?cat=<?php echo $cat['id']; ?>#vitrine" onclick="toggleMobileMenu()"
                    class="px-4 py-3.5 text-[11px] font-bold tracking-[0.22em] uppercase text-[#2B7A8F] rounded-xl hover:bg-[#3C9AAE]/5 transition flex items-center gap-3">
                    <span class="w-1.5 h-1.5 bg-gray-200 rounded-full"></span> <?php echo $cat['nome']; ?>
                </a>
                <?php endforeach; ?>
                <div class="border-t border-gray-100 mt-2 pt-2">
                    <a href="https://wa.me/<?php echo $WHATSAPP_GLOBAL; ?>" target="_blank"
                        class="px-4 py-3.5 text-[11px] font-bold tracking-[0.22em] uppercase text-[#3C9AAE] rounded-xl hover:bg-[#3C9AAE]/5 transition flex items-center gap-3">
                        <span>💬</span> Atendimento VIP
                    </a>
                </div>
            </div>
        </div>
    </nav>
