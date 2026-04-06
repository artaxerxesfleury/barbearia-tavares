<?php
require_once 'functions.php';
session_start();

$p_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$p = get_produto_por_id($pdo, $p_id);

if (!$p) {
    header("Location: index.php");
    exit;
}

$page_title = $p['nome'] . " | Damas Acessórios";
include 'header.php';

$imagens = array_filter(explode(',', $p['imagens_url'] ?? ''));
$imagens = array_values($imagens);
$preco_formatado = number_format($p['preco'], 2, ',', '.');

// Monta linhas da descricao_curta (atributos)
$atributos = [];
if (!empty($p['descricao_curta'])) {
    foreach (explode("\n", $p['descricao_curta']) as $linha) {
        $linha = trim($linha);
        if (strpos($linha, ':') !== false) {
            [$nome_attr, $valor_attr] = explode(':', $linha, 2);
            $atributos[trim($nome_attr)] = trim($valor_attr);
        } elseif ($linha) {
            $atributos[] = $linha;
        }
    }
}
?>

<!-- BREADCRUMB -->
<div class="max-w-7xl mx-auto px-4 md:px-8 pt-6 pb-2">
    <nav class="flex items-center gap-2 text-[10px] text-gray-400 uppercase tracking-widest font-bold">
        <a href="/" class="hover:text-[#3C9AAE] transition">Início</a>
        <span class="text-gray-200">›</span>
        <a href="/index.php" class="hover:text-[#3C9AAE] transition">Vitrine</a>
        <span class="text-gray-200">›</span>
        <span class="text-[#3C9AAE] truncate max-w-[160px] md:max-w-none"><?php echo htmlspecialchars($p['nome']); ?></span>
    </nav>
</div>

<div class="max-w-7xl mx-auto px-4 md:px-8 py-8 md:py-20">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 md:gap-20">

        <!-- ===== GALERIA DE FOTOS (LADO ESQUERDO) ===== -->
        <div class="lg:col-span-7 space-y-8">
            <!-- Imagem principal limitada para não esticar no PC -->
            <div class="relative bg-white border border-gray-100 overflow-hidden group aspect-[4/5] md:max-h-[75vh] mx-auto rounded-sm shadow-sm transition-all duration-700 hover:shadow-2xl">
                <img id="main-product-image"
                    src="<?php echo !empty($imagens[0]) ? $imagens[0] : ''; ?>"
                    alt="<?php echo htmlspecialchars($p['nome']); ?>"
                    class="w-full h-full object-contain transition-all duration-1000 group-hover:scale-105">
                
                <!-- Badge visual -->
                <div class="absolute top-6 left-6 z-10">
                    <span class="bg-white/90 backdrop-blur-md text-[#2A6B7A] text-[9px] font-black uppercase tracking-[0.3em] px-4 py-2 border border-gray-100 shadow-sm">
                        Original
                    </span>
                </div>

                <!-- Zoom hint -->
                <div class="absolute bottom-6 right-6 bg-white/90 backdrop-blur-md p-3 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-500 shadow-lg border border-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#2A6B7A]">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/>
                    </svg>
                </div>
            </div>

            <!-- Miniaturas -->
            <?php if (count($imagens) > 1): ?>
            <div class="flex gap-3 overflow-x-auto no-scrollbar pb-2">
                <?php foreach ($imagens as $i => $img): if(empty(trim($img))) continue; ?>
                <button onclick="changeImage('<?php echo htmlspecialchars(trim($img)); ?>', this)"
                    class="thumb-btn w-20 md:w-24 aspect-[3/4] flex-shrink-0 border-2 <?php echo $i === 0 ? 'border-[#3C9AAE]' : 'border-transparent'; ?> hover:border-[#3C9AAE] transition-all overflow-hidden bg-white shadow-sm rounded-sm">
                    <img src="<?php echo htmlspecialchars(trim($img)); ?>" class="w-full h-full object-cover opacity-80 hover:opacity-100 transition-opacity" loading="lazy" alt="">
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Descrição Longa (Mergulhada aqui para manter o layout compacto no PC) -->
            <?php if (!empty($p['descricao_longa'])): ?>
            <div class="pt-12 border-t border-gray-50 mt-4 overflow-hidden">
                <h3 class="text-[10px] font-black text-[#2A6B7A] uppercase tracking-[0.25em] mb-6 text-gray-400">Sobre a Peça</h3>
                <div class="text-[14px] text-gray-500 leading-relaxed font-medium max-w-prose">
                    <?php echo nl2br(htmlspecialchars($p['descricao_longa'])); ?>
                    <p class="mt-6 text-[10px] text-gray-300 italic">* Imagem meramente ilustrativa. Garantia Rommanel inclusa.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ===== INFORMAÇÕES (LADO DIREITO - STICKY NO PC) ===== -->
        <div class="lg:col-span-5 flex flex-col pt-4 lg:pt-0 lg:sticky lg:top-32 h-fit">

            <!-- Título e Preço -->
            <div class="pb-10 mb-10">
                <div class="flex items-center gap-3 mb-6">
                    <div class="h-px w-6 bg-[#3C9AAE]"></div>
                    <span class="text-[10px] font-extrabold text-[#3C9AAE] uppercase tracking-[0.4em]">Curadoria Premium</span>
                </div>
                <h1 class="font-serif text-[#2A6B7A] mb-8 leading-[1.15] font-black" style="font-size: clamp(1.8rem, 4vw, 3rem);">
                    <?php echo htmlspecialchars($p['nome']); ?>
                </h1>
                <div class="flex flex-col gap-2 mb-6">
                    <span class="text-4xl md:text-5xl font-serif font-black text-[#3C9AAE] tracking-tighter">R$ <?php echo $preco_formatado; ?></span>
                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-[0.2em]">em até 12x no Cartão</p>
                </div>
                <div class="flex items-center gap-2 p-3 bg-gray-50/50 border border-gray-100 rounded-sm w-fit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="text-green-500"><path d="m5 12 5 5L20 7"/></svg>
                    <span class="text-[9px] text-[#2A6B7A] font-black uppercase tracking-widest">Estoque Disponível</span>
                </div>
            </div>

            <!-- Características Principais (descricao_curta) -->
            <?php if (!empty($atributos)): ?>
            <div class="mb-10">
                <h3 class="text-[10px] font-black text-[#2A6B7A] uppercase tracking-[0.25em] mb-6 flex items-center gap- text-gray-400">Características</h3>
                <div class="grid grid-cols-1 gap-1">
                    <?php foreach ($atributos as $k => $v): ?>
                    <div class="flex items-center justify-between py-4 border-b border-gray-50 group hover:border-[#3C9AAE]/20 transition-colors">
                        <span class="text-[10px] font-extrabold text-[#2A6B7A] uppercase tracking-widest opacity-60">
                            <?php echo is_string($k) ? htmlspecialchars($k) : 'Detalhe'; ?>
                        </span>
                        <span class="text-xs text-[#2A6B7A] font-bold">
                            <?php echo htmlspecialchars($v); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- CTAs Desktop -->
            <div class="hidden md:flex flex-col gap-4 mt-auto pt-8">
                <button onclick='addToCart(<?php echo json_encode(["id"=>$p["id"], "nome"=>$p["nome"], "preco"=>$p["preco"], "img"=>$imagens[0] ?? ""]); ?>)'
                    class="jewel-shine w-full bg-[#3C9AAE] text-white py-6 text-[11px] font-black uppercase tracking-[0.4em] transition hover:bg-[#2A6B7A] shadow-2xl shadow-[#3C9AAE]/20 rounded-sm">
                    Adicionar à Vitrine Pessoal
                </button>
                <div class="grid grid-cols-2 gap-4">
                    <a href="https://wa.me/<?php echo $WHATSAPP_GLOBAL; ?>?text=Olá! Quero saber mais sobre: <?php echo urlencode($p['nome']); ?>" target="_blank"
                        class="w-full border border-gray-200 text-[#2A6B7A] py-5 text-center text-[9px] font-black uppercase tracking-[0.3em] hover:bg-gray-50 transition rounded-sm">
                        💬 WhatsApp
                    </a>
                    <?php if (!empty($p['link_mercadolivre'])): ?>
                    <a href="<?php echo htmlspecialchars($p['link_mercadolivre']); ?>" target="_blank"
                        class="w-full bg-[#fff159] text-[#333] py-5 text-center text-[9px] font-black uppercase tracking-[0.3em] hover:bg-[#f0e000] border border-[#f0c000] transition rounded-sm flex items-center justify-center gap-2">
                        <span>🛒</span> Mercado Livre
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA STICKY MOBILE -->
<div class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-white/95 backdrop-blur-xl border-t border-gray-100 px-4 py-3 flex gap-3 shadow-2xl">
    <button onclick='addToCart(<?php echo json_encode(["id"=>$p["id"], "nome"=>$p["nome"], "preco"=>$p["preco"], "img"=>$imagens[0] ?? ""]); ?>)'
        class="jewel-shine flex-1 bg-[#3C9AAE] text-white py-4 text-[10px] font-bold uppercase tracking-widest transition hover:bg-[#2B7A8F]">
        + Carrinho
    </button>
    <a href="https://wa.me/<?php echo $WHATSAPP_GLOBAL; ?>?text=Quero saber mais sobre: <?php echo urlencode($p['nome']); ?>" target="_blank"
        class="flex-1 border border-[#2B7A8F]/20 text-[#2B7A8F] py-4 text-center text-[10px] font-bold uppercase tracking-widest hover:bg-gray-50 transition">
        💬 WhatsApp
    </a>
</div>

<!-- Espaço extra mobile para o sticky CTA -->
<div class="h-20 md:hidden"></div>

<script>
function changeImage(src, btn) {
    document.getElementById('main-product-image').src = src;
    document.querySelectorAll('.thumb-btn').forEach(b => b.classList.replace('border-[#3C9AAE]', 'border-transparent'));
    btn.classList.replace('border-transparent', 'border-[#3C9AAE]');
}
</script>

<?php include 'footer.php'; ?>
