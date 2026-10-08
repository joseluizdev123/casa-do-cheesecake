<?php
/**
 * Seção 03b — banner amarelo da fatia por conta da casa, abaixo do cardápio.
 * Mesmo layout da seção 02; textos, botão e fotos (chocolate) próprios.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'cdc_customizer_sections', function ( $sections ) {
	$sections['cdc_primeira_fatia_2'] = array(
		'title'    => 'Fatia por conta da casa — banner abaixo do cardápio',
		'priority' => 35,
		'fields'   => array(
			'cdc_fatia2_titulo'            => array(
				'label'   => 'Título',
				'type'    => 'textarea',
				'default' => "Primeira vez por aqui?\nProve uma fatia por conta da casa",
				'help'    => 'Cada quebra de linha vira uma quebra no título.',
			),
			'cdc_fatia2_subtitulo'         => array(
				'label'   => 'Subtítulo',
				'type'    => 'text',
				'default' => '',
				'help'    => 'Opcional. Em branco, a linha não aparece.',
			),
			'cdc_fatia2_texto'             => array(
				'label'   => 'Texto (condições)',
				'type'    => 'textarea',
				'default' => '1 fatia por pessoa · de segunda a sexta, das 10h às 18h · enquanto durar o estoque do dia · dentro da área de entrega em São Paulo.',
			),
			'cdc_fatia2_botao'             => array(
				'label'   => 'Texto do botão',
				'type'    => 'text',
				'default' => 'Quero minha fatia',
			),
			'cdc_fatia2_botao_link'        => array(
				'label'   => 'Link do botão',
				'type'    => 'url',
				'default' => '',
				'help'    => 'Em branco = o botão aparece, mas não leva a lugar nenhum.',
			),
			'cdc_fatia2_imagem_frente'     => array(
				'label'   => 'Foto da fatia de cima (206 × 191)',
				'type'    => 'image',
				'default' => 'images/02-primeira-fatia-foreground-image.webp',
				'help'    => 'PNG/WebP com fundo transparente. Aparece espelhada, como no layout.',
			),
			'cdc_fatia2_imagem_frente_alt' => array(
				'label'   => 'Descrição da fatia de cima (acessibilidade)',
				'type'    => 'text',
				'default' => 'Fatia de cheesecake tradicional, sem cobertura',
			),
			'cdc_fatia2_imagem_fundo'      => array(
				'label'   => 'Foto da fatia da frente (305 × 305)',
				'type'    => 'image',
				'default' => 'images/02-primeira-fatia-background-image.webp',
				'help'    => 'PNG/WebP quadrado com fundo transparente.',
			),
			'cdc_fatia2_imagem_fundo_alt'  => array(
				'label'   => 'Descrição da fatia da frente (acessibilidade)',
				'type'    => 'text',
				'default' => 'Fatia de cheesecake com calda de frutas vermelhas escorrendo pela lateral, ao lado de morangos',
			),
		),
	);
	return $sections;
} );
