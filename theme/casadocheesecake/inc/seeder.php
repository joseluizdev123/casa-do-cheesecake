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
 *   Chaves extras de cada post: 'terms' => array( 'cdc_categoria' => array( 'slug' ) ),
 *   'featured_alt' => 'texto alternativo da imagem destacada', 'key' => 'chave fixa'
 *   (padrão: post_type:título-em-slug; use ao mudar o título de um item já publicado).
 * - Cria termos declarados via `cdc_seed_terms` (taxonomy, slug, name, description, meta).
 * - Cria as páginas internas via `cdc_seed_pages` (slug, title, template, posts_page)
 *   e leva para a Mídia as imagens/galerias padrão das option pages (inc/options.php).
 * - Cria a página "Home" e define como página inicial.
 * Idempotente: posts são identificados pela chave do item (meta _cdc_seed_key) e
 * cada chave é criada uma vez só (option cdc_seeded_keys) — renomear, mandar para a
 * lixeira ou apagar não faz o post voltar; termos e páginas por slug; mídia por
 * caminho de origem. Em posts já existentes só completa o que nunca foi preenchido
 * (categoria vazia, meta inexistente) — nada que o cliente editou.
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
	echo '<p>' . esc_html__( 'Cria produtos e categorias do cardápio, canais de contato, diferenciais, depoimentos e perguntas frequentes com o conteúdo do layout, cria as páginas Produtos, Sobre nós, Contato e Blog, importa as imagens para a Mídia e define a página inicial. Pode rodar mais de uma vez: nada é duplicado e nada que você editou é sobrescrito.', 'casadocheesecake' ) . '</p>';
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

	// 1b) Imagens e galerias padrão das option pages → Mídia + option.
	foreach ( cdc_option_pages() as $slug => $page ) {
		$values  = cdc_opt_values( $slug );
		$changed = false;
		foreach ( $page['fields'] as $key => $field ) {
			$type = isset( $field['type'] ) ? $field['type'] : '';
			if ( ! in_array( $type, array( 'image', 'gallery' ), true ) || empty( $field['default'] ) || ! empty( $values[ $key ] ) ) {
				continue;
			}
			$ids = array();
			foreach ( (array) $field['default'] as $rel ) {
				$id = cdc_seed_media( $rel );
				if ( $id ) {
					$ids[] = $id;
					$report['images']++;
				}
			}
			if ( $ids ) {
				$values[ $key ] = 'image' === $type ? (string) $ids[0] : implode( ',', $ids );
				$changed        = true;
			}
		}
		if ( $changed ) {
			update_option( 'cdc_opt_' . $slug, $values );
		}
	}

	// 1c) Termos (categorias do cardápio…).
	foreach ( apply_filters( 'cdc_seed_terms', array() ) as $t ) {
		if ( ! taxonomy_exists( $t['taxonomy'] ) || term_exists( $t['slug'], $t['taxonomy'] ) ) {
			continue;
		}
		$created = wp_insert_term(
			$t['name'],
			$t['taxonomy'],
			array(
				'slug'        => $t['slug'],
				'description' => isset( $t['description'] ) ? $t['description'] : '',
			)
		);
		if ( ! is_wp_error( $created ) && ! empty( $t['meta'] ) ) {
			foreach ( $t['meta'] as $mk => $mv ) {
				update_term_meta( $created['term_id'], $mk, $mv );
			}
		}
	}

	// 2) Posts dos CPTs. Cada item tem uma chave fixa (meta _cdc_seed_key); a lista
	// cdc_seeded_keys guarda o que já foi criado, para não recriar o que o cliente
	// renomeou, mandou para a lixeira ou apagou.
	$seeded_keys = array_map( 'strval', (array) get_option( 'cdc_seeded_keys', array() ) );
	foreach ( apply_filters( 'cdc_seed_posts', array() ) as $item ) {
		$key     = cdc_seed_key( $item );
		$known   = in_array( $key, $seeded_keys, true );
		$found   = cdc_seed_find_post( $item, $key, ! $known );
		if ( $found ) {
			// Já existe: só completa o que nunca foi preenchido (versões novas do tema).
			cdc_seed_complete_post( $found, $item );
			if ( ! $known ) {
				$seeded_keys[] = $key;
			}
			$report['skipped']++;
			continue;
		}
		if ( $known ) {
			// Criado antes e removido pelo cliente: não volta.
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
		update_post_meta( $post_id, '_cdc_seed_key', $key );
		$seeded_keys[] = $key;
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
				if ( ! empty( $item['featured_alt'] ) && '' === (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) ) {
					update_post_meta( $id, '_wp_attachment_image_alt', $item['featured_alt'] );
				}
				$report['images']++;
			}
		}
		if ( ! empty( $item['terms'] ) ) {
			foreach ( $item['terms'] as $tax => $slugs ) {
				if ( taxonomy_exists( $tax ) ) {
					wp_set_object_terms( $post_id, (array) $slugs, $tax );
				}
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
	update_option( 'cdc_seeded_keys', array_values( array_unique( $seeded_keys ) ), false );

	// 2b) Páginas internas (Produtos, Sobre nós, Contato, Blog).
	foreach ( apply_filters( 'cdc_seed_pages', array() ) as $pg ) {
		$page = get_page_by_path( $pg['slug'] );
		if ( $page ) {
			$page_id = $page->ID;
		} else {
			$page_id = wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_title'  => $pg['title'],
					'post_name'   => $pg['slug'],
					'post_status' => 'publish',
				)
			);
			if ( ! $page_id || is_wp_error( $page_id ) ) {
				continue;
			}
			$report['created']++;
		}
		if ( ! empty( $pg['template'] ) && ! get_post_meta( $page_id, '_wp_page_template', true ) ) {
			update_post_meta( $page_id, '_wp_page_template', $pg['template'] );
		}
		if ( ! empty( $pg['posts_page'] ) && ! (int) get_option( 'page_for_posts' ) ) {
			update_option( 'page_for_posts', (int) $page_id );
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
	update_option( 'cdc_seeded_version', CDC_VERSION );
	return $report;
}

/**
 * Chave fixa de um item do seed: 'key' do item ou post_type:título-em-slug.
 *
 * @param array $item Item do seed.
 * @return string
 */
function cdc_seed_key( $item ) {
	return ! empty( $item['key'] ) ? (string) $item['key'] : $item['post_type'] . ':' . sanitize_title( $item['title'] );
}

/**
 * Post já criado para um item do seed (fora da lixeira), pela chave.
 *
 * @param array  $item     Item do seed.
 * @param string $key      Chave (cdc_seed_key).
 * @param bool   $by_title Procurar também pelo título (posts de versões antigas, sem chave).
 * @return int ID ou 0.
 */
function cdc_seed_find_post( $item, $key, $by_title ) {
	$found = get_posts(
		array(
			'post_type'      => $item['post_type'],
			'post_status'    => 'any',
			'meta_key'       => '_cdc_seed_key', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery
			'fields'         => 'ids',
			'posts_per_page' => 1,
		)
	);
	if ( $found || ! $by_title ) {
		return $found ? (int) $found[0] : 0;
	}
	// Importado antes da chave existir: acha pelo título e grava a chave.
	$found = get_posts(
		array(
			'post_type'      => $item['post_type'],
			'title'          => $item['title'],
			'post_status'    => 'any',
			'meta_query'     => array( array( 'key' => '_cdc_seed_key', 'compare' => 'NOT EXISTS' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			'fields'         => 'ids',
			'posts_per_page' => 1,
		)
	);
	if ( ! $found ) {
		return 0;
	}
	update_post_meta( (int) $found[0], '_cdc_seed_key', $key );
	return (int) $found[0];
}

/**
 * Completa um post que já existia: categoria (se não tiver nenhuma) e metas que
 * nunca foram gravadas. Não mexe em nada que o cliente editou ou apagou.
 *
 * @param int   $post_id Post existente.
 * @param array $item    Item do seed.
 */
function cdc_seed_complete_post( $post_id, $item ) {
	if ( ! empty( $item['terms'] ) ) {
		foreach ( $item['terms'] as $tax => $slugs ) {
			if ( taxonomy_exists( $tax ) && ! wp_get_object_terms( $post_id, $tax, array( 'fields' => 'ids' ) ) ) {
				wp_set_object_terms( $post_id, (array) $slugs, $tax );
			}
		}
	}
	if ( ! empty( $item['meta'] ) ) {
		foreach ( $item['meta'] as $k => $v ) {
			if ( ! metadata_exists( 'post', $post_id, '_cdc_' . $k ) ) {
				update_post_meta( $post_id, '_cdc_' . $k, $v );
			}
		}
	}
	if ( ! empty( $item['meta_images'] ) ) {
		foreach ( $item['meta_images'] as $k => $rel ) {
			if ( ! metadata_exists( 'post', $post_id, '_cdc_' . $k ) ) {
				$id = cdc_seed_media( $rel );
				if ( $id ) {
					update_post_meta( $post_id, '_cdc_' . $k, (string) $id );
				}
			}
		}
	}
	if ( ! empty( $item['featured'] ) && ! has_post_thumbnail( $post_id ) ) {
		$id = cdc_seed_media( $item['featured'] );
		if ( $id ) {
			set_post_thumbnail( $post_id, $id );
		}
	}
}
