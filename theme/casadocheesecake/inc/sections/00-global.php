<?php
/**
 * Campos globais: logo + contatos/links usados em várias seções.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'cdc_customizer_sections', function ( $sections ) {
	$sections['cdc_global'] = array(
		'title'    => 'Contato e links globais',
		'priority' => 1,
		'fields'   => array(
			'cdc_global_logo'        => array( 'label' => 'Logo (foto do selo)', 'type' => 'image', 'default' => 'images/logo-photo.png' ),
			'cdc_contato_whatsapp'   => array( 'label' => 'Link do WhatsApp', 'type' => 'url', 'default' => 'https://wa.me/5511956022366' ),
			'cdc_contato_telefone'   => array( 'label' => 'Telefone (exibição)', 'type' => 'text', 'default' => '(11) 95602-2366' ),
			'cdc_contato_cardapio'   => array( 'label' => 'Link do cardápio / pedido', 'type' => 'url', 'default' => '#cardapio' ),
			'cdc_contato_ifood'      => array( 'label' => 'Link do iFood', 'type' => 'url', 'default' => 'https://www.ifood.com.br' ),
			'cdc_contato_instagram'  => array( 'label' => 'Instagram', 'type' => 'url', 'default' => 'https://www.instagram.com/' ),
			'cdc_contato_facebook'   => array( 'label' => 'Facebook', 'type' => 'url', 'default' => 'https://www.facebook.com/' ),
		),
	);
	return $sections;
} );
