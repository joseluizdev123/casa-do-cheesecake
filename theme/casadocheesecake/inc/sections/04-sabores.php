<?php
/**
 * Seção 04 · Sabores (Figma 7057:329 — component set "Sabores" 7057:518).
 *
 * - Customizer: selo ("DESDE" / "2004"), título, rótulo e link "Ver todas as opções".
 * - Abas: uma por post do CPT cdc_cheesecake (título + campo imagem_fatia + cor_fundo).
 *   O seed dos cheesecakes é feito pela seção 03 (inc/sections/03-cardapio.php).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'cdc_customizer_sections', function ( $sections ) {
	$sections['cdc_sabores'] = array(
		'title'    => 'Sabores',
		'priority' => 40,
		'fields'   => array(
			'cdc_sabores_selo_rotulo' => array( 'label' => 'Selo — texto', 'type' => 'text', 'default' => 'DESDE' ),
			'cdc_sabores_selo_ano'    => array( 'label' => 'Selo — ano', 'type' => 'text', 'default' => '2004' ),
			'cdc_sabores_titulo'      => array( 'label' => 'Título', 'type' => 'text', 'default' => 'Yes, we have cheesecake.' ),
			'cdc_sabores_link_rotulo' => array( 'label' => 'Rótulo do link final', 'type' => 'text', 'default' => 'Ver todas as opções' ),
			'cdc_sabores_link_url'    => array( 'label' => 'Link final', 'type' => 'url', 'default' => '#cardapio', 'help' => 'Âncora ou URL. Padrão: #cardapio (seção Nossos cheesecakes).' ),
		),
	);
	return $sections;
} );

// Rótulos/ajuda dos campos de cheesecake usados por esta seção (os campos em si vêm de inc/cpt.php).
add_filter( 'cdc_post_fields', function ( $fields ) {
	if ( isset( $fields['cdc_cheesecake']['cor_fundo'] ) ) {
		$fields['cdc_cheesecake']['cor_fundo']['label'] = 'Cor de fundo (card do cardápio e seção Sabores)';
		$fields['cdc_cheesecake']['cor_fundo']['help']  = 'Fundos escuros deixam o texto da seção Sabores em creme; fundos claros, em vermelho.';
	}
	if ( isset( $fields['cdc_cheesecake']['imagem_fatia'] ) ) {
		$fields['cdc_cheesecake']['imagem_fatia']['help'] = 'PNG recortado com fundo transparente, proporção 273 × 258 (ideal 546 × 516 px). Sem foto, o sabor não aparece nas abas.';
	}
	return $fields;
}, 20 );

/**
 * Cores padrão do layout por sabor (variantes Figma 7057:519/566/613/660).
 *
 * @return array<string, array{0: string, 1: string}> slug => (título, cor).
 */
function cdc_sabores_defaults() {
	return array(
		'frutas-vermelhas' => array( 'Frutas vermelhas', 'var(--color-red-700)' ),
		'doce-de-leite'    => array( 'Doce de leite', 'var(--color-yellow-400)' ),
		'morangos'         => array( 'Morangos', 'var(--color-pink-200)' ),
		'blue-berry'       => array( 'Blue berry', 'var(--color-blue-200)' ),
	);
}

/**
 * Tema de contraste para a cor de fundo: 'escuro' (texto creme) ou 'claro' (texto vermelho).
 *
 * @param string $color Hex (#rrggbb / #rgb) ou token var(--color-…).
 * @return string
 */
function cdc_sabores_tema( $color ) {
	$color = trim( (string) $color );
	if ( 0 === strpos( $color, 'var(' ) ) {
		return 'var(--color-red-700)' === $color ? 'escuro' : 'claro';
	}
	$hex = ltrim( $color, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( ! preg_match( '/^[0-9a-f]{6}$/i', $hex ) ) {
		return 'escuro';
	}
	$lum = 0;
	$weights = array( 0.2126, 0.7152, 0.0722 );
	foreach ( array( 0, 2, 4 ) as $i => $offset ) {
		$c    = hexdec( substr( $hex, $offset, 2 ) ) / 255;
		$c    = $c <= 0.04045 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
		$lum += $weights[ $i ] * $c;
	}
	return $lum < 0.35 ? 'escuro' : 'claro';
}

/**
 * Itens das abas: posts de cdc_cheesecake com foto da fatia (meta ou asset sabor-<slug>.png).
 * Sem posts publicados, usa os 4 sabores do layout (tema idêntico ao estático antes do seed).
 *
 * @return array[] Cada item: slug, titulo, imagem, bg, tema.
 */
function cdc_sabores_items() {
	$defaults = cdc_sabores_defaults();
	$items    = array();

	foreach ( cdc_posts( 'cdc_cheesecake' ) as $post ) {
		$slug  = sanitize_title( $post->post_title );
		$image = cdc_meta_image( $post->ID, 'imagem_fatia' );
		if ( ! $image && file_exists( CDC_DIR . '/assets/images/sabor-' . $slug . '.png' ) ) {
			$image = cdc_asset( 'images/sabor-' . $slug . '.png' );
		}
		if ( ! $image ) {
			continue;
		}
		$bg = (string) cdc_meta( $post->ID, 'cor_fundo' );
		if ( '' === $bg ) {
			$bg = isset( $defaults[ $slug ] ) ? $defaults[ $slug ][1] : 'var(--color-red-700)';
		}
		$items[] = array(
			'slug'   => $slug,
			'titulo' => $post->post_title,
			'imagem' => $image,
			'bg'     => $bg,
			'tema'   => cdc_sabores_tema( $bg ),
		);
	}

	if ( ! $items ) {
		foreach ( $defaults as $slug => $default ) {
			$items[] = array(
				'slug'   => $slug,
				'titulo' => $default[0],
				'imagem' => cdc_asset( 'images/sabor-' . $slug . '.png' ),
				'bg'     => $default[1],
				'tema'   => cdc_sabores_tema( $default[1] ),
			);
		}
	}

	return $items;
}
