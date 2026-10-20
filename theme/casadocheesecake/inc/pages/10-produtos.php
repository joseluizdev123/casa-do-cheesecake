<?php
/**
 * Página Produtos (templates/produtos.php). Espelha cardapio.html / src/paginas/cardapio.
 *
 * - Produtos: CPT cdc_cheesecake, um post por produto com todas as linhas de preço
 *   (fatia + inteiros). Categorias (cdc_categoria) = seções da página, na ordem do
 *   campo "Ordem"; a primeira dá o título e o texto do topo e a sua seção fica sem
 *   cabeçalho. Linhas com tamanho "Fatia…" vão para a seção Fatias, que entra logo
 *   depois da última categoria com fatia.
 * - Option page "Produtos" (Páginas do site): banner do desconto e seção Fatias.
 * - Seed: categorias, produtos e textos de inc/data/produtos.json (gerado por
 *   tools/gen_cardapio.py) e a página "Produtos". Os 4 sabores da home vêm de
 *   inc/sections/03-cardapio.php.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cardápio do HTML estático (inc/data/produtos.json): categorias, produtos, banner e Fatias.
 *
 * @return array
 */
function cdc_produtos_data() {
	static $data = null;
	if ( null === $data ) {
		$file = CDC_DIR . '/inc/data/produtos.json';
		$json = file_exists( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- arquivo local do tema.
		$data = ( is_array( $json ) ? $json : array() ) + array(
			'categorias' => array(),
			'produtos'   => array(),
			'fatias'     => array(),
			'banner'     => array(),
		);
		$data['fatias'] += array(
			'titulo' => 'Fatias',
			'texto'  => '',
		);
		$data['banner'] += array(
			'imagem' => 'images/cardapio-banner-goiaba-fatia.webp',
			'alt'    => '',
		);
	}
	return $data;
}

add_filter( 'cdc_option_pages', function ( $pages ) {
	$data              = cdc_produtos_data();
	$pages['produtos'] = array(
		'title'  => 'Produtos',
		'intro'  => 'Produtos e preços ficam em Produtos (menu à esquerda): um produto por sabor, com uma linha por preço; as linhas cujo tamanho começa com "Fatia" vão para a seção Fatias. As seções da página são as categorias, em Produtos → Categorias, na ordem do campo "Ordem": a primeira dá o título e o texto do topo da página. Aqui ficam o banner e a seção Fatias.',
		'order'  => 10,
		'fields' => array(
			'banner'           => array(
				'label' => 'Banner',
				'type'  => 'heading',
				'help'  => 'O banner amarelo do desconto da home, logo abaixo do título. Título, textos e botão são os da home (Aparência → Personalizar → Desconto na primeira compra).',
			),
			'banner_mostrar'   => array(
				'label'   => 'Mostrar o banner',
				'type'    => 'checkbox',
				'default' => '1',
			),
			'banner_fatia'     => array(
				'label'   => 'Foto da fatia da frente',
				'type'    => 'image',
				'default' => $data['banner']['imagem'],
				'help'    => 'Fatia com calda, sem fundo (PNG ou WebP recortado). Vazio = a fatia de goiaba do layout.',
			),
			'banner_fatia_alt' => array(
				'label'   => 'Descrição da foto (acessibilidade)',
				'type'    => 'text',
				'default' => $data['banner']['alt'],
			),
			'fatias'           => array(
				'label' => 'Fatias',
				'type'  => 'heading',
				'help'  => 'Seção com as linhas "Fatia…" de todos os produtos, com a foto da fatia de cada um.',
			),
			'fatias_titulo'    => array(
				'label'   => 'Título',
				'type'    => 'text',
				'default' => $data['fatias']['titulo'],
			),
			'fatias_texto'     => array(
				'label'   => 'Texto',
				'type'    => 'textarea',
				'rows'    => 3,
				'default' => $data['fatias']['texto'],
			),
		),
	);
	return $pages;
} );

// Seed: categorias = seções da página (slug = id da seção).
add_filter( 'cdc_seed_terms', function ( $terms ) {
	foreach ( cdc_produtos_data()['categorias'] as $cat ) {
		$terms[] = array(
			'taxonomy'    => 'cdc_categoria',
			'slug'        => $cat['slug'],
			'name'        => $cat['nome'],
			'description' => $cat['descricao'],
			'meta'        => array( 'cdc_ordem' => (int) $cat['ordem'] ),
		);
	}
	return $terms;
} );

// Categoria criada sem o campo "Ordem" (caixa "+ Adicionar categoria" do produto, API):
// vai para o fim da página em vez de tomar o título do topo (sem meta, a ordem seria 0).
add_action( 'created_cdc_categoria', function ( $term_id ) {
	if ( metadata_exists( 'term', $term_id, 'cdc_ordem' ) ) {
		return;
	}
	$ids   = get_terms(
		array(
			'taxonomy'   => 'cdc_categoria',
			'hide_empty' => false,
			'exclude'    => array( (int) $term_id ),
			'fields'     => 'ids',
		)
	);
	$maior = 0;
	foreach ( is_wp_error( $ids ) ? array() : $ids as $id ) {
		$maior = max( $maior, (int) get_term_meta( $id, 'cdc_ordem', true ) );
	}
	update_term_meta( $term_id, 'cdc_ordem', $maior + 10 );
}, 20 );

// Seed: um produto por item do cardápio, com todas as linhas de preço.
add_filter( 'cdc_seed_posts', function ( $posts ) {
	// Os 4 sabores da home já vêm de inc/sections/03-cardapio.php.
	$home = wp_list_pluck( cdc_cardapio_defaults(), 'title' );
	foreach ( cdc_produtos_data()['produtos'] as $p ) {
		if ( in_array( $p['titulo'], $home, true ) ) {
			continue;
		}
		$linhas = array();
		foreach ( $p['precos'] as $l ) {
			$linhas[] = $l[0] . ' | ' . $l[1] . ( empty( $l[2] ) ? '' : ' | ' . $l[2] );
		}
		$item = array(
			'post_type'  => 'cdc_cheesecake',
			'title'      => $p['titulo'],
			'menu_order' => (int) $p['ordem'],
			'meta'       => array(
				'descricao' => $p['descricao'],
				'tag'       => $p['tag'],
				'cor_fundo' => $p['cor'],
				'precos'    => implode( "\n", $linhas ),
			),
			'terms'      => array( 'cdc_categoria' => array( $p['categoria'] ) ),
		);
		if ( ! empty( $p['foto'] ) ) {
			$item['featured'] = $p['foto'];
		}
		if ( ! empty( $p['foto_alt'] ) ) {
			$item['featured_alt']     = $p['foto_alt'];
			$item['meta']['foto_alt'] = $p['foto_alt']; // vale também para a foto que o cliente subir depois
		}
		if ( ! empty( $p['foto_fatia'] ) ) {
			$item['meta_images'] = array( 'imagem_fatia' => $p['foto_fatia'] );
		}
		$posts[] = $item;
	}
	return $posts;
} );

add_filter( 'cdc_seed_pages', function ( $pages ) {
	$pages[] = array(
		'slug'     => 'produtos',
		'title'    => 'Produtos',
		'template' => 'templates/produtos.php',
	);
	return $pages;
} );

/**
 * Produtos publicados de uma categoria (sem os das subcategorias), na ordem do admin.
 *
 * @param WP_Term $term Categoria.
 * @return WP_Post[]
 */
function cdc_produtos_da_categoria( $term ) {
	return get_posts(
		array(
			'post_type'        => 'cdc_cheesecake',
			'posts_per_page'   => -1,
			'orderby'          => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
			'post_status'      => 'publish',
			'suppress_filters' => false,
			'tax_query'        => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'taxonomy'         => 'cdc_categoria',
					'terms'            => $term->term_id,
					'include_children' => false,
				),
			),
		)
	);
}

/**
 * Card normalizado de um produto (mesmos dados do _card de tools/gen_cardapio.py).
 *
 * @param WP_Post $post   Produto.
 * @param array[] $linhas Linhas de preço do card (cdc_cardapio_parse_precos).
 * @param bool    $fatia  Card da seção Fatias (foto da fatia, sem tag).
 * @return array
 */
function cdc_produtos_card( $post, $linhas, $fatia ) {
	$nome = get_the_title( $post );
	$img  = cdc_attachment( 0 );
	if ( $fatia ) {
		$img = cdc_attachment( (int) cdc_meta( $post->ID, 'imagem_fatia' ) );
		if ( $img['url'] ) {
			$img['alt'] = 'Fatia de cheesecake sabor ' . $nome;
		}
	}
	if ( ! $img['url'] ) {
		// Inteiro (ou fatia ainda sem foto própria): imagem destacada.
		$img = cdc_attachment( (int) get_post_thumbnail_id( $post ) );
		if ( $img['url'] && '' === $img['alt'] ) {
			$alt        = (string) cdc_meta( $post->ID, 'foto_alt' );
			$img['alt'] = '' !== $alt ? $alt : 'Cheesecake inteiro sabor ' . $nome;
		}
	}
	$link = (string) cdc_meta( $post->ID, 'link_pedido' );
	$cor  = (string) sanitize_hex_color( cdc_meta( $post->ID, 'cor_fundo' ) );
	return array(
		'nome'   => $nome,
		'desc'   => (string) cdc_meta( $post->ID, 'descricao' ),
		'tag'    => $fatia ? '' : (string) cdc_meta( $post->ID, 'tag' ),
		'cor'    => '' !== $cor ? $cor : 'var(--color-red-700)',
		'img'    => $img,
		'link'   => cdc_cardapio_href( '' !== $link ? $link : cdc_mod( 'cdc_contato_cardapio' ) ),
		'linhas' => $linhas,
	);
}

/**
 * Conteúdo da página Produtos, na ordem do HTML estático.
 *
 * - intro: a 1ª categoria (título e texto do topo) ou null sem categorias.
 * - secoes: uma por categoria com inteiros (a 1ª sem cabeçalho) e a seção Fatias
 *   logo depois da última categoria que tem fatia. Cada seção: id, titulo, lead,
 *   sem_cabecalho, rotulo (aria-labelledby) e cards (cdc_produtos_card).
 *
 * @return array{intro:WP_Term|null,secoes:array[]}
 */
function cdc_produtos_pagina() {
	$terms  = cdc_categorias();
	$secoes = array();
	$fatias = array();
	$vistos = array();
	$depois = 0; // posição da seção Fatias em $secoes
	foreach ( $terms as $i => $term ) {
		$cards     = array();
		$tem_fatia = false;
		foreach ( cdc_produtos_da_categoria( $term ) as $post ) {
			$so_fatia   = array();
			$so_inteiro = array();
			foreach ( cdc_cardapio_parse_precos( cdc_meta( $post->ID, 'precos' ) ) as $linha ) {
				if ( 0 === stripos( $linha['tamanho'], 'fatia' ) ) {
					$so_fatia[] = $linha;
				} else {
					$so_inteiro[] = $linha;
				}
			}
			if ( $so_inteiro ) {
				$cards[] = cdc_produtos_card( $post, $so_inteiro, false );
			}
			if ( $so_fatia ) {
				$tem_fatia = true;
				if ( ! isset( $vistos[ $post->ID ] ) ) {
					$vistos[ $post->ID ] = true;
					$fatias[]            = cdc_produtos_card( $post, $so_fatia, true );
				}
			}
		}
		if ( $cards ) {
			$secoes[] = array(
				'id'            => $term->slug,
				'titulo'        => $term->name,
				'lead'          => $term->description,
				'sem_cabecalho' => 0 === $i,
				'rotulo'        => 0 === $i ? 'produtos-titulo' : $term->slug . '-titulo',
				'cards'         => $cards,
			);
		}
		if ( $tem_fatia ) {
			$depois = count( $secoes );
		}
	}
	if ( $fatias ) {
		$secao_fatias = array(
			'id'            => 'fatias',
			'titulo'        => cdc_opt( 'produtos', 'fatias_titulo' ),
			'lead'          => cdc_opt( 'produtos', 'fatias_texto' ),
			'sem_cabecalho' => false,
			'rotulo'        => 'fatias-titulo',
			'cards'         => $fatias,
		);
		array_splice( $secoes, $depois, 0, array( $secao_fatias ) );
	}
	return array(
		'intro'  => $terms ? $terms[0] : null,
		'secoes' => $secoes,
	);
}
