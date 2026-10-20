<?php
/**
 * Página Contato (templates/contato.php). Espelha src/paginas/contato/*.html.
 *
 * - Option page "Contato" (Páginas do site): título, texto, fotos do carrossel e
 *   os interruptores do selo e das Perguntas frequentes.
 * - CPT cdc_canal ("Canais de contato", campos em inc/cpt.php) + seed dos 6 canais
 *   do layout, na ordem da página.
 * - Seed da página /contato/ com o template.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fotos padrão do carrossel (caminho em assets/ → texto alternativo do layout).
 *
 * @return array<string, string>
 */
function cdc_contato_fotos_defaults() {
	return array(
		'images/contato-foto.webp'              => 'Fatia de New York Cheesecake com calda de frutas vermelhas em frente à caixa da Casa do Cheesecake',
		'images/contato-combo.webp'             => 'Fatias de cheesecake de frutas vermelhas e de chocolate em frente às caixas da Casa do Cheesecake',
		'images/contato-duplo-chocolate.webp'   => 'Fatia de cheesecake Duplo Chocolate com calda de chocolate, ao lado da caixa',
		'images/contato-goiaba.webp'            => 'Cheesecake inteiro de goiaba com uma fatia cortada, em frente à caixa',
		'images/contato-oreo.webp'              => 'Fatia de cheesecake de Oreo com calda de chocolate, ao lado da caixa',
		'images/contato-frutas-vermelhas.webp'  => 'Cheesecake inteiro de frutas vermelhas com uma fatia cortada, cercado de frutas vermelhas',
		'images/contato-limone.webp'            => 'Fatia de cheesecake Limone com raspas de limão, ao lado da calda de morango e da caixa',
		'images/contato-morango.webp'           => 'Cheesecake inteiro de morango com uma fatia cortada, cercado de morangos',
	);
}

/**
 * Canais iniciais (copy exata do layout, na ordem da página).
 *
 * Alimenta o seeder e serve de fallback no template enquanto o CPT estiver vazio.
 *
 * @return array[] Lista de array( 'title', 'contato', 'botao', 'link' ).
 */
function cdc_contato_canais_defaults() {
	return array(
		array(
			'title'   => 'WhatsApp',
			'contato' => '(11) 95602-2366',
			'botao'   => 'Conversar',
			'link'    => 'https://wa.me/5511956022366?text=Ol%C3%A1!+Passei+pelo+site+e+gostaria+de+mais+informa%C3%A7%C3%B5es.',
		),
		array(
			'title'   => 'Pedidos delivery',
			'contato' => 'Cardápio online, entrega em São Paulo',
			'botao'   => 'Pedir',
			'link'    => 'https://pedido.brendi.com.br/a-casa-do-cheesecake',
		),
		array(
			'title'   => 'Pedidos para restaurantes',
			'contato' => '(11) 99328-0819',
			'botao'   => 'Conversar',
			'link'    => 'https://wa.me/5511993280819',
		),
		array(
			'title'   => 'Outros assuntos',
			'contato' => 'atendimento@acasadocheesecake.com.br',
			'botao'   => 'Enviar',
			'link'    => 'mailto:atendimento@acasadocheesecake.com.br',
		),
		array(
			'title'   => 'Instagram',
			'contato' => '@acasadocheesecakeoficial',
			'botao'   => 'Seguir',
			'link'    => 'https://www.instagram.com/acasadocheesecakeoficial/',
		),
		array(
			'title'   => 'Facebook',
			'contato' => 'acasadocheesecakeoficial',
			'botao'   => 'Curtir',
			'link'    => 'https://www.facebook.com/acasadocheesecakeoficial',
		),
	);
}

/**
 * Canais para o template: posts do CPT cdc_canal (ordem do admin) ou defaults do layout.
 *
 * @return array[] Lista de array( 'title', 'contato', 'botao', 'link' ).
 */
function cdc_contato_canais() {
	$items = array();
	foreach ( cdc_posts( 'cdc_canal' ) as $cdc_post ) {
		$items[] = array(
			'title'   => (string) $cdc_post->post_title,
			'contato' => (string) cdc_meta( $cdc_post->ID, 'contato' ),
			'botao'   => (string) cdc_meta( $cdc_post->ID, 'botao' ),
			'link'    => trim( (string) cdc_meta( $cdc_post->ID, 'link' ) ),
		);
	}
	return $items ? $items : cdc_contato_canais_defaults();
}

/**
 * Texto do contato com quebra opcional depois do "@" de e-mails (<wbr>), escapado.
 *
 * @param string $text Texto do campo Contato.
 * @return string HTML seguro.
 */
function cdc_contato_texto( $text ) {
	$at = strpos( $text, '@' );
	if ( ! $at ) {
		return esc_html( $text );
	}
	return esc_html( substr( $text, 0, $at + 1 ) ) . '<wbr>' . esc_html( substr( $text, $at + 1 ) );
}

/**
 * Texto alternativo de uma foto do carrossel: o da Mídia; vazio = o do layout
 * (fotos padrão) ou um genérico.
 *
 * @param array $img Item de cdc_opt_gallery().
 * @return string
 */
function cdc_contato_foto_alt( $img ) {
	if ( '' !== trim( $img['alt'] ) ) {
		return $img['alt'];
	}
	$file = basename( (string) wp_parse_url( $img['url'], PHP_URL_PATH ) );
	foreach ( cdc_contato_fotos_defaults() as $rel => $alt ) {
		if ( basename( $rel ) === $file ) {
			return $alt;
		}
	}
	return __( 'Cheesecake da Casa do Cheesecake', 'casadocheesecake' );
}

add_filter( 'cdc_option_pages', function ( $pages ) {
	$pages['contato'] = array(
		'title'  => 'Contato',
		'intro'  => 'Textos e fotos da página Contato. A lista de canais (WhatsApp, delivery, e-mail, redes sociais) fica em "Canais de contato", no menu do painel: um item por linha, na ordem do campo Ordem.',
		'order'  => 30,
		'fields' => array(
			'titulo'    => array(
				'label'   => 'Título',
				'type'    => 'text',
				'default' => 'Fale conosco',
			),
			'texto'     => array(
				'label'   => 'Texto',
				'type'    => 'textarea',
				'rows'    => 3,
				'default' => 'Que bom que você veio conversar com a gente. Escolha sobre o que quer falar.',
				'help'    => 'Linha em branco separa parágrafos.',
			),
			'fotos'     => array(
				'label'   => 'Fotos do carrossel',
				'type'    => 'gallery',
				'default' => array_keys( cdc_contato_fotos_defaults() ),
				'help'    => 'Passam sozinhas ao lado dos canais, na ordem escolhida. Fotos quadradas. O texto alternativo de cada foto é o da Mídia.',
			),
			'selo'      => array(
				'label'   => 'Mostrar o selo sobre as fotos',
				'type'    => 'checkbox',
				'default' => '1',
				'help'    => 'O mesmo selo da home (Personalizar → Sobre).',
			),
			'perguntas' => array(
				'label'   => 'Mostrar Perguntas frequentes',
				'type'    => 'checkbox',
				'default' => '1',
				'help'    => 'No fim da página. As perguntas ficam em "Perguntas frequentes", no menu do painel.',
			),
		),
	);
	return $pages;
} );

add_filter( 'cdc_seed_posts', function ( $posts ) {
	foreach ( cdc_contato_canais_defaults() as $cdc_i => $cdc_item ) {
		$posts[] = array(
			'post_type'  => 'cdc_canal',
			'title'      => $cdc_item['title'],
			'menu_order' => $cdc_i + 1,
			'meta'       => array(
				'contato' => $cdc_item['contato'],
				'botao'   => $cdc_item['botao'],
				'link'    => $cdc_item['link'],
			),
		);
	}
	return $posts;
} );

add_filter( 'cdc_seed_pages', function ( $pages ) {
	$pages[] = array(
		'slug'     => 'contato',
		'title'    => 'Contato',
		'template' => 'templates/contato.php',
	);
	return $pages;
} );

// Fim do importador: fotos padrão do carrossel ganham na Mídia o texto alternativo do layout (só se vazio).
$cdc_contato_alts = function () {
	foreach ( cdc_contato_fotos_defaults() as $rel => $alt ) {
		$found = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'meta_key'       => '_cdc_seed_src', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => $rel, // phpcs:ignore WordPress.DB.SlowDBQuery
				'fields'         => 'ids',
				'posts_per_page' => 1,
			)
		);
		if ( $found && '' === (string) get_post_meta( $found[0], '_wp_attachment_image_alt', true ) ) {
			update_post_meta( $found[0], '_wp_attachment_image_alt', $alt );
		}
	}
};
add_action( 'add_option_cdc_seeded', $cdc_contato_alts );
add_action( 'update_option_cdc_seeded', $cdc_contato_alts );
