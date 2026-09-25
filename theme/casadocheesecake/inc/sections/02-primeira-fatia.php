<?php
/**
 * Seção 02 — Primeira fatia (Figma "Call to action" 7057:164).
 *
 * Banner amarelo com oferta da primeira fatia. Tudo editável no Customizer:
 * título, textos, botão (link padrão = WhatsApp global) e as duas fotos de fatia.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'cdc_customizer_sections', function ( $sections ) {
	$sections['cdc_primeira_fatia'] = array(
		'title'    => 'Primeira fatia (banner amarelo)',
		'priority' => 20,
		'fields'   => array(
			'cdc_fatia_titulo'            => array(
				'label'   => 'Título',
				'type'    => 'textarea',
				'default' => "Primeira vez por aqui?\nProve uma fatia por conta da casa",
				'help'    => 'Cada quebra de linha vira uma quebra no título.',
			),
			'cdc_fatia_subtitulo'         => array(
				'label'   => 'Subtítulo',
				'type'    => 'text',
				'default' => 'Você paga só os R$15 da entrega.',
			),
			'cdc_fatia_texto'             => array(
				'label'   => 'Texto (condições)',
				'type'    => 'textarea',
				'default' => '1 fatia por pessoa · de segunda a sexta, das 10h às 17h · enquanto durar o estoque do dia · dentro da área de entrega em São Paulo.',
			),
			'cdc_fatia_botao'             => array(
				'label'   => 'Texto do botão',
				'type'    => 'text',
				'default' => 'Quero minha fatia',
			),
			'cdc_fatia_botao_link'        => array(
				'label'   => 'Link do botão',
				'type'    => 'url',
				'default' => 'https://wa.me/5511956022366?text=Ol%C3%A1!+Quero+provar+minha+fatia+por+conta+da+casa.',
				'help'    => 'Padrão: WhatsApp com a mensagem "Quero provar minha fatia por conta da casa". Em branco = WhatsApp de "Contato e links globais".',
			),
			'cdc_fatia_imagem_frente'     => array(
				'label'   => 'Foto da fatia de cima (206 × 191)',
				'type'    => 'image',
				'default' => 'images/02-primeira-fatia-foreground-image.webp',
				'help'    => 'PNG/WebP com fundo transparente. Aparece espelhada, como no layout.',
			),
			'cdc_fatia_imagem_frente_alt' => array(
				'label'   => 'Descrição da fatia de cima (acessibilidade)',
				'type'    => 'text',
				'default' => 'Fatia de cheesecake tradicional, sem cobertura',
			),
			'cdc_fatia_imagem_fundo'      => array(
				'label'   => 'Foto da fatia da frente (305 × 305)',
				'type'    => 'image',
				'default' => 'images/02-primeira-fatia-background-image.webp',
				'help'    => 'PNG/WebP quadrado com fundo transparente.',
			),
			'cdc_fatia_imagem_fundo_alt'  => array(
				'label'   => 'Descrição da fatia da frente (acessibilidade)',
				'type'    => 'text',
				'default' => 'Fatia de cheesecake com calda de frutas vermelhas e um morango por cima',
			),
		),
	);
	return $sections;
} );
