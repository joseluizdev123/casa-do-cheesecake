<?php
/**
 * Locais de menu + leitura com fallback para os links do Figma.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	register_nav_menus(
		array(
			'header-left'    => __( 'Header — esquerda', 'casadocheesecake' ),
			'header-right'   => __( 'Header — direita', 'casadocheesecake' ),
			'footer-comprar' => __( 'Footer — Comprar', 'casadocheesecake' ),
			'footer-marca'   => __( 'Footer — A marca', 'casadocheesecake' ),
			'footer-contato' => __( 'Footer — Contato', 'casadocheesecake' ),
			'footer-social'  => __( 'Footer — Social', 'casadocheesecake' ),
		)
	);
} );

/**
 * Links de um local de menu como array simples; usa $fallback se não houver menu atribuído.
 *
 * @param string $location Local registrado.
 * @param array  $fallback Lista de array( 'label' => ..., 'url' => ... ).
 * @return array<int, array{label:string,url:string,target:string}>
 */
function cdc_menu_links( $location, $fallback = array() ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return array_map(
			function ( $l ) {
				return array(
					'label'  => $l['label'],
					'url'    => $l['url'],
					'target' => isset( $l['target'] ) ? $l['target'] : '',
				);
			},
			$fallback
		);
	}
	$items = wp_get_nav_menu_items( $locations[ $location ] );
	if ( ! $items ) {
		return array();
	}
	$links = array();
	foreach ( $items as $item ) {
		if ( (int) $item->menu_item_parent ) {
			continue;
		}
		$links[] = array(
			'label'  => $item->title,
			'url'    => $item->url,
			'target' => $item->target,
		);
	}
	return $links;
}

/**
 * Âncora da home: em páginas internas prefixa com a URL da home.
 *
 * @param string $hash Ex. '#cardapio'.
 * @return string
 */
function cdc_anchor( $hash ) {
	return is_front_page() ? $hash : home_url( '/' ) . $hash;
}

/**
 * Link vindo do Customizer: "#ancora" vira âncora da home; URL completa passa direto.
 *
 * @param string $link Link salvo.
 * @return string
 */
function cdc_anchor_or_url( $link ) {
	return ( is_string( $link ) && 0 === strpos( $link, '#' ) ) ? cdc_anchor( $link ) : $link;
}
