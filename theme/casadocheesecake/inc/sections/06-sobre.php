<?php
/**
 * Seção 06 — Sobre ("Produção própria, não de prateleira"). Figma 7057:349.
 *
 * Tudo editável no Customizer: título, parágrafos, foto principal, selo,
 * foto/nome/descrição da especialista. Defaults = copy exata do Figma.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'cdc_customizer_sections', function ( $sections ) {
	$sections['cdc_sobre'] = array(
		'title'    => 'Sobre (Produção própria, não de prateleira)',
		'priority' => 60,
		'fields'   => array(
			'cdc_sobre_titulo'             => array(
				'label'   => 'Título',
				'type'    => 'text',
				'default' => 'Produção própria, não de prateleira',
			),
			'cdc_sobre_texto'              => array(
				'label'   => 'Texto (um parágrafo por linha)',
				'type'    => 'textarea',
				'default' => "Somos uma fábrica própria em São Paulo, com fornadas todos os dias. Desde 2004, cada torta segue a mesma receita e o mesmo padrão.\nCada cheesecake sai direto da fábrica para a sua casa, sem loja física nem revenda em prateleira.",
			),
			'cdc_sobre_imagem'             => array(
				'label'   => 'Foto principal',
				'type'    => 'image',
				'default' => 'images/06-sobre-foto.webp',
				'help'    => 'Retrato 3:4 (ex. 1047 × 1400). O layout recorta em quadrado no computador e em faixa horizontal no tablet/celular: mantenha o assunto no centro da imagem.',
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
				'help'    => 'Imagem circular exibida no canto inferior esquerdo da foto. Envie PNG ou WebP 284 × 284 com fundo transparente (a Biblioteca de Mídia do WordPress não aceita SVG).',
			),
			'cdc_sobre_selo_alt'           => array(
				'label'   => 'Texto do selo (acessibilidade)',
				'type'    => 'text',
				'default' => '5 milhões de fatias. Yes, we have it.',
			),
			'cdc_sobre_especialista_foto'  => array(
				'label'   => 'Foto do especialista (quadrada, exibida em círculo 100 × 100) — opcional',
				'type'    => 'image',
				'default' => '',
			),
			'cdc_sobre_especialista_alt'   => array(
				'label'   => 'Descrição da foto do especialista (acessibilidade)',
				'type'    => 'text',
				'default' => 'Retrato de Otavio Paulino, especialista em cheesecake da Casa do Cheesecake',
			),
			'cdc_sobre_especialista_nome'  => array(
				'label'   => 'Nome e cargo do especialista',
				'type'    => 'text',
				'default' => 'Otavio Paulino, especialista em cheesecake',
			),
			'cdc_sobre_especialista_texto' => array(
				'label'   => 'Descrição do especialista (opcional)',
				'type'    => 'textarea',
				'default' => '',
			),
		),
	);
	return $sections;
} );
