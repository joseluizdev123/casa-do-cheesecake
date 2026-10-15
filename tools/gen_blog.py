"""Gera as seções da página Blog (src/paginas/blog/*.html).

Estrutura do Blog da Studio Artemis (Figma i23qiP70wQhTOjyhYLT9LM, 8294:1703):
topo com as categorias → artigo em destaque → uma seção por categoria com 3
artigos e "Ver todos os artigos". O visual é só o da home:
  topo        cabeçalho da seção 05 (título + texto) + botões secundários
  destaque    bloco "Produção própria" (06): foto | categoria, título, resumo, botão
  categorias  seção 05 ("O que faz um cheesecake…"): 3 cards texto + foto 4:3
Os posts são os do blog atual (WordPress), em tools/blog_posts.json: o mais
recente fica em destaque e cada categoria mostra os 3 mais recentes com capa.

Uso: python tools/gen_blog.py
"""
import json
from html import escape
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
OUT = ROOT / "src" / "paginas" / "blog"
POSTS = json.loads((ROOT / "tools" / "blog_posts.json").read_text(encoding="utf-8"))
BLOG_ANTIGO = "https://acasadocheesecake.com.br/blog/"

CATEGORIAS = [
    ("delivery", "Delivery em São Paulo",
     "Onde pedir, quais bairros recebem, prazos e formas de pedir o seu cheesecake em São Paulo."),
    ("presentes", "Presentes e ocasiões",
     "Cheesecake para presentear e para as datas especiais: Páscoa, Natal, aniversários e festas."),
    ("tamanhos", "Tamanhos, sabores e preços",
     "Qual tamanho e qual sabor escolher, fatia ou inteiro, e quanto cada opção rende."),
    ("conservacao", "Conservação e dicas",
     "Quanto tempo dura, como guardar na geladeira e no freezer e como descongelar."),
    ("receitas", "Receitas e curiosidades",
     "Receitas, diferenças entre os tipos de cheesecake e o que faz um NY Cheesecake ser autêntico."),
]
NOME_CAT = {k: nome for k, nome, _ in CATEGORIAS}
MESES = "jan fev mar abr mai jun jul ago set out nov dez".split()


def data(iso):
    a, m, d = iso.split("-")
    return f"{int(d)} {MESES[int(m) - 1]} {a}"


def topo():
    botoes = "\n".join(
        f'          <li><a class="btn-link" href="#{k}">{escape(nome)}</a></li>' for k, nome, _ in CATEGORIAS)
    return f'''<section class="diferenciais blog-topo" id="blog" aria-labelledby="blog-titulo">
  <div class="container diferenciais__inner">
    <header class="diferenciais__head">
      <h1 class="diferenciais__title" id="blog-titulo">Blog</h1>
      <p class="diferenciais__lead">Dicas para escolher, pedir, presentear e conservar o seu cheesecake, além de receitas e curiosidades sobre o autêntico NY Cheesecake.</p>
    </header>
    <nav aria-label="Categorias do blog">
      <ul class="blog-topo__categorias" role="list">
{botoes}
      </ul>
    </nav>
  </div>
</section>
'''


def destaque():
    p = POSTS["destaque"][0]
    w, h = p["capa_wh"]
    cat = NOME_CAT["delivery"]
    return f'''<section class="sobre blog-destaque" id="destaque" aria-labelledby="destaque-titulo">
  <div class="sobre__inner container">
    <figure class="sobre__media">
      <img class="sobre__foto" src="assets/images/{p["capa"]}" width="{w}" height="{h}" decoding="async" alt="">
      <p class="diferenciais__tag blog-tag"><span class="diferenciais__tag-text">Em destaque</span></p>
    </figure>

    <div class="sobre__content">
      <div class="sobre__text">
        <p class="blog-meta">{escape(cat)} · <time datetime="{p["date"]}">{data(p["date"])}</time></p>
        <h2 class="sobre__title" id="destaque-titulo">{escape(p["title"])}</h2>
        <div class="sobre__body">
          <p>{escape(p["excerpt"])}</p>
        </div>
      </div>
      <a class="btn btn--primary" href="{p["link"]}" target="_blank" rel="noopener" aria-label="Ler o artigo: {escape(p["title"])}">Ler artigo</a>
    </div>
  </div>
</section>
'''


def card(p):
    w, h = p["capa_wh"]
    return f'''      <li class="diferenciais__card blog-card">
        <div class="diferenciais__body">
          <h3 class="diferenciais__card-title"><a class="blog-card__link" href="{p["link"]}" target="_blank" rel="noopener">{escape(p["title"])}</a></h3>
          <p class="diferenciais__card-text">{escape(p["excerpt"])}</p>
        </div>
        <figure class="diferenciais__media">
          <img class="diferenciais__img" src="assets/images/{p["capa"]}" alt="" width="{w}" height="{h}" loading="lazy" decoding="async">
          <p class="diferenciais__tag blog-tag"><time class="diferenciais__tag-text" datetime="{p["date"]}">{data(p["date"])}</time></p>
        </figure>
      </li>'''


def categoria(k, nome, lead):
    cards = "\n".join(card(p) for p in POSTS[k])
    return f'''<section class="diferenciais blog-categoria" id="{k}" aria-labelledby="{k}-titulo">
  <div class="container diferenciais__inner">
    <header class="diferenciais__head">
      <h2 class="diferenciais__title" id="{k}-titulo">{escape(nome)}</h2>
      <p class="diferenciais__lead">{escape(lead)}</p>
    </header>
    <ul class="diferenciais__list" role="list">
{cards}
    </ul>
    <div class="blog-categoria__mais">
      <a class="btn btn--primary" href="{BLOG_ANTIGO}" target="_blank" rel="noopener">Ver todos os artigos</a>
    </div>
  </div>
</section>
'''


def main():
    OUT.mkdir(parents=True, exist_ok=True)
    for old in OUT.glob("*.html"):
        old.unlink()
    (OUT / "01-topo.html").write_text(topo(), encoding="utf-8")
    (OUT / "02-destaque.html").write_text(destaque(), encoding="utf-8")
    for i, (k, nome, lead) in enumerate(CATEGORIAS, 3):
        (OUT / f"{i:02d}-{k}.html").write_text(categoria(k, nome, lead), encoding="utf-8")
    print(f"{2 + len(CATEGORIAS)} seções em {OUT}")


if __name__ == "__main__":
    main()
