<?php
/**
 * Custom Post Types + campos base (ver MODEL.md).
 *
 * Seções podem acrescentar campos via filtro `cdc_post_fields` em inc/sections/*.php.
 *
 * cdc_cheesecake = "Produtos" no painel (o slug do CPT fica para não perder o
 * conteúdo já importado): tudo do cardápio, com a taxonomia cdc_categoria
 * ("Categorias": Nossos sabores, Especiais, Bolos…). Os marcados "Mostrar na
 * home" alimentam os cards e as abas de sabores da home.
 * cdc_canal = "Canais de contato" (lista da página Contato).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	$types = array(
		'cdc_cheesecake'  => array( 'Produtos', 'Produto', 'dashicons-carrot', array( 'title', 'thumbnail', 'page-attributes' ) ),
		'cdc_canal'       => array( 'Canais de contato', 'Canal de contato', 'dashicons-phone', array( 'title', 'page-attributes' ) ),
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

	register_taxonomy(
		'cdc_categoria',
		'cdc_cheesecake',
		array(
			'labels'            => array(
				'name'          => __( 'Categorias', 'casadocheesecake' ),
				'singular_name' => __( 'Categoria', 'casadocheesecake' ),
				'add_new_item'  => __( 'Adicionar categoria', 'casadocheesecake' ),
				'edit_item'     => __( 'Editar categoria', 'casadocheesecake' ),
				'menu_name'     => __( 'Categorias', 'casadocheesecake' ),
			),
			'description'       => __( 'Cada categoria vira uma seção da página Produtos (nome = título, descrição = texto ao lado).', 'casadocheesecake' ),
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
		)
	);
} );

// Categorias: campo "Ordem" (posição da seção na página Produtos).
add_action( 'cdc_categoria_add_form_fields', function () {
	echo '<div class="form-field"><label for="cdc-ordem">' . esc_html__( 'Ordem na página Produtos', 'casadocheesecake' ) . '</label><input type="number" id="cdc-ordem" name="cdc_ordem" value="10"><p>' . esc_html__( 'Menor aparece antes. A primeira categoria dá o título e o texto do topo da página.', 'casadocheesecake' ) . '</p></div>';
	wp_nonce_field( 'cdc_ordem', 'cdc_ordem_nonce' );
} );
add_action( 'cdc_categoria_edit_form_fields', function ( $term ) {
	$ordem = (int) get_term_meta( $term->term_id, 'cdc_ordem', true );
	echo '<tr class="form-field"><th scope="row"><label for="cdc-ordem">' . esc_html__( 'Ordem na página Produtos', 'casadocheesecake' ) . '</label></th><td><input type="number" id="cdc-ordem" name="cdc_ordem" value="' . esc_attr( $ordem ) . '"><p class="description">' . esc_html__( 'Menor aparece antes. A primeira categoria dá o título e o texto do topo da página.', 'casadocheesecake' ) . '</p>';
	wp_nonce_field( 'cdc_ordem', 'cdc_ordem_nonce' );
	echo '</td></tr>';
} );
$cdc_save_ordem = function ( $term_id ) {
	if ( ! isset( $_POST['cdc_ordem'], $_POST['cdc_ordem_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['cdc_ordem_nonce'] ) ), 'cdc_ordem' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	update_term_meta( $term_id, 'cdc_ordem', (int) $_POST['cdc_ordem'] );
};
add_action( 'created_cdc_categoria', $cdc_save_ordem );
add_action( 'edited_cdc_categoria', $cdc_save_ordem );
add_filter( 'manage_edit-cdc_categoria_columns', function ( $columns ) {
	$columns['cdc_ordem'] = __( 'Ordem', 'casadocheesecake' );
	return $columns;
} );
add_filter( 'manage_cdc_categoria_custom_column', function ( $out, $column, $term_id ) {
	return 'cdc_ordem' === $column ? (string) (int) get_term_meta( $term_id, 'cdc_ordem', true ) : $out;
}, 10, 3 );

add_filter( 'cdc_post_fields', function ( $fields ) {
	$fields['cdc_cheesecake'] = array(
		'descricao'    => array( 'label' => 'Descrição curta', 'type' => 'textarea', 'rows' => 2 ),
		'tag'          => array( 'label' => 'Tag (opcional)', 'type' => 'text', 'help' => 'Ex.: Mais pedido' ),
		'cor_fundo'    => array( 'label' => 'Cor de fundo do card', 'type' => 'color', 'help' => 'Cores da marca: vermelho #b0282e · amarelo #ffb82e · rosa #ffc0c5 · azul #b5d9fb · chocolate #150506 · creme #f6e7c6' ),
		'precos'       => array( 'label' => 'Preços', 'type' => 'textarea', 'rows' => 4, 'help' => 'Uma linha por opção: preço | tamanho | link do produto (opcional). Ex.: R$ 28,90 | Fatia 150 g | https://pedido.brendi.com.br/a-casa-do-cheesecake/produto/... — linhas que começam com "Fatia" no tamanho vão para a seção Fatias da página Produtos.' ),
		'imagem_fatia' => array( 'label' => 'Foto da fatia', 'type' => 'image', 'help' => 'Seção Fatias da página Produtos e abas de sabores da home. A foto do produto inteiro é a "Imagem destacada".' ),
		'home'         => array( 'label' => 'Mostrar na home', 'type' => 'checkbox', 'help' => 'Cards "Nossos cheesecakes" e abas de sabores da home (até 4, na ordem do campo Ordem).' ),
		'link_pedido'  => array( 'label' => 'Link do pedido', 'type' => 'url', 'help' => 'Vazio = link do cardápio definido no Customizer' ),
	);
	$fields['cdc_canal'] = array(
		'contato' => array( 'label' => 'Contato', 'type' => 'text', 'help' => 'O que aparece embaixo do nome. Ex.: (11) 95602-2366' ),
		'botao'   => array( 'label' => 'Texto do botão', 'type' => 'text', 'help' => 'Ex.: Conversar, Pedir, Enviar' ),
		// url (esc_url_raw): mantém o %C3%A1 da mensagem do WhatsApp; aceita mailto: e tel:.
		'link'    => array( 'label' => 'Link do botão', 'type' => 'url', 'help' => 'Começando com https://, mailto: ou tel:. Ex.: https://wa.me/55…, mailto:email@…' ),
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
