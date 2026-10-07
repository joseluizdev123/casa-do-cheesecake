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
		'title'    => 'Desconto na primeira compra (banner amarelo)',
		'priority' => 20,
		'fields'   => array(
			'cdc_fatia_titulo'            => array(
				'label'   => 'Título',
				'type'    => 'textarea',
				'default' => '20% de desconto na sua primeira compra, sem pedido mínimo',
				'help'    => 'Cada quebra de linha vira uma quebra no título.',
			),
			'cdc_fatia_subtitulo'         => array(
				'label'   => 'Subtítulo',
				'type'    => 'text',
				'default' => '',
				'help'    => 'Opcional. Em branco, a linha não aparece.',
			),
			'cdc_fatia_texto'             => array(
				'label'   => 'Texto (acima do botão)',
				'type'    => 'textarea',
				'default' => 'Cupom aplicado automaticamente no pedido',
				'help'    => 'Opcional. Em branco, a linha não aparece.',
			),
			'cdc_fatia_botao'             => array(
				'label'   => 'Texto do botão',
				'type'    => 'text',
				'default' => 'Aproveitar desconto',
			),
			'cdc_fatia_botao_link'        => array(
				'label'   => 'Link do botão',
				'type'    => 'url',
				'default' => 'https://pedido.brendi.com.br/a-casa-do-cheesecake',
				'help'    => 'Padrão: cardápio online (Brendi). Em branco = WhatsApp de "Contato e links globais".',
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
				'default' => 'Fatia de cheesecake com calda de frutas vermelhas escorrendo pela lateral, ao lado de morangos',
			),
		),
	);
	return $sections;
} );
