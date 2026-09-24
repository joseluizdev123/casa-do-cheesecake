<?php
/**
 * Seção 06 — Sobre ("Uma cozinha, não uma embalagem"). Figma 7057:349.
 *
 * Tudo editável no Customizer: título, parágrafos, foto principal, selo,
 * foto/nome/descrição da especialista. Defaults = copy exata do Figma.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'cdc_customizer_sections', function ( $sections ) {
	$sections['cdc_sobre'] = array(
		'title'    => 'Sobre (Uma cozinha, não uma embalagem)',
		'priority' => 60,
		'fields'   => array(
			'cdc_sobre_titulo'             => array(
				'label'   => 'Título',
				'type'    => 'text',
				'default' => 'Uma cozinha, não uma embalagem',
			),
			'cdc_sobre_texto'              => array(
				'label'   => 'Texto (um parágrafo por linha)',
				'type'    => 'textarea',
				'default' => "Somos uma cozinha em São Paulo, com uma equipe que assa, resfria, cobre e embala cada torta que sai daqui — e que faz isso desde 2004, com a mesma receita e o mesmo padrão.\nCada cheesecake é assado por encomenda e sai da cozinha direto para a sua casa. Não trabalhamos com loja física nem com revenda em prateleira.",
			),
			'cdc_sobre_imagem'             => array(
				'label'   => 'Foto principal (quadrada, 568 × 568 no layout)',
				'type'    => 'image',
				'default' => 'images/06-sobre-foto.webp',
			),
			'cdc_sobre_imagem_alt'         => array(
				'label'   => 'Descrição da foto principal (acessibilidade)',
				'type'    => 'text',
				'default' => 'Fatia de New York Cheesecake com calda de frutas vermelhas em frente à caixa da Casa do Cheesecake',
			),
			'cdc_sobre_selo'               => array(
				'label'   => 'Selo sobre a foto (142 × 142)',
				'type'    => 'image',
				'default' => 'images/06-sobre-selo.svg',
				'help'    => 'Imagem circular exibida no canto inferior esquerdo da foto.',
			),
			'cdc_sobre_selo_alt'           => array(
				'label'   => 'Texto do selo (acessibilidade)',
				'type'    => 'text',
				'default' => '5 milhões de fatias. Yes, we have it.',
			),
			'cdc_sobre_especialista_foto'  => array(
				'label'   => 'Foto da especialista (quadrada, exibida em círculo 100 × 100)',
				'type'    => 'image',
				'default' => 'images/06-sobre-especialista.png',
			),
			'cdc_sobre_especialista_alt'   => array(
				'label'   => 'Descrição da foto da especialista (acessibilidade)',
				'type'    => 'text',
				'default' => 'Retrato da especialista em cheesecake da Casa do Cheesecake, de dólmã e touca de chef',
			),
			'cdc_sobre_especialista_nome'  => array(
				'label'   => 'Nome e cargo da especialista',
				'type'    => 'text',
				'default' => '[Nome], especialista em cheesecake',
			),
			'cdc_sobre_especialista_texto' => array(
				'label'   => 'Descrição da especialista',
				'type'    => 'textarea',
				// "do\u{00A0}Cheesecake": espaço não separável, como no Figma (quebra "Casa / do Cheesecake.").
				'default' => "Responsável pela receita e pelo padrão de forno da Casa do\u{00A0}Cheesecake.",
			),
		),
	);
	return $sections;
} );
