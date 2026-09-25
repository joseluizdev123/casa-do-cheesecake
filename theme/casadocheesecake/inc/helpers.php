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
