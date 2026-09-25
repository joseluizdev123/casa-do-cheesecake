<?php
/**
 * Seção 07 — Depoimentos (Figma 7057:361).
 *
 * - Customizer: título, nota exibida (estrelas), contagem de avaliações e rótulo da fonte.
 * - CPT cdc_depoimento (campos base 'texto', 'estrelas', 'data', 'fonte' em inc/cpt.php)
 *   + seed dos 3 depoimentos do Figma + 1 do site oficial com a copy exata (data corrigida de "Setenbro" para "Setembro 2026";
 *   os pontos sem espaço do 1º depoimento seguem como no layout — revisar no admin).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'cdc_customizer_sections', function ( $sections ) {
	$sections['cdc_depoimentos'] = array(
		'title'    => 'Depoimentos',
		'priority' => 70,
		'fields'   => array(
			'cdc_depo_titulo'    => array(
				'label'   => 'Título',
				'type'    => 'text',
				'default' => 'O que dizem quem já pediu',
			),
			'cdc_depo_nota'      => array(
				'label'   => 'Nota exibida (estrelas, 1 a 5)',
				'type'    => 'text',
				'default' => '5',
			),
			'cdc_depo_contagem'  => array(
				'label'   => 'Número de avaliações',
				'type'    => 'text',
				'default' => '+354',
				'help'    => 'Ex.: +354',
			),
			'cdc_depo_fonte'     => array(
				'label'   => 'Rótulo da fonte das avaliações',
				'type'    => 'text',
				'default' => 'Avaliações verificadas no Google',
			),
		),
	);
	return $sections;
} );

/**
 * Depoimentos iniciais (copy exata do Figma, na ordem do layout).
 *
 * Alimenta o seeder e serve de fallback no template enquanto o CPT estiver vazio.
 *
 * @return array[] Lista de array( 'nome', 'texto', 'estrelas', 'data', 'fonte' ).
 */
function cdc_depoimentos_defaults() {
	return array(
		array(
			'nome'     => 'Layza Souza',
			'texto'    => '“Comprei para o Dia das Mães e foi um baita sucesso em casa. Todos amaram e comeram muito.A torta é cremosa na medida, a calda é uma fartura só.Atendimento, entrega, embalagem e cheesecake impecáveis.”',
			'estrelas' => 5,
			'data'     => 'Setembro 2026',
			'fonte'    => 'Avaliação publicada no Google.',
		),
		array(
			'nome'     => 'Lu Cunha',
			'texto'    => '“Atendimento atencioso e simpático. Cheesecake de frutas vermelhas maravilhosa, entregue perfeitamente embalada. O presente foi um sucesso e surpreendeu quem recebeu. Recomendo a Casa do Cheesecake.',
			'estrelas' => 5,
			'data'     => 'Setembro 2026',
			'fonte'    => 'Avaliação publicada no Google.',
		),
		array(
			'nome'     => 'Giovanna Marescalchi',
			'texto'    => '“Muito bom! Desde o atendimento até a entrega! E com toda certeza o sabor e a qualidade do cheesecake… super recomendo 🤍.”',
			'estrelas' => 5,
			'data'     => 'Setembro 2026',
			'fonte'    => 'Avaliação publicada no Google.',
		),
		// 4º depoimento: site oficial (acasadocheesecake.com.br), sem data publicada.
		array(
			'nome'     => 'Sandra Sejtman',
			'texto'    => '“Uma delícia!!! Sempre peço nos eventos. Atendimento maravilhoso e eficiência na entrega.”',
			'estrelas' => 5,
			'data'     => '',
			'fonte'    => 'Avaliação publicada no Google.',
		),
	);
}

add_filter( 'cdc_seed_posts', function ( $posts ) {
	foreach ( cdc_depoimentos_defaults() as $cdc_i => $cdc_item ) {
		$posts[] = array(
			'post_type'  => 'cdc_depoimento',
			'title'      => $cdc_item['nome'],
			'menu_order' => $cdc_i + 1,
			'meta'       => array(
				'texto'    => $cdc_item['texto'],
				'estrelas' => (string) $cdc_item['estrelas'],
				'data'     => $cdc_item['data'],
				'fonte'    => $cdc_item['fonte'],
			),
		);
	}
	return $posts;
} );

/**
 * Normaliza uma nota para o intervalo 1–5 (vazio/0 = 5, como no layout).
 *
 * @param mixed $value Valor bruto.
 * @return int
 */
function cdc_depoimentos_stars( $value ) {
	$n = (int) $value;
	return $n < 1 ? 5 : min( 5, $n );
}

/**
 * Depoimentos para o template: posts do CPT cdc_depoimento (ordem do admin) ou defaults do Figma.
 *
 * @return array[] Lista de array( 'nome', 'texto', 'estrelas', 'data', 'fonte' ).
 */
function cdc_depoimentos_items() {
	$items = array();
	foreach ( cdc_posts( 'cdc_depoimento' ) as $cdc_post ) {
		$items[] = array(
			'nome'     => (string) $cdc_post->post_title,
			'texto'    => (string) cdc_meta( $cdc_post->ID, 'texto' ),
			'estrelas' => cdc_depoimentos_stars( cdc_meta( $cdc_post->ID, 'estrelas' ) ),
			'data'     => (string) cdc_meta( $cdc_post->ID, 'data' ),
			'fonte'    => (string) cdc_meta( $cdc_post->ID, 'fonte' ),
		);
	}
	if ( ! $items ) {
		$items = cdc_depoimentos_defaults();
	}
	return $items;
}
