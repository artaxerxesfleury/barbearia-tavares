<?php
require_once '../functions.php';

if (!isset($_SESSION['logado'])) {
    header("Location: login.php");
    exit;
}

// Gera token CSRF para as ações
$csrf_token = get_csrf_token();

// Dados do Dashboard
$stmt_total = $pdo->query("SELECT COUNT(*) FROM produtos");
$total_produtos = $stmt_total->fetchColumn();

$stmt_cats = $pdo->query("SELECT COUNT(*) FROM categorias");
$total_categorias = $stmt_cats->fetchColumn();

$stmt_valor = $pdo->query("SELECT SUM(preco) FROM produtos WHERE ativo = 1");
$valor_estoque = $stmt_valor->fetchColumn() ?: 0;

$categorias = get_categorias($pdo);
$produtos = get_todos_produtos($pdo);

// === CREDENCIAIS DINÂMICAS ML (vindo do config/env) ===
$ml_client_id     = $_ENV['ML_CLIENT_ID'] ?? '8304432912396900';
$ml_client_secret = $_ENV['ML_CLIENT_SECRET'] ?? 'BNSEzl4zbEHpAQNBhgCo36kolyNZVQy5';

// Garante que ml_config.json tem as credenciais mais recentes
$ml_config_file = __DIR__ . '/ml_config.json';
$ml_config      = file_exists($ml_config_file) ? json_decode(file_get_contents($ml_config_file), true) : [];

if (empty($ml_config['client_id'])) {
    $ml_config['client_id']     = $ml_client_id;
    $ml_config['client_secret'] = $ml_client_secret;
    file_put_contents($ml_config_file, json_encode($ml_config, JSON_PRETTY_PRINT));
}

$ml_token     = $ml_config['access_token'] ?? '';
$ml_expires   = $ml_config['token_expires'] ?? 0;
$ml_conectado = !empty($ml_token) && ($ml_expires === 0 || $ml_expires > time());
$ml_expirado  = !empty($ml_token) && $ml_expires > 0 && $ml_expires <= time();

$msg_sucesso = $_GET['sucesso'] ?? '';
$msg_erro    = $_GET['erro'] ?? '';

$page_title = "Damas | Gestão de Luxo";
include '../header.php';
?>

<div class="max-w-7xl mx-auto px-6 py-12">
    <div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-8 animate-on-scroll fade-in">
        <div>
            <h1 class="text-4xl font-serif font-black text-[#2A6B7A] mb-3 italic">Gestão de Luxo</h1>
            <p class="text-[10px] text-gray-300 uppercase tracking-[0.4em] font-black">Curadoria e Controle de Inventário</p>
        </div>
        <div class="flex flex-wrap justify-center gap-4">
            <button onclick="sincronizarTudo()" id="btn-sync-total"
                class="px-8 py-4 bg-[#3C9AAE] text-white text-[10px] font-black uppercase tracking-widest hover:bg-[#2A6B7A] transition rounded-sm flex items-center gap-3 shadow-xl shadow-[#3C9AAE]/20">
                <svg id="sync-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
                <span id="sync-text">Sincronizar Mercado Livre</span>
            </button>
            <a href="../index.php" class="px-8 py-4 border border-gray-100 text-gray-300 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition rounded-sm">Ver Site</a>
            <a href="logout.php" class="px-8 py-4 border border-red-50 text-red-300 text-[10px] font-black uppercase tracking-widest hover:bg-red-50 transition rounded-sm">Sair</a>
        </div>
    </div>


    <!-- ALERTAS DE STATUS ML -->
    <?php if ($msg_sucesso): ?>
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-medium flex items-center gap-3 animate-on-scroll fade-in">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <?php echo htmlspecialchars($msg_sucesso); ?>
    </div>
    <?php endif; ?>
    <?php if ($msg_erro): ?>
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm font-medium flex items-center gap-3 animate-on-scroll fade-in">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        <?php echo htmlspecialchars($msg_erro); ?>
    </div>
    <?php endif; ?>

    <!-- DASHBOARD STATS -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12 animate-on-scroll fade-in">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div><p class="text-[9px] text-gray-400 uppercase tracking-widest font-bold mb-1">Total Produtos</p><h3 class="text-3xl font-serif text-[#2B7A8F]"><?php echo $total_produtos; ?></h3></div>
            <div class="p-4 bg-blue-50 text-[#3C9AAE] rounded-xl"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 16V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v9m16 0H4m16 0 1.28 2.55a1 1 0 0 1-.9 1.45H3.62a1 1 0 0 1-.9-1.45L4 16" /></svg></div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div><p class="text-[9px] text-gray-400 uppercase tracking-widest font-bold mb-1">Categorias</p><h3 class="text-3xl font-serif text-[#2B7A8F]"><?php echo $total_categorias; ?></h3></div>
            <div class="p-4 bg-teal-50 text-teal-600 rounded-xl"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div><p class="text-[9px] text-gray-400 uppercase tracking-widest font-bold mb-1">Valor em Estoque</p><h3 class="text-3xl font-serif text-[#2B7A8F]">R$ <?php echo number_format($valor_estoque, 2, ',', '.'); ?></h3></div>
            <div class="p-4 bg-amber-50 text-amber-600 rounded-xl"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        </div>
        <!-- CARD STATUS ML COM OAUTH -->
        <div class="bg-white p-6 rounded-2xl border <?php echo $ml_conectado ? 'border-green-100' : ($ml_expirado ? 'border-yellow-100' : 'border-gray-100'); ?> shadow-sm flex items-center justify-between cursor-pointer hover:border-[#3C9AAE] transition" onclick="document.getElementById('mlModal').classList.remove('hidden')">
            <div>
                <p class="text-[9px] text-gray-400 uppercase tracking-widest font-bold mb-1">Integração API ML</p>
                <?php if ($ml_conectado): ?>
                    <h3 class="text-lg font-bold text-green-600 flex items-center gap-2"><div class="w-2.5 h-2.5 bg-green-500 animate-pulse rounded-full"></div> Conectada</h3>
                <?php elseif ($ml_expirado): ?>
                    <h3 class="text-lg font-bold text-yellow-500 flex items-center gap-2"><div class="w-2.5 h-2.5 bg-yellow-400 rounded-full"></div> Token Expirado</h3>
                <?php else: ?>
                    <h3 class="text-lg font-bold text-red-500 flex items-center gap-2"><div class="w-2.5 h-2.5 bg-red-500 rounded-full"></div> Offline</h3>
                <?php endif; ?>
                <p class="text-[9px] text-gray-400 mt-1">Clique para configurar</p>
            </div>
            <div class="p-4 bg-yellow-50 text-yellow-600 rounded-xl"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg></div>
        </div>
    </div>

    <!-- ML CONFIG MODAL (com OAuth) -->
    <div id="mlModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white p-8 rounded-2xl w-full max-w-lg shadow-2xl">
            <h2 class="text-2xl font-serif text-[#2B7A8F] mb-2">Configurar Mercado Livre</h2>
            <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-6">Insira suas chaves e conecte sua conta</p>
            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">App ID / Client ID</label>
                    <input type="text" id="mlAppId" value="<?php echo htmlspecialchars($ml_config['client_id'] ?? ''); ?>" placeholder="Ex: 8304432912396900" class="w-full p-4 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-[#3C9AAE] transition">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Client Secret</label>
                    <input type="password" id="mlSecret" value="<?php echo htmlspecialchars($ml_config['client_secret'] ?? ''); ?>" placeholder="Seu Client Secret" class="w-full p-4 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-[#3C9AAE] transition">
                </div>
                <p class="text-[10px] text-gray-400 bg-blue-50 p-3 rounded-xl">💡 <strong>Redirect URI</strong> para configurar no painel do ML Developer:<br>
                <code class="text-[#3C9AAE] font-bold"><?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/admin/ml_oauth_callback.php'; ?></code></p>
            </div>
            <div class="flex gap-3">
                <button onclick="document.getElementById('mlModal').classList.add('hidden')" class="flex-1 px-4 py-3 border border-gray-200 text-gray-400 rounded-xl text-[10px] font-bold uppercase tracking-widest">Fechar</button>
                <button onclick="salvarMlConfig()" class="px-4 py-3 bg-gray-100 text-gray-600 rounded-xl text-[10px] font-bold uppercase tracking-widest">Salvar Chaves</button>
                <a href="ml_oauth.php" class="flex-1 px-4 py-3 bg-[#3C9AAE] text-white rounded-xl text-[10px] font-bold uppercase tracking-widest text-center shadow-lg shadow-[#3C9AAE]/20 hover:bg-[#2B7A8F] transition">
                    🔗 Conectar ao ML
                </a>
            </div>
        </div>
    </div>

    <!-- GESTÃO DE ESTRUTURA -->
    <section id="categorias" class="bg-white p-8 md:p-12 rounded-2xl border border-gray-100 shadow-sm mb-16 animate-on-scroll fade-in">
        <div class="flex items-center gap-4 mb-10 border-b border-gray-50 pb-6">
            <div class="w-10 h-10 bg-[#3C9AAE] rounded-full flex items-center justify-center text-white"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></div>
            <h2 class="text-2xl font-serif text-[#2B7A8F]">Gestão de Estrutura</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <form action="admin_actions.php?action=add_cat" method="POST" class="flex gap-2">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="text" name="nome" placeholder="Nova Categoria (ex: Joias)" class="flex-1 p-3 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-[#3C9AAE] transition" required>
                <button type="submit" class="bg-[#3C9AAE] text-white px-6 py-3 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-[#2A6B7A] transition">Add</button>
            </form>
            <div class="space-y-2">
                <?php foreach ($categorias as $cat): ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl group">
                    <span class="text-sm font-bold text-[#2B7A8F] uppercase tracking-wider"><?php echo $cat['nome']; ?></span>
                    <a href="admin_actions.php?action=del_cat&id=<?php echo $cat['id']; ?>&csrf_token=<?php echo $csrf_token; ?>" onclick="return confirm('Excluir?')" class="text-red-300 hover:text-red-500 transition"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg></a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- FORMULÁRIO DE CADASTRO -->
    <section class="bg-white p-8 md:p-12 rounded-2xl border border-gray-100 shadow-sm mb-16 animate-on-scroll fade-in">
        <div class="flex items-center gap-4 mb-10 border-b border-gray-50 pb-6">
            <div class="w-10 h-10 bg-[#3C9AAE] rounded-full flex items-center justify-center text-white"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg></div>
            <h2 class="text-2xl font-serif text-[#2B7A8F]">Cadastrar Novo Produto</h2>
        </div>
        <form action="admin_actions.php?action=add_produto" method="POST" class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="space-y-6">
                <div><label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Nome da Peça</label><input type="text" name="nome" placeholder="Ex: Anel Solitário" class="w-full p-4 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-[#3C9AAE] font-medium" required></div>
                <div class="grid grid-cols-2 gap-6">
                    <div><label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Categoria</label>
                        <select name="categoria_id" class="w-full p-4 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-[#3C9AAE] font-medium">
                            <option value="">Selecionar...</option><?php foreach ($categorias as $cat): ?><option value="<?php echo $cat['id']; ?>"><?php echo $cat['nome']; ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div><label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Preço (R$)</label><input type="text" name="preco" placeholder="129,90" class="w-full p-4 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-[#3C9AAE] font-medium" required></div>
                </div>
                <div><label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Descrição Curta</label><textarea name="descricao_curta" class="w-full p-4 bg-gray-50 border-none rounded-xl h-24 focus:ring-2 focus:ring-[#3C9AAE] font-medium"></textarea></div>
                
                <!-- CAMPO MERCADO LIVRE -->
                <div>
                    <label class="block text-[10px] font-bold text-[#3C9AAE] uppercase tracking-widest mb-2 flex justify-between items-center">
                        <span>Link ML (Colar aqui)</span>
                        <span id="ml-status-indicator" class="hidden text-[#3C9AAE] text-[8px] bg-[#3C9AAE]/10 px-2 py-0.5 rounded font-bold animate-pulse">Importando...</span>
                    </label>
                    <input type="text" id="link_ml_input" name="link_mercadolivre" placeholder="Cole o link do Mercado Livre aqui..." class="w-full p-3 bg-blue-50/50 border border-[#3C9AAE]/30 rounded-xl text-[11px] focus:ring-2 focus:ring-[#3C9AAE]">
                </div>
            </div>
            <div class="space-y-6">
                <div><label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Descrição Detalhada</label><textarea name="descricao_longa" class="w-full p-4 bg-gray-50 border-none rounded-xl h-24 focus:ring-2 focus:ring-[#3C9AAE] font-medium"></textarea></div>
                <div class="grid grid-cols-2 gap-4">
                    <?php for ($i=1; $i<=4; $i++): ?>
                    <div><label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Foto <?php echo $i; ?></label><input type="text" name="imagem_<?php echo $i; ?>" class="w-full p-3 bg-gray-50 border-none rounded-xl text-[11px] focus:ring-2 focus:ring-[#3C9AAE]"></div>
                    <?php endfor; ?>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Vídeo</label><input type="text" name="video_url" class="w-full p-3 bg-gray-50 border-none rounded-xl text-[11px]"></div>
                    <div><label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">WhatsApp</label><input type="text" name="whatsapp" value="<?php echo $WHATSAPP_GLOBAL; ?>" class="w-full p-3 bg-gray-50 border-none rounded-xl text-[11px]"></div>
                </div>
            </div>
            <div class="lg:col-span-2 pt-4"><button type="submit" class="w-full bg-[#3C9AAE] text-white py-5 rounded-xl uppercase font-bold tracking-widest shadow-xl shadow-[#3C9AAE]/20 transition hover:bg-[#2B7A8F]">Cadastrar Produto no Sistema</button></div>
        </form>
    </section>

    <!-- LISTA DE PRODUTOS -->
    <section>
        <form id="bulk-delete-form" action="admin_actions.php?action=bulk_del" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="flex items-center justify-between mb-8 border-b border-gray-100 pb-6">
                <div class="flex items-center gap-4">
                    <input type="checkbox" id="select-all" class="w-5 h-5 rounded border-gray-200 text-[#3C9AAE] focus:ring-[#3C9AAE] cursor-pointer">
                    <h2 class="text-2xl font-serif text-[#2B7A8F]">Produtos no Catálogo</h2>
                </div>
                <div class="flex items-center gap-4">
                    <button type="button" id="btn-bulk-delete" onclick="confirmBulkDelete()" 
                        class="hidden px-6 py-3 bg-red-500 text-white text-[10px] font-black uppercase tracking-widest rounded-sm hover:bg-red-600 transition shadow-xl shadow-red-500/10 animate-fade-in">
                        Excluir Selecionados (<span id="selected-count">0</span>)
                    </button>
                    <span class="bg-gray-100 text-gray-500 text-[10px] px-3 py-1 rounded-full font-bold uppercase tracking-widest"><?php echo count($produtos); ?> Itens</span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
<?php foreach ($produtos as $pr): ?>
                <div class="bg-white p-6 rounded-2xl border border-gray-100 flex flex-col md:flex-row items-center gap-6 group hover:border-[#3C9AAE]/30 transition-all">
                    <div class="flex items-center justify-center">
                        <input type="checkbox" name="ids[]" value="<?php echo $pr['id']; ?>" 
                            class="product-checkbox w-5 h-5 rounded border-gray-200 text-[#3C9AAE] focus:ring-[#3C9AAE] cursor-pointer">
                    </div>
                    <div class="w-20 h-24 bg-gray-50 rounded-lg overflow-hidden border border-gray-100 flex-shrink-0">
                        <img src="<?php echo get_imagem_principal($pr['imagens_url']); ?>" class="w-full h-full object-contain">
                    </div>
                    <div class="flex-1 text-center md:text-left">
                        <h4 class="text-sm font-bold text-[#2B7A8F] uppercase tracking-wider mb-1"><?php echo $pr['nome']; ?></h4>
                        <p class="text-xs text-gray-400 font-serif font-bold italic text-[#3C9AAE]">R$ <?php echo number_format($pr['preco'], 2, ',', '.'); ?></p>
                    </div>
                    <div class="flex gap-3">
                        <a href="admin_actions.php?action=toggle&id=<?php echo $pr['id']; ?>&csrf_token=<?php echo $csrf_token; ?>" 
                            class="px-4 py-3 <?php echo $pr['ativo'] ? 'bg-blue-50 text-blue-500' : 'bg-gray-100 text-gray-400'; ?> text-[9px] font-bold uppercase rounded-lg">
                            <?php echo $pr['ativo'] ? 'Visível' : 'Oculto'; ?>
                        </a>
                        <a href="produto_editar.php?id=<?php echo $pr['id']; ?>" 
                            class="px-5 py-3 bg-gray-50 text-gray-500 text-[9px] font-bold uppercase rounded-lg transition hover:bg-gray-100">Editar</a>
                        <a href="admin_actions.php?action=del_produto&id=<?php echo $pr['id']; ?>&csrf_token=<?php echo $csrf_token; ?>" 
                            onclick="return confirm('Excluir este produto?')" 
                            class="px-6 py-3 border border-red-50 text-red-300 text-[9px] font-bold uppercase rounded-lg hover:text-red-500 transition">Excluir</a>
                    </div>
                </div>
<?php endforeach; ?>
            </div>
        </form>
    </section>
</div>

<script>
    function sincronizarTudo() {
        const btn = document.getElementById('btn-sync-total');
        const icon = document.getElementById('sync-icon');
        const text = document.getElementById('sync-text');
        
        if (!confirm('Deseja iniciar a Sincronização Total? Isso buscará todos os seus produtos do Mercado Livre e criará as categorias automaticamente.')) return;

        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        icon.classList.add('animate-spin');
        text.textContent = 'Sincronizando...';

        fetch('ml_full_sync.php')
            .then(async res => {
                const text = await res.text();
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Falha ao processar JSON:', text);
                    throw new Error('O servidor retornou uma resposta inválida. Verifique os logs.');
                }
            })
            .then(data => {
                if (data.status === 'ok') {
                    let msg = `✅ Sincronização concluída!\n\n`;
                    msg += `📦 Total no ML: ${data.total_ml}\n`;
                    msg += `🆕 Novos importados: ${data.importados}\n`;
                    msg += `🔄 Já existentes (atualizados): ${data.ignorados}\n`;
                    if (data.erros && data.erros.length > 0) {
                        msg += `⚠️ Erros em alguns itens: ${data.erros.length}\n`;
                        msg += data.erros.slice(0, 3).join('\n');
                    }
                    alert(msg);
                    location.reload();
                } else {
                    alert('Erro na sincronização: ' + (data.error || 'Erro desconhecido'));
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    icon.classList.remove('animate-spin');
                    text.textContent = 'Sincronizar Mercado Livre';
                }
            })
            .catch(err => {
                alert('Falha na Sincronização: ' + err.message);
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                icon.classList.remove('animate-spin');
                text.textContent = 'Sincronizar Mercado Livre';
            });
    }

    function salvarMlConfig() {
        const appId = document.getElementById('mlAppId').value;
        const secret = document.getElementById('mlSecret').value;
        fetch('api_ml_config.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({client_id: appId, client_secret: secret}) }).then(() => location.reload());
    }

    const linkInput = document.getElementById('link_ml_input');
    const mlStatus  = document.getElementById('ml-status-indicator');

    if (linkInput) {
        linkInput.addEventListener('input', function () {
            clearTimeout(window.mlDebounce);
            const url = this.value.trim();

            // Só dispara se parecer um link do ML com MLB
            if (!url.includes('MLB')) return;

            // Feedback imediato
            mlStatus.classList.remove('hidden');
            mlStatus.textContent = 'Buscando...';
            mlStatus.className = 'text-[#3C9AAE] text-[8px] bg-[#3C9AAE]/10 px-2 py-0.5 rounded font-bold animate-pulse';

            window.mlDebounce = setTimeout(() => {
                fetch('api_ml_info.php?url=' + encodeURIComponent(url))
                    .then(res => res.json())
                    .then(data => {
                        mlStatus.classList.remove('animate-pulse');

                        // Se o servidor retornou erro
                        if (data.error) {
                            mlStatus.textContent = '✗ ' + data.error;
                            mlStatus.className = 'text-red-500 text-[8px] bg-red-50 px-2 py-0.5 rounded font-bold';
                            return;
                        }

                        // Se não tem título, não houve importação real
                        if (!data.nome) {
                            mlStatus.textContent = '✗ Dados não encontrados';
                            mlStatus.className = 'text-red-500 text-[8px] bg-red-50 px-2 py-0.5 rounded font-bold';
                            return;
                        }

                        // Preenche todos os campos do formulário
                        const setVal = (sel, val) => {
                            const el = document.querySelector(sel);
                            if (el && val !== undefined && val !== null && val !== '') {
                                el.value = val;
                            }
                        };

                        setVal('input[name="nome"]',               data.nome);
                        setVal('input[name="preco"]',              data.preco);
                        setVal('textarea[name="descricao_curta"]', data.descricao_curta);
                        setVal('textarea[name="descricao_longa"]', data.descricao_longa);
                        setVal('input[name="video_url"]',          data.video_url);

                        // Preenche as 4 fotos
                        if (Array.isArray(data.imagens)) {
                            data.imagens.forEach((img, i) => {
                                if (i < 4) setVal(`input[name="imagem_${i + 1}"]`, img);
                            });
                        }

                        // ✅ Só mostra "Importado!" se realmente preencheu algo
                        mlStatus.textContent = '✓ Importado com sucesso!';
                        mlStatus.className = 'text-green-600 text-[8px] bg-green-50 px-2 py-0.5 rounded font-bold';
                        setTimeout(() => mlStatus.classList.add('hidden'), 4000);
                    })
                    .catch(() => {
                        mlStatus.textContent = '✗ Erro de conexão';
                        mlStatus.className = 'text-red-500 text-[8px] bg-red-50 px-2 py-0.5 rounded font-bold';
                    });
            }, 900); // 0.9s de debounce após parar de digitar
        });
    }

    // Gerenciamento de Exclusão em Massa
    const selectAll = document.getElementById('select-all');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    const btnBulkDelete = document.getElementById('btn-bulk-delete');
    const selectedCountSpan = document.getElementById('selected-count');

    function updateBulkDeleteButton() {
        const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;
        if (checkedCount > 0) {
            btnBulkDelete.classList.remove('hidden');
            selectedCountSpan.textContent = checkedCount;
        } else {
            btnBulkDelete.classList.add('hidden');
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            productCheckboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
            updateBulkDeleteButton();
        });
    }

    productCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkDeleteButton);
    });

    function confirmBulkDelete() {
        const count = document.querySelectorAll('.product-checkbox:checked').length;
        if (confirm(`⚠️ Atenção: Você está prestes a excluir ${count} produtos permanentemente. Deseja continuar?`)) {
            document.getElementById('bulk-delete-form').submit();
        }
    }
</script>
<?php include '../footer.php'; ?>