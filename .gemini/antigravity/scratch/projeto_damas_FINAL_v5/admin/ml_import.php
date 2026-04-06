<?php
require_once '../functions.php';
session_start();

if (!isset($_SESSION['logado'])) {
    header("Location: login.php");
    exit;
}

$page_title = "Importação em Massa ML - Damas Acessórios";
include '../header.php';

$categorias = get_categorias($pdo);
?>

<div class="max-w-4xl mx-auto px-6 py-12">
    <div class="mb-12 animate-on-scroll fade-in">
        <a href="index.php" class="text-[10px] font-bold text-gray-400 uppercase tracking-widest hover:text-[#3C9AAE] transition flex items-center gap-2 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            Voltar ao Painel
        </a>
        <h1 class="text-4xl font-serif font-black text-[#2B7A8F] mb-2">Importação Automática</h1>
        <p class="text-xs text-gray-400 uppercase tracking-widest font-bold">Puxe todos os produtos do seu Mercado Livre</p>
    </div>

    <div class="bg-white p-8 md:p-12 rounded-2xl border border-gray-100 shadow-sm mb-8 animate-on-scroll fade-in">
        <div class="space-y-8">
            <!-- Passo 1: Buscar do Seller ID -->
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Digite seu Seller ID do ML</label>
                <div class="flex gap-4">
                    <input type="text" id="seller_id" value="1148466601" placeholder="Ex: 1148466601" 
                        class="flex-1 p-4 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-[#3C9AAE] transition font-medium">
                    <button onclick="buscarProdutos()" class="px-8 py-4 bg-[#3C9AAE] text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-[#2B7A8F] transition shadow-lg shadow-[#3C9AAE]/20">
                        Buscar do ML
                    </button>
                </div>
            </div>

            <!-- Lista de Resultados (Aparece após a busca) -->
            <div id="import-results" class="hidden pt-8 border-t border-gray-50">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-xl font-serif text-[#2B7A8F]">Produtos Encontrados (<span id="count-ml">0</span>)</h3>
                    <div class="flex gap-4 items-center">
                         <select id="import_cat_id" class="p-3 bg-gray-50 border-none rounded-xl text-[10px] font-bold uppercase focus:ring-2 focus:ring-[#3C9AAE]">
                            <option value="">Categoria Destino...</option>
                            <?php foreach($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['nome']; ?></option>
                            <?php endforeach; ?>
                         </select>
                         <button onclick="importarSelecionados()" class="px-6 py-3 bg-green-500 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-green-600 transition">
                            Importar Tudo
                         </button>
                    </div>
                </div>
                
                <div id="ml-list" class="space-y-4 max-h-[500px] overflow-y-auto pr-2">
                    <!-- Cards dos produtos do ML aparecem aqui -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let produtosEncontrados = [];

function buscarProdutos() {
    const sid = document.getElementById('seller_id').value;
    const list = document.getElementById('ml-list');
    const resultsDiv = document.getElementById('import-results');
    
    list.innerHTML = '<div class="text-center py-12"><div class="w-8 h-8 border-4 border-[#3C9AAE] border-t-transparent rounded-full animate-spin mx-auto mb-4"></div><p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Acessando API do Mercado Livre...</p></div>';
    resultsDiv.classList.remove('hidden');

    fetch(`api_ml_sync.php?seller_id=${sid}`)
        .then(res => res.json())
        .then(data => {
            produtosEncontrados = data;
            document.getElementById('count-ml').textContent = data.length;
            list.innerHTML = '';
            
            data.forEach(p => {
                const card = `
                    <div class="bg-gray-50 p-4 rounded-xl flex items-center gap-4">
                        <img src="${p.thumbnail}" class="w-12 h-12 rounded object-cover">
                        <div class="flex-1">
                            <h4 class="text-[11px] font-bold text-[#2B7A8F] uppercase">${p.title}</h4>
                            <p class="text-[10px] text-gray-400">R$ ${p.price}</p>
                        </div>
                    </div>
                `;
                list.insertAdjacentHTML('beforeend', card);
            });
        });
}

function importarSelecionados() {
    const catId = document.getElementById('import_cat_id').value;
    if(!catId) { alert('Selecione uma categoria de destino!'); return; }
    
    if(!confirm(`Deseja importar ${produtosEncontrados.length} produtos para esta categoria?`)) return;

    fetch('admin_actions.php?action=bulk_import', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({cat_id: catId, produtos: produtosEncontrados})
    })
    .then(res => res.json())
    .then(data => {
        alert('Importação concluída com sucesso!');
        window.location.href = 'index.php';
    });
}
</script>

<?php include '../footer.php'; ?>
