<?php
/**
 * Option pages nativas (sem ACF): menu "Páginas do site" no painel, com uma
 * subpágina de campos por página interna (Produtos, Sobre nós, Contato, Blog).
 *
 * Cada inc/pages/<pagina>.php declara a sua:
 *   add_filter( 'cdc_option_pages', function ( $pages ) {
 *       $pages['sobre'] = array(
 *           'title'  => 'Sobre nós',                 // item do menu e título da tela
 *           'intro'  => 'Texto de ajuda no topo.',   // opcional
 *           'order'  => 20,                          // posição no menu (opcional)
 *           'fields' => array(
 *               'grupo'  => array( 'label' => 'Bloco 1', 'type' => 'heading' ),
 *               'titulo' => array( 'label' => 'Título', 'type' => 'text', 'default' => 'Nossa história' ),
 *               'foto'   => array( 'label' => 'Foto', 'type' => 'image', 'default' => 'images/sobre-historia.webp' ),
 *               'fotos'  => array( 'label' => 'Fotos', 'type' => 'gallery', 'default' => array( 'images/a.webp', 'images/b.webp' ) ),
 *           ),
 *       );
 *       return $pages;
 *   } );
 *
 * Tipos: os do motor de campos (inc/fields.php). Valores ficam numa option só
 * por página (`cdc_opt_<pagina>`, array). Leitura com default do registro:
 *   cdc_opt( 'sobre', 'titulo' )   cdc_opt_image( 'sobre', 'foto' )   cdc_opt_gallery( 'contato', 'fotos' )
 * Imagens: default = caminho relativo a assets/; o importador (inc/seeder.php)
 * leva os defaults para a Mídia e grava os IDs, como faz com o Customizer.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registro das option pages.
 *
 * @return array<string, array>
 */
function cdc_option_pages() {
	static $pages = null;
	if ( null === $pages ) {
		$pages = apply_filters( 'cdc_option_pages', array() );
		uasort(
			$pages,
			static function ( $a, $b ) {
				return ( isset( $a['order'] ) ? $a['order'] : 100 ) - ( isset( $b['order'] ) ? $b['order'] : 100 );
			}
		);
	}
	return $pages;
}

/**
 * Definição de um campo de option page.
 *
 * @param string $page Chave da página.
 * @param string $key  Chave do campo.
 * @return array|null
 */
function cdc_opt_field( $page, $key ) {
	$pages = cdc_option_pages();
	return isset( $pages[ $page ]['fields'][ $key ] ) ? $pages[ $page ]['fields'][ $key ] : null;
}

/**
 * Valores salvos de uma página (array).
 *
 * @param string $page Chave da página.
 * @return array
 */
function cdc_opt_values( $page ) {
	$values = get_option( 'cdc_opt_' . $page, array() );
	return is_array( $values ) ? $values : array();
}

/**
 * Valor de um campo; vazio = default do registro.
 *
 * @param string $page Chave da página.
 * @param string $key  Chave do campo.
 * @return mixed
 */
function cdc_opt( $page, $key ) {
	$values = cdc_opt_values( $page );
	$field  = cdc_opt_field( $page, $key );
	$value  = isset( $values[ $key ] ) ? $values[ $key ] : null;
	if ( $field && 'checkbox' === ( isset( $field['type'] ) ? $field['type'] : '' ) ) {
		// Checkbox: '' salvo = desmarcado de propósito; só cai no default se nunca foi salvo.
		return null === $value ? ( ! empty( $field['default'] ) ? '1' : '' ) : $value;
	}
	if ( null === $value || '' === $value ) {
		return ( $field && isset( $field['default'] ) ) ? $field['default'] : '';
	}
	return $value;
}

/**
 * Imagem de um campo: attachment salvo ou asset default.
 *
 * @param string $page Chave da página.
 * @param string $key  Chave do campo.
 * @param string $size Tamanho WP.
 * @return array{url:string,w:int,h:int,alt:string} url '' quando não há imagem.
 */
function cdc_opt_image( $page, $key, $size = 'full' ) {
	$values = cdc_opt_values( $page );
	$id     = isset( $values[ $key ] ) ? absint( $values[ $key ] ) : 0;
	if ( $id ) {
		$img = cdc_attachment( $id, $size );
		if ( $img['url'] ) {
			return $img;
		}
	}
	$field = cdc_opt_field( $page, $key );
	return ( $field && ! empty( $field['default'] ) ) ? cdc_asset_image( $field['default'] ) : cdc_attachment( 0 );
}

/**
 * Galeria de um campo: attachments salvos ou assets default.
 *
 * @param string $page Chave da página.
 * @param string $key  Chave do campo.
 * @param string $size Tamanho WP.
 * @return array[] Lista de array{url,w,h,alt}.
 */
function cdc_opt_gallery( $page, $key, $size = 'full' ) {
	$values = cdc_opt_values( $page );
	$out    = array();
	foreach ( cdc_ids( isset( $values[ $key ] ) ? $values[ $key ] : '' ) as $id ) {
		$img = cdc_attachment( $id, $size );
		if ( $img['url'] ) {
			$out[] = $img;
		}
	}
	if ( ! $out ) {
		$field = cdc_opt_field( $page, $key );
		foreach ( ( $field && ! empty( $field['default'] ) ) ? (array) $field['default'] : array() as $rel ) {
			$out[] = cdc_asset_image( $rel );
		}
	}
	return $out;
}

add_action( 'admin_menu', function () {
	$pages = cdc_option_pages();
	if ( ! $pages ) {
		return;
	}
	$first = key( $pages );
	add_menu_page(
		__( 'Páginas do site', 'casadocheesecake' ),
		__( 'Páginas do site', 'casadocheesecake' ),
		'edit_theme_options',
		'cdc-pagina-' . $first,
		'cdc_render_option_page',
		'dashicons-layout',
		21
	);
	foreach ( $pages as $slug => $page ) {
		add_submenu_page(
			'cdc-pagina-' . $first,
			$page['title'] . ' — ' . __( 'Páginas do site', 'casadocheesecake' ),
			$page['title'],
			'edit_theme_options',
			'cdc-pagina-' . $slug,
			'cdc_render_option_page'
		);
	}
} );

add_action( 'admin_init', function () {
	foreach ( cdc_option_pages() as $slug => $page ) {
		register_setting(
			'cdc_opt_' . $slug,
			'cdc_opt_' . $slug,
			array(
				'type'              => 'array',
				'sanitize_callback' => static function ( $input ) use ( $slug ) {
					return cdc_sanitize_option_page( $slug, $input );
				},
				'default'           => array(),
			)
		);
	}
} );

// Quem edita o conteúdo das páginas (edit_theme_options) também pode salvar (options.php pede manage_options).
add_action( 'admin_init', function () {
	foreach ( array_keys( cdc_option_pages() ) as $slug ) {
		add_filter(
			'option_page_capability_cdc_opt_' . $slug,
			static function () {
				return 'edit_theme_options';
			}
		);
	}
} );

/**
 * Sanitiza o array enviado pelo formulário, campo a campo.
 *
 * @param string $slug  Chave da página.
 * @param mixed  $input Valores enviados.
 * @return array
 */
function cdc_sanitize_option_page( $slug, $input ) {
	$pages = cdc_option_pages();
	$input = is_array( $input ) ? $input : array();
	$out   = array();
	foreach ( isset( $pages[ $slug ]['fields'] ) ? $pages[ $slug ]['fields'] : array() as $key => $field ) {
		$type = isset( $field['type'] ) ? $field['type'] : 'text';
		if ( 'heading' === $type ) {
			continue;
		}
		$out[ $key ] = cdc_sanitize_field( isset( $input[ $key ] ) ? $input[ $key ] : '', $type );
	}
	return $out;
}

/**
 * Tela de uma option page.
 */
function cdc_render_option_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$slug  = isset( $_GET['page'] ) ? substr( sanitize_key( wp_unslash( $_GET['page'] ) ), strlen( 'cdc-pagina-' ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$pages = cdc_option_pages();
	if ( ! isset( $pages[ $slug ] ) ) {
		return;
	}
	$page   = $pages[ $slug ];
	$values = cdc_opt_values( $slug );
	echo '<div class="wrap"><h1>' . esc_html( $page['title'] ) . '</h1>';
	if ( ! empty( $page['intro'] ) ) {
		echo '<p class="description" style="max-width:720px">' . esc_html( $page['intro'] ) . '</p>';
	}
	settings_errors();
	echo '<form method="post" action="options.php">';
	settings_fields( 'cdc_opt_' . $slug );
	cdc_fields_table(
		$page['fields'],
		static function ( $key ) use ( $values, $page ) {
			if ( isset( $values[ $key ] ) ) {
				return $values[ $key ];
			}
			// Nunca salvo: o formulário já mostra o texto do layout (o que está no site).
			$field = $page['fields'][ $key ];
			$type  = isset( $field['type'] ) ? $field['type'] : 'text';
			if ( in_array( $type, array( 'image', 'gallery' ), true ) ) {
				return '';
			}
			if ( 'checkbox' === $type ) {
				return ! empty( $field['default'] ) ? '1' : '';
			}
			return isset( $field['default'] ) && is_scalar( $field['default'] ) ? $field['default'] : '';
		},
		'cdc_opt_' . $slug
	);
	echo '<p class="description">' . esc_html__( 'Campo de texto vazio volta ao texto original do layout.', 'casadocheesecake' ) . '</p>';
	submit_button();
	echo '</form></div>';
}
