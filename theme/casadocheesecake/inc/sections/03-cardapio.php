<?php
/**
 * Seção 03 — Cardápio "Nossos cheesecakes" (Figma 7057:175).
 *
 * - Customizer: título, texto, rótulos dos botões e link do botão central.
 * - Cards: CPT cdc_cheesecake (4 primeiros por menu_order). Campos base em inc/cpt.php
 *   (descricao, tag, cor_fundo, precos, imagem_fatia, link_pedido).
 * - Seed: os 4 sabores do Figma. Enquanto não houver posts publicados, o template
 *   usa os mesmos dados do seed (render idêntico ao HTML estático).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'cdc_customizer_sections', function ( $sections ) {
	$sections['cdc_cardapio'] = array(
		'title'    => 'Cardápio (Nossos cheesecakes)',
		'priority' => 30,
		'fields'   => array(
			'cdc_cardapio_titulo'      => array(
				'label'   => 'Título',
				'type'    => 'text',
				'default' => 'Nossos cheesecakes',
			),
			'cdc_cardapio_texto'       => array(
				'label'   => 'Texto à direita do título',
				'type'    => 'textarea',
				'default' => 'Todos assados na nossa cozinha em São Paulo e entregues gelados, prontos para servir. Escolha o tamanho pela quantidade de pessoas.',
			),
			'cdc_cardapio_botao_pedir' => array(
				'label'   => 'Rótulo do botão de cada preço',
				'type'    => 'text',
				'default' => 'Pedir',
			),
			'cdc_cardapio_botao'       => array(
				'label'   => 'Rótulo do botão central',
				'type'    => 'text',
				'default' => 'Ver cardápio completo',
			),
			'cdc_cardapio_botao_link'  => array(
				'label'   => 'Link do botão central',
				'type'    => 'url',
				'default' => '',
				'help'    => 'Vazio = link do cardápio definido em "Contato e links globais".',
			),
		),
	);
	return $sections;
} );

/**
 * Conteúdo dos 4 cheesecakes do Figma (seed + fallback do template).
 *
 * @return array[]
 */
function cdc_cardapio_defaults() {
	// Links dos produtos no cardápio online (Brendi), conferidos um a um no site atual.
	$loja   = 'https://pedido.brendi.com.br/a-casa-do-cheesecake/produto/';
	$precos = static function ( $fatia, $p600, $p1200, $p1800 ) use ( $loja ) {
		return "R$ 28,90 | Fatia 150 g | {$loja}{$fatia}\n"
			. "R$ 99 | 600 g · 6 mini fatias | {$loja}{$p600}\n"
			. "R$ 185 | 1,2 kg · serve 8 | {$loja}{$p1200}\n"
			. "R$ 252 | 1,8 kg · serve 10 a 12 | {$loja}{$p1800}";
	};
	return array(
		array(
			'title'       => 'Frutas Vermelhas',
			'menu_order'  => 1,
			'featured'    => 'images/03-cardapio-frutas-vermelhas.webp',
			'meta'        => array(
				'descricao' => 'Base assada de cream cheese com calda de frutas vermelhas inteiras.',
				'tag'       => 'Mais pedido',
				'cor_fundo' => '#b0282e',
				'precos'    => $precos( 'cheesecake-de-frutas-vermelhas-fatia', 'cheesecake-de-frutas-vermelhas-600g-rende-6-mini-fatias', 'cheesecake-de-frutas-vermelhas-12-kg-rende-8-fatias', 'cheesecake-de-frutas-vermelhas-18-kg-rende-de-10-a-12-fatias' ),
			),
			'meta_images' => array( 'imagem_fatia' => 'images/sabor-frutas-vermelhas.png' ),
		),
		array(
			'title'       => 'Doce de Leite',
			'menu_order'  => 2,
			'featured'    => 'images/03-cardapio-doce-de-leite.webp',
			'meta'        => array(
				'descricao' => 'A mesma base assada, coberta com doce de leite.',
				'tag'       => '',
				'cor_fundo' => '#ffb82e',
				'precos'    => $precos( 'cheesecake-de-doce-de-leite-fatia', 'cheesecake-de-doce-de-leite-600gr-rende-6-mini-fatias', 'cheesecake-de-doce-de-leite-12-kg-rende-8-fatias', 'cheesecake-de-doce-de-leite-18-kg-rende-de-10-a-12-fatias' ),
			),
			'meta_images' => array( 'imagem_fatia' => 'images/sabor-doce-de-leite.png' ),
		),
		array(
			'title'       => 'Morangos',
			'menu_order'  => 3,
			'featured'    => 'images/03-cardapio-morangos.webp',
			'meta'        => array(
				'descricao' => 'Calda de morangos frescos sobre a base assada.',
				'tag'       => '',
				'cor_fundo' => '#ffc0c5',
				'precos'    => $precos( 'cheesecake-de-morango-fatia', 'cheesecake-de-morango-600gr-rende-6-mini-fatias', 'cheesecake-de-morangos-12-kg-rende-8-fatias', 'cheesecake-de-morango-18-kg-rende-de-10-a-12-fatias' ),
			),
			'meta_images' => array( 'imagem_fatia' => 'images/sabor-morangos.png' ),
		),
		array(
			'title'       => 'Blue Berry',
			'menu_order'  => 4,
			'featured'    => 'images/03-cardapio-blue-berry.webp',
			'meta'        => array(
				'descricao' => 'Calda de blueberry, ácida na medida para cortar o creme.',
				'tag'       => '',
				'cor_fundo' => '#b5d9fb',
				'precos'    => $precos( 'cheesecake-de-blueberry-fatia', 'cheesecake-de-blue-berry-600gr-rende-6-mini-fatias', 'cheesecake-de-blue-berry-12-kg-rende-8-fatias', 'cheesecake-de-blue-berry-18-kg-rende-de-10-a-12-fatias' ),
			),
			'meta_images' => array( 'imagem_fatia' => 'images/sabor-blue-berry.png' ),
		),
	);
}

add_filter( 'cdc_seed_posts', function ( $posts ) {
	foreach ( cdc_cardapio_defaults() as $item ) {
		$item['post_type'] = 'cdc_cheesecake';
		$posts[]           = $item;
	}
	return $posts;
} );

/**
 * "preço | tamanho | link (opcional)" por linha → lista de opções.
 *
 * @param string $text Conteúdo do campo precos.
 * @return array[] Cada item: array( 'preco' => '', 'link' => '', 'tamanho' => '' ).
 */
function cdc_cardapio_parse_precos( $text ) {
	$out = array();
	foreach ( cdc_lines( $text ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 3 ) );
		$out[] = array(
			'preco'   => $parts[0],
			'link'    => isset( $parts[2] ) ? esc_url_raw( $parts[2] ) : '',
			// Espaço não separável antes do "·": a linha nunca quebra com o ponto
			// órfão no início ("1,8 kg / · serve 10 a 12"), igual ao HTML estático.
			'tamanho' => isset( $parts[1] ) ? str_replace( ' · ', "\u{00A0}· ", $parts[1] ) : '',
		);
	}
	return $out;
}

/**
 * Link interno (#âncora) funciona também fora da home.
 *
 * @param string $url URL ou âncora.
 * @return string
 */
function cdc_cardapio_href( $url ) {
	return ( is_string( $url ) && 0 === strpos( $url, '#' ) ) ? cdc_anchor( $url ) : $url;
}

/**
 * Cards normalizados: posts do CPT (até 4) ou, sem posts, os defaults do Figma.
 *
 * @return array[]
 */
function cdc_cardapio_items() {
	$fallback_link = cdc_mod( 'cdc_contato_cardapio' );
	$items         = array();
	// A foto provisória do Figma é a mesma nos 4 sabores (frutas vermelhas):
	// enquanto ela for usada, o alt descreve o que a imagem mostra.
	$fallback_alt = 'Cheesecake inteiro com calda de frutas vermelhas (foto ilustrativa)';

	foreach ( cdc_posts( 'cdc_cheesecake', 4 ) as $post ) {
		$title = get_the_title( $post );
		$image = cdc_asset( 'images/03-cardapio-cheesecake.png' );
		$img_w = 801;
		$img_h = 801;
		$alt   = '';
		$thumb = get_post_thumbnail_id( $post );
		if ( $thumb ) {
			$src = wp_get_attachment_image_src( $thumb, 'full' );
			if ( $src ) {
				$image = $src[0];
				$img_w = (int) $src[1];
				$img_h = (int) $src[2];
			}
			$alt = (string) get_post_meta( $thumb, '_wp_attachment_image_alt', true );
			if ( '' === $alt ) {
				// Destacada importada pelo seed = a foto provisória compartilhada.
				$placeholder = 'images/03-cardapio-cheesecake.png' === get_post_meta( $thumb, '_cdc_seed_src', true );
				$alt         = $placeholder ? $fallback_alt : 'Cheesecake inteiro sabor ' . $title;
			}
		} else {
			$alt = $fallback_alt;
		}
		$link    = (string) cdc_meta( $post->ID, 'link_pedido' );
		$items[] = array(
			'title'     => $title,
			'descricao' => (string) cdc_meta( $post->ID, 'descricao' ),
			'tag'       => (string) cdc_meta( $post->ID, 'tag' ),
			'cor'       => (string) sanitize_hex_color( cdc_meta( $post->ID, 'cor_fundo' ) ),
			'image'     => $image,
			'image_w'   => $img_w,
			'image_h'   => $img_h,
			'alt'       => $alt,
			'link'      => cdc_cardapio_href( '' !== $link ? $link : $fallback_link ),
			'precos'    => cdc_cardapio_parse_precos( cdc_meta( $post->ID, 'precos' ) ),
		);
	}

	if ( ! $items ) {
		foreach ( cdc_cardapio_defaults() as $d ) {
			$cdc_dim  = @getimagesize( CDC_DIR . '/assets/' . $d['featured'] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			$items[] = array(
				'title'     => $d['title'],
				'descricao' => $d['meta']['descricao'],
				'tag'       => $d['meta']['tag'],
				'cor'       => $d['meta']['cor_fundo'],
				'image'     => cdc_asset( $d['featured'] ),
				'image_w'   => $cdc_dim ? (int) $cdc_dim[0] : 801,
				'image_h'   => $cdc_dim ? (int) $cdc_dim[1] : 801,
				'alt'       => 'Cheesecake inteiro sabor ' . $d['title'],
				'link'      => cdc_cardapio_href( $fallback_link ),
				'precos'    => cdc_cardapio_parse_precos( $d['meta']['precos'] ),
			);
		}
	}

	return $items;
}

/**
 * IDs de nós do Figma por card (espelha data-figma-node do HTML estático).
 *
 * @return array[]
 */
function cdc_cardapio_figma_nodes() {
	return array(
		array(
			'card'     => '7057:181',
			'media'    => '7057:182',
			'photo'    => '7057:183',
			'tag'      => '7057:186',
			'tag_icon' => '7057:191',
			'tag_text' => '7057:195',
			'content'  => '7057:196',
			'info'     => '7057:197',
			'name'     => '7057:198',
			'desc'     => '7057:199',
			'prices'   => '7057:200',
			'rows'     => array(
				array( '7057:201', '7057:202', '7057:203', '7057:204', '7073:341' ),
				array( '7057:207', '7057:208', '7057:209', '7057:210', '7073:347' ),
				array( '7057:213', '7057:214', '7057:215', '7057:216', '7073:352' ),
				array( '7057:219', '7057:220', '7057:221', '7057:222', '7073:351' ),
			),
		),
		array(
			'card'    => '7057:225',
			'media'   => '7057:226',
			'photo'   => '7057:227',
			'content' => '7057:230',
			'info'    => '7057:231',
			'name'    => '7057:232',
			'desc'    => '7057:233',
			'prices'  => '7057:234',
			'rows'    => array(
				array( '7057:235', '7057:236', '7057:237', '7057:238', '7073:384' ),
				array( '7057:241', '7057:242', '7057:243', '7057:244', '7073:357' ),
				array( '7057:247', '7057:248', '7057:249', '7057:250', '7073:360' ),
				array( '7057:253', '7057:254', '7057:255', '7057:256', '7073:363' ),
			),
		),
		array(
			'card'    => '7057:259',
			'media'   => '7057:260',
			'photo'   => '7057:261',
			'content' => '7057:264',
			'info'    => '7057:265',
			'name'    => '7057:266',
			'desc'    => '7057:267',
			'prices'  => '7057:268',
			'rows'    => array(
				array( '7057:269', '7057:270', '7057:271', '7057:272', '7073:387' ),
				array( '7057:275', '7057:276', '7057:277', '7057:278', '7073:358' ),
				array( '7057:281', '7057:282', '7057:283', '7057:284', '7073:361' ),
				array( '7057:287', '7057:288', '7057:289', '7057:290', '7073:364' ),
			),
		),
		array(
			'card'    => '7057:293',
			'media'   => '7057:294',
			'photo'   => '7057:295',
			'content' => '7057:298',
			'info'    => '7057:299',
			'name'    => '7057:300',
			'desc'    => '7057:301',
			'prices'  => '7057:302',
			'rows'    => array(
				array( '7057:303', '7057:304', '7057:305', '7057:306', '7073:388' ),
				array( '7057:309', '7057:310', '7057:311', '7057:312', '7073:359' ),
				array( '7057:315', '7057:316', '7057:317', '7057:318', '7073:362' ),
				array( '7057:321', '7057:322', '7057:323', '7057:324', '7073:365' ),
			),
		),
	);
}

/**
 * Atributo data-figma-node (vazio quando o nó não existe no Figma, ex. 5º card).
 *
 * @param array  $nodes Mapa do card (item de cdc_cardapio_figma_nodes()).
 * @param string $key   Chave do nó ('card', 'media'…) ou 'rows'.
 * @param int    $row   Índice da linha de preço (só para 'rows').
 * @param int    $col   0 linha · 1 opção · 2 preço · 3 tamanho · 4 botão.
 * @return string Atributo pronto (com espaço inicial) ou ''.
 */
function cdc_cardapio_node_attr( $nodes, $key, $row = null, $col = null ) {
	$id = '';
	if ( 'rows' === $key ) {
		if ( isset( $nodes['rows'][ $row ][ $col ] ) ) {
			$id = $nodes['rows'][ $row ][ $col ];
		}
	} elseif ( isset( $nodes[ $key ] ) ) {
		$id = $nodes[ $key ];
	}
	return '' === $id ? '' : ' data-figma-node="' . esc_attr( $id ) . '"';
}
