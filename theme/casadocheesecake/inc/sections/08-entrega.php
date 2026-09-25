<?php
/**
 * Seção 08 — Entrega (Figma 7057:490).
 *
 * Customizer: título, 4 itens fixos (título, texto, ícone) e botão.
 * Defaults = copy exata do Figma; ícones default em assets/images/08-entrega-icon-*.svg.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

/**
 * Itens fixos da seção: defaults do Figma (título, texto, ícone).
 * A geometria do slot do ícone fica no template-part ($cdc_entrega_slots).
 *
 * @return array[]
 */
function cdc_entrega_items_defaults() {
	return array(
		1 => array(
			'titulo' => 'Toda São Paulo',
			'texto'  => 'Qualquer região dentro da nossa área de cobertura. Taxa a partir de R$15.',
			'icone'  => 'images/08-entrega-icon-local.svg',
		),
		2 => array(
			'titulo' => 'Hoje ou agendado',
			// "data\u{00A0}—": espaço não separável evita o travessão abrindo linha no tablet/mobile.
			'texto'  => "Receba no mesmo dia ou escolha a data\u{00A0}— festa, presente ou data comemorativa.",
			'icone'  => 'images/08-entrega-icon-calendario.svg',
		),
		3 => array(
			'titulo' => 'Chega gelado e pronto',
			'texto'  => 'Embalagem própria e temperatura de servir. Não precisa fazer nada além de cortar.',
			'icone'  => 'images/08-entrega-icon-cheesecake.svg',
		),
		4 => array(
			'titulo' => 'Também no iFood',
			'texto'  => '[Pinheiros](https://www.ifood.com.br/delivery/sao-paulo-sp/a-casa-do-cheesecake-pinheiros-butanta/f42ba7bf-5aab-4c8e-a2bd-722b0a60e6b7), [Vila Leopoldina](https://www.ifood.com.br/delivery/sao-paulo-sp/a-casa-do-cheesecake-vila-leopoldina-vila-santa-edwiges/97da8119-0fd3-4a74-9032-71037049bcf3), [Bela Vista](https://www.ifood.com.br/delivery/sao-paulo-sp/a-casa-do-cheesecake-bela-vista-cerqueira-cesar/2aceeb0e-6129-45af-b7fb-ca8f8dcafb73) e [Brooklin](https://www.ifood.com.br/delivery/sao-paulo-sp/a-casa-do-cheesecake-brooklin-brooklin/8c12ef63-1673-4db5-858b-380af22f58d8).',
			'icone'  => 'images/08-entrega-icon-ifood.svg',
		),
	);
}

add_filter( 'cdc_customizer_sections', function ( $sections ) {
	$fields = array(
		'cdc_entrega_titulo' => array(
			'label'   => 'Título',
			'type'    => 'text',
			'default' => 'Entrega em toda a cidade de São Paulo',
		),
	);

	foreach ( cdc_entrega_items_defaults() as $n => $item ) {
		$fields[ "cdc_entrega_item{$n}_titulo" ] = array(
			'label'   => sprintf( 'Item %d — título', $n ),
			'type'    => 'text',
			'default' => $item['titulo'],
		);
		$fields[ "cdc_entrega_item{$n}_texto" ] = array(
			'label'   => sprintf( 'Item %d — texto', $n ),
			'type'    => 'textarea',
			'default' => $item['texto'],
			'help'    => 'Use **texto** para negrito e [texto](https://link) para link.',
		);
		$fields[ "cdc_entrega_item{$n}_icone" ] = array(
			'label'   => sprintf( 'Item %d — ícone', $n ),
			'type'    => 'image',
			'default' => $item['icone'],
			'help'    => 'Ícone vermelho (#b0282e) com fundo transparente, até 32×32 px, em PNG ou WebP. Sem imagem, usa o ícone padrão do layout (já aplicado).',
		);
	}

	$fields['cdc_entrega_botao_rotulo'] = array(
		'label'   => 'Botão — rótulo',
		'type'    => 'text',
		'default' => 'Ver cardápio completo',
	);
	$fields['cdc_entrega_botao_link'] = array(
		'label'   => 'Botão — link',
		'type'    => 'url',
		'default' => '',
		'help'    => 'Deixe vazio para usar o link global do cardápio (Contato e links globais).',
	);

	$sections['cdc_entrega'] = array(
		'title'    => '08 · Entrega',
		'priority' => 80,
		'fields'   => $fields,
	);
	return $sections;
} );
