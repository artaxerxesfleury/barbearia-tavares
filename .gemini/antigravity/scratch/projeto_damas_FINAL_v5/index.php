<?php
require_once 'functions.php';
session_start();

$page_title = "Damas Acessórios | Elegância em Detalhes";
include 'header.php';

$cat_id      = isset($_GET['cat']) ? (int)$_GET['cat'] : null;
$sub_id      = isset($_GET['sub']) ? (int)$_GET['sub'] : null;
$termo_busca = isset($_GET['q'])   ? $_GET['q']         : null;

$categorias = get_categorias($pdo, null, true);

if ($cat_id || $sub_id || $termo_busca) {
    $produtos    = get_produtos_filtrados($pdo, $cat_id, $sub_id, $termo_busca);
    $vitrine_home = null;
} else {
    $produtos     = null;
    $vitrine_home = [];
    foreach ($categorias as $cat) {
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE categoria_id = ? AND ativo = 1 LIMIT 4");
        $stmt->execute([$cat['id']]);
        $prod_cat = $stmt->fetchAll();
        if ($prod_cat) $vitrine_home[$cat['nome']] = $prod_cat;
    }
}

function render_card_produto($p) {
    $img = get_imagem_principal($p['imagens_url']);
    $preco_formatado = number_format($p['preco'], 2, ',', '.');
    $p_json = htmlspecialchars(json_encode([
        "id"    => $p['id'],
        "nome"  => $p['nome'],
        "preco" => $p['preco'],
        "img"   => $img,
        "ml"    => $p['link_mercadolivre'] ?? ""
    ]), ENT_QUOTES, 'UTF-8');
    ?>
    <div class="luxury-card group animate-on-scroll fade-in flex flex-col bg-white">
        <a href="detalhes.php?id=<?php echo $p['id']; ?>"
            class="block relative overflow-hidden bg-gray-50 border border-gray-100/50 transition-all duration-700 group-hover:border-[#3C9AAE]/40"
            style="aspect-ratio:3/4;">
            <?php if ($img): ?>
            <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($p['nome']); ?>"
                class="w-full h-full object-contain transition-transform duration-1000 group-hover:scale-110 opacity-90 group-hover:opacity-100" loading="lazy">
            <?php else: ?>
            <div class="w-full h-full flex items-center justify-center bg-gray-50">
                <span class="text-gray-300 text-[9px] uppercase tracking-widest font-black">Indisponível</span>
            </div>
            <?php endif; ?>
            <!-- Selection indicator -->
            <div class="absolute top-4 right-4 translate-x-2 opacity-0 group-hover:translate-x-0 group-hover:opacity-100 transition-all duration-500">
                <div class="bg-white/90 backdrop-blur-md p-2 shadow-sm border border-gray-100 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="text-[#3C9AAE]"><path d="m5 12 5 5L20 7"/></svg>
                </div>
            </div>
            <!-- Quick View -->
            <div class="absolute inset-x-0 bottom-0 translate-y-full group-hover:translate-y-0 transition-transform duration-500 bg-white/90 backdrop-blur-md py-3 text-center border-t border-gray-100">
                <span class="text-[#2A6B7A] text-[9px] font-black uppercase tracking-[0.25em]">Ver Detalhes</span>
            </div>
        </a>
        <div class="text-center pt-5 pb-2 px-1 flex-1 flex flex-col justify-between gap-3">
            <h4 class="text-[#2A6B7A] text-[11px] md:text-[12px] font-extrabold uppercase tracking-[0.2em] leading-relaxed group-hover:text-[#3C9AAE] transition-colors duration-500 px-2 min-h-[40px] flex items-center justify-center">
                <?php echo htmlspecialchars($p['nome']); ?>
            </h4>
            <div>
                <p class="text-[#3C9AAE] font-serif font-black text-lg md:text-xl tracking-tight mb-3">
                    R$ <?php echo $preco_formatado; ?>
                </p>
                <button data-produto='<?php echo $p_json; ?>'
                    onclick='addToCart(JSON.parse(this.getAttribute("data-produto")))'
                    class="jewel-shine w-full border border-gray-100 bg-gray-50/50 text-[#2A6B7A] py-3.5 text-[9px] font-extrabold uppercase tracking-[0.3em] hover:bg-[#3C9AAE] hover:text-white hover:border-[#3C9AAE] transition-all duration-500">
                    + Carrinho
                </button>
            </div>
        </div>
    </div>
    <?php
}
?>

<!-- HERO -->
<header class="relative overflow-hidden flex items-center justify-center diamond-header bg-[#0a2025]"
    style="min-height: clamp(70vh, 85vh, 100vh);">
    <div class="absolute inset-0 z-0 bg-cover bg-center opacity-60 scale-105 transition-transform duration-[10s] hover:scale-100" style="background-image: url('/static/hero_bg.png');"></div>
    <div class="relative z-20 text-center px-6 max-w-5xl mx-auto animate-on-scroll fade-in py-20 md:py-0">
        <div class="inline-flex items-center gap-3 mb-8">
            <div class="h-px w-8 bg-[#3C9AAE]"></div>
            <span class="text-white/90 font-extrabold tracking-[0.5em] text-[10px] uppercase text-glow">Curadoria de Luxo</span>
            <div class="h-px w-8 bg-[#3C9AAE]"></div>
        </div>
        <h1 class="font-serif text-white font-black tracking-tight text-glow leading-[0.95] mb-8 md:mb-10"
            style="font-size: clamp(3rem, 10vw, 7.5rem);">
            Elegância em<br><span class="text-[#3C9AAE] italic font-light">Detalhes.</span>
        </h1>
        <p class="text-white/80 uppercase tracking-[0.5em] mb-12 font-medium leading-relaxed text-glow max-w-2xl mx-auto"
            style="font-size: clamp(10px, 1.4vw, 12px);">
            Semijoias Rommanel • Moda Feminina • Cacau Show
        </p>
        <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
            <a href="#vitrine"
                class="jewel-shine bg-[#3C9AAE] text-white px-12 py-5 text-[10px] uppercase tracking-[0.35em] font-black transition-all hover:bg-[#2A6B7A] shadow-2xl shadow-[#3C9AAE]/40 w-full sm:w-auto text-center rounded-sm">
                Explorar Coleções
            </a>
            <a href="https://wa.me/<?php echo $WHATSAPP_GLOBAL; ?>" target="_blank"
                class="px-12 py-5 text-[10px] border border-white/20 text-white uppercase tracking-[0.35em] font-black hover:bg-white hover:text-[#2A6B7A] transition-all backdrop-blur-xl w-full sm:w-auto text-center rounded-sm group">
                Atendimento VIP <span class="inline-block transition-transform group-hover:translate-x-1">→</span>
            </a>
        </div>
    </div>
    <!-- Simple scroll indicator -->
    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 z-20 animate-bounce opacity-40">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="m7 13 5 5 5-5M7 6l5 5 5-5"/></svg>
    </div>
</header>

<!-- FILTROS POR CATEGORIA -->
<nav class="bg-white/95 backdrop-blur-2xl border-b border-gray-100 sticky top-[56px] md:top-[64px] z-40 overflow-x-auto no-scrollbar py-1">
    <div class="px-4 md:px-8 py-4 flex items-center gap-3 min-w-max mx-auto justify-start md:justify-center">
        <a href="index.php#vitrine"
            class="px-6 py-2.5 text-[10px] font-black uppercase tracking-[0.2em] transition-all rounded-full border-2 whitespace-nowrap
                <?php echo !$cat_id ? 'bg-[#3C9AAE] text-white border-[#3C9AAE] shadow-xl shadow-[#3C9AAE]/20' : 'text-gray-400 border-gray-100 hover:border-[#3C9AAE]/30 hover:text-[#3C9AAE] hover:bg-gray-50'; ?>">
            Todos
        </a>
        <?php foreach ($categorias as $cat): ?>
        <a href="index.php?cat=<?php echo $cat['id']; ?>#vitrine"
            class="px-6 py-2.5 text-[10px] font-black uppercase tracking-[0.2em] transition-all rounded-full border-2 whitespace-nowrap
                <?php echo $cat_id == $cat['id'] ? 'bg-[#3C9AAE] text-white border-[#3C9AAE] shadow-xl shadow-[#3C9AAE]/20' : 'text-gray-400 border-gray-100 hover:border-[#3C9AAE]/30 hover:text-[#3C9AAE] hover:bg-gray-50'; ?>">
            <?php echo htmlspecialchars($cat['nome']); ?>
        </a>
        <?php endforeach; ?>
    </div>
</nav>

<!-- FILTROS DE SUBCATEGORIA (QUANDO CATEGORIA PAI SELECIONADA) -->
<?php 
if ($cat_id): 
    $subcategorias = get_categorias($pdo, $cat_id, true);
    if ($subcategorias):
?>
<nav class="bg-gray-50/50 border-b border-gray-100 overflow-x-auto no-scrollbar animate-on-scroll fade-in">
    <div class="px-4 md:px-6 py-2.5 flex items-center gap-2 min-w-max mx-auto justify-center">
        <span class="text-[8px] font-bold text-gray-300 uppercase tracking-widest mr-2 ml-4">Filtrar por:</span>
        <a href="index.php?cat=<?php echo $cat_id; ?>#vitrine"
            class="px-3 py-1.5 text-[8px] font-bold uppercase tracking-widest transition-all rounded-lg
                <?php echo !$sub_id ? 'bg-white text-[#3C9AAE] shadow-sm border border-gray-100' : 'text-gray-400 hover:text-[#3C9AAE]'; ?>">
            Todas
        </a>
        <?php foreach ($subcategorias as $sub): ?>
        <a href="index.php?cat=<?php echo $cat_id; ?>&sub=<?php echo $sub['id']; ?>#vitrine"
            class="px-3 py-1.5 text-[8px] font-bold uppercase tracking-widest transition-all rounded-lg
                <?php echo $sub_id == $sub['id'] ? 'bg-white text-[#3C9AAE] shadow-sm border border-gray-100' : 'text-gray-400 hover:text-[#3C9AAE]'; ?>">
            <?php echo htmlspecialchars($sub['nome']); ?>
        </a>
        <?php endforeach; ?>
    </div>
</nav>
<?php 
    endif;
endif; 
?>

<!-- VITRINE -->
<div id="vitrine" class="bg-white">
    <main class="max-w-7xl mx-auto px-4 md:px-8 py-10 md:py-20">

        <!-- Título da Seção -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 md:mb-16 gap-4 animate-on-scroll fade-in">
            <div>
                <h2 class="text-3xl md:text-5xl font-serif text-[#2B7A8F] mb-3 italic">
                    <?php if ($termo_busca):
                        echo "Resultados para \"<span class='text-[#3C9AAE]'>$termo_busca</span>\"";
                    else:
                        echo "Nossa Vitrine";
                    endif; ?>
                </h2>
                <div class="h-0.5 w-16 bg-[#3C9AAE]"></div>
            </div>
            <?php if ($cat_id): ?>
            <a href="index.php#vitrine" class="text-[10px] text-gray-400 uppercase tracking-widest hover:text-[#3C9AAE] transition font-bold flex items-center gap-1">
                ← Ver tudo
            </a>
            <?php endif; ?>
        </div>

        <!-- Grid de produtos: Redimensionado para 1 coluna no mobile para peças saltarem aos olhos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 md:gap-x-10 gap-y-12 md:gap-y-20 px-2 md:px-0">
            <?php if ($vitrine_home): ?>
                <?php foreach ($vitrine_home as $cat_nome => $items): ?>
                    <!-- Separador de Categoria -->
                    <div class="col-span-full mt-4 md:mt-10 mb-2 flex items-center gap-4">
                        <h3 class="text-[11px] md:text-xs font-bold text-[#2B7A8F]/40 uppercase tracking-[0.4em] whitespace-nowrap"><?php echo htmlspecialchars($cat_nome); ?></h3>
                        <div class="flex-1 h-px bg-gray-100"></div>
                    </div>
                    <?php foreach ($items as $p) { render_card_produto($p); } ?>
                <?php endforeach; ?>
            <?php elseif ($produtos): ?>
                <?php foreach ($produtos as $p) { render_card_produto($p); } ?>
            <?php else: ?>
                <div class="col-span-full text-center py-20">
                    <p class="text-gray-300 text-sm font-light uppercase tracking-widest">Nenhum produto encontrado.</p>
                    <a href="index.php" class="inline-block mt-6 text-[10px] text-[#3C9AAE] uppercase tracking-widest font-bold hover:underline">← Voltar à vitrine</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- SOBRE NÓS -->
<section class="py-16 md:py-24 border-t border-gray-100 bg-gray-50/50">
    <div class="max-w-5xl mx-auto px-6 grid grid-cols-1 md:grid-cols-2 gap-12 md:gap-20 items-center">
        <div class="order-2 md:order-1 flex justify-center animate-on-scroll fade-in">
            <div class="w-64 h-64 md:w-80 md:h-80 rounded-full border-[8px] border-white shadow-2xl relative overflow-hidden bg-white">
                <div class="absolute inset-0 bg-no-repeat bg-center transition-transform duration-700 hover:scale-105"
                    style="background-image: url('/static/juliana_circulo.jpg'); background-size: cover;">
                </div>
            </div>
        </div>
        <div class="order-1 md:order-2 animate-on-scroll fade-in text-center md:text-left">
            <span class="text-[#3C9AAE] font-bold tracking-[0.2em] text-[10px] uppercase mb-3 block">Fundadora</span>
            <h2 class="text-3xl md:text-4xl font-serif font-bold text-[#2B7A8F] mb-6 leading-tight">
                Sobre<br><span class="italic font-light">Juliana.</span>
            </h2>
            <div class="space-y-4 text-gray-500 text-sm font-light leading-relaxed">
                <p><strong>Juliana Damas</strong> atua há 8 anos como parceira oficial <strong>Rommanel</strong>.</p>
                <p>Sua curadoria é focada em peças que unem durabilidade e sofisticação para cada ocasião.</p>
            </div>
            <a href="https://wa.me/<?php echo $WHATSAPP_GLOBAL; ?>" target="_blank"
                class="inline-flex items-center gap-2 mt-8 px-6 py-3 border border-[#3C9AAE]/30 text-[#3C9AAE] text-[10px] uppercase tracking-widest font-bold hover:bg-[#3C9AAE] hover:text-white transition-all rounded-sm">
                💬 Falar com Juliana
            </a>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
