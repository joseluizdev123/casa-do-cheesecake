<?php
/**
 * Seção 10 — Perguntas frequentes (Figma 7057:763 · respostas em 7057:1039).
 *
 * - Customizer: título, texto do rodapé e botão (rótulo + link; link vazio = WhatsApp global).
 * - CPT cdc_faq (campo base 'resposta' em inc/cpt.php) + seed das 8 perguntas/respostas
 *   com a copy exata do Figma. Os trechos "[confirmar …]" / "[X dias]" são do próprio
 *   Figma (anotação "Confirmar informações com o cliente") e devem ser revisados no admin.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

/**
 * Perguntas iniciais (copy exata do Figma, na ordem do layout).
 *
 * Alimenta o seeder e serve de fallback no template enquanto o CPT estiver vazio.
 *
 * @return array[] Lista de array( 'title' => pergunta, 'resposta' => resposta ).
 */
function cdc_faq_defaults() {
	return array(
		array(
			'title'    => 'Vocês têm loja física?',
			'resposta' => 'Não. Trabalhamos exclusivamente com delivery em São Paulo, pelo nosso cardápio online e pelo iFood. Toda a produção sai da nossa cozinha direto para a entrega.',
		),
		array(
			'title'    => 'Qual o tamanho certo para o meu número de convidados?',
			'resposta' => 'A fatia de 150 g serve uma pessoa. A torta de 600 g rende até 6 mini fatias, a de 1,2 kg serve até 8 pessoas e a de 1,8 kg, de 10 a 12. Se o cheesecake for a única sobremesa da mesa, vá para o tamanho de cima — porção de cheesecake some rápido. Se houver bolo e outros doces, o tamanho de baixo resolve.',
		),
		array(
			'title'    => 'Consigo receber hoje?',
			'resposta' => 'Sim, para pedidos feitos dentro do horário de funcionamento e enquanto houver estoque do dia. É só escolher a entrega para hoje no cardápio — o sistema confirma a disponibilidade pelo seu endereço antes de fechar o pedido. Para data específica, como festa ou presente, o ideal é agendar com antecedência. [confirmar: horário de funcionamento e prazo mínimo]',
		),
		array(
			'title'    => 'Quanto tempo dura na geladeira? Pode congelar?',
			'resposta' => 'Na geladeira, bem fechado, o cheesecake dura [X dias]. Ele também pode ser congelado por até [X dias]: leve ao congelador inteiro ou em fatias, e transfira para a geladeira algumas horas antes de servir — nunca descongele em temperatura ambiente nem no micro-ondas, porque a textura se perde. [confirmar os dois prazos e a orientação de descongelamento]',
		),
		array(
			'title'    => 'Quais as formas de pagamento?',
			'resposta' => 'Aceitamos Pix, cartão de crédito e cartão de débito, todos pelo cardápio online, com confirmação automática. O pagamento acontece na hora do pedido, então a entrega chega sem nenhuma etapa a mais.',
		),
		array(
			'title'    => 'A entrega chega em qualquer bairro de São Paulo?',
			'resposta' => 'Atendemos toda a cidade de São Paulo, dentro da nossa área de cobertura, com taxa a partir de R$15. O cardápio confirma a disponibilidade pelo seu endereço antes de você fechar o pedido, então não tem risco de comprar e descobrir depois que não chega. Também estamos no iFood em Pinheiros, Vila Leopoldina, Bela Vista e Brooklin. [confirmar: se a taxa varia por região]',
		),
		array(
			'title'    => 'Como funciona a fatia por conta da casa?',
			'resposta' => 'É para quem ainda não conhece a marca: você preenche o formulário, escolhe o sabor e a data, e recebe uma fatia pagando só os R$15 da entrega. É 1 fatia por pessoa, de segunda a sexta das 10h às 17h, enquanto durar o estoque do dia, dentro da nossa área de entrega em São Paulo.',
		),
		array(
			'title'    => 'O cheesecake de vocês é assado?',
			'resposta' => 'Sim. É a receita original americana, assada no forno — não é uma sobremesa fria montada com gelatina ou creme batido. É isso que dá a textura densa, o sabor lácteo e a fatia que se sustenta em pé no prato. A quantidade de cream cheese Philadelphia é a da receita original, sem redução.',
		),
	);
}

/**
 * Perguntas para o template: posts do CPT cdc_faq (ordem do admin) ou defaults do Figma.
 *
 * @return array[] Lista de array( 'pergunta' => string, 'resposta' => string ).
 */
function cdc_faq_items() {
	$items = array();
	foreach ( cdc_posts( 'cdc_faq' ) as $cdc_post ) {
		$items[] = array(
			'pergunta' => (string) $cdc_post->post_title,
			'resposta' => (string) cdc_meta( $cdc_post->ID, 'resposta' ),
		);
	}
	if ( ! $items ) {
		foreach ( cdc_faq_defaults() as $cdc_item ) {
			$items[] = array(
				'pergunta' => $cdc_item['title'],
				'resposta' => $cdc_item['resposta'],
			);
		}
	}
	return $items;
}

add_filter( 'cdc_customizer_sections', function ( $sections ) {
	$sections['cdc_perguntas'] = array(
		'title'    => 'Perguntas frequentes',
		'priority' => 100,
		'fields'   => array(
			'cdc_faq_titulo'        => array(
				'label'   => 'Título',
				'type'    => 'text',
				'default' => 'Perguntas frequentes',
				'help'    => 'As perguntas e respostas ficam em Perguntas frequentes, no menu do painel.',
			),
			'cdc_faq_rodape_texto'  => array(
				'label'   => 'Texto do rodapé',
				'type'    => 'text',
				'default' => 'Ainda tem dúvidas? Fale conosco',
			),
			'cdc_faq_botao_texto'   => array(
				'label'   => 'Texto do botão',
				'type'    => 'text',
				'default' => 'Falar no WhatsApp',
			),
			'cdc_faq_botao_link'    => array(
				'label'   => 'Link do botão',
				'type'    => 'url',
				'default' => '',
				'help'    => 'Vazio = link do WhatsApp definido em "Contato e links globais".',
			),
		),
	);
	return $sections;
} );

add_filter( 'cdc_seed_posts', function ( $posts ) {
	foreach ( cdc_faq_defaults() as $cdc_i => $cdc_item ) {
		$posts[] = array(
			'post_type'  => 'cdc_faq',
			'title'      => $cdc_item['title'],
			'menu_order' => $cdc_i + 1,
			'meta'       => array(
				'resposta' => $cdc_item['resposta'],
			),
		);
	}
	return $posts;
} );
