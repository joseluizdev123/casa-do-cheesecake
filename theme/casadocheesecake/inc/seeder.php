<?php
/**
 * Importador one-click do conteúdo inicial (Figma → WordPress).
 *
 * - Roda automaticamente na ativação do tema (uma vez) e sob demanda em
 *   Ferramentas → Importar conteúdo.
 * - Importa para a Mídia todos os defaults de imagem do Customizer e grava o
 *   attachment ID no theme_mod correspondente.
 * - Cria os posts declarados por inc/sections/*.php via filtro `cdc_seed_posts`:
 *     add_filter( 'cdc_seed_posts', function ( $posts ) {
 *         $posts[] = array(
 *             'post_type'  => 'cdc_cheesecake',
 *             'title'      => 'Frutas vermelhas',
 *             'menu_order' => 1,
 *             'featured'   => 'images/cardapio-frutas-vermelhas.png',   // relativo a assets/
 *             'meta'       => array( 'descricao' => '…', 'precos' => "R$ 28,90 | Fatia 150 g" ),
 *             'meta_images'=> array( 'imagem_fatia' => 'images/sabor-frutas-vermelhas.png' ),
 *         );
 *         return $posts;
 *     } );
 * - Cria a página "Home" e define como página inicial.
 * Idempotente: posts são identificados por (post_type, título); mídia por caminho de origem.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_switch_theme', function () {
	if ( ! get_option( 'cdc_seeded' ) ) {
		cdc_run_seeder();
	}
} );

add_action( 'admin_menu', function () {
	add_management_page(
		__( 'Importar conteúdo — Casa do Cheesecake', 'casadocheesecake' ),
		__( 'Importar conteúdo', 'casadocheesecake' ),
		'manage_options',
		'cdc-seeder',
		'cdc_render_seeder_page'
	);
} );

/**
 * Página do importador.
 */
function cdc_render_seeder_page() {
	$report = null;
	if ( isset( $_POST['cdc_seed'] ) && check_admin_referer( 'cdc_seed' ) && current_user_can( 'manage_options' ) ) {
		$report = cdc_run_seeder();
	}
	echo '<div class="wrap"><h1>' . esc_html__( 'Importar conteúdo — Casa do Cheesecake', 'casadocheesecake' ) . '</h1>';
	if ( $report ) {
		printf(
			'<div class="notice notice-success"><p>%s</p></div>',
			esc_html( sprintf( 'Pronto: %d posts criados, %d já existiam, %d imagens importadas.', $report['created'], $report['skipped'], $report['images'] ) )
		);
	}
	echo '<p>' . esc_html__( 'Cria cheesecakes, diferenciais, depoimentos e perguntas frequentes com o conteúdo do layout, importa as imagens para a Mídia e define a página inicial. Pode rodar mais de uma vez: nada é duplicado.', 'casadocheesecake' ) . '</p>';
	echo '<form method="post">';
	wp_nonce_field( 'cdc_seed' );
	submit_button( __( 'Importar conteúdo', 'casadocheesecake' ), 'primary', 'cdc_seed' );
	echo '</form></div>';
}

/**
 * Importa (ou reaproveita) um arquivo de assets/ para a Mídia.
 *
 * @param string $rel Caminho relativo a assets/.
 * @return int Attachment ID (0 em falha).
 */
function cdc_seed_media( $rel ) {
	$rel = ltrim( $rel, '/' );
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'meta_key'       => '_cdc_seed_src', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $rel, // phpcs:ignore WordPress.DB.SlowDBQuery
			'fields'         => 'ids',
			'posts_per_page' => 1,
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}
	$src = CDC_DIR . '/assets/' . $rel;
	if ( ! file_exists( $src ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = wp_tempnam( basename( $src ) );
	copy( $src, $tmp );
	$id = media_handle_sideload(
		array(
			'name'     => basename( $src ),
			'tmp_name' => $tmp,
		),
		0
	);
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return 0;
	}
	update_post_meta( $id, '_cdc_seed_src', $rel );
	return (int) $id;
}

/**
 * Executa o seed completo.
 *
 * @return array{created:int,skipped:int,images:int}
 */
function cdc_run_seeder() {
	$report = array(
		'created' => 0,
		'skipped' => 0,
		'images'  => 0,
	);

	// 1) Imagens do Customizer → Mídia + theme_mod.
	foreach ( cdc_customizer_sections() as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			if ( 'image' !== ( isset( $field['type'] ) ? $field['type'] : '' ) || empty( $field['default'] ) ) {
				continue;
			}
			if ( get_theme_mod( $key ) ) {
				continue;
			}
			$id = cdc_seed_media( $field['default'] );
			if ( $id ) {
				set_theme_mod( $key, $id );
				$report['images']++;
			}
		}
	}

	// 2) Posts dos CPTs.
	foreach ( apply_filters( 'cdc_seed_posts', array() ) as $item ) {
		$found = get_posts(
			array(
				'post_type'      => $item['post_type'],
				'title'          => $item['title'],
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => 1,
			)
		);
		if ( $found ) {
			$report['skipped']++;
			continue;
		}
		$post_id = wp_insert_post(
			array(
				'post_type'    => $item['post_type'],
				'post_title'   => $item['title'],
				'post_content' => isset( $item['content'] ) ? $item['content'] : '',
				'post_status'  => 'publish',
				'menu_order'   => isset( $item['menu_order'] ) ? (int) $item['menu_order'] : 0,
			)
		);
		if ( ! $post_id || is_wp_error( $post_id ) ) {
			continue;
		}
		$report['created']++;
		if ( ! empty( $item['meta'] ) ) {
			foreach ( $item['meta'] as $k => $v ) {
				update_post_meta( $post_id, '_cdc_' . $k, $v );
			}
		}
		if ( ! empty( $item['featured'] ) ) {
			$id = cdc_seed_media( $item['featured'] );
			if ( $id ) {
				set_post_thumbnail( $post_id, $id );
				$report['images']++;
			}
		}
		if ( ! empty( $item['meta_images'] ) ) {
			foreach ( $item['meta_images'] as $k => $rel ) {
				$id = cdc_seed_media( $rel );
				if ( $id ) {
					update_post_meta( $post_id, '_cdc_' . $k, (string) $id );
					$report['images']++;
				}
			}
		}
	}

	// 3) Página inicial.
	$home = get_page_by_path( 'home' );
	if ( ! $home ) {
		$home_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Home',
				'post_name'   => 'home',
				'post_status' => 'publish',
			)
		);
	} else {
		$home_id = $home->ID;
	}
	if ( $home_id && ! is_wp_error( $home_id ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $home_id );
	}

	update_option( 'cdc_seeded', time() );
	return $report;
}
