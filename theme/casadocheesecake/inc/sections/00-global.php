<?php
/**
 * Campos globais: logo + contatos/links usados em várias seções.
 * Defaults = links do site atual (acasadocheesecake.com.br), com autorização do cliente.
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
			'cdc_contato_whatsapp'   => array(
				'label'   => 'Link do WhatsApp',
				'type'    => 'url',
				'default' => 'https://wa.me/5511956022366?text=Ol%C3%A1!+Passei+pelo+site+e+gostaria+de+mais+informa%C3%A7%C3%B5es.',
				'help'    => 'Formato: https://wa.me/55DDDNUMERO?text=mensagem',
			),
			'cdc_contato_telefone'   => array( 'label' => 'Telefone (exibição)', 'type' => 'text', 'default' => '(11) 95602-2366' ),
			'cdc_contato_email'      => array( 'label' => 'E-mail de atendimento', 'type' => 'text', 'default' => 'atendimento@acasadocheesecake.com.br' ),
			'cdc_contato_cardapio'   => array(
				'label'   => 'Link do cardápio / pedido online',
				'type'    => 'url',
				'default' => 'https://pedido.brendi.com.br/a-casa-do-cheesecake',
			),
			'cdc_contato_ifood'      => array(
				'label'   => 'Link do iFood (loja principal)',
				'type'    => 'url',
				'default' => 'https://www.ifood.com.br/delivery/sao-paulo-sp/a-casa-do-cheesecake-pinheiros-butanta/f42ba7bf-5aab-4c8e-a2bd-722b0a60e6b7',
				'help'    => 'As 4 lojas do iFood ficam no texto do item "Também no iFood" (seção Entrega).',
			),
			'cdc_contato_b2b'        => array(
				'label'   => 'Página para restaurantes (B2B)',
				'type'    => 'url',
				'default' => 'https://acasadocheesecake.com.br/b2b/',
			),
			'cdc_contato_blog'       => array( 'label' => 'Blog', 'type' => 'url', 'default' => 'https://acasadocheesecake.com.br/blog/' ),
			'cdc_contato_instagram'  => array( 'label' => 'Instagram', 'type' => 'url', 'default' => 'https://www.instagram.com/acasadocheesecakeoficial/' ),
			'cdc_contato_facebook'   => array( 'label' => 'Facebook', 'type' => 'url', 'default' => 'https://www.facebook.com/acasadocheesecakeoficial' ),
		),
	);
	return $sections;
} );
