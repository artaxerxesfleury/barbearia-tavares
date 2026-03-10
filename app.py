from flask import Flask, render_template, request, redirect, url_for, session, flash
from flask_sqlalchemy import SQLAlchemy
from functools import wraps
import urllib.parse
import os

app = Flask(__name__)

# --- CONFIGURAÇÕES ---
app.secret_key = os.environ.get('SECRET_KEY', 'chave_super_secreta_damas_123')
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///loja.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False
db = SQLAlchemy(app)

# --- CREDENCIAIS ---
USUARIO_ADMIN = 'juliana'
SENHA_ADMIN = 'damas2026'

# --- NÚMERO DE WHATSAPP GLOBAL ---
# Altere aqui para o número da Juliana (formato internacional, sem +)
WHATSAPP_GLOBAL = '5511999999999'

# ============================================================
# MODELOS DO BANCO
# ============================================================

class Categoria(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    nome = db.Column(db.String(50), nullable=False, unique=True)
    subcategorias = db.relationship('Subcategoria', backref='categoria_pai', lazy=True, cascade='all, delete-orphan')
    produtos = db.relationship('Produto', backref='categoria_ref', lazy=True)

class Subcategoria(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    nome = db.Column(db.String(50), nullable=False)
    categoria_id = db.Column(db.Integer, db.ForeignKey('categoria.id'), nullable=False)
    produtos = db.relationship('Produto', backref='subcategoria_ref', lazy=True)

class Produto(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    nome = db.Column(db.String(100), nullable=False)

    # Relações com Categorias
    categoria_id = db.Column(db.Integer, db.ForeignKey('categoria.id'), nullable=True)
    subcategoria_id = db.Column(db.Integer, db.ForeignKey('subcategoria.id'), nullable=True)

    # Campo legado (compatibilidade com dados antigos)
    categoria = db.Column(db.String(50), nullable=True)

    descricao = db.Column(db.Text, nullable=True)
    descricao_longa = db.Column(db.Text, nullable=True)
    preco = db.Column(db.Float, nullable=False)
    imagens_url = db.Column(db.Text, nullable=True)
    link_mercadolivre = db.Column(db.String(500), nullable=True)
    whatsapp = db.Column(db.String(500), nullable=True)
    ativo = db.Column(db.Boolean, default=True, nullable=False)

    def get_todas_imagens(self):
        if self.imagens_url:
            return [img.strip() for img in self.imagens_url.split(',') if img.strip()]
        return []

    def get_imagem_principal(self):
        imgs = self.get_todas_imagens()
        return imgs[0] if imgs else ''

    def get_categoria_nome(self):
        """Retorna o nome da categoria, priorizando a relação nova."""
        if self.categoria_ref:
            return self.categoria_ref.nome
        return self.categoria or ''

    def get_subcategoria_nome(self):
        """Retorna o nome da subcategoria."""
        if self.subcategoria_ref:
            return self.subcategoria_ref.nome
        return ''


with app.app_context():
    db.create_all()


# ============================================================
# CONTEXT PROCESSOR
# ============================================================

@app.context_processor
def inject_globals():
    """Injeta variáveis globais em todos os templates."""
    return dict(
        categorias=Categoria.query.order_by(Categoria.nome).all(),
        whatsapp_global=WHATSAPP_GLOBAL
    )


# ============================================================
# DECORATOR: PROTEÇÃO DE ROTAS
# ============================================================

def login_obrigatorio(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'logado' not in session:
            flash('Faça login para acessar o painel.', 'warning')
            return redirect(url_for('login'))
        return f(*args, **kwargs)
    return decorated_function


# ============================================================
# ROTAS DE AUTENTICAÇÃO
# ============================================================

@app.route('/login', methods=['GET', 'POST'])
def login():
    # Se já logado, redireciona
    if 'logado' in session:
        return redirect(url_for('admin'))

    erro = None
    if request.method == 'POST':
        usuario = request.form.get('usuario', '').strip()
        senha = request.form.get('senha', '')
        if usuario == USUARIO_ADMIN and senha == SENHA_ADMIN:
            session['logado'] = True
            return redirect(url_for('admin'))
        else:
            erro = 'Usuário ou senha incorretos. Tente novamente.'

    return render_template('login.html', erro=erro)


@app.route('/logout')
def logout():
    session.pop('logado', None)
    return redirect(url_for('index'))


# ============================================================
# ROTAS DA VITRINE (CLIENTE)
# ============================================================

@app.route('/')
def index():
    cat_id = request.args.get('cat', type=int)
    sub_id = request.args.get('sub', type=int)

    if sub_id:
        produtos = Produto.query.filter_by(subcategoria_id=sub_id, ativo=True).all()
    elif cat_id:
        produtos = Produto.query.filter_by(categoria_id=cat_id, ativo=True).all()
    else:
        produtos = None  # Não usado quando vitrine_home está ativo

    # Home sem filtros: agrupar por categoria
    vitrine_home = {}
    if not cat_id and not sub_id:
        cats = Categoria.query.order_by(Categoria.nome).all()
        for cat in cats:
            items = Produto.query.filter_by(categoria_id=cat.id, ativo=True).all()
            if items:
                vitrine_home[cat.nome] = items

    return render_template(
        'index.html',
        produtos=produtos or [],
        vitrine_home=vitrine_home,
        cat_id=cat_id,
        sub_id=sub_id,
    )


@app.route('/produto/<int:id>')
def produto_detalhes(id):
    produto = Produto.query.get_or_404(id)
    return render_template('detalhes.html', p=produto)


@app.route('/busca')
def busca():
    q = request.args.get('q', '').strip()
    if not q:
        return redirect(url_for('index'))
    
    # Pesquisa simples ignorando case (usando ilike) e apenas ativos
    termo = f"%{q}%"
    produtos = Produto.query.filter(
        Produto.ativo == True,
        db.or_(
            Produto.nome.ilike(termo),
            Produto.descricao.ilike(termo),
            Produto.descricao_longa.ilike(termo)
        )
    ).all()
    
    return render_template('index.html', produtos=produtos, termo_busca=q)


# ============================================================
# ROTAS DO PAINEL ADMIN
# ============================================================

@app.route('/admin/toggle-visibilidade/<int:id>', methods=['POST'])
@login_obrigatorio
def admin_toggle_visibilidade(id):
    produto = Produto.query.get_or_404(id)
    produto.ativo = not produto.ativo
    db.session.commit()
    
    estado = "visível na vitrine" if produto.ativo else "oculto"
    flash(f"Produto '{produto.nome}' agora está {estado}.", "success")
    return redirect(url_for('admin'))

def _processar_preco(valor_str):
    """Converte string de preço (123,45 ou 123.45) para float."""
    return float(valor_str.replace('.', '').replace(',', '.'))


def _processar_whatsapp(numero_raw, nome_produto):
    """Gera link de WhatsApp a partir de um número raw."""
    numero = numero_raw.strip()
    if numero and numero.isdigit():
        texto = urllib.parse.quote(f"Olá! Tenho interesse no produto: {nome_produto}")
        return f"https://wa.me/{numero}?text={texto}"
    return numero  # Se já for uma URL completa, retorna como está


def _processar_imagens(form):
    """Extrai e limpa URLs de imagem do formulário (imagem_1 a imagem_4)."""
    urls = []
    for i in range(1, 5):
        url = form.get(f'imagem_{i}', '').strip()
        if url:
            urls.append(url)
    return ','.join(urls)


@app.route('/admin', methods=['GET', 'POST'])
@login_obrigatorio
def admin():
    if request.method == 'POST':
        nome = request.form.get('nome', '').strip()
        if not nome:
            flash('Nome do produto é obrigatório.', 'error')
            return redirect(url_for('admin'))

        try:
            preco = _processar_preco(request.form.get('preco', '0'))
        except ValueError:
            flash('Preço inválido. Use o formato: 129,90', 'error')
            return redirect(url_for('admin'))

        cat_id = request.form.get('categoria_id') or None
        sub_id = request.form.get('subcategoria_id') or None

        novo = Produto(
            nome=nome,
            categoria_id=int(cat_id) if cat_id else None,
            subcategoria_id=int(sub_id) if sub_id else None,
            descricao=request.form.get('descricao_curta', ''),
            descricao_longa=request.form.get('descricao_longa', ''),
            preco=preco,
            imagens_url=_processar_imagens(request.form),
            link_mercadolivre=request.form.get('link_mercadolivre', '').strip(),
            whatsapp=_processar_whatsapp(
                request.form.get('whatsapp', ''),
                nome
            ),
        )
        db.session.add(novo)
        db.session.commit()
        flash(f'"{nome}" cadastrado com sucesso!', 'success')
        return redirect(url_for('admin'))

    produtos = Produto.query.order_by(Produto.id.desc()).all()
    
    # Estatísticas do Dashboard
    total_produtos = len(produtos)
    total_categorias = Categoria.query.count()
    valor_estoque = sum(p.preco for p in produtos)
    
    return render_template('admin.html', 
                           produtos=produtos,
                           total_produtos=total_produtos,
                           total_categorias=total_categorias,
                           valor_estoque=valor_estoque)


@app.route('/admin/editar/<int:id>', methods=['GET', 'POST'])
@login_obrigatorio
def editar(id):
    produto = Produto.query.get_or_404(id)

    if request.method == 'POST':
        nome = request.form.get('nome', '').strip()
        if not nome:
            flash('Nome do produto é obrigatório.', 'error')
            return redirect(url_for('editar', id=id))

        try:
            preco = _processar_preco(request.form.get('preco', '0'))
        except ValueError:
            flash('Preço inválido. Use o formato: 129,90', 'error')
            return redirect(url_for('editar', id=id))

        cat_id = request.form.get('categoria_id') or None
        sub_id = request.form.get('subcategoria_id') or None

        produto.nome = nome
        produto.categoria_id = int(cat_id) if cat_id else None
        produto.subcategoria_id = int(sub_id) if sub_id else None
        produto.descricao = request.form.get('descricao_curta', '')
        produto.descricao_longa = request.form.get('descricao_longa', '')
        produto.preco = preco
        produto.imagens_url = _processar_imagens(request.form)
        produto.link_mercadolivre = request.form.get('link_mercadolivre', '').strip()
        produto.whatsapp = _processar_whatsapp(
            request.form.get('whatsapp', ''),
            produto.nome
        )

        db.session.commit()
        flash(f'"{produto.nome}" atualizado com sucesso!', 'success')
        return redirect(url_for('admin'))

    imgs = produto.get_todas_imagens()
    img_urls = imgs + [''] * (4 - len(imgs))
    return render_template('editar.html', p=produto, imgs=img_urls)


@app.route('/admin/deletar/<int:id>', methods=['POST'])
@login_obrigatorio
def deletar(id):
    produto = Produto.query.get_or_404(id)
    nome = produto.nome
    db.session.delete(produto)
    db.session.commit()
    flash(f'"{nome}" excluído com sucesso.', 'success')
    return redirect(url_for('admin'))


# ============================================================
# ROTAS: GESTÃO DE CATEGORIAS E SUBCATEGORIAS
# ============================================================

@app.route('/admin/categorias', methods=['POST'])
@login_obrigatorio
def admin_add_categoria():
    nome = request.form.get('nome', '').strip()
    if nome:
        existe = Categoria.query.filter_by(nome=nome).first()
        if existe:
            flash(f'Categoria "{nome}" já existe.', 'warning')
        else:
            db.session.add(Categoria(nome=nome))
            db.session.commit()
            flash(f'Categoria "{nome}" criada!', 'success')
    return redirect(url_for('admin') + '#categorias')


@app.route('/admin/categorias/deletar/<int:id>', methods=['POST'])
@login_obrigatorio
def admin_del_categoria(id):
    cat = Categoria.query.get_or_404(id)
    nome = cat.nome
    db.session.delete(cat)
    db.session.commit()
    flash(f'Categoria "{nome}" e todos os vínculos foram excluídos.', 'success')
    return redirect(url_for('admin') + '#categorias')


@app.route('/admin/subcategorias', methods=['POST'])
@login_obrigatorio
def admin_add_subcategoria():
    nome = request.form.get('nome', '').strip()
    cat_id = request.form.get('categoria_id')
    if nome and cat_id:
        db.session.add(Subcategoria(nome=nome, categoria_id=int(cat_id)))
        db.session.commit()
        flash(f'Subcategoria "{nome}" criada!', 'success')
    return redirect(url_for('admin') + '#categorias')


@app.route('/admin/subcategorias/deletar/<int:id>', methods=['POST'])
@login_obrigatorio
def admin_del_subcategoria(id):
    sub = Subcategoria.query.get_or_404(id)
    nome = sub.nome
    db.session.delete(sub)
    db.session.commit()
    flash(f'Subcategoria "{nome}" excluída.', 'success')
    return redirect(url_for('admin') + '#categorias')


# ============================================================
# INICIALIZAÇÃO
# ============================================================

def auto_seed():
    """Popula o banco automaticamente se estiver vazio."""
    if Categoria.query.count() > 0:
        return  # já tem dados

    categorias_data = [
        {"nome": "Roupas"},
        {"nome": "Joias"},
        {"nome": "Chocolates"},
    ]
    cat_map = {}
    for c in categorias_data:
        cat = Categoria(nome=c["nome"])
        db.session.add(cat)
        db.session.flush()
        cat_map[c["nome"]] = cat.id

    produtos_data = [
        {"nome": "Argola Clássica Zircônia", "categoria": "Joias", "preco": 65.0,
         "descricao": "Argolinha clássica com zircônias. Perfeita para o primeiro ou segundo furo.",
         "descricao_longa": "Zircônias de classe AAA\n- Fecho click de alta segurança",
         "imagens_url": "https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?ixlib=rb-4.0.3&w=800&q=80,https://images.unsplash.com/photo-1602751584552-8ba73aad10e1?ixlib=rb-4.0.3&w=800&q=80"},
        {"nome": "Bracelete Rígido Geométrico", "categoria": "Joias", "preco": 149.0,
         "descricao": "Peça de afirmação para os pulsos. Acabamento polido espelhado.",
         "descricao_longa": "Bracelete rígido com linhas geométricas fortes.\n- Material: Aço Inoxidável Antialérgico\n- Banhado a Ouro Amarelo 18k",
         "imagens_url": "https://images.unsplash.com/photo-1611591437281-460bfbe1220a?ixlib=rb-4.0.3&w=800&q=80,https://images.unsplash.com/photo-1599643478524-fb66f70d00f8?ixlib=rb-4.0.3&w=800&q=80"},
        {"nome": "Vestido Midi Estampa Floral Exclusiva", "categoria": "Roupas", "preco": 129.9,
         "descricao": "Vestido midi em tecido leve estilo viscolinho. Acompanha cinto do mesmo tecido.",
         "descricao_longa": "O vestido perfeito para dias ensolarados.\n- Modelagem Veste do 38 ao 42\n- Composição: 100% Viscolinho Premium",
         "imagens_url": "https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?ixlib=rb-4.0.3&w=800&q=80,https://images.unsplash.com/photo-1550639525-c97d455acf70?ixlib=rb-4.0.3&w=800&q=80",
         "link_mercadolivre": "https://mercadolivre.com.br"},
        {"nome": "Trench Coat Elegance Caramelo", "categoria": "Roupas", "preco": 289.0,
         "descricao": "Casaco sobretudo com faixa para amarração e botões perolados.",
         "descricao_longa": "Um item essencial no guarda-roupa de inverno.",
         "imagens_url": "https://images.unsplash.com/photo-1591047139829-d91aecb6caea?ixlib=rb-4.0.3&w=800&q=80",
         "link_mercadolivre": "https://mercadolivre.com.br"},
        {"nome": "Conjunto Alfaiataria Risca de Giz", "categoria": "Roupas", "preco": 320.0,
         "descricao": "Blazer alongado e calça reta com caimento perfeito, estampa clássica.",
         "descricao_longa": "O Conjunto Alfaiataria traz muito poder ao Office Look.\n- Ternos (Blazer + Calça)\n- Ombros estruturados",
         "imagens_url": "https://images.unsplash.com/photo-1584273143981-41c073dfe8f8?ixlib=rb-4.0.3&w=800&q=80"},
        {"nome": "Caixa Presente Trufas Sortidas", "categoria": "Chocolates", "preco": 54.9,
         "descricao": "Caixa com 12 trufas sortidas nos sabores tradicionais e frutados.",
         "descricao_longa": "3x Maracujá\n- 3x Brigadeiro Intenso\n- 3x Branco clássico\n- 3x Morango fresco",
         "imagens_url": "https://images.unsplash.com/photo-1549007994-cb92caebd54b?ixlib=rb-4.0.3&w=800&q=80",
         "link_mercadolivre": "https://mercadolivre.com.br"},
        {"nome": "Anel Solitário Classic", "categoria": "Joias", "preco": 129.9,
         "descricao": "O símbolo máximo da sofisticação.",
         "descricao_longa": "Peça essencial em qualquer coleção. Zircônia central com brilho de diamante.",
         "imagens_url": "https://images.unsplash.com/photo-1605100804763-247f67b3557e?q=80&w=600"},
        {"nome": "Pulseira Elo Português", "categoria": "Joias", "preco": 198.0,
         "descricao": "Robusta e elegante para o dia a dia.",
         "descricao_longa": "Folheada a ouro, essa pulseira combina com diversos estilos.",
         "imagens_url": "https://images.unsplash.com/photo-1611591437281-460bfbe1220a?q=80&w=600"},
        {"nome": "Vestido Alfaiataria Noite", "categoria": "Roupas", "preco": 459.0,
         "descricao": "Corte impecável para noites inesquecíveis.",
         "descricao_longa": "Tecido estruturado que valoriza a silhueta com extremo conforto.",
         "imagens_url": "https://images.unsplash.com/photo-1539109136881-3be0616acf4b?q=80&w=600"},
        {"nome": "Blusa Seda Soft", "categoria": "Roupas", "preco": 189.0,
         "descricao": "Toque suave e brilho discreto da seda.",
         "descricao_longa": "Item versátil para o closet feminino, transita bem do escritório ao jantar.",
         "imagens_url": "https://images.unsplash.com/photo-1582533561751-ef6f6ab93a2e?q=80&w=600"},
        {"nome": "Calça Flare Premium", "categoria": "Roupas", "preco": 229.0,
         "descricao": "Modelagem que alonga e sofistica.",
         "descricao_longa": "Jeans de alta gramatura com elastano que mantém a forma o dia todo.",
         "imagens_url": "https://images.unsplash.com/photo-1541099649105-f69ad21f3246?q=80&w=600"},
        {"nome": "Blazer Minimalist Blue", "categoria": "Roupas", "preco": 580.0,
         "descricao": "Estrutura moderna na cor da temporada.",
         "descricao_longa": "Forro acetinado e botões banhados, um investimento em estilo.",
         "imagens_url": "https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=600"},
        {"nome": "Cardigan Tricot Lux", "categoria": "Roupas", "preco": 195.0,
         "descricao": "O aconchego com fios de alta qualidade.",
         "descricao_longa": "Tramas trabalhadas que dão um ar artesanal e chique.",
         "imagens_url": "https://images.unsplash.com/photo-1516762689617-e1cffcef479d?q=80&w=600"},
        {"nome": "Macacão Longo Chic", "categoria": "Roupas", "preco": 349.0,
         "descricao": "Look completo e poderoso em peça única.",
         "descricao_longa": "Decote V e amarração na cintura para ajuste personalizado.",
         "imagens_url": "https://images.unsplash.com/photo-1434389677669-e08b4cac3105?q=80&w=600"},
        {"nome": "Body Renda Delicate", "categoria": "Roupas", "preco": 128.0,
         "descricao": "Sensualidade e delicadeza nos detalhes.",
         "descricao_longa": "Renda francesa aplicada e fecho invisível para maior conforto.",
         "imagens_url": "https://images.unsplash.com/photo-1539109136881-3be0616acf4b?q=80&w=600"},
        {"nome": "Shorts Couro Eco", "categoria": "Roupas", "preco": 179.0,
         "descricao": "Atitude e modernidade para seu look.",
         "descricao_longa": "Material sintético de alta durabilidade com toque macio similar ao couro natural.",
         "imagens_url": "https://images.unsplash.com/photo-1591369822096-ffd140ec948f?q=80&w=600"},
        {"nome": "Cesta Presente Especial", "categoria": "Chocolates", "preco": 125.0,
         "descricao": "O presente perfeito para qualquer ocasião.",
         "descricao_longa": "Mix de tabletes, trufas e itens exclusivos da coleção sazonal.",
         "imagens_url": "https://images.unsplash.com/photo-1553452118-621e1f860f43?q=80&w=600"},
    ]

    whatsapp_num = WHATSAPP_GLOBAL
    for p in produtos_data:
        cat_id = cat_map.get(p.get("categoria", ""), 1)
        msg = urllib.parse.quote(f'Olá! Tenho interesse no produto: {p["nome"]}')
        wa_link = f'https://wa.me/{whatsapp_num}?text={msg}'
        prod = Produto(
            nome=p["nome"],
            categoria_id=cat_id,
            categoria=p.get("categoria", ""),
            descricao=p.get("descricao", ""),
            descricao_longa=p.get("descricao_longa", ""),
            preco=p.get("preco", 0),
            imagens_url=p.get("imagens_url", ""),
            link_mercadolivre=p.get("link_mercadolivre", ""),
            whatsapp=wa_link,
            ativo=True,
        )
        db.session.add(prod)
    db.session.commit()


with app.app_context():
    db.create_all()
    auto_seed()

if __name__ == '__main__':
    debug_mode = os.environ.get('FLASK_DEBUG', 'false').lower() == 'true'
    app.run(debug=debug_mode)
