<?php
/**
 * Custom Post Types + campos base (ver MODEL.md).
 *
 * Seções podem acrescentar campos via filtro `cdc_post_fields` em inc/sections/*.php.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	$types = array(
		'cdc_cheesecake'  => array( 'Cheesecakes', 'Cheesecake', 'dashicons-carrot', array( 'title', 'thumbnail', 'page-attributes' ) ),
		'cdc_diferencial' => array( 'Diferenciais', 'Diferencial', 'dashicons-star-filled', array( 'title', 'thumbnail', 'page-attributes' ) ),
		'cdc_depoimento'  => array( 'Depoimentos', 'Depoimento', 'dashicons-format-quote', array( 'title', 'page-attributes' ) ),
		'cdc_faq'         => array( 'Perguntas frequentes', 'Pergunta', 'dashicons-editor-help', array( 'title', 'page-attributes' ) ),
	);
	foreach ( $types as $slug => $t ) {
		register_post_type(
			$slug,
			array(
				'labels'        => array(
					'name'          => $t[0],
					'singular_name' => $t[1],
					'add_new'       => __( 'Adicionar', 'casadocheesecake' ),
					'add_new_item'  => sprintf( /* translators: %s: nome */ __( 'Adicionar %s', 'casadocheesecake' ), $t[1] ),
					'edit_item'     => sprintf( /* translators: %s: nome */ __( 'Editar %s', 'casadocheesecake' ), $t[1] ),
					'menu_name'     => $t[0],
				),
				'public'        => false,
				'show_ui'       => true,
				'show_in_rest'  => true,
				'menu_icon'     => $t[2],
				'menu_position' => 20,
				'supports'      => $t[3],
				'hierarchical'  => false,
			)
		);
	}
} );

add_filter( 'cdc_post_fields', function ( $fields ) {
	$fields['cdc_cheesecake'] = array(
		'descricao'    => array( 'label' => 'Descrição curta', 'type' => 'textarea', 'rows' => 2 ),
		'tag'          => array( 'label' => 'Tag (opcional)', 'type' => 'text', 'help' => 'Ex.: Mais pedido' ),
		'cor_fundo'    => array( 'label' => 'Cor de fundo do card', 'type' => 'color' ),
		'precos'       => array( 'label' => 'Preços', 'type' => 'textarea', 'rows' => 4, 'help' => 'Uma linha por opção: preço | tamanho. Ex.: R$ 28,90 | Fatia 150 g' ),
		'imagem_fatia' => array( 'label' => 'Foto da fatia (carrossel de sabores)', 'type' => 'image' ),
		'link_pedido'  => array( 'label' => 'Link do pedido', 'type' => 'url', 'help' => 'Vazio = link do cardápio definido no Customizer' ),
	);
	$fields['cdc_diferencial'] = array(
		'texto'    => array( 'label' => 'Texto', 'type' => 'textarea', 'rows' => 3 ),
		'tag'      => array( 'label' => 'Tag (opcional)', 'type' => 'text' ),
		'destaque' => array( 'label' => 'Card em destaque (vermelho)', 'type' => 'checkbox' ),
	);
	$fields['cdc_depoimento'] = array(
		'texto'    => array( 'label' => 'Depoimento', 'type' => 'textarea', 'rows' => 4 ),
		'estrelas' => array( 'label' => 'Estrelas (1–5)', 'type' => 'number' ),
		'data'     => array( 'label' => 'Data', 'type' => 'text', 'help' => 'Ex.: Setembro 2026' ),
		'fonte'    => array( 'label' => 'Fonte', 'type' => 'text', 'help' => 'Ex.: Avaliação publicada no Google.' ),
	);
	$fields['cdc_faq'] = array(
		'resposta' => array( 'label' => 'Resposta', 'type' => 'textarea', 'rows' => 5 ),
	);
	return $fields;
}, 5 );
