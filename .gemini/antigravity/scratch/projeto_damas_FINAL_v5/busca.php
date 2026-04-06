<?php
require_once 'functions.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$categorias_globais = get_categorias($pdo);
$produtos = get_produtos($pdo, null, null, $q);

$page_title = "Resultados para: " . htmlspecialchars($q);
include 'header.php';
?>

<div class="bg-white pt-24 min-h-screen">
    <main class="max-w-7xl mx-auto px-4 md:px-6 py-12 md:py-24 relative z-10">
        <div class="flex flex-col md:flex-row justify-between items-end mb-8 md:mb-16 gap-4 md:gap-8">
            <div class="animate-on-scroll fade-in">
                <h2 class="text-3xl md:text-5xl font-serif text-[#2B7A8F] mb-4 italic">
                    Busca: "<span class="text-[#3C9AAE]"><?php echo htmlspecialchars($q); ?></span>"
                </h2>
                <div class="h-1 w-24 bg-[#3C9AAE]"></div>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-x-4 md:gap-x-8 gap-y-10 md:gap-y-16">
            <?php if ($produtos): ?>
                <?php foreach ($produtos as $p): ?>
                <div class="group animate-on-scroll fade-in flex flex-col">
                    <a href="detalhes.php?id=<?php echo $p['id']; ?>" class="block relative aspect-[3/4] overflow-hidden bg-gray-50 mb-6 border border-gray-100 transition-all duration-700 group-hover:border-[#3C9AAE]/30">
                        <?php 
                        $img = get_imagem_principal($p['imagens_url']);
                        if ($img): ?>
                        <img src="<?php echo $img; ?>" alt="<?php echo $p['nome']; ?>" class="w-full h-full object-contain transition-transform duration-1000 group-hover:scale-110 opacity-90 group-hover:opacity-100">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center bg-gray-100"><span class="text-gray-300 text-xs uppercase tracking-widest">Sem Imagem</span></div>
                        <?php endif; ?>
                    </a>
                    <div class="text-center">
                        <h4 class="text-[#2B7A8F] text-xs font-bold uppercase tracking-[0.2em] mb-3 group-hover:text-[#3C9AAE] transition-colors duration-300"><?php echo $p['nome']; ?></h4>
                        <p class="text-[#3C9AAE] font-serif font-bold text-lg mb-6 tracking-tight">R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?></p>
                        <button 
                            data-produto='<?php echo json_encode(["id" => $p['id'], "nome" => $p['nome'], "preco" => $p['preco'], "img" => $img, "ml" => $p['link_mercadolivre']]); ?>'
                            onclick='addToCart(JSON.parse(this.getAttribute("data-produto")))'
                            class="jewel-shine w-full border border-gray-100 text-gray-400 py-4 text-[9px] uppercase tracking-[0.3em] font-bold hover:bg-[#3C9AAE] hover:text-white hover:border-[#3C9AAE] transition-all duration-500">
                            Adicionar ao Carrinho
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-24"><p class="text-gray-300 text-sm font-light uppercase tracking-widest">Nenhum resultado para "<?php echo htmlspecialchars($q); ?>"</p></div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>
