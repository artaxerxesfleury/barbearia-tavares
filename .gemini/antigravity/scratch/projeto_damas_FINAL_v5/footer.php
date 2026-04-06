    <footer class="bg-[#fafafa] text-[#2A6B7A] pt-24 pb-12 border-t border-gray-100 mt-20 md:mt-32">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-12 gap-12 md:gap-20 mb-16">
            <!-- Sobre -->
            <div class="md:col-span-4 flex flex-col items-center md:items-start text-center md:text-left">
                <img src="/static/logo_damas_2026.jpg" alt="Logo Damas Acessórios" class="h-14 rounded-xl mb-8 opacity-90 transition-opacity hover:opacity-100">
                <h4 class="text-2xl font-serif mb-6 text-[#2A6B7A] font-black italic">Damas Acessórios</h4>
                <p class="text-[11px] leading-loose text-gray-400 tracking-[0.15em] font-medium max-w-xs uppercase">
                    Elegância curada há mais de 8 anos, transformando cada detalhe em uma expressão de luxo e sofisticação.
                </p>
                <div class="flex gap-5 mt-10">
                    <a href="https://www.instagram.com/damaas_acessorios_" target="_blank" aria-label="Instagram"
                        class="p-4 bg-white shadow-sm border border-gray-100 rounded-full hover:bg-[#3C9AAE] hover:text-white transition-all group text-[#2A6B7A] hover:-translate-y-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="20" x="2" y="2" rx="5" ry="5"/>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                            <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>
                        </svg>
                    </a>
                    <a href="https://wa.me/<?php echo $WHATSAPP_GLOBAL; ?>" target="_blank" aria-label="WhatsApp"
                        class="p-4 bg-white shadow-sm border border-gray-100 rounded-full hover:bg-[#3C9AAE] hover:text-white transition-all text-[#2A6B7A] hover:-translate-y-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.83 12.1 19.79 19.79 0 0 1 1.76 3.47A2 2 0 0 1 3.74 1.45h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Coluna Mapa e Avaliações -->
            <div class="md:col-span-8 flex flex-col gap-10">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12">
                    
                    <!-- Google Reviews Card -->
                    <div class="flex flex-col gap-6">
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] font-black text-gray-300 uppercase tracking-[0.3em]">Excelência no Google</span>
                            <div class="flex-1 h-px bg-gray-100/50"></div>
                        </div>
                        <div class="bg-white p-6 rounded-sm border border-gray-100 shadow-sm transition-all hover:shadow-md h-full space-y-6">
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="text-3xl font-serif font-black text-[#3C9AAE]">5.0</span>
                                    <div class="flex text-yellow-400 mt-1">
                                        <?php for($i=0;$i<5;$i++): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-[#2A6B7A]">13+ Análises</span>
                                    <p class="text-[8px] text-gray-300 mt-1 font-bold">100% de satisfação</p>
                                </div>
                            </div>

                            <!-- Depoimentos Reais -->
                            <div class="space-y-4 pt-4 border-t border-gray-50">
                                <div class="relative pl-4 border-l-2 border-[#3C9AAE]/10">
                                    <p class="text-[10px] italic text-gray-500 leading-relaxed font-medium">"Só peças lindas e de qualidades, fora o atendimento da Ju que é uma querida 🥰❤️"</p>
                                    <p class="text-[8px] font-black text-[#2A6B7A] uppercase tracking-widest mt-2">— Larissa Pinheiro</p>
                                </div>
                                <div class="relative pl-4 border-l-2 border-[#3C9AAE]/10">
                                    <p class="text-[10px] italic text-gray-500 leading-relaxed font-medium">"Experiência de compra impecável! Peças lindas, qualidade excepcional. Atendimento nota 10..."</p>
                                    <p class="text-[8px] font-black text-[#2A6B7A] uppercase tracking-widest mt-2">— Benildes Damas</p>
                                </div>
                            </div>
                            
                            <a href="https://share.google/ThVuV9ncJ3LxUYypy" target="_blank" 
                               class="block w-full text-center py-3 border border-[#3C9AAE]/20 text-[#3C9AAE] text-[9px] font-black uppercase tracking-[0.2em] hover:bg-[#3C9AAE] hover:text-white transition-all rounded-sm mt-4">
                                Ler Todas as Análises
                            </a>
                        </div>
                    </div>

                    <!-- Mapa com Link Direto -->
                    <div class="flex flex-col gap-6">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black text-gray-300 uppercase tracking-[0.3em]">Localização</span>
                            <a href="https://share.google/ThVuV9ncJ3LxUYypy" target="_blank" 
                               class="text-[9px] font-bold text-[#3C9AAE] uppercase tracking-widest hover:underline flex items-center gap-1.5 transition-all">
                                📍 Como Chegar
                            </a>
                        </div>
                        <div class="h-full min-h-[220px] border border-gray-100 bg-white shadow-inner overflow-hidden rounded-sm hover:scale-[1.01] transition-all duration-700">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3655.0174367238906!2d-47.828900026044494!3d-23.639546578746156!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94c5bf23b8936f11%3A0x3876f97ab5385907!2sR.%20Nossa%20Sra.%20das%20Dores%2C%20131%20-%20Per%C3%ADmetro%20Urbano%2C%20Sarapu%C3%AD%20-%20SP%2C%2018225-000!5e0!3m2!1spt-BR!2sbr!4v1775401529451!5m2!1spt-BR!2sbr"
                                width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" title="Localização"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom bar -->
        <div class="max-w-7xl mx-auto px-6 border-t border-gray-100 pt-10 flex flex-col sm:flex-row items-center justify-between gap-6">
            <p class="text-[9px] text-gray-300 tracking-[0.25em] font-extrabold uppercase text-center sm:text-left">
                &copy; <?php echo date('Y'); ?> Damas Acessórios. Excelência Artesanal.
            </p>
            <div class="flex items-center gap-8">
                <a href="/admin/login.php"
                    class="text-[9px] text-gray-300 font-extrabold tracking-widest hover:text-[#3C9AAE] transition uppercase">
                    Área Administrativa
                </a>
                <div class="h-1 w-1 bg-gray-200 rounded-full"></div>
                <span class="text-[9px] text-gray-300 font-extrabold tracking-widest uppercase">Brasil</span>
            </div>
        </div>
    </footer>

    <!-- CART OVERLAY -->
    <div id="cart-overlay" class="cart-overlay" onclick="toggleCart()"></div>

    <!-- CART DRAWER -->
    <div id="cart-drawer" class="cart-drawer flex flex-col border-l border-gray-100">
        <div class="px-8 py-8 border-b border-gray-50 flex justify-between items-center flex-shrink-0 bg-white">
            <div>
                <h3 class="text-2xl font-serif text-[#2A6B7A] font-black italic">Vitrine Pessoal</h3>
                <p class="text-[9px] text-[#3C9AAE] uppercase tracking-[0.3em] font-black mt-1">Sua seleção exclusiva</p>
            </div>
            <button onclick="toggleCart()" class="p-3 text-gray-300 hover:text-[#3C9AAE] hover:bg-gray-50 rounded-full transition-all" aria-label="Fechar">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                </svg>
            </button>
        </div>
        <div id="cart-items" class="flex-1 overflow-y-auto p-8 space-y-8 no-scrollbar bg-white"></div>
        
        <div class="p-8 border-t border-gray-100 bg-[#fafafa] space-y-4 flex-shrink-0">
            <div class="flex justify-between items-center mb-2">
                <span class="text-[10px] uppercase tracking-[0.4em] text-gray-400 font-black">Investimento Total</span>
                <span id="cart-total" class="text-2xl font-serif font-black text-[#3C9AAE]">R$ 0,00</span>
            </div>
            
            <div class="grid grid-cols-1 gap-3">
                <!-- Botão WhatsApp -->
                <button onclick="openCheckoutModal()"
                    class="jewel-shine w-full bg-[#3C9AAE] text-white py-5 text-[10px] font-black uppercase tracking-[0.4em] transition hover:bg-[#2A6B7A] shadow-xl shadow-[#3C9AAE]/20 rounded-sm">
                    Finalizar via WhatsApp VIP
                </button>
                <!-- Botão Mercado Pago -->
                <button onclick="processMPCheckout()" id="btn-mp-checkout"
                    class="w-full bg-[#009EE3] text-white py-4 text-[9px] font-black uppercase tracking-[0.3em] transition hover:bg-[#007EB5] rounded-sm shadow-md flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                    <span>Comprar via Mercado Pago</span>
                </button>
            </div>
            <p class="text-[8px] text-gray-300 text-center uppercase tracking-widest font-bold">Parcelamento em até 12x disponível</p>
        </div>
    </div>

    <!-- ========================= -->
    <!-- MODAL CHECKOUT WHATSAPP   -->
    <!-- ========================= -->
    <div id="checkout-modal"
        class="fixed inset-0 z-[200] flex items-center justify-center p-4 opacity-0 pointer-events-none transition-opacity duration-300"
        style="background: rgba(10, 32, 37, 0.6); backdrop-filter: blur(12px);">
        <div class="checkout-modal-content bg-white w-full max-w-md rounded-sm shadow-2xl transform scale-95 transition-all duration-500 overflow-hidden border border-white/20">
            <!-- Header -->
            <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between bg-[#fafafa]">
                <div>
                    <h3 class="text-2xl font-serif text-[#2A6B7A] font-black italic">Atendimento VIP</h3>
                    <p class="text-[9px] text-[#3C9AAE] uppercase tracking-[0.3em] font-black mt-1">Finalização via WhatsApp</p>
                </div>
                <button onclick="closeCheckoutModal()" class="p-3 text-gray-300 hover:text-[#3C9AAE] rounded-full transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
            <!-- Body -->
            <div class="px-8 py-10 space-y-8">
                <!-- CSRF Token (Exemplo de uso, embora aqui seja JS-side, mantemos por segurança se fosse POST real) -->
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                
                <div class="relative group">
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3 group-focus-within:text-[#3C9AAE] transition-colors">Nome Completo</label>
                    <input type="text" id="checkout-nome" placeholder="Como deseja ser chamado(a)?"
                        class="w-full px-5 py-4 bg-gray-50/50 border border-gray-100 rounded-sm text-sm focus:outline-none focus:border-[#3C9AAE] focus:ring-0 transition-all font-bold text-[#2A6B7A] placeholder-gray-300">
                </div>
                <div class="relative group">
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3 group-focus-within:text-[#3C9AAE] transition-colors">Observações Especiais</label>
                    <textarea id="checkout-obs" placeholder="Algum detalhe ou preferência sobre o presente?"
                        class="w-full px-5 py-4 bg-gray-50/50 border border-gray-100 rounded-sm text-sm focus:outline-none focus:border-[#3C9AAE] focus:ring-0 transition-all resize-none h-28 font-medium text-[#2A6B7A] placeholder-gray-300"></textarea>
                </div>
            </div>
            <!-- Footer -->
            <div class="px-8 pb-10 flex gap-4">
                <button onclick="closeCheckoutModal()" class="flex-1 py-5 border border-gray-100 text-gray-300 text-[10px] font-black uppercase tracking-[0.3em] rounded-sm hover:bg-gray-50 transition-all">
                    Voltar
                </button>
                <button onclick="enviarPedidoWhatsApp()"
                    class="jewel-shine flex-1 py-5 bg-[#3C9AAE] text-white text-[10px] font-black uppercase tracking-[0.3em] rounded-sm hover:bg-[#2A6B7A] transition-all shadow-xl shadow-[#3C9AAE]/20">
                    Enviar via WHATSAPP
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL DO CHECKOUT MERCADO LIVRE FOI SUBSTITUÍDO PELO CHECKOUT MP DIRETO -->


    <!-- Scripts Globais -->
    <script>window.WHATSAPP_GLOBAL = '<?php echo $WHATSAPP_GLOBAL; ?>';</script>
    <script src="/static/js/cart.js?v=2"></script>
    <script>
        // Animações de scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    // observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.05, rootMargin: '0px 0px -20px 0px' });
        
        document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));

        // Mobile menu
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const icon = document.getElementById('hamburger-icon');
            const isOpen = !menu.classList.contains('hidden');
            menu.classList.toggle('hidden', isOpen);
            icon.innerHTML = isOpen
                ? '<line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="18" y2="18"/>'
                : '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>';
            document.body.style.overflow = isOpen ? '' : 'hidden';
        }

        // Mobile search toggle
        function toggleMobileSearch() {
            const bar = document.getElementById('mobile-search-bar');
            const isOpen = bar.classList.contains('open');
            bar.classList.toggle('open', !isOpen);
            if (!isOpen) {
                setTimeout(() => document.getElementById('mobile-search-input')?.focus(), 300);
            }
        }

        // Cart open/close
        function toggleCart() {
            const drawer  = document.getElementById('cart-drawer');
            const overlay = document.getElementById('cart-overlay');
            const isOpen  = drawer.classList.contains('open');
            drawer.classList.toggle('open', !isOpen);
            overlay.classList.toggle('open', !isOpen);
            document.body.style.overflow = isOpen ? '' : 'hidden';
        }

        // Navbar scroll shrink
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('nav.glass-nav');
            if (nav) {
                if (window.scrollY > 20) {
                    nav.classList.add('shadow-premium', 'py-1');
                    nav.classList.remove('py-3');
                } else {
                    nav.classList.remove('shadow-premium', 'py-1');
                    nav.classList.add('py-3');
                }
            }
        });
    </script>
</body>
</html>
