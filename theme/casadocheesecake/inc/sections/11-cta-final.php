<?php
/**
 * Seção 11 — CTA final "Peça o seu cheesecake hoje" (Figma 7057:955): registro do Customizer.
 *
 * Defaults = copy exata do Figma. O título respeita as quebras de linha do
 * campo (uma linha por linha do textarea) e o espaço final de "Peça o seu "
 * faz parte do layout do Figma (a 1ª linha é centralizada contando esse espaço).
 * Links dos botões: se ficarem em branco, usam os links globais
 * (cdc_contato_cardapio / cdc_contato_whatsapp).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'cdc_customizer_sections', function ( $sections ) {
	$sections['cdc_cta_final'] = array(
		'title'    => 'CTA final (Peça o seu cheesecake)',
		'priority' => 110,
		'fields'   => array(
			'cdc_cta_titulo'       => array(
				'label'   => 'Título',
				'type'    => 'textarea',
				'default' => "Peça o seu \ncheesecake hoje",
				'help'    => 'Cada linha do campo vira uma linha do título.',
			),
			'cdc_cta_texto'        => array(
				'label'   => 'Texto de apoio',
				'type'    => 'textarea',
				'default' => 'Assado na nossa cozinha, entregue gelado em toda São Paulo.',
			),
			'cdc_cta_imagem'       => array(
				'label'   => 'Fatia sobreposta ao título (PNG/WebP com transparência)',
				'type'    => 'image',
				'default' => 'images/11-cta-final-fatia.webp',
				'help'    => 'Exibida em 198 × 184 px, espelhada na horizontal e centralizada sobre o título.',
			),
			'cdc_cta_imagem_alt'   => array(
				'label'   => 'Descrição da fatia (texto alternativo)',
				'type'    => 'text',
				'default' => 'Fatia de cheesecake tradicional, sem cobertura',
			),
			'cdc_cta_botao'        => array(
				'label'   => 'Botão principal — rótulo',
				'type'    => 'text',
				'default' => 'Pedir agora',
			),
			'cdc_cta_botao_url'    => array(
				'label'   => 'Botão principal — link',
				'type'    => 'url',
				'default' => '',
				'help'    => 'Em branco = link global do cardápio / pedido.',
			),
			'cdc_cta_ou'           => array(
				'label'   => 'Separador entre os botões',
				'type'    => 'text',
				'default' => 'Ou',
			),
			'cdc_cta_whatsapp'     => array(
				'label'   => 'Link de texto — rótulo',
				'type'    => 'text',
				'default' => 'Falar no WhatsApp',
			),
			'cdc_cta_whatsapp_url' => array(
				'label'   => 'Link de texto — link',
				'type'    => 'url',
				'default' => '',
				'help'    => 'Em branco = link global do WhatsApp.',
			),
		),
	);
	return $sections;
} );
