<?php
/**
 * Rodapé (Figma "Footer" 7073:66) — Customizer.
 *
 * - Links das 4 colunas vêm dos menus WP footer-comprar / footer-marca / footer-contato /
 *   footer-social (registrados em inc/menus.php); sem menu atribuído, o template usa os
 *   links do Figma como fallback (âncoras da home + contatos globais).
 * - Aqui ficam os títulos das colunas, o logo central, o texto central, o copyright e o
 *   crédito "Criado por" (seção "Rodapé" do Customizer).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'cdc_customizer_sections', function ( $sections ) {
	$sections['cdc_rodape'] = array(
		'title'    => 'Rodapé',
		'priority' => 120,
		'fields'   => array(
			'cdc_rodape_titulo_comprar' => array(
				'label'   => 'Título da coluna 1 (links em Aparência → Menus → "Footer — Comprar")',
				'type'    => 'text',
				'default' => 'Comprar',
			),
			'cdc_rodape_titulo_marca'   => array(
				'label'   => 'Título da coluna 2 (menu "Footer — A marca")',
				'type'    => 'text',
				'default' => 'A marca',
			),
			'cdc_rodape_titulo_contato' => array(
				'label'   => 'Título da coluna 3 (menu "Footer — Contato")',
				'type'    => 'text',
				'default' => 'Contato',
			),
			'cdc_rodape_titulo_social'  => array(
				'label'   => 'Título da coluna 4 (menu "Footer — Social")',
				'type'    => 'text',
				'default' => 'Social',
			),
			'cdc_rodape_logo'           => array(
				'label'   => 'Logo central (148 × 150)',
				'type'    => 'image',
				'default' => 'images/footer-logo.webp',
				'help'    => 'PNG/WebP com fundo transparente (selo dourado).',
			),
			'cdc_rodape_logo_alt'       => array(
				'label'   => 'Descrição do logo (acessibilidade)',
				'type'    => 'text',
				'default' => 'Selo dourado A Casa do Cheesecake',
			),
			'cdc_rodape_texto'          => array(
				'label'   => 'Texto central',
				'type'    => 'textarea',
				'default' => 'O autêntico New York Cheesecake, assado em São Paulo desde 2004. Só delivery.',
			),
			'cdc_rodape_copyright'      => array(
				'label'   => 'Copyright',
				'type'    => 'text',
				'default' => 'A Casa do Cheesecake ® · todos os direitos reservados',
			),
			'cdc_rodape_credito'        => array(
				'label'   => 'Crédito (texto antes do símbolo)',
				'type'    => 'text',
				'default' => 'Criado por',
			),
			'cdc_rodape_credito_nome'   => array(
				'label'   => 'Crédito — nome de quem criou (texto alternativo do símbolo)',
				'type'    => 'text',
				'default' => '',
				'help'    => 'Lido por leitores de tela no lugar do símbolo.',
			),
			'cdc_rodape_credito_link'   => array(
				'label'   => 'Crédito — link (opcional)',
				'type'    => 'url',
				'default' => '',
				'help'    => 'Se preenchido junto com o nome acima, o símbolo vira link (abre em nova aba).',
			),
		),
	);
	return $sections;
} );

/**
 * Links do rodapé por coluna: menu WP atribuído ou fallback com os links do Figma.
 *
 * @return array<string, array> Chaves comprar, marca, contato, social.
 */
function cdc_rodape_links() {
	$telefone = (string) cdc_mod( 'cdc_contato_telefone' );
	$digitos  = preg_replace( '/\D+/', '', $telefone );
	$tel_url  = $digitos ? 'tel:+55' . $digitos : '';

	return array(
		'comprar' => cdc_menu_links(
			'footer-comprar',
			array(
				array( 'label' => 'Cardápio', 'url' => cdc_anchor( '#cardapio' ) ),
				array( 'label' => 'Frutas Vermelhas', 'url' => cdc_anchor( '#sabores' ) ),
				array( 'label' => 'Provar uma fatia', 'url' => cdc_anchor( '#primeira-fatia' ) ),
			)
		),
		'marca'   => cdc_menu_links(
			'footer-marca',
			array(
				array( 'label' => 'Sobre nós', 'url' => cdc_anchor( '#sobre' ) ),
				array( 'label' => 'Nossa especialista', 'url' => cdc_anchor( '#sobre' ) ),
				array( 'label' => 'Blog', 'url' => cdc_anchor( '#blog' ) ),
			)
		),
		'contato' => cdc_menu_links(
			'footer-contato',
			array(
				array( 'label' => 'Fale com a gente', 'url' => cdc_mod( 'cdc_contato_whatsapp' ), 'target' => '_blank' ),
				array( 'label' => $telefone, 'url' => $tel_url ),
			)
		),
		'social'  => cdc_menu_links(
			'footer-social',
			array(
				array( 'label' => 'Instagram', 'url' => cdc_mod( 'cdc_contato_instagram' ), 'target' => '_blank' ),
				array( 'label' => 'Facebook', 'url' => cdc_mod( 'cdc_contato_facebook' ), 'target' => '_blank' ),
			)
		),
	);
}
