"""Gera as seções da página Cardápio (src/paginas/cardapio/*.html).

A página não tem Figma: cada categoria é a seção "Nossos cheesecakes" da home
(03-cardapio) repetida — mesmo cabeçalho, mesmo card, mesmo carrossel no
mobile — só com outros produtos. Preços e links vêm do cardápio do Brendi.
Os inteiros mostram a foto do bolo inteiro; a seção "Fatias" mostra a foto da
fatia (pedido do cliente: quando há as duas fotos, a fatia ganha seção própria).

Fotos: tools/fotos_cardapio.py converte fotos_recortadas/sem_fundo em
assets/images/cardapio-<produto>[-fatia].webp; aqui vale a primeira da lista
de cada card que existir.

Tema WordPress: grava também theme/casadocheesecake/inc/data/produtos.json
(categorias, produtos, banner e textos da seção Fatias), que o importador do
tema (inc/pages/10-produtos.php) usa para criar o conteúdo inicial.

Uso: python tools/gen_cardapio.py   (depois de editar SABORES/SECOES abaixo)
"""
import json
from html import escape
from pathlib import Path

from PIL import Image

ROOT = Path(__file__).resolve().parent.parent
OUT = ROOT / "src" / "paginas" / "cardapio"
IMG = ROOT / "assets" / "images"
BRENDI = "https://pedido.brendi.com.br/a-casa-do-cheesecake/produto/"
JSON_TEMA = ROOT / "theme" / "casadocheesecake" / "inc" / "data" / "produtos.json"

# cores: só as da paleta da home (aprovado pelo cliente)
VERMELHO, AMARELO, ROSA, AZUL = "red-700", "yellow-400", "pink-200", "blue-200"
ESCURO, CREME = "red-950", "cream-100"
# o tema guarda a cor do card em hex (assets/css/tokens.css)
HEX = {VERMELHO: "#b0282e", AMARELO: "#ffb82e", ROSA: "#ffc0c5", AZUL: "#b5d9fb",
       ESCURO: "#150506", CREME: "#f6e7c6"}

FATIA, P600, P12, P18 = "Fatia 150 g", "600 g · 6 mini fatias", "1,2 kg · serve 8", "1,8 kg · serve 10 a 12"

# nome: (descrição, cor, fotos do inteiro, fotos da fatia)
SABORES = {
    "Frutas Vermelhas": ("Base assada de cream cheese com calda de frutas vermelhas naturais cozidas.", VERMELHO,
                         ["03-cardapio-frutas-vermelhas.webp"], ["sabor-frutas-vermelhas.png"]),
    "Doce de Leite": ("A mesma base assada, coberta com doce de leite.", AMARELO,
                      ["03-cardapio-doce-de-leite.webp"], ["sabor-doce-de-leite.png"]),
    "Morangos": ("Calda de morangos frescos sobre a base assada.", ROSA,
                 ["03-cardapio-morangos.webp"], ["sabor-morangos.png"]),
    "Blue Berry": ("Calda de blueberry, ácida na medida para cortar o creme.", AZUL,
                   ["03-cardapio-blue-berry.webp"], ["sabor-blue-berry.png"]),
    "Chocolate": ("A mesma base assada, coberta com calda de chocolate.", ESCURO,
                  ["cardapio-chocolate.webp"], ["cardapio-chocolate-fatia.webp"]),
    "Goiaba": ("A mesma base assada, coberta com calda de goiaba.", ROSA,
               ["cardapio-goiaba.webp"], ["cardapio-goiaba-fatia.webp"]),
    "Amora": ("Calda de amoras sobre a base assada.", VERMELHO,
              ["cardapio-amora.webp"], ["cardapio-amora-fatia.webp"]),
    "Maracujá": ("Calda de maracujá sobre a base assada.", AMARELO,
                 ["cardapio-maracuja.webp"], ["cardapio-maracuja-fatia.webp"]),
    "Plain": ("A receita original, sem cobertura, para comer pura ou com a sua calda.", CREME,
              ["cardapio-plain.webp"], ["cardapio-plain-fatia.webp"]),
    "Morangos ao Porto": ("Morangos cozidos por 24 horas no vinho do Porto, até sair todo o álcool.", ROSA,
                          ["cardapio-morangos-ao-porto.webp"], ["cardapio-morangos-ao-porto-fatia.webp"]),
    "Limone": ("Creme de limão siciliano, prosecco e cream cheese, com calda de morango.", AMARELO,
               ["cardapio-limone.webp"], ["cardapio-limone-fatia.webp"]),
    "Duplo Chocolate": ("Base de biscoito de chocolate, chocolate na massa e calda de chocolate.", ESCURO,
                        ["cardapio-duplo-chocolate.webp"], ["cardapio-duplo-chocolate-fatia.webp"]),
    "Oreo": ("Oreo triturado na massa e um biscoito inteiro em cada fatia.", ESCURO,
             ["cardapio-oreo.webp"], ["cardapio-oreo-fatia.webp"]),
    "Duplo Frutas Vermelhas": ("Morangos, amoras, framboesas e blueberry na massa e calda de frutas vermelhas.", VERMELHO,
                               ["cardapio-duplo-frutas-vermelhas.webp"], ["cardapio-duplo-frutas-vermelhas-fatia.webp"]),
    "Duplo Chocolate com Doce de Leite": ("O Duplo Chocolate com calda de doce de leite no lugar da de chocolate.", ESCURO,
                                          ["cardapio-duplo-chocolate-com-doce-de-leite.webp"],
                                          ["cardapio-duplo-chocolate-com-doce-de-leite-fatia.webp"]),
}


def inteiro(nome, linhas, tag=None):
    desc, cor, fotos, _ = SABORES[nome]
    return dict(nome=nome, desc=desc, cor=cor, fotos=fotos, linhas=linhas, tag=tag)


def fatia(nome, preco, slug):
    desc, cor, fotos_inteiro, fotos = SABORES[nome]
    # sem foto da fatia ainda: usa a do inteiro até chegar
    return dict(nome=nome, desc=desc, cor=cor, fotos=fotos + fotos_inteiro, linhas=[(preco, FATIA, slug)])


def outro(nome, desc, foto, linhas, alt):
    return dict(nome=nome, desc=desc, cor=CREME, fotos=[foto], linhas=linhas, alt=alt)


INTRO_TITULO = "Nossos sabores de cheesecake"
INTRO_LEAD = "Todos assados na nossa fábrica em São Paulo. Os cheesecakes inteiros chegam congelados, para não rachar no trajeto, e precisam de cerca de 2 horas em temperatura ambiente para descongelar. As fatias chegam refrigeradas, prontas para servir. Escolha o tamanho pela quantidade de pessoas."

SECOES = [
    # pedido do cliente: título e texto no topo da página, banner amarelo logo abaixo
    dict(id="produtos", h="h1", titulo=INTRO_TITULO, lead=INTRO_LEAD, so_cabecalho=True, cards=[]),
    # banner dos 15% da home (02-primeira-fatia), repetido daqui — editar lá.
    # Pedido do cliente: nesta página a fatia da frente é a de goiaba.
    dict(id="desconto", repeat="02-primeira-fatia", trocas=[
        # mesma fatia do card, espelhada e no tamanho/posição da de chocolate da home
        ("assets/images/03b-fatia-chocolate-calda.webp", "assets/images/cardapio-banner-goiaba-fatia.webp"),
        ('alt="Fatia de cheesecake de chocolate com calda de chocolate escorrendo pela lateral, ao lado de pedaços de chocolate"',
         'alt="Fatia de cheesecake de goiaba com calda de goiaba"'),
    ]),
    dict(
        # o cabeçalho fica na seção "produtos", acima do banner
        id="cheesecakes", titulo=INTRO_TITULO, lead=INTRO_LEAD, sem_cabecalho=True, rotulo="produtos-titulo",
        cards=[
            inteiro("Frutas Vermelhas", [
                ("R$ 99", P600, "cheesecake-de-frutas-vermelhas-600g-rende-6-mini-fatias"),
                ("R$ 185", P12, "cheesecake-de-frutas-vermelhas-12-kg-rende-8-fatias"),
                ("R$ 252", P18, "cheesecake-de-frutas-vermelhas-18-kg-rende-de-10-a-12-fatias"),
            ], tag="Mais pedido"),
            inteiro("Doce de Leite", [
                ("R$ 99", P600, "cheesecake-de-doce-de-leite-600gr-rende-6-mini-fatias"),
                ("R$ 185", P12, "cheesecake-de-doce-de-leite-12-kg-rende-8-fatias"),
                ("R$ 252", P18, "cheesecake-de-doce-de-leite-18-kg-rende-de-10-a-12-fatias"),
            ]),
            inteiro("Morangos", [
                ("R$ 99", P600, "cheesecake-de-morango-600gr-rende-6-mini-fatias"),
                ("R$ 185", P12, "cheesecake-de-morangos-12-kg-rende-8-fatias"),
                ("R$ 252", P18, "cheesecake-de-morango-18-kg-rende-de-10-a-12-fatias"),
            ]),
            inteiro("Blue Berry", [
                ("R$ 99", P600, "cheesecake-de-blue-berry-600gr-rende-6-mini-fatias"),
                ("R$ 185", P12, "cheesecake-de-blue-berry-12-kg-rende-8-fatias"),
                ("R$ 252", P18, "cheesecake-de-blue-berry-18-kg-rende-de-10-a-12-fatias"),
            ]),
            inteiro("Chocolate", [
                ("R$ 99", P600, "cheesecake-de-chocolate-600gr-rende-6-mini-fatias"),
                ("R$ 252", P18, "cheesecake-de-chocolate-18-kg-rende-de-10-a-12-fatias"),
            ]),
            inteiro("Goiaba", [
                ("R$ 99", P600, "cheesecake-de-goiaba-600gr-rende-6-mini-fatias"),
                ("R$ 252", P18, "cheesecake-de-goiaba-18-kg-rende-de-10-a-12-fatias"),
            ]),
            inteiro("Amora", [
                ("R$ 99", P600, "cheesecake-de-amora-600gr-rende-6-mini-fatias"),
                ("R$ 252", P18, "cheesecake-de-amora-18-kg-rende-de-10-a-12-fatias"),
            ]),
            inteiro("Maracujá", [
                ("R$ 99", P600, "cheesecake-de-frutas-vermelhas-600g-rende-6-mini-fatias-copia"),
                ("R$ 252", P18, "cheesecake-de-damasco-18-kg-rende-de-10-a-12-fatias"),
            ]),
            inteiro("Plain", [
                ("R$ 89", "500 g · 6 mini fatias", "cheesecake-plain-500gr-sem-cobertura-rende-6-mini-fatias"),
                ("R$ 215", "1,5 kg · serve 10 a 12", "cheesecake-15-kg-sem-cobertura-rende-10-a-12-fatias"),
            ]),
        ],
    ),
    dict(
        id="especiais", titulo="Cheesecakes especiais",
        lead="Receitas da casa que vão além da calda: chocolate, Oreo ou frutas na massa, e o creme de limão siciliano do Limone. O cheesecake inteiro de 1,2 kg serve 8 pessoas e precisa ser reservado com 72 horas de antecedência.",
        cards=[
            inteiro("Limone", [("R$ 205", P12, "cheesecake-limone-12-kg-acompanha-calda-de-morango-rende-8-fatias")]),
            inteiro("Duplo Chocolate", [("R$ 205", P12, "cheesecake-duplo-chocolate-12kg-acompanha-calda-de-chocolate-rende-8-fatias")]),
            inteiro("Oreo", [("R$ 205", P12, "cheesecake-de-oreo-12kg-acompanha-calda-de-doce-de-leite-rende-8-fatias")]),
            inteiro("Duplo Frutas Vermelhas", [("R$ 205", P12, "cheesecake-duplo-frutas-vermelhas-12-kg-acompanha-calda-de-frutas-vermelhas")]),
        ],
    ),
    dict(
        id="fatias", titulo="Fatias",
        lead="Fatias de 150 g, para uma pessoa. Chegam refrigeradas, prontas para servir, numa caixinha com a calda à parte para colocar na hora.",
        cards=[
            fatia("Frutas Vermelhas", "R$ 28,90", "cheesecake-de-frutas-vermelhas-fatia"),
            fatia("Doce de Leite", "R$ 28,90", "cheesecake-de-doce-de-leite-fatia"),
            fatia("Morangos", "R$ 28,90", "cheesecake-de-morango-fatia"),
            fatia("Blue Berry", "R$ 28,90", "cheesecake-de-blueberry-fatia"),
            fatia("Chocolate", "R$ 28,90", "cheesecake-de-chocolate-fatia"),
            fatia("Goiaba", "R$ 28,90", "cheesecake-de-goiaba-fatia"),
            fatia("Amora", "R$ 28,90", "cheesecake-de-amoras"),
            fatia("Plain", "R$ 25,90", "cheesecake-de-plain-fatia-sem-cobertura"),
            fatia("Morangos ao Porto", "R$ 31,90", "cheesecake-de-morangos-ao-porto-fatia"),
            fatia("Limone", "R$ 31,90", "cheesecake-limone-com-calda-de-morango-fatia"),
            fatia("Duplo Chocolate", "R$ 31,90", "cheesecake-duplo-chocolate-fatia"),
            fatia("Oreo", "R$ 31,90", "cheesecake-de-oreo-fatia"),
            fatia("Duplo Frutas Vermelhas", "R$ 31,90", "cheesecake-duplo-frutas-vermelhas-fatia"),
            fatia("Duplo Chocolate com Doce de Leite", "R$ 31,90", "cheesecake-duplo-chocolate-com-doce-de-leite-fatia"),
        ],
    ),
    dict(
        id="bolos", titulo="Bolos especiais",
        lead="Bolos de 1,5 kg que servem de 10 a 12 pessoas, feitos sob encomenda. Precisam ser reservados com 72 horas de antecedência.",
        cards=[
            outro("Ninho com Morango", "Bolo de leite Ninho com creme de morangos frescos e morangos por cima.",
                  "cardapio-bolo-ninho-com-morango.webp", [("R$ 198", "1,5 kg · serve 10 a 12", "bolo-de-ninho-com-morango")],
                  "Bolo de Ninho com Morango"),
            outro("Brigadeiro", "Camadas de bolo envoltas em brigadeiro cremoso.",
                  "cardapio-bolo-brigadeiro.webp", [("R$ 198", "1,5 kg · serve 10 a 12", "bolo-de-chcolate-trufado")],
                  "Bolo de Brigadeiro"),
            outro("Abacaxi com Coco", "Recheado com pedaços de abacaxi e coberto com coco ralado.",
                  "cardapio-bolo-abacaxi-com-coco.webp", [("R$ 198", "1,5 kg · serve 10 a 12", "bolo-ded-abacaxi-com-coco-15-kg")],
                  "Bolo de Abacaxi com Coco"),
            outro("Do seu jeito", "Conte na observação do pedido como quer o bolo e a gente prepara.",
                  "cardapio-bolo-do-seu-jeito.webp", [("R$ 250", "1,5 kg · serve 10 a 12", "bolo-de-abacaxi-com-coco")],
                  "Bolo do seu jeito"),
        ],
    ),
    dict(
        id="brownie-velas-cartao", titulo="Brownie, velas e cartão",
        lead="Brownie de chocolate com calda para dividir, e velas e cartão com mensagem para completar o presente.",
        cards=[
            outro("Brownie", "Chocolate premium com cacau em alta concentração e calda de chocolate.", "cardapio-brownie.webp", [
                ("R$ 140", "Com calda · 1 kg · serve 10", "brownie-tradicional-de-chocolate-com-calda-de-chocolate-1kg"),
                ("R$ 120", "Sem calda", "brownie-tradicional-de-chocolate"),
            ], "Brownie de chocolate"),
            outro("Velas", "Velinha de aniversário para o cheesecake ou o bolo.", "cardapio-velas.webp", [
                ("R$ 4,50", "Azul · unidade", "velinha-azul-unidade"),
                ("R$ 4,50", "Rosa · unidade", "velinha-rosa-unidade"),
            ], "Velinhas azul e rosa"),
            outro("Cartão", "Escreva na observação do pedido: de, para e a mensagem.", "cardapio-cartao.webp", [
                ("R$ 5", "Cartão com mensagem", "cartao"),
            ], "Cartão com mensagem"),
        ],
    ),
]


def _foto(c):
    return next((f for f in c["fotos"] if (IMG / f).exists()), None)


def _img_tag(c):
    f = _foto(c)
    if not f:
        return ""
    w, h = Image.open(IMG / f).size
    if c.get("alt"):
        alt = c["alt"]
    elif "fatia" in f or f.startswith("sabor-"):
        alt = f"Fatia de cheesecake sabor {c['nome']}"
    else:
        alt = f"Cheesecake inteiro sabor {c['nome']}"
    return (f'\n                <div class="cardapio__photo">\n'
            f'                  <img class="cardapio__img cardapio__img--recorte" src="assets/images/{f}" '
            f'alt="{escape(alt)}" width="{w}" height="{h}" loading="lazy" decoding="async">\n'
            f'                </div>')


def _card(c):
    tag = ""
    if c.get("tag"):
        tag = ('\n                <p class="cardapio__tag">\n'
               '                  <img class="cardapio__tag-icon" src="assets/images/03-cardapio-icon-mais-pedido.svg" alt="" width="16" height="16">\n'
               f'                  <span class="cardapio__tag-text">{escape(c["tag"])}</span>\n'
               '                </p>')
    rows = []
    for i, (preco, tamanho, slug) in enumerate(c["linhas"]):
        kind = "primary" if i == 0 else "secondary"
        tam = escape(tamanho).replace(" · ", "&nbsp;· ")
        rows.append(f'''                  <li class="cardapio__row">
                    <p class="cardapio__option">
                      <span class="cardapio__price">{preco}</span>
                      <span class="cardapio__size">{tam}</span>
                    </p>
                    <a class="btn btn--{kind} cardapio__btn" href="{BRENDI}{slug}" target="_blank" rel="noopener" aria-label="Pedir {escape(c["nome"])} — {escape(tamanho)}">Pedir</a>
                  </li>''')
    return f'''            <li class="cardapio__card">
              <div class="cardapio__media" style="--cardapio-bg: var(--color-{c["cor"]})">{_img_tag(c)}{tag}
              </div>
              <div class="cardapio__content">
                <div class="cardapio__info">
                  <h3 class="cardapio__name">{escape(c["nome"])}</h3>
                  <p class="cardapio__desc">{escape(c["desc"])}</p>
                </div>
                <ul class="cardapio__prices" role="list">
{chr(10).join(rows)}
                </ul>
              </div>
            </li>'''


def _secao(s):
    sid, h, n = s["id"], s.get("h", "h2"), len(s["cards"])
    dots = "\n".join(
        f'              <button class="cardapio__dot" type="button" aria-controls="{sid}-lista" '
        f'aria-label="{escape(c["nome"])}, {i + 1} de {n}"{" aria-current=\"true\"" if i == 0 else ""} data-cardapio-dot></button>'
        for i, c in enumerate(s["cards"]))
    cards = "\n\n".join(_card(c) for c in s["cards"])
    cabecalho = f'''
        <header class="cardapio__head">
          <{h} class="cardapio__title" id="{sid}-titulo">{escape(s["titulo"])}</{h}>
          <p class="cardapio__lead">{escape(s["lead"])}</p>
        </header>
'''
    if s.get("so_cabecalho"):
        return f'''    <section class="cardapio cardapio--pagina cardapio--intro" id="{sid}" aria-labelledby="{sid}-titulo">
      <div class="container cardapio__inner">{cabecalho}      </div>
    </section>
'''
    if s.get("sem_cabecalho"):
        cabecalho = ""
    extra = " cardapio--sem-cabecalho" if s.get("sem_cabecalho") else ""
    return f'''    <section class="cardapio cardapio--pagina{extra}" id="{sid}" aria-labelledby="{s.get("rotulo", sid + "-titulo")}">
      <div class="container cardapio__inner">{cabecalho}
        <div class="cardapio__body" data-cardapio-carousel>
          <ul class="cardapio__grid" id="{sid}-lista" role="list" data-cardapio-track>
{cards}
          </ul>

          <div class="cardapio__controls" role="group" aria-label="Navegar pelos produtos" data-cardapio-controls hidden>
            <div class="cardapio__dots" data-cardapio-dots>
{dots}
            </div>
            <div class="cardapio__arrows">
              <button class="cardapio__nav cardapio__nav--prev" type="button" aria-controls="{sid}-lista" aria-label="Produto anterior" aria-disabled="true" data-cardapio-prev>
                <svg viewBox="0 0 20 20" width="20" height="20" fill="none" aria-hidden="true" focusable="false"><path d="M12.5 15 7.5 10l5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>
              <button class="cardapio__nav cardapio__nav--next" type="button" aria-controls="{sid}-lista" aria-label="Próximo produto" aria-disabled="false" data-cardapio-next>
                <svg viewBox="0 0 20 20" width="20" height="20" fill="none" aria-hidden="true" focusable="false"><path d="m7.5 15 5-5-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>
            </div>
            <p class="sr-only" aria-live="polite" aria-atomic="true" data-cardapio-status></p>
          </div>
        </div>
      </div>
    </section>
'''


def main():
    OUT.mkdir(parents=True, exist_ok=True)
    for old in OUT.glob("*.html"):
        old.unlink()
    for i, s in enumerate(SECOES, 1):
        f = OUT / f"{i:02d}-{s['id']}.html"
        if "repeat" in s:   # seção da home, montada pelo assemble.py (<!-- @repeat -->)
            trocas = "".join(f"\n  {a} => {b}" for a, b in s["trocas"])
            f.write_text(f"<!-- @repeat {s['repeat']}{trocas}\n-->\n", encoding="utf-8")
            print(f"{f.name}: seção {s['repeat']} da home")
            continue
        f.write_text(_secao(s), encoding="utf-8")
        sem_foto = [c["nome"] for c in s["cards"] if not _foto(c)]
        provisoria = [c["nome"] for c in s["cards"] if s["id"] == "fatias" and _foto(c) and _foto(c) != c["fotos"][0]]
        print(f"{f.name}: {len(s['cards'])} produtos"
              + (f" | sem foto: {', '.join(sem_foto)}" if sem_foto else "")
              + (f" | fatia com foto do inteiro: {', '.join(provisoria)}" if provisoria else ""))
    gravar_json_tema()


# Tema WordPress: cada seção com cards (menos "fatias") vira uma categoria, com
# esta ordem; cada produto vira um post com todas as linhas de preço (fatia +
# inteiros). Quem só tem fatia entra na categoria indicada aqui.
ORDEM_CATEGORIA = {"cheesecakes": 10, "especiais": 20, "bolos": 40, "brownie-velas-cartao": 50}
CATEGORIA_SO_FATIA = {"Morangos ao Porto": "cheesecakes", "Duplo Chocolate com Doce de Leite": "especiais"}


def _foto_rel(fotos):
    f = next((f for f in fotos if (IMG / f).exists()), None)
    return f"images/{f}" if f else ""


def gravar_json_tema():
    """Grava inc/data/produtos.json do tema (lido pelo importador do WordPress)."""
    secoes = {s["id"]: s for s in SECOES}
    intro = next(s for s in SECOES if s.get("so_cabecalho"))
    banner = next(s for s in SECOES if s.get("repeat") == "02-primeira-fatia")
    categorias, produtos = [], {}

    def produto(c, categoria):
        return dict(titulo=c["nome"], categoria=categoria, descricao=c["desc"], cor=HEX[c["cor"]],
                    tag=c.get("tag") or "", foto="", foto_alt=c.get("alt") or "", foto_fatia="", precos=[])

    for s in SECOES:
        if s["id"] not in ORDEM_CATEGORIA:
            continue
        # a 1ª categoria leva o título e o texto do topo da página
        titulo, lead = (intro["titulo"], intro["lead"]) if s.get("sem_cabecalho") else (s["titulo"], s["lead"])
        categorias.append(dict(slug=s["id"], nome=titulo, descricao=lead, ordem=ORDEM_CATEGORIA[s["id"]]))
        for c in s["cards"]:
            p = produtos.setdefault(c["nome"], produto(c, s["id"]))
            p["foto"] = _foto_rel(c["fotos"])
            p["precos"] += [[preco, tamanho, BRENDI + slug] for preco, tamanho, slug in c["linhas"]]
    for c in secoes["fatias"]["cards"]:
        if c["nome"] not in produtos:
            produtos[c["nome"]] = produto(c, CATEGORIA_SO_FATIA[c["nome"]])
        p = produtos[c["nome"]]
        # foto do inteiro para quem só tem fatia; a da fatia só se existir (senão o tema usa a destacada)
        p["foto"] = p["foto"] or _foto_rel(SABORES[c["nome"]][2])
        p["foto_fatia"] = _foto_rel(SABORES[c["nome"]][3])
        p["precos"] = [[preco, tamanho, BRENDI + slug] for preco, tamanho, slug in c["linhas"]] + p["precos"]

    ordem = [c["slug"] for c in categorias]
    lista = sorted(produtos.values(), key=lambda p: ordem.index(p["categoria"]))
    for i, p in enumerate(lista, 1):
        p["ordem"] = i
    img = next(b for _, b in banner["trocas"] if b.startswith("assets/images/"))
    alt = next(b for _, b in banner["trocas"] if b.startswith('alt="'))
    dados = {
        "_origem": "Gerado por tools/gen_cardapio.py a partir do cardápio do HTML estático. Não edite à mão.",
        "categorias": categorias,
        "fatias": {"titulo": secoes["fatias"]["titulo"], "texto": secoes["fatias"]["lead"]},
        "banner": {"imagem": img.removeprefix("assets/"), "alt": alt[len('alt="'):-1]},
        "produtos": lista,
    }
    JSON_TEMA.parent.mkdir(parents=True, exist_ok=True)
    JSON_TEMA.write_text(json.dumps(dados, ensure_ascii=False, indent="\t") + "\n", encoding="utf-8")
    print(f"{JSON_TEMA.relative_to(ROOT).as_posix()}: {len(categorias)} categorias, {len(lista)} produtos")


if __name__ == "__main__":
    main()
