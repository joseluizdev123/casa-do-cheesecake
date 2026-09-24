<?php
/**
 * Seção 05 — Diferenciais (Figma 7057:330).
 *
 * - Customizer: título e texto do cabeçalho.
 * - CPT cdc_diferencial (campos base em inc/cpt.php: texto, tag, destaque) + seed
 *   dos 3 cards com a copy exata do Figma e as fotos de assets/images/.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

/**
 * Conteúdo inicial dos cards (copy exata do Figma).
 *
 * Alimenta o seeder e serve de fallback no template enquanto o CPT estiver vazio,
 * para o tema renderizar idêntico ao estático mesmo antes do seed.
 *
 * @return array[]
 */
function cdc_diferenciais_defaults() {
	return array(
		array(
			'title'    => 'Cream cheese Philadelphia',
			'texto'    => 'É o item mais caro da receita — e exatamente o que não se pode reduzir. Diminuir seria entregar outra coisa no lugar do que a pessoa pediu.',
			'tag'      => 'na quantidade certa',
			'destaque' => '1',
			'featured' => 'images/05-diferenciais-cream-cheese.webp',
			'alt'      => 'Tigela de cream cheese batido ao lado da embalagem de Philadelphia',
		),
		array(
			'title'    => 'Assado, com ponto de forno',
			'texto'    => 'O ponto depende de uma combinação exata de tempo e temperatura. Erra em um dos dois e a torta racha, seca ou não firma.',
			'tag'      => '',
			'destaque' => '',
			'featured' => 'images/05-diferenciais-assado.webp',
			'alt'      => 'Fatia de cheesecake assado coberta de framboesas e mirtilos',
		),
		array(
			'title'    => 'Calda até a borda',
			'texto'    => 'A cobertura é generosa por decisão, não por acidente. Fatia com calda escassa não é a sobremesa que a gente aprendeu a fazer.',
			'tag'      => '',
			'destaque' => '',
			'featured' => 'images/05-diferenciais-calda.webp',
			'alt'      => 'Cheesecake inteiro com calda de frutas vermelhas cobrindo a superfície até a borda',
		),
	);
}

add_filter( 'cdc_customizer_sections', function ( $sections ) {
	$sections['cdc_diferenciais'] = array(
		'title'    => 'Diferenciais',
		'priority' => 50,
		'fields'   => array(
			'cdc_dif_titulo' => array(
				'label'   => 'Título',
				'type'    => 'textarea',
				'default' => 'O que faz um cheesecake ser o de verdade',
			),
			'cdc_dif_texto'  => array(
				'label'   => 'Texto',
				'type'    => 'textarea',
				'default' => 'Existe uma diferença grande entre um cheesecake assado e uma sobremesa fria com cara de cheesecake. A nossa receita é a original americana, e ela não admite substituição de ingrediente nem atalho de processo. É por isso que a textura é densa, o sabor é lácteo e a fatia se sustenta em pé no prato.',
			),
		),
	);
	return $sections;
} );

add_filter( 'cdc_seed_posts', function ( $posts ) {
	foreach ( cdc_diferenciais_defaults() as $cdc_i => $cdc_item ) {
		$posts[] = array(
			'post_type'  => 'cdc_diferencial',
			'title'      => $cdc_item['title'],
			'menu_order' => $cdc_i + 1,
			'featured'   => $cdc_item['featured'],
			'meta'       => array(
				'texto'    => $cdc_item['texto'],
				'tag'      => $cdc_item['tag'],
				'destaque' => $cdc_item['destaque'],
			),
		);
	}
	return $posts;
} );
