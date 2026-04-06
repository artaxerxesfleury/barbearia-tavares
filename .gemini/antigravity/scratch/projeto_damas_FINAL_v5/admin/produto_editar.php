<?php
require_once '../functions.php';
session_start();

if (!isset($_SESSION['logado'])) { header("Location: login.php"); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pr = get_produto_por_id($pdo, $id);
if (!$pr) { header("Location: index.php"); exit; }

$categorias = get_categorias($pdo);
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // === PROTEÇÃO CSRF ===
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Acesso negado: Token CSRF inválido.");
    }

    $nome = trim($_POST['nome']);
    $preco = (float)str_replace(['.', ','], ['', '.'], $_POST['preco'] ?? '0');
    $cat_id = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
    $desc_c = trim($_POST['descricao'] ?? '');
    $desc_l = trim($_POST['descricao_longa'] ?? '');
    $link_ml = trim($_POST['link_mercadolivre'] ?? '');
    $video = trim($_POST['video_url'] ?? '');
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    $imgs = [];
    for ($i=1; $i<=4; $i++) {
        $val = trim($_POST["imagem_$i"] ?? '');
        if ($val) $imgs[] = $val;
    }
    $imagens_url = implode(',', $imgs);

    try {
        $stmt = $pdo->prepare("UPDATE produtos SET nome = ?, preco = ?, categoria_id = ?, descricao_curta = ?, descricao_longa = ?, imagens_url = ?, link_mercadolivre = ?, video_url = ?, ativo = ? WHERE id = ?");
        $stmt->execute([$nome, $preco, $cat_id, $desc_c, $desc_l, $imagens_url, $link_ml, $video, $ativo, $id]);
        $_SESSION['msg'] = "Produto atualizado com sucesso!";
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        $msg = "Erro ao atualizar: " . $e->getMessage();
    }
}

// Extrai imagens individuais para o form
$img_array = explode(',', $pr['imagens_url']);
$csrf_token = get_csrf_token();

$page_title = "Editar | " . htmlspecialchars($pr['nome']);
include '../header.php';
?>
    <div class="max-w-7xl mx-auto py-12 px-6">
        <section class="bg-white p-8 md:p-12 rounded-sm border border-gray-100 shadow-sm mb-16 animate-on-scroll fade-in">
            <div class="flex items-center justify-between mb-12 pb-6 border-b border-gray-50">
                <div class="flex items-center gap-6">
                    <div class="w-12 h-12 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center text-[#3C9AAE]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" /><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" /></svg>
                    </div>
                    <div>
                        <h2 class="text-3xl font-serif text-[#2A6B7A] font-black italic">Editar Peça</h2>
                        <p class="text-[10px] text-gray-300 uppercase tracking-[0.3em] font-black mt-1">Refinando o catálogo</p>
                    </div>
                </div>
                <a href="index.php" class="text-[10px] text-gray-300 hover:text-[#3C9AAE] uppercase font-black tracking-widest transition-all">Descartar</a>
            </div>

            <?php if ($msg): ?>
                <div class="bg-red-50 text-red-400 p-5 rounded-sm border border-red-100 mb-10 text-[10px] font-black uppercase tracking-widest">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="space-y-8">
                    <div class="relative group">
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3">Identificação da Peça</label>
                        <input type="text" name="nome" value="<?php echo htmlspecialchars($pr['nome']); ?>" 
                            class="w-full p-5 bg-gray-50/50 border border-gray-100 rounded-sm focus:outline-none focus:border-[#3C9AAE] transition font-bold text-[#2A6B7A]" required>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3">Categoria Luxo</label>
                            <select name="categoria_id" class="w-full p-5 bg-gray-50/50 border border-gray-100 rounded-sm focus:outline-none focus:border-[#3C9AAE] transition font-bold text-[#2A6B7A]">
                                <option value="">Indefinida</option>
                                <?php foreach($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id']==$pr['categoria_id']?'selected':''); ?>>
                                        <?php echo htmlspecialchars($cat['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3">Investimento (R$)</label>
                            <input type="text" name="preco" value="<?php echo number_format($pr['preco'], 2, ',', '.'); ?>" 
                                class="w-full p-5 bg-gray-50/50 border border-gray-100 rounded-sm focus:outline-none focus:border-[#3C9AAE] transition font-black text-[#3C9AAE]" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3">Essência (Descrição Curta)</label>
                        <textarea name="descricao" class="w-full p-5 bg-gray-50/50 border border-gray-100 rounded-sm h-32 focus:outline-none focus:border-[#3C9AAE] transition font-medium text-gray-500 text-sm leading-relaxed"><?php echo htmlspecialchars($pr['descricao_curta']); ?></textarea>
                    </div>
                </div>

                <div class="space-y-8">
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3">História e Detalhes</label>
                        <textarea name="descricao_longa" class="w-full p-5 bg-gray-50/50 border border-gray-100 rounded-sm h-32 focus:outline-none focus:border-[#3C9AAE] transition font-medium text-gray-500 text-sm leading-relaxed"><?php echo htmlspecialchars($pr['descricao_longa']); ?></textarea>
                    </div>

                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3">Galeria de Imagens (URLs)</label>
                        <div class="grid grid-cols-2 gap-4">
                            <?php for($i=1; $i<=4; $i++): ?>
                            <input type="text" name="imagem_<?php echo $i; ?>" value="<?php echo htmlspecialchars($img_array[$i-1] ?? ''); ?>" 
                                placeholder="Foto <?php echo $i; ?>"
                                class="w-full p-4 bg-gray-50/50 border border-gray-100 rounded-sm text-[10px] focus:outline-none focus:border-[#3C9AAE] transition font-medium">
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="flex items-center gap-8">
                        <div class="flex-1">
                            <label class="block text-[9px] font-black text-[#3C9AAE] uppercase tracking-[0.3em] mb-3">Mercado Livre URL</label>
                            <input type="text" name="link_mercadolivre" value="<?php echo htmlspecialchars($pr['link_mercadolivre']); ?>" 
                                class="w-full p-4 bg-[#3C9AAE]/5 border border-[#3C9AAE]/20 rounded-sm text-[10px] focus:outline-none focus:border-[#3C9AAE] transition font-bold text-[#2A6B7A]">
                        </div>
                        <div class="flex items-center gap-3 pt-6">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="ativo" value="1" class="sr-only peer" <?php echo ($pr['ativo']?'checked':''); ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#3C9AAE]"></div>
                                <span class="ml-3 text-[9px] font-black text-gray-400 uppercase tracking-widest">Ativo</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 pt-8">
                    <button type="submit" class="jewel-shine w-full bg-[#3C9AAE] text-white py-6 text-[11px] font-black uppercase tracking-[0.5em] transition hover:bg-[#2A6B7A] shadow-2xl shadow-[#3C9AAE]/20 rounded-sm">
                        Confirmar Alterações
                    </button>
                    <p class="text-center text-[8px] text-gray-300 mt-6 uppercase tracking-[0.3em] font-bold">As mudanças serão refletidas instantaneamente na vitrine.</p>
                </div>
            </form>
        </section>
    </div>
<?php include '../footer.php'; ?>
