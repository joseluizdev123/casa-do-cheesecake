<?php
/**
 * Seção 09 — Restaurantes (Figma "Delivery" 7057:508).
 *
 * Banner amarelo B2B com a moto de entrega. Editável no Customizer: título,
 * texto (com trecho em **negrito**), botão (link padrão = WhatsApp global) e a
 * imagem da moto. O selo "Desde 2004 · Yes, we have cheesecake" é decoração
 * impressa na caixa da moto-padrão: só aparece enquanto a imagem for a padrão.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'cdc_customizer_sections', function ( $sections ) {
	$sections['cdc_restaurantes'] = array(
		'title'    => 'Restaurantes (banner B2B)',
		'priority' => 90,
		'fields'   => array(
			'cdc_rest_titulo'      => array(
				'label'   => 'Título',
				'type'    => 'text',
				'default' => 'Tem restaurante, pizzaria ou delivery?',
			),
			'cdc_rest_texto'       => array(
				'label'   => 'Texto',
				'type'    => 'textarea',
				'default' => 'Cheesecakes inteiros prontos para porcionar, entrega semanal programada e **pedido mínimo de R$500.**',
				'help'    => 'Use **asteriscos duplos** para deixar um trecho em negrito.',
			),
			'cdc_rest_botao'       => array(
				'label'   => 'Texto do botão',
				'type'    => 'text',
				'default' => 'Ver condições',
			),
			'cdc_rest_botao_link'  => array(
				'label'   => 'Link do botão',
				'type'    => 'url',
				'default' => '',
				'help'    => 'Deixe em branco para usar a "Página para restaurantes (B2B)" de "Contato e links globais".',
			),
			'cdc_rest_imagem'      => array(
				'label'   => 'Imagem da moto (357 × 305)',
				'type'    => 'image',
				'default' => 'images/09-restaurantes-moto.webp',
				'help'    => 'PNG/WebP com fundo transparente, alinhado pela base. Ao trocar a imagem, o selo impresso na caixa deixa de aparecer.',
			),
			'cdc_rest_imagem_alt'  => array(
				'label'   => 'Descrição da imagem (acessibilidade)',
				'type'    => 'text',
				'default' => 'Scooter vermelha de entrega com uma caixa de papelão no bagageiro',
			),
		),
	);
	return $sections;
} );

/**
 * O selo decorativo só faz sentido sobre a caixa da moto-padrão do layout.
 *
 * @return bool True quando a imagem da seção é a padrão (asset do tema ou a cópia importada pelo seeder).
 */
function cdc_rest_selo_visivel() {
	$value = get_theme_mod( 'cdc_rest_imagem' );
	if ( ! $value ) {
		return true;
	}
	if ( is_numeric( $value ) ) {
		return 'images/09-restaurantes-moto.webp' === get_post_meta( (int) $value, '_cdc_seed_src', true );
	}
	return false;
}
