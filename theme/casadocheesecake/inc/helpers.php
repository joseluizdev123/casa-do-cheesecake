<?php
/**
 * Utilitários compartilhados pelos template-parts.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

/**
 * glob() ordenado (garante ordem NN- das seções).
 *
 * @param string $pattern Padrão glob.
 * @return string[]
 */
function cdc_sorted_glob( $pattern ) {
	$files = glob( $pattern );
	if ( ! $files ) {
		return array();
	}
	sort( $files, SORT_NATURAL );
	return $files;
}

/**
 * Versão de asset baseada em filemtime (cache-busting).
 *
 * @param string $rel Caminho relativo a assets/.
 * @return string
 */
function cdc_asset_version( $rel ) {
	$path = CDC_DIR . '/assets/' . ltrim( $rel, '/' );
	return file_exists( $path ) ? (string) filemtime( $path ) : CDC_VERSION;
}

/**
 * URL de um arquivo em assets/.
 *
 * @param string $rel Caminho relativo a assets/ (ex. 'images/hero-bg.jpg').
 * @return string
 */
function cdc_asset( $rel ) {
	return CDC_URI . '/assets/' . ltrim( $rel, '/' );
}

/**
 * Lê um theme_mod registrado em cdc_customizer_sections, com default do registro.
 *
 * @param string $key Chave completa (ex. 'cdc_hero_titulo').
 * @return mixed
 */
function cdc_mod( $key ) {
	$field = cdc_customizer_field( $key );
	$default = $field && isset( $field['default'] ) ? $field['default'] : '';
	// Sem default no get_theme_mod(): o WP roda sprintf() em defaults com "%…s" (URLs codificadas).
	$value = get_theme_mod( $key, null );
	return ( '' === $value || null === $value ) ? $default : $value;
}

/**
 * URL de imagem vinda do Customizer: attachment ID (após seed/edição) ou asset default.
 *
 * @param string $key  Chave do theme_mod de imagem.
 * @param string $size Tamanho WP.
 * @return string
 */
function cdc_image( $key, $size = 'full' ) {
	$value = get_theme_mod( $key );
	if ( $value && is_numeric( $value ) ) {
		$url = wp_get_attachment_image_url( (int) $value, $size );
		if ( $url ) {
			return $url;
		}
	} elseif ( $value && is_string( $value ) && preg_match( '#^https?://#', $value ) ) {
		return $value;
	}
	$field = cdc_customizer_field( $key );
	return ( $field && ! empty( $field['default'] ) ) ? cdc_asset( $field['default'] ) : '';
}

/**
 * Meta de post registrado pelo motor de campos.
 *
 * @param int    $post_id ID do post.
 * @param string $key     Chave sem prefixo (ex. 'descricao').
 * @return mixed
 */
function cdc_meta( $post_id, $key ) {
	return get_post_meta( $post_id, '_cdc_' . $key, true );
}

/**
 * URL de imagem armazenada em meta (attachment ID).
 *
 * @param int    $post_id ID do post.
 * @param string $key     Chave do campo.
 * @param string $size    Tamanho WP.
 * @return string
 */
function cdc_meta_image( $post_id, $key, $size = 'full' ) {
	$id = (int) cdc_meta( $post_id, $key );
	return $id ? (string) wp_get_attachment_image_url( $id, $size ) : '';
}

/**
 * Quebra um textarea em linhas não vazias.
 *
 * @param string $text Texto.
 * @return string[]
 */
function cdc_lines( $text ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $text ) ), 'strlen' ) );
}

/**
 * Posts de um CPT na ordem do admin (menu_order).
 *
 * @param string $post_type CPT.
 * @param int    $limit     Limite (-1 = todos).
 * @return WP_Post[]
 */
function cdc_posts( $post_type, $limit = -1 ) {
	return get_posts(
		array(
			'post_type'        => $post_type,
			'posts_per_page'   => $limit,
			'orderby'          => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
			'post_status'      => 'publish',
			'suppress_filters' => false,
		)
	);
}

/**
 * Link externo = http(s) para outro domínio (Brendi, iFood, WhatsApp, redes sociais).
 *
 * @param string $url URL.
 * @return bool
 */
function cdc_is_external( $url ) {
	if ( ! is_string( $url ) || ! preg_match( '#^https?://#i', $url ) ) {
		return false;
	}
	$host = wp_parse_url( $url, PHP_URL_HOST );
	$home = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
	return $host && strtolower( $host ) !== strtolower( (string) $home );
}

/**
 * Atributos de nova aba para links externos (já escapados; '' para links internos).
 *
 * @param string $url URL.
 * @return string
 */
function cdc_target_attr( $url ) {
	return cdc_is_external( $url ) ? ' target="_blank" rel="noopener"' : '';
}

/**
 * Texto com **negrito** → <strong> e [rótulo](https://url) → link, escapado.
 *
 * @param string $text Texto.
 * @return string HTML seguro.
 */
function cdc_rich( $text ) {
	$html = esc_html( $text );
	$html = preg_replace( '/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html );
	$html = preg_replace_callback(
		'/\[([^\]]+)\]\(((?:https?:\/\/|mailto:|tel:|#)[^\s)]+)\)/',
		function ( $m ) {
			$url = html_entity_decode( $m[2], ENT_QUOTES, 'UTF-8' );
			return '<a href="' . esc_url( $url ) . '"' . cdc_target_attr( $url ) . '>' . $m[1] . '</a>';
		},
		$html
	);
	return nl2br( $html );
}

/**
 * Dados de um attachment.
 *
 * @param int    $id   Attachment ID (0 = vazio).
 * @param string $size Tamanho WP.
 * @return array{url:string,w:int,h:int,alt:string}
 */
function cdc_attachment( $id, $size = 'full' ) {
	$src = $id ? wp_get_attachment_image_src( (int) $id, $size ) : false;
	if ( ! $src ) {
		return array( 'url' => '', 'w' => 0, 'h' => 0, 'alt' => '' );
	}
	return array(
		'url' => (string) $src[0],
		'w'   => (int) $src[1],
		'h'   => (int) $src[2],
		'alt' => (string) get_post_meta( (int) $id, '_wp_attachment_image_alt', true ),
	);
}

/**
 * Dados de uma imagem de assets/ (default antes do importador rodar).
 *
 * @param string $rel Caminho relativo a assets/.
 * @return array{url:string,w:int,h:int,alt:string}
 */
function cdc_asset_image( $rel ) {
	$dim = @getimagesize( CDC_DIR . '/assets/' . ltrim( $rel, '/' ) ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	return array(
		'url' => cdc_asset( $rel ),
		'w'   => $dim ? (int) $dim[0] : 0,
		'h'   => $dim ? (int) $dim[1] : 0,
		'alt' => '',
	);
}

/**
 * Texto em parágrafos (separados por linha em branco).
 *
 * @param string $text Texto do campo.
 * @return string[]
 */
function cdc_paragraphs( $text ) {
	$parts = preg_split( '/\R\s*\R/', trim( (string) $text ) );
	return array_values( array_filter( array_map( 'trim', (array) $parts ), 'strlen' ) );
}

/**
 * URL de uma página interna do tema.
 *
 * produtos | sobre | contato → página que usa templates/<chave>.php;
 * blog → página de posts (Configurações → Leitura). Sem a página, cai no
 * endereço padrão criado pelo importador.
 *
 * @param string $key Chave da página.
 * @return string
 */
function cdc_page_url( $key ) {
	static $cache = array();
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}
	$slugs = array(
		'produtos' => 'produtos',
		'sobre'    => 'sobre-nos',
		'contato'  => 'contato',
		'blog'     => 'blog',
	);
	$url = '';
	if ( 'blog' === $key ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		$url        = $posts_page ? (string) get_permalink( $posts_page ) : '';
	} else {
		$found = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => 'templates/' . $key . '.php', // phpcs:ignore WordPress.DB.SlowDBQuery
				'fields'         => 'ids',
				'posts_per_page' => 1,
			)
		);
		$url = $found ? (string) get_permalink( $found[0] ) : '';
	}
	if ( '' === $url ) {
		$url = home_url( '/' . ( isset( $slugs[ $key ] ) ? $slugs[ $key ] : $key ) . '/' );
	}
	$cache[ $key ] = $url;
	return $url;
}

/**
 * O link aponta para a página que está aberta? (item ativo do menu)
 *
 * @param string $url URL do link.
 * @return bool
 */
function cdc_is_current_url( $url ) {
	if ( ! is_string( $url ) || '' === $url || false !== strpos( $url, '#' ) ) {
		return false;
	}
	$here = untrailingslashit( strtok( home_url( add_query_arg( array() ) ), '?' ) );
	return untrailingslashit( strtok( $url, '?' ) ) === $here;
}

/**
 * Produtos da home (cards "Nossos cheesecakes", abas "Sabores" e atalhos do menu
 * mobile): os marcados "Mostrar na home"; sem nenhum marcado, os primeiros da
 * categoria do topo do cardápio; sem categorias, os primeiros do CPT.
 *
 * @param int $limit Máximo.
 * @return WP_Post[]
 */
function cdc_home_products( $limit = 4 ) {
	$base = array(
		'post_type'        => 'cdc_cheesecake',
		'posts_per_page'   => $limit,
		'orderby'          => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
		'post_status'      => 'publish',
		'suppress_filters' => false,
	);
	$marked = get_posts( $base + array( 'meta_key' => '_cdc_home', 'meta_value' => '1' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	if ( $marked ) {
		return $marked;
	}
	$terms = taxonomy_exists( 'cdc_categoria' ) ? cdc_categorias() : array();
	if ( $terms ) {
		$first = get_posts(
			$base + array(
				'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'taxonomy' => 'cdc_categoria',
						'terms'    => $terms[0]->term_id,
					),
				),
			)
		);
		if ( $first ) {
			return $first;
		}
	}
	return get_posts( $base );
}

/**
 * Categorias do cardápio na ordem do campo "Ordem" (term meta), depois nome.
 *
 * @param bool $hide_empty Só categorias com produtos publicados.
 * @return WP_Term[]
 */
function cdc_categorias( $hide_empty = true ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'cdc_categoria',
			'hide_empty' => $hide_empty,
		)
	);
	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}
	usort(
		$terms,
		static function ( $a, $b ) {
			$oa = (int) get_term_meta( $a->term_id, 'cdc_ordem', true );
			$ob = (int) get_term_meta( $b->term_id, 'cdc_ordem', true );
			return $oa === $ob ? strcmp( $a->name, $b->name ) : $oa - $ob;
		}
	);
	return $terms;
}
