/*
    Damas Acessórios - Lógica do Carrinho de Compras
    Gerenciamento de localStorage e checkout via WhatsApp.
*/

let cart = JSON.parse(localStorage.getItem('damas_cart') || '[]');
let selectedFrete = null;

function formatCEP(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length > 5) v = v.substring(0,5) + '-' + v.substring(5, 8);
    input.value = v;
}

async function calcularFrete() {
    const cepInput = document.getElementById('cart-cep');
    if (!cepInput) return;
    const cep = cepInput.value.replace(/\D/g, '');
    if (cep.length !== 8) {
        showNotification('Digite um CEP válido com 8 números.', 'info');
        return;
    }

    const loading = document.getElementById('frete-loading');
    const results = document.getElementById('frete-results');
    
    if (loading) loading.classList.remove('hidden');
    if (results) results.classList.add('hidden');
    
    try {
        const response = await fetch('/api_frete.php?cep=' + cep);
        const data = await response.json();
        
        if (!response.ok) throw new Error(data.error || 'Erro ao calcular frete.');
        
        let html = `<p class="text-[9px] text-[#3C9AAE] uppercase tracking-widest font-bold mb-2 break-words">Destino: ${data.cidade}</p>`;
        
        data.opcoes.forEach((op, index) => {
            const precoFormat = op.preco.toFixed(2).replace('.', ',');
            html += `
                <label class="flex items-center justify-between p-3 border border-gray-100 rounded-sm cursor-pointer hover:bg-white transition-all bg-gray-50/50 mb-2">
                    <div class="flex items-center gap-3">
                        <input type="radio" name="frete_opcao" value="${index}" onchange="selecionarFrete('${op.tipo}', ${op.preco})" class="text-[#3C9AAE] focus:ring-[#3C9AAE]">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-[#2A6B7A] uppercase">${op.tipo}</span>
                            <span class="text-[8px] text-gray-400 uppercase tracking-wider">${op.prazo}</span>
                        </div>
                    </div>
                    <span class="text-xs font-black text-[#3C9AAE]">R$ ${precoFormat}</span>
                </label>
            `;
        });
        
        if (results) {
            results.innerHTML = html;
            results.classList.remove('hidden');
        }
    } catch (err) {
        showNotification(err.message, 'info');
    } finally {
        if (loading) loading.classList.add('hidden');
    }
}

function selecionarFrete(tipo, preco) {
    selectedFrete = { tipo, preco };
    renderCart(); // Atualiza o total
}

/**
 * Abre/Fecha o drawer do carrinho
 */
function toggleCart() {
    const drawer = document.getElementById('cart-drawer');
    const overlay = document.getElementById('cart-overlay');
    if (drawer && overlay) {
        const isOpen = drawer.classList.contains('open');
        drawer.classList.toggle('open', !isOpen);
        overlay.classList.toggle('open', !isOpen);
        // Impede rolagem do body quando o drawer está aberto
        document.body.style.overflow = !isOpen ? 'hidden' : '';
    }
}

/**
 * Adiciona um produto ao carrinho
 * @param {Object} product - {id, nome, preco, img, ml}
 */
function addToCart(product) {
    // Verificar se já existe no carrinho (mesmo id)
    const existente = cart.findIndex(i => i.id === product.id);
    if (existente > -1) {
        // Se já existe, apenas notifica
        showNotification(`"${product.nome}" já está no carrinho.`, 'info');
    } else {
        cart.push(product);
        saveCart();
        renderCart();
        showNotification(`"${product.nome}" adicionado!`);
    }

    // Abre o carrinho automaticamente
    const drawer = document.getElementById('cart-drawer');
    if (drawer && !drawer.classList.contains('open')) {
        toggleCart();
    }
}

/**
 * Exibe um toast premium ao adicionar ao carrinho
 * @param {string} msg - Mensagem a exibir
 * @param {string} type - 'success' ou 'info'
 */
function showNotification(msg, type = 'success') {
    // Remove toast anterior se existir
    const old = document.getElementById('cart-toast');
    if (old) old.remove();

    const toast = document.createElement('div');
    toast.id = 'cart-toast';

    const icon = type === 'success'
        ? `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>`
        : `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>`;

    const bg = type === 'success' ? '#3C9AAE' : '#2B7A8F';

    toast.innerHTML = `${icon}<span>${msg}</span>`;
    toast.style.cssText = `
        position: fixed;
        top: 80px;
        left: 50%;
        transform: translateX(-50%) translateY(-20px);
        background: ${bg};
        color: white;
        padding: 12px 22px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.35s ease, transform 0.35s cubic-bezier(0.34,1.56,0.64,1);
        pointer-events: none;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 8px 24px rgba(20,107,138,0.35);
        border: 1px solid rgba(255,255,255,0.15);
        max-width: 90vw;
        text-overflow: ellipsis;
        overflow: hidden;
    `;
    document.body.appendChild(toast);

    // Anima entrada
    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';
    });

    // Anima saída
    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(-50%) translateY(-10px)';
        setTimeout(() => toast.remove(), 400);
    }, 2800);
}

/**
 * Remove um item pelo índice
 */
function removeFromCart(index) {
    cart.splice(index, 1);
    saveCart();
    renderCart();
}

/**
 * Limpa todo o carrinho
 */
function clearCart() {
    if (confirm('Limpar todos os itens do carrinho?')) {
        cart = [];
        saveCart();
        renderCart();
    }
}

/**
 * Salva no localStorage e atualiza o badge
 */
function saveCart() {
    localStorage.setItem('damas_cart', JSON.stringify(cart));
    updateCartCount();
}

/**
 * Atualiza o número no ícone do carrinho (Navbar)
 */
function updateCartCount() {
    const badge = document.getElementById('cart-count-badge');
    if (badge) {
        badge.textContent = cart.length;
        badge.style.display = cart.length > 0 ? 'flex' : 'none';
    }
}

/**
 * Renderiza os itens dentro do Drawer
 */
function renderCart() {
    const container = document.getElementById('cart-items');
    const totalElement = document.getElementById('cart-total');
    if (!container || !totalElement) return;

    container.innerHTML = '';

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"
                    class="mx-auto mb-4 text-gray-200">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                    <path d="M3 6h18"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                <p class="text-sm font-light">Seu carrinho está vazio.</p>
                <p class="text-[9px] uppercase tracking-widest text-gray-300 mt-2">Explore nossa coleção</p>
            </div>
        `;
        totalElement.textContent = 'R$ 0,00';
        return;
    }

    let total = 0;
    cart.forEach((item, index) => {
        total += Number(item.preco) || 0;
        const itemEl = document.createElement('div');
        itemEl.className = 'flex gap-4 items-center border-b border-gray-50 pb-4';
        const precoFormatado = (Number(item.preco) || 0).toFixed(2).replace('.', ',');
        const isML = item.ml && item.ml !== 'None' && item.ml !== '';

        itemEl.innerHTML = `
            <div class="w-16 h-20 bg-gray-100 overflow-hidden rounded-sm flex-shrink-0">
                ${item.img ? `<img src="${item.img}" alt="${item.nome}" class="w-full h-full object-cover">` : '<div class="w-full h-full bg-gray-100"></div>'}
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-[10px] font-bold text-[#2B7A8F] uppercase tracking-wider leading-tight truncate">${item.nome}</h4>
                <p class="text-[11px] text-[#3C9AAE] font-serif font-bold mt-1">R$ ${precoFormatado}</p>
                ${isML ? '<span class="text-[7px] text-amber-600 font-bold uppercase tracking-tighter bg-amber-50 px-1.5 py-0.5 rounded-sm mt-1 inline-block">Pagamento via ML*</span>' : ''}
            </div>
            <button onclick="removeFromCart(${index})" title="Remover" class="text-gray-300 hover:text-red-400 transition ml-2 flex-shrink-0 p-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                </svg>
            </button>
        `;
        container.appendChild(itemEl);
    });

    if (selectedFrete) {
        total += selectedFrete.preco;
        const freteEl = document.createElement('div');
        freteEl.className = 'flex justify-between items-center border-t border-dashed border-gray-200 pt-3 mt-2 mb-2';
        const freteFormatado = selectedFrete.preco.toFixed(2).replace('.', ',');
        freteEl.innerHTML = `
            <span class="text-[10px] uppercase font-bold text-[#2A6B7A]">📦 Entrega: ${selectedFrete.tipo}</span>
            <span class="text-xs font-black text-[#3C9AAE]">+ R$ ${freteFormatado}</span>
        `;
        container.appendChild(freteEl);
    }

    totalElement.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;

    // Visibilidade do painel de frete
    const freteCont = document.getElementById('frete-container');
    if (freteCont) {
        freteCont.style.display = cart.length > 0 ? 'block' : 'none';
    }
}

/**
 * Abre o Modal de Checkout para pegar o Nome do Cliente
 */
function openCheckoutModal() {
    if (cart.length === 0) return;
    toggleCart(); // Fecha o carrinho

    const modal = document.getElementById('checkout-modal');
    if (modal) {
        modal.classList.remove('opacity-0', 'pointer-events-none');
        const content = modal.querySelector('.checkout-modal-content');
        if (content) content.classList.remove('scale-95');
    }
}

/**
 * Fecha o Modal de Checkout
 */
function closeCheckoutModal() {
    const modal = document.getElementById('checkout-modal');
    if (modal) {
        modal.classList.add('opacity-0', 'pointer-events-none');
        const content = modal.querySelector('.checkout-modal-content');
        if (content) content.classList.add('scale-95');
    }
}

/**
 * Constrói a mensagem com Nome + Produtos e redireciona para o WhatsApp
 */
function enviarPedidoWhatsApp() {
    if (cart.length === 0) return;

    const nomeInput = document.getElementById('checkout-nome');
    const obsInput = document.getElementById('checkout-obs');

    if (!nomeInput.value.trim()) {
        alert("Por favor, informe seu nome para continuarmos!");
        nomeInput.focus();
        return;
    }

    const nome = nomeInput.value.trim();
    const obs = obsInput ? obsInput.value.trim() : '';

    // Número injetado pelo servidor via variável global
    const phone = window.WHATSAPP_GLOBAL || '5515996710838';

    let message = `💎 *Novo Pedido - Damas Acessórios* 💎\n\n`;
    message += `Olá Juliana! Meu nome é *${nome}* e escolhi estas peças no site:\n\n`;

    let hasML = false;

    cart.forEach((item, index) => {
        const priceFormatted = (Number(item.preco) || 0).toFixed(2).replace('.', ',');
        message += `*${index + 1}.* ${item.nome} — R$ ${priceFormatted}\n`;
        if (item.ml && item.ml !== 'None' && item.ml !== '') hasML = true;
    });

    const total = cart.reduce((sum, item) => sum + (Number(item.preco) || 0), 0);
    message += `\n*Total Estimado: R$ ${total.toFixed(2).replace('.', ',')}*\n`;

    if (obs) {
        message += `\n📝 *Observações do Cliente:*\n_${obs}_\n`;
    }

    message += `\n------------------\n`;
    message += `Pode me passar as instruções para o pagamento?`;

    if (hasML) {
        message += '\n\n_(Obs: Alguns itens são pagos via Mercado Livre)_';
    }

    const whatsappUrl = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;

    closeCheckoutModal();

    if (hasML) {
        alert('✨ Atenção: Seu pedido contém itens do Mercado Livre. Após enviar a mensagem no WhatsApp, você poderá finalizar o pagamento pelos links correspondentes.');
    }

    window.open(whatsappUrl, '_blank');
}

/**
 * Processa o Checkout Unificado via Mercado Pago
 */
async function processMPCheckout() {
    if (cart.length === 0) {
        showNotification('Seu carrinho está vazio!', 'info');
        return;
    }

    if (selectedFrete === null) {
        alert('Por favor, digite seu CEP e escolha um Frete (PAC ou SEDEX) antes de pagar.');
        return;
    }

    const btn = document.getElementById('btn-mp-checkout');
    const originalText = btn.innerHTML;
    
    try {
        // Estado de Loading
        btn.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>Gerando Pagamento...</span>`;
        btn.disabled = true;
        btn.classList.add('opacity-80', 'cursor-not-allowed');

        const response = await fetch('/checkout_mp.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ cart: cart, frete: selectedFrete })
        });

        const data = await response.json();

        if (response.ok && data.init_point) {
            // Sucesso! Redireciona para o Mercado Pago
            window.location.href = data.init_point;
        } else {
            throw new Error(data.error || 'Erro ao gerar link de pagamento.');
        }

    } catch (error) {
        console.error('Erro no checkout MP:', error);
        alert('Ops! Ocorreu um erro ao processar o pagamento: ' + error.message);
        
        // Restaura botão
        btn.innerHTML = originalText;
        btn.disabled = false;
        btn.classList.remove('opacity-80', 'cursor-not-allowed');
    }
}

/**
 * Inicialização ao carregar a página
 */
document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
    renderCart();
});
