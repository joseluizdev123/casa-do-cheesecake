<?php
/**
 * Blog (posts nativos do WordPress). Espelha blog.html / src/paginas/blog.
 *
 * - home.php: página de posts (Configurações → Leitura) — topo com as categorias,
 *   artigo em destaque (o post fixado; sem fixado, o mais recente) e uma seção
 *   por categoria com os últimos artigos e "Ver todos os artigos".
 * - category.php: arquivo da categoria ("Ver todos os artigos"), paginado.
 * - single.php: página do artigo, com "Leia também" no fim.
 * - Option page "Blog" (Páginas do site): textos, categorias exibidas e rótulos.
 * - Seed: página "Blog" (slug blog) como página de posts.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'cdc_option_pages', function ( $pages ) {
	$pages['blog'] = array(
		'title'  => 'Blog',
		'intro'  => 'Os artigos são os Posts do WordPress (Posts → Todos os posts). O post fixado no topo do blog vira o destaque; sem post fixado, o destaque é o mais recente. Cada categoria tem a sua seção, com o nome e a descrição da categoria (Posts → Categorias).',
		'order'  => 40,
		'fields' => array(
			'topo'          => array(
				'label' => 'Topo da página',
				'type'  => 'heading',
			),
			'titulo'        => array(
				'label'   => 'Título',
				'type'    => 'text',
				'default' => 'Blog',
			),
			'texto'         => array(
				'label'   => 'Texto',
				'type'    => 'textarea',
				'rows'    => 3,
				'default' => 'Dicas para escolher, pedir, presentear e conservar o seu cheesecake, além de receitas e curiosidades sobre o autêntico NY Cheesecake.',
			),
			'secoes'        => array(
				'label' => 'Seções por categoria',
				'type'  => 'heading',
			),
			'categorias'    => array(
				'label'   => 'Categorias exibidas',
				'type'    => 'textarea',
				'rows'    => 6,
				'default' => '',
				'help'    => 'Um slug de categoria por linha (Posts → Categorias), na ordem em que as seções aparecem. Vazio = todas as categorias com artigos, da que tem mais artigos para a que tem menos.',
			),
			'por_categoria' => array(
				'label'   => 'Artigos por categoria',
				'type'    => 'number',
				'default' => '3',
			),
			'ver_todos'     => array(
				'label'   => 'Botão das seções',
				'type'    => 'text',
				'default' => 'Ver todos os artigos',
				'help'    => 'Leva para a página da categoria, com todos os artigos dela.',
			),
			'destaque'      => array(
				'label' => 'Artigo em destaque',
				'type'  => 'heading',
			),
			'destaque_tag'  => array(
				'label'   => 'Etiqueta sobre a foto',
				'type'    => 'text',
				'default' => 'Em destaque',
				'help'    => 'Apague o texto e salve para não mostrar etiqueta.',
			),
			'ler'           => array(
				'label'   => 'Botão',
				'type'    => 'text',
				'default' => 'Ler artigo',
			),
			'artigos'       => array(
				'label' => 'Página da categoria e do artigo',
				'type'  => 'heading',
			),
			'por_pagina'    => array(
				'label'   => 'Artigos por página na categoria',
				'type'    => 'number',
				'default' => '9',
			),
			'anteriores'    => array(
				'label'   => 'Botão da paginação (mais antigos)',
				'type'    => 'text',
				'default' => 'Artigos anteriores',
			),
			'recentes'      => array(
				'label'   => 'Botão da paginação (mais novos)',
				'type'    => 'text',
				'default' => 'Mais recentes',
			),
			'relacionados'  => array(
				'label'   => 'Título no fim do artigo',
				'type'    => 'text',
				'default' => 'Leia também',
				'help'    => 'Seção com outros artigos da mesma categoria.',
			),
			'imagem_padrao' => array(
				'label'   => 'Foto dos artigos sem imagem destacada',
				'type'    => 'image',
				'default' => 'images/05-diferenciais-assado.webp',
				'help'    => 'Usada nos cards quando o post não tem imagem destacada. Horizontal 4:3 ou 3:2.',
			),
		),
	);
	return $pages;
} );

add_filter( 'cdc_seed_pages', function ( $pages ) {
	$pages[] = array(
		'slug'       => 'blog',
		'title'      => 'Blog',
		'posts_page' => true,
	);
	return $pages;
} );

// Arquivo da categoria: N artigos por página (Páginas do site → Blog).
// Página de posts (/blog/): o home.php monta as próprias listas, então a query
// principal só busca 1 post, sem contar o total.
add_action( 'pre_get_posts', function ( $query ) {
	if ( is_admin() || ! $query->is_main_query() || $query->is_feed() ) {
		return;
	}
	if ( $query->is_category() ) {
		$query->set( 'posts_per_page', max( 1, absint( cdc_opt( 'blog', 'por_pagina' ) ) ) );
	} elseif ( $query->is_home() && ! $query->is_front_page() ) {
		$query->set( 'posts_per_page', 1 );
		$query->set( 'no_found_rows', true );
		$query->set( 'ignore_sticky_posts', true );
	}
} );

// /blog/page/N/ repetiria o /blog/ (o home.php não pagina): volta para o /blog/.
add_action( 'template_redirect', function () {
	if ( is_home() && ! is_front_page() && is_paged() && ! is_feed() ) {
		wp_safe_redirect( cdc_page_url( 'blog' ), 301 );
		exit;
	}
}, 1 );

/**
 * Data do artigo no formato do layout ("14 out 2026") e em ISO para o <time>.
 *
 * @param int|WP_Post $post Post.
 * @return array{iso:string,label:string}
 */
function cdc_blog_date( $post ) {
	$meses = array( 'jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez' );
	$mes   = (int) get_the_date( 'n', $post );
	return array(
		'iso'   => (string) get_the_date( 'Y-m-d', $post ),
		'label' => get_the_date( 'j', $post ) . ' ' . ( isset( $meses[ $mes - 1 ] ) ? $meses[ $mes - 1 ] : '' ) . ' ' . get_the_date( 'Y', $post ),
	);
}

/**
 * Resumo em texto puro: o resumo do post; sem resumo, o começo do conteúdo.
 *
 * @param int|WP_Post $post  Post.
 * @param int         $words Palavras quando o resumo vem do conteúdo.
 * @return string Texto sem HTML (escapar na saída).
 */
function cdc_blog_excerpt( $post, $words = 30 ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	if ( has_excerpt( $post ) ) {
		$text = $post->post_excerpt;
	} else {
		$text = strip_shortcodes( excerpt_remove_blocks( $post->post_content ) );
	}
	$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $text ) ) );
	return has_excerpt( $post ) ? $text : wp_trim_words( $text, $words, '…' );
}

/**
 * Categoria principal do artigo: a primária do Yoast/Rank Math, se houver; senão
 * a primeira que não é "Sem categoria".
 *
 * @param int|WP_Post $post Post.
 * @return WP_Term|null
 */
function cdc_blog_post_categoria( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}
	foreach ( array( '_yoast_wpseo_primary_category', 'rank_math_primary_category' ) as $key ) {
		$id = (int) get_post_meta( $post->ID, $key, true );
		if ( $id && has_category( $id, $post ) ) {
			$term = get_term( $id, 'category' );
			if ( $term instanceof WP_Term ) {
				return $term;
			}
		}
	}
	$cats = get_the_category( $post->ID );
	if ( ! $cats ) {
		return null;
	}
	$padrao = (int) get_option( 'default_category' );
	foreach ( $cats as $cat ) {
		if ( (int) $cat->term_id !== $padrao ) {
			return $cat;
		}
	}
	return $cats[0];
}

/**
 * Categorias com seção no blog: as da option page, na ordem; sem lista, todas as
 * categorias com artigos (da maior para a menor; "Sem categoria" só se for a única).
 *
 * @return WP_Term[]
 */
function cdc_blog_categorias() {
	$linhas = cdc_lines( cdc_opt( 'blog', 'categorias' ) );
	$out    = array();
	if ( $linhas ) {
		foreach ( $linhas as $linha ) {
			$term = get_term_by( 'slug', sanitize_title( $linha ), 'category' );
			if ( ! $term ) {
				$term = get_term_by( 'name', $linha, 'category' );
			}
			if ( $term instanceof WP_Term && $term->count > 0 ) {
				$out[ $term->term_id ] = $term;
			}
		}
		return array_values( $out );
	}
	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);
	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}
	$padrao = (int) get_option( 'default_category' );
	foreach ( $terms as $term ) {
		if ( (int) $term->term_id !== $padrao ) {
			$out[] = $term;
		}
	}
	return $out ? $out : $terms;
}

/**
 * Artigo em destaque: o post fixado mais recente; sem fixado, o mais recente.
 *
 * @return WP_Post|null
 */
function cdc_blog_destaque() {
	$base   = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 1,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'suppress_filters'    => false,
	);
	$sticky = array_filter( array_map( 'absint', (array) get_option( 'sticky_posts', array() ) ) );
	if ( $sticky ) {
		$found = get_posts( $base + array( 'post__in' => $sticky ) );
		if ( $found ) {
			return $found[0];
		}
	}
	$found = get_posts( $base );
	return $found ? $found[0] : null;
}

/**
 * Últimos artigos de uma categoria.
 *
 * @param int   $term_id Categoria.
 * @param int   $limit   Quantos.
 * @param int[] $exclude IDs fora da lista (o destaque, o artigo aberto).
 * @return WP_Post[]
 */
function cdc_blog_posts_categoria( $term_id, $limit, $exclude = array() ) {
	return get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $limit,
			'cat'                 => (int) $term_id,
			'post__not_in'        => array_map( 'absint', $exclude ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'suppress_filters'    => false,
		)
	);
}

/**
 * Quantos artigos cada seção de categoria mostra.
 *
 * @return int
 */
function cdc_blog_por_categoria() {
	$n = absint( cdc_opt( 'blog', 'por_categoria' ) );
	return $n ? min( $n, 12 ) : 3;
}

/**
 * Id da seção de uma categoria (âncora dos botões do topo): o slug, sem colidir
 * com os ids fixos da página.
 *
 * @param WP_Term $term Categoria.
 * @return string
 */
function cdc_blog_section_id( $term ) {
	$id = sanitize_html_class( $term->slug );
	if ( '' === $id || in_array( $id, array( 'topo', 'conteudo', 'blog', 'destaque', 'contato', 'menu-mobile', 'artigo', 'leia-tambem' ), true ) ) {
		$id = 'categoria-' . $term->term_id;
	}
	return $id;
}

/**
 * <img> da foto do artigo: imagem destacada; sem ela, a foto padrão do blog.
 *
 * @param int|WP_Post $post Post.
 * @param array       $attr Atributos (class, loading, sizes…).
 * @return string HTML seguro ('' sem imagem).
 */
function cdc_blog_image( $post, $attr ) {
	$id = (int) get_post_thumbnail_id( $post );
	if ( ! $id ) {
		$values = cdc_opt_values( 'blog' );
		$id     = isset( $values['imagem_padrao'] ) ? absint( $values['imagem_padrao'] ) : 0;
	}
	if ( $id ) {
		$html = wp_get_attachment_image( $id, 'large', false, $attr );
		if ( $html ) {
			return $html;
		}
	}
	$img = cdc_opt_image( 'blog', 'imagem_padrao' );
	if ( '' === $img['url'] ) {
		return '';
	}
	return sprintf(
		'<img class="%1$s" src="%2$s" alt=""%3$s%4$s decoding="async">',
		esc_attr( isset( $attr['class'] ) ? $attr['class'] : '' ),
		esc_url( $img['url'] ),
		$img['w'] ? sprintf( ' width="%d" height="%d"', (int) $img['w'], (int) $img['h'] ) : '',
		! empty( $attr['loading'] ) ? ' loading="' . esc_attr( $attr['loading'] ) . '"' : ''
	);
}
