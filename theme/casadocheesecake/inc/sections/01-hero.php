<?php
/**
 * Seção 01 — Hero (Figma 7057:81): registro do Customizer.
 *
 * Defaults = copy exata do Figma. Imagens editáveis: foto de fundo da faixa
 * (7057:86) e PNG recortado sobreposto (7057:87). Links dos botões vêm dos
 * campos globais (cdc_contato_cardapio / cdc_contato_whatsapp).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'cdc_customizer_sections', function ( $sections ) {
	$sections['cdc_hero'] = array(
		'title'    => 'Hero (topo da página)',
		'priority' => 10,
		'fields'   => array(
			'cdc_hero_titulo'       => array(
				'label'   => 'Título',
				'type'    => 'textarea',
				'default' => 'O autêntico New York Cheesecake, entregue em São Paulo',
			),
			'cdc_hero_imagem'       => array(
				'label'   => 'Foto de fundo da faixa',
				'type'    => 'image',
				'default' => 'images/01-hero-background.webp',
				'help'    => 'Foto principal (1440 × 454 visível no desktop; o enquadramento segue o layout).',
			),
			'cdc_hero_imagem_alt'   => array(
				'label'   => 'Descrição da foto (texto alternativo)',
				'type'    => 'text',
				'default' => 'Fatia de New York Cheesecake coberta com calda de frutas vermelhas sobre uma pedra escura, com um potinho de calda e a caixa da Casa do Cheesecake ao fundo',
			),
			'cdc_hero_recorte'      => array(
				'label'   => 'Recorte sobreposto (PNG/WebP com transparência)',
				'type'    => 'image',
				'default' => 'images/01-hero-png.webp',
				'help'    => 'Imagem recortada que “sai” da foto por cima do título. Precisa ter o mesmo enquadramento da foto de fundo.',
			),
			'cdc_hero_desde_rotulo' => array(
				'label'   => 'Selo — rótulo',
				'type'    => 'text',
				'default' => 'DESDE',
			),
			'cdc_hero_desde_ano'    => array(
				'label'   => 'Selo — ano',
				'type'    => 'text',
				'default' => '2004',
			),
			'cdc_hero_info_1'       => array(
				'label'   => 'Destaque 1 (entrega)',
				'type'    => 'text',
				'default' => 'ENTREGA EM TODA SÃO PAULO',
			),
			'cdc_hero_info_2'       => array(
				'label'   => 'Destaque 2 (fatias)',
				'type'    => 'text',
				'default' => '+5 milhões de fatias servidas',
			),
			'cdc_hero_info_3'       => array(
				'label'   => 'Destaque 3 (avaliações)',
				'type'    => 'text',
				'default' => '+355 avaliações no Google',
			),
			'cdc_hero_botao_1'      => array(
				'label'   => 'Botão principal (leva ao link do cardápio)',
				'type'    => 'text',
				'default' => 'Ver cardápio e pedir',
			),
			'cdc_hero_botao_2'      => array(
				'label'   => 'Botão secundário (leva ao WhatsApp)',
				'type'    => 'text',
				'default' => 'Falar no WhatsApp',
			),
			'cdc_hero_texto'        => array(
				'label'   => 'Texto de apoio',
				'type'    => 'textarea',
				'default' => 'Assado no estilo americano, com cream cheese Philadelphia de verdade, textura densa e calda até a borda. Mais de **5 milhões** de fatias servidas desde 2004.',
				'help'    => 'Use **texto** para negrito.',
			),
		),
	);
	return $sections;
} );
