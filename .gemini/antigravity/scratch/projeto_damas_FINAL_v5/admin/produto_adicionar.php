<?php
require_once '../functions.php';
session_start();

if (!isset($_SESSION['logado'])) { header("Location: login.php"); exit; }

$categorias = get_categorias($pdo);
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Proteção CSRF
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Acesso negado: Token CSRF inválido.");
    }

    $nome = trim($_POST['nome']);
    $preco = (float)str_replace(['.', ','], ['', '.'], $_POST['preco'] ?? '0');
    $cat_id = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
    $desc_c = trim($_POST['descricao_curta'] ?? '');
    $desc_l = trim($_POST['descricao_longa'] ?? '');
    $link_ml = trim($_POST['link_mercadolivre'] ?? '');
    $video = trim($_POST['video_url'] ?? '');

    $imgs = [];
    for ($i=1; $i<=4; $i++) {
        $val = trim($_POST["imagem_$i"] ?? '');
        if ($val) $imgs[] = $val;
    }
    $imagens_url = implode(',', $imgs);

    try {
        $stmt = $pdo->prepare("INSERT INTO produtos (nome, preco, categoria_id, descricao_curta, descricao_longa, imagens_url, link_mercadolivre, video_url, ativo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$nome, $preco, $cat_id, $desc_c, $desc_l, $imagens_url, $link_ml, $video]);
        $_SESSION['msg'] = "Produto cadastrado com sucesso!";
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        $msg = "Erro ao cadastrar: " . $e->getMessage();
    }
}

$csrf_token = get_csrf_token();
$page_title = "Cadastrar Produto | Damas";
include '../header.php';
?>
    <div class="max-w-7xl mx-auto py-12 px-6">
        <section class="bg-white p-8 md:p-12 rounded-sm border border-gray-100 shadow-sm mb-16 animate-on-scroll fade-in">
            <div class="flex items-center justify-between mb-12 pb-6 border-b border-gray-50">
                <div class="flex items-center gap-6">
                    <div class="w-12 h-12 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center text-[#3C9AAE]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14" /><path d="M12 5v14" /></svg>
                    </div>
                    <div>
                        <h2 class="text-3xl font-serif text-[#2A6B7A] font-black italic">Nova Peça</h2>
                        <p class="text-[10px] text-gray-300 uppercase tracking-[0.3em] font-black mt-1">Cadastrar no catálogo</p>
                    </div>
                </div>
                <a href="index.php" class="text-[10px] text-gray-300 hover:text-[#3C9AAE] uppercase font-black tracking-widest transition-all">Voltar</a>
            </div>

            <?php if ($msg): ?>
                <div class="bg-red-50 text-red-400 p-5 rounded-sm border border-red-100 mb-10 text-[10px] font-black uppercase tracking-widest">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="space-y-8">
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3">Nome da Peça</label>
                        <input type="text" name="nome" placeholder="Ex: Anel Solitário Brilhante" class="w-full p-5 bg-gray-50/50 border border-gray-100 rounded-sm focus:outline-none focus:border-[#3C9AAE] transition font-bold text-[#2A6B7A]" required>
                    </div>
                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3">Categoria</label>
                            <select name="categoria_id" class="w-full p-5 bg-gray-50/50 border border-gray-100 rounded-sm focus:outline-none focus:border-[#3C9AAE] transition font-bold text-[#2A6B7A]">
                                <option value="">Selecionar...</option>
                                <?php foreach($categorias as $cat): ?><option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nome']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3">Preço (R$)</label>
                            <input type="text" name="preco" placeholder="129,90" class="w-full p-5 bg-gray-50/50 border border-gray-100 rounded-sm focus:outline-none focus:border-[#3C9AAE] transition font-black text-[#3C9AAE]" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3">Descrição Curta</label>
                        <textarea name="descricao_curta" placeholder="Breve resumo para atrair o cliente..." class="w-full p-5 bg-gray-50/50 border border-gray-100 rounded-sm h-32 focus:outline-none focus:border-[#3C9AAE] transition font-medium text-gray-500 text-sm leading-relaxed"></textarea>
                    </div>
                </div>

                <div class="space-y-8">
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3">Descrição Detalhada</label>
                        <textarea name="descricao_longa" placeholder="Materiais, tamanhos, cuidados..." class="w-full p-5 bg-gray-50/50 border border-gray-100 rounded-sm h-32 focus:outline-none focus:border-[#3C9AAE] transition font-medium text-gray-500 text-sm leading-relaxed"></textarea>
                    </div>
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3">Galeria de Imagens (URLs)</label>
                        <div class="grid grid-cols-2 gap-4">
                            <?php for($i=1; $i<=4; $i++): ?>
                            <input type="text" name="imagem_<?php echo $i; ?>" placeholder="Foto <?php echo $i; ?>" class="w-full p-4 bg-gray-50/50 border border-gray-100 rounded-sm text-[10px] focus:outline-none focus:border-[#3C9AAE] transition font-medium">
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[9px] font-black text-[#3C9AAE] uppercase tracking-[0.3em] mb-3">Link Mercado Livre</label>
                            <input type="text" id="link_ml_input" name="link_mercadolivre" placeholder="Cole o link aqui..." class="w-full p-4 bg-[#3C9AAE]/5 border border-[#3C9AAE]/20 rounded-sm text-[10px] focus:outline-none focus:border-[#3C9AAE] transition font-bold text-[#2A6B7A]">
                        </div>
                        <div>
                            <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3">Vídeo (URL)</label>
                            <input type="text" name="video_url" placeholder="Link do YouTube" class="w-full p-4 bg-gray-50/50 border border-gray-100 rounded-sm text-[10px] focus:outline-none focus:border-[#3C9AAE] transition font-medium">
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 pt-8">
                    <button type="submit" class="jewel-shine w-full bg-[#3C9AAE] text-white py-6 text-[11px] font-black uppercase tracking-[0.5em] transition hover:bg-[#2A6B7A] shadow-2xl shadow-[#3C9AAE]/20 rounded-sm">
                        Cadastrar Produto
                    </button>
                    <p class="text-center text-[8px] text-gray-300 mt-6 uppercase tracking-[0.3em] font-bold">O produto será exibido imediatamente na vitrine.</p>
                </div>
            </form>
        </section>
    </div>

    <script>
    const linkInput = document.getElementById('link_ml_input');
    if (linkInput) {
        linkInput.addEventListener('input', function() {
            const url = this.value.trim();
            if (url.includes('MLB')) {
                this.classList.add('bg-blue-50', 'animate-pulse');
                fetch('api_ml_info.php?url=' + encodeURIComponent(url))
                    .then(res => res.json())
                    .then(data => {
                        this.classList.remove('animate-pulse');
                        if (data.nome) {
                            document.querySelector('input[name="nome"]').value = data.nome;
                            document.querySelector('input[name="preco"]').value = data.preco;
                            if (data.video_url) document.querySelector('input[name="video_url"]').value = data.video_url;
                            if (data.imagens) {
                                data.imagens.forEach((img, i) => {
                                    const inp = document.querySelector(`input[name="imagem_${i + 1}"]`);
                                    if (inp) inp.value = img;
                                });
                            }
                        }
                    })
                    .catch(() => this.classList.remove('animate-pulse'));
            }
        });
    }
    </script>
<?php include '../footer.php'; ?>
