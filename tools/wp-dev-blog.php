<?php
/**
 * Só desenvolvimento (WordPress local de tools/wp-serve.sh) — NÃO faz parte do tema.
 *
 * O WP local nasce sem posts; este script cria os do blog.html estático, a partir
 * de tools/blog_posts.json, para testar home.php, category.php e single.php:
 * - categorias = chaves do JSON (nomes e descrições de CATEGORIAS em tools/gen_blog.py);
 * - posts com título, slug, resumo (excerpt), um parágrafo de conteúdo, data,
 *   categoria e imagem destacada (theme/casadocheesecake/assets/images/<capa>);
 * - o "destaque" fica fixado (sticky) e na categoria Delivery, como no estático;
 * - "Páginas do site → Blog": categorias na ordem do estático.
 * Idempotente: post identificado pelo slug, categoria pelo slug.
 *
 * Roda no blueprint (tools/wp-blueprint.json), depois do seeder do tema.
 *
 * @package casadocheesecake
 */

require_once '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/admin.php';

try {
	wp_set_current_user( 1 );

	$cdc_dev_posts = json_decode( (string) file_get_contents( __DIR__ . '/blog_posts.json' ), true );
	if ( ! is_array( $cdc_dev_posts ) ) {
		throw new RuntimeException( 'tools/blog_posts.json inválido' );
	}

	// Mesmas categorias (slug, nome, descrição) de tools/gen_blog.py, na mesma ordem.
	$cdc_dev_cats = array(
		'delivery'    => array( 'Delivery em São Paulo', 'Onde pedir, quais bairros recebem, prazos e formas de pedir o seu cheesecake em São Paulo.' ),
		'presentes'   => array( 'Presentes e ocasiões', 'Cheesecake para presentear e para as datas especiais: Páscoa, Natal, aniversários e festas.' ),
		'tamanhos'    => array( 'Tamanhos, sabores e preços', 'Qual tamanho e qual sabor escolher, fatia ou inteiro, e quanto cada opção rende.' ),
		'conservacao' => array( 'Conservação e dicas', 'Quanto tempo dura, como guardar na geladeira e no freezer e como descongelar.' ),
		'receitas'    => array( 'Receitas e curiosidades', 'Receitas, diferenças entre os tipos de cheesecake e o que faz um NY Cheesecake ser autêntico.' ),
	);
	$cdc_dev_term_ids = array();
	foreach ( $cdc_dev_cats as $cdc_slug => $cdc_cat ) {
		$cdc_term = get_term_by( 'slug', $cdc_slug, 'category' );
		if ( ! $cdc_term ) {
			$cdc_new = wp_insert_term( $cdc_cat[0], 'category', array( 'slug' => $cdc_slug, 'description' => $cdc_cat[1] ) );
			if ( is_wp_error( $cdc_new ) ) {
				continue;
			}
			$cdc_dev_term_ids[ $cdc_slug ] = (int) $cdc_new['term_id'];
		} else {
			$cdc_dev_term_ids[ $cdc_slug ] = (int) $cdc_term->term_id;
		}
	}

	$cdc_dev_created = 0;
	foreach ( $cdc_dev_posts as $cdc_key => $cdc_list ) {
		$cdc_cat_slug = 'destaque' === $cdc_key ? 'delivery' : $cdc_key;
		foreach ( array_values( (array) $cdc_list ) as $cdc_i => $cdc_p ) {
			$cdc_slug = basename( untrailingslashit( wp_parse_url( $cdc_p['link'], PHP_URL_PATH ) ) );
			if ( get_page_by_path( $cdc_slug, OBJECT, 'post' ) ) {
				continue;
			}
			// Mesma data: o 1º do JSON fica mais recente (ordem do estático).
			$cdc_date = $cdc_p['date'] . sprintf( ' %02d:00:00', 20 - $cdc_i );
			$cdc_body = '<p>' . esc_html( $cdc_p['excerpt'] ) . '</p>';
			if ( 'destaque' === $cdc_key ) {
				// Conteúdo mais longo só no destaque, para ver a tipografia do artigo.
				$cdc_body .= "\n\n<h2>Como validar o seu CEP</h2>\n\n<p>Antes de fechar o pedido, informe o CEP no site ou no aplicativo de entrega. Se aparecer a opção de entrega, o seu endereço está na rota. <a href=\"#\">Veja os canais de pedido</a>.</p>\n\n<ul>\n<li>Zona Oeste: Perdizes, Pinheiros, Vila Madalena e Lapa.</li>\n<li>Zona Sul: Moema, Vila Mariana e Brooklin.</li>\n<li>Centro: Higienópolis, Consolação e Bela Vista.</li>\n</ul>\n\n<h3>Prazos</h3>\n\n<p>Pedidos feitos até as 18h costumam chegar no mesmo dia.</p>\n\n<blockquote><p>Cheesecake bom chega gelado e inteiro: a embalagem faz parte da receita.</p></blockquote>\n\n<p>Se o seu endereço ficar fora da rota, fale com a gente pelo WhatsApp.</p>";
			}
			$cdc_id = wp_insert_post(
				array(
					'post_type'     => 'post',
					'post_status'   => 'publish',
					'post_title'    => $cdc_p['title'],
					'post_name'     => $cdc_slug,
					'post_excerpt'  => $cdc_p['excerpt'],
					'post_content'  => $cdc_body,
					'post_date'     => $cdc_date,
					'post_category' => isset( $cdc_dev_term_ids[ $cdc_cat_slug ] ) ? array( $cdc_dev_term_ids[ $cdc_cat_slug ] ) : array(),
				),
				true
			);
			if ( is_wp_error( $cdc_id ) || ! $cdc_id ) {
				continue;
			}
			$cdc_dev_created++;
			if ( function_exists( 'cdc_seed_media' ) && ! empty( $cdc_p['capa'] ) ) {
				$cdc_att = cdc_seed_media( 'images/' . $cdc_p['capa'] );
				if ( $cdc_att ) {
					set_post_thumbnail( $cdc_id, $cdc_att );
				}
			}
			if ( 'destaque' === $cdc_key ) {
				stick_post( $cdc_id );
			}
		}
	}

	// "Páginas do site → Blog": categorias na ordem do estático (como o cliente configuraria).
	$cdc_dev_opt = get_option( 'cdc_opt_blog', array() );
	$cdc_dev_opt = is_array( $cdc_dev_opt ) ? $cdc_dev_opt : array();
	if ( empty( $cdc_dev_opt['categorias'] ) ) {
		$cdc_dev_opt['categorias'] = implode( "\n", array_keys( $cdc_dev_cats ) );
		update_option( 'cdc_opt_blog', $cdc_dev_opt );
	}

	file_put_contents( '/wordpress/dev-blog-report.json', wp_json_encode( array( 'created' => $cdc_dev_created ) ) );
} catch ( Throwable $cdc_e ) {
	// Nunca derruba o servidor local: só registra o erro.
	file_put_contents( '/wordpress/dev-blog-report.json', wp_json_encode( array( 'error' => $cdc_e->getMessage() ) ) );
}
