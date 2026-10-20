<?php
/**
 * Página Sobre nós (templates/sobre.php). Espelha sobre.html / src/paginas/sobre.
 *
 * - Option page "Sobre nós" (Páginas do site): blocos Nossa história e Pioneiros
 *   (título, parágrafos, foto, alt), selo e especialista, e quais seções da home
 *   (Entrega, Restaurantes) aparecem no fim da página.
 * - O selo é o mesmo da seção Sobre da home (Customizer "Sobre (Produção própria)").
 * - Seed: página "Sobre nós" (slug sobre-nos) com o template.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'cdc_option_pages', function ( $pages ) {
	$pages['sobre'] = array(
		'title'  => 'Sobre nós',
		'intro'  => 'Textos e fotos da página Sobre nós. Parágrafos separados por uma linha em branco; use **negrito** e [texto](https://link) se precisar.',
		'order'  => 20,
		'fields' => array(
			'historia'               => array(
				'label' => 'Bloco 1 — Nossa história',
				'type'  => 'heading',
			),
			'historia_titulo'        => array(
				'label'   => 'Título',
				'type'    => 'text',
				'default' => 'Nossa história',
			),
			'historia_texto'         => array(
				'label'   => 'Texto',
				'type'    => 'textarea',
				'rows'    => 10,
				'default' => "Em 2001, no Rio de Janeiro, nascemos como comissária, produzindo para outras marcas do mercado carioca — o início de um ofício aprendido por dentro. Em 2004, mudamos para São Paulo e assumimos o nome A Casa do Cheesecake, decididos a produzir o autêntico cheesecake americano, sem atalhos na receita.\n\nUm ano depois, criamos uma versão inédita no Brasil: o cheesecake vendido em pote, para ser saboreado de colher. A ideia ganhou reportagens na televisão e na imprensa especializada, e ajudou a apresentar o cheesecake americano ao paladar paulistano.\n\nEntre 2011 e 2019, abrimos três lojas físicas em São Paulo, e uma parceria com a Philadelphia Cream Cheese nos levou aos festivais gastronômicos do Rio e de São Paulo com o CheesecakeTruck.",
				'help'    => 'Um parágrafo por bloco, separados por uma linha em branco.',
			),
			'historia_foto'          => array(
				'label'   => 'Foto',
				'type'    => 'image',
				'default' => 'images/sobre-historia.webp',
				'help'    => 'Horizontal 3:2 (ex. 1400 × 934).',
			),
			'historia_foto_alt'      => array(
				'label'   => 'Descrição da foto (acessibilidade)',
				'type'    => 'text',
				'default' => 'Fatia de cheesecake com calda de morango num prato, com o cheesecake inteiro ao fundo',
			),
			'historia_selo'          => array(
				'label'   => 'Mostrar selo 5 milhões de fatias',
				'type'    => 'checkbox',
				'default' => '1',
				'help'    => 'O selo é o mesmo da home (Aparência → Personalizar → Sobre (Produção própria)).',
			),
			'pioneiros'              => array(
				'label' => 'Bloco 2 — Pioneiros',
				'type'  => 'heading',
			),
			'pioneiros_titulo'       => array(
				'label'   => 'Título',
				'type'    => 'text',
				'default' => 'Pioneiros no autêntico NY Cheesecake',
			),
			'pioneiros_texto'        => array(
				'label'   => 'Texto',
				'type'    => 'textarea',
				'rows'    => 10,
				'default' => "A pandemia nos fez repensar tudo — e escolhemos nos dedicar inteiramente ao que sempre fizemos de melhor: entregar, com a mesma qualidade de uma loja física, direto na casa de quem pediu. Hoje seguimos em expansão, com novas unidades a caminho da Grande São Paulo e um retorno planejado ao Rio de Janeiro, onde tudo começou.\n\nCom mais de 20 anos no mercado de Food Service, somos pioneiros na produção do autêntico NY Cheesecake no Brasil — e o compromisso é o mesmo do primeiro dia.\n\nPresença na mídia: SBT, TV Gazeta e imprensa especializada em gastronomia.",
				'help'    => 'Um parágrafo por bloco, separados por uma linha em branco.',
			),
			'pioneiros_foto'         => array(
				'label'   => 'Foto',
				'type'    => 'image',
				'default' => 'images/sobre-producao.webp',
				'help'    => 'Horizontal 3:2 (ex. 1400 × 934).',
			),
			'pioneiros_foto_alt'     => array(
				'label'   => 'Descrição da foto (acessibilidade)',
				'type'    => 'text',
				'default' => 'Fatias de cheesecake estilo americano sobre a tábua, com potes de calda',
			),
			'pioneiros_especialista' => array(
				'label'   => 'Especialista (nome e cargo)',
				'type'    => 'text',
				'default' => 'Otavio Paulino, especialista em cheesecake',
				'help'    => 'Salve vazio para esconder a linha.',
			),
			'secoes'                 => array(
				'label' => 'Seções da home',
				'type'  => 'heading',
				'help'  => 'Mesmo conteúdo da home, editado em Aparência → Personalizar.',
			),
			'mostrar_entrega'        => array(
				'label'   => 'Mostrar Entrega em São Paulo',
				'type'    => 'checkbox',
				'default' => '1',
			),
			'mostrar_restaurantes'   => array(
				'label'   => 'Mostrar Restaurantes (banner B2B)',
				'type'    => 'checkbox',
				'default' => '1',
			),
		),
	);
	return $pages;
} );

add_filter( 'cdc_seed_pages', function ( $pages ) {
	$pages[] = array(
		'slug'     => 'sobre-nos',
		'title'    => 'Sobre nós',
		'template' => 'templates/sobre.php',
	);
	return $pages;
} );

/**
 * Especialista do bloco 2: salvo vazio = sem a linha; nunca salvo = texto do layout.
 *
 * @return string
 */
function cdc_sobre_especialista() {
	$values = cdc_opt_values( 'sobre' );
	if ( isset( $values['pioneiros_especialista'] ) ) {
		return trim( (string) $values['pioneiros_especialista'] );
	}
	return (string) cdc_opt( 'sobre', 'pioneiros_especialista' );
}

/**
 * Dados de um bloco da página (história | pioneiros) para o template.
 *
 * @param string $bloco Prefixo dos campos.
 * @return array{titulo:string,paragrafos:string[],foto:array,alt:string}
 */
function cdc_sobre_bloco( $bloco ) {
	return array(
		'titulo'     => (string) cdc_opt( 'sobre', $bloco . '_titulo' ),
		'paragrafos' => cdc_paragraphs( cdc_opt( 'sobre', $bloco . '_texto' ) ),
		'foto'       => cdc_opt_image( 'sobre', $bloco . '_foto' ),
		'alt'        => (string) cdc_opt( 'sobre', $bloco . '_foto_alt' ),
	);
}
