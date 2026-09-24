<?php
/**
 * Rodapé (Figma "Footer" 7073:66) — espelha src/partials/footer.html (mesmas classes e data-figma-node).
 * Links: menus footer-* com fallback do Figma (cdc_rodape_links() em inc/sections/99-footer.php);
 * textos, logo e crédito: Customizer → Casa do Cheesecake → Rodapé.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

$cdc_rodape_links = cdc_rodape_links();

// Colunas por grupo: chave de links, id do título, theme_mod do título e node IDs do Figma
// (coluna, título, links por posição) — os node IDs são só marcação de QA.
$cdc_rodape_grupos = array(
	'start' => array(
		'node'    => '7073:73',
		'colunas' => array(
			array( 'comprar', 'site-footer-comprar', 'cdc_rodape_titulo_comprar', '7073:74', '7073:75', array( '7073:76', '7073:77', '7073:78' ) ),
			array( 'marca', 'site-footer-marca', 'cdc_rodape_titulo_marca', '7073:79', '7073:80', array( '7073:81', '7073:82', '7073:83' ) ),
		),
	),
	'end'   => array(
		'node'    => '7073:97',
		'colunas' => array(
			array( 'contato', 'site-footer-contato', 'cdc_rodape_titulo_contato', '7073:84', '7073:85', array( '7073:86', '7073:98' ) ),
			array( 'social', 'site-footer-social', 'cdc_rodape_titulo_social', '7073:87', '7073:88', array( '7073:89', '7073:90' ) ),
		),
	),
);

$cdc_rodape_credito_nome = (string) cdc_mod( 'cdc_rodape_credito_nome' );
$cdc_rodape_credito_link = (string) cdc_mod( 'cdc_rodape_credito_link' );

/**
 * Renderiza um grupo de colunas de links do rodapé.
 *
 * @param string $modifier Modificador BEM (start|end).
 * @param array  $grupo    Definição do grupo.
 * @param array  $links    Links por coluna.
 */
$cdc_rodape_grupo = function ( $modifier, $grupo, $links ) {
	?>
		<div class="site-footer__group site-footer__group--<?php echo esc_attr( $modifier ); ?>" data-figma-node="<?php echo esc_attr( $grupo['node'] ); ?>">
			<?php foreach ( $grupo['colunas'] as $cdc_col ) : ?>
				<?php list( $cdc_key, $cdc_id, $cdc_titulo_mod, $cdc_col_node, $cdc_titulo_node, $cdc_link_nodes ) = $cdc_col; ?>
				<nav class="site-footer__col" data-figma-node="<?php echo esc_attr( $cdc_col_node ); ?>" aria-labelledby="<?php echo esc_attr( $cdc_id ); ?>">
					<h2 class="site-footer__heading" id="<?php echo esc_attr( $cdc_id ); ?>" data-figma-node="<?php echo esc_attr( $cdc_titulo_node ); ?>"><?php echo esc_html( cdc_mod( $cdc_titulo_mod ) ); ?></h2>
					<ul class="site-footer__list" role="list">
						<?php foreach ( $links[ $cdc_key ] as $cdc_i => $cdc_link ) : ?>
							<?php
							if ( '' === $cdc_link['url'] || '' === $cdc_link['label'] ) {
								continue;
							}
							$cdc_node = isset( $cdc_link_nodes[ $cdc_i ] ) ? ' data-figma-node="' . esc_attr( $cdc_link_nodes[ $cdc_i ] ) . '"' : '';
							$cdc_attr = $cdc_link['target'] ? ' target="' . esc_attr( $cdc_link['target'] ) . '" rel="noopener"' : '';
							?>
							<li><a class="site-footer__link"<?php echo $cdc_node; // phpcs:ignore WordPress.Security.EscapeOutput -- escapado acima. ?> href="<?php echo esc_url( $cdc_link['url'] ); ?>"<?php echo $cdc_attr; // phpcs:ignore WordPress.Security.EscapeOutput -- escapado acima. ?>><?php echo esc_html( $cdc_link['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endforeach; ?>
		</div>
	<?php
};
?>
<footer class="site-footer" id="contato" data-figma-node="7073:66">
	<div class="site-footer__main container" data-figma-node="7073:149">
		<div class="site-footer__row" data-figma-node="7073:67">
			<?php $cdc_rodape_grupo( 'start', $cdc_rodape_grupos['start'], $cdc_rodape_links ); ?>

			<div class="site-footer__brand" data-figma-node="7073:68">
				<a class="site-footer__logo" data-figma-node="7073:69" href="<?php echo esc_url( cdc_anchor( '#topo' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) . ' — voltar ao topo' ); ?>">
					<img src="<?php echo esc_url( cdc_image( 'cdc_rodape_logo' ) ); ?>" width="148" height="150" alt="<?php echo esc_attr( cdc_mod( 'cdc_rodape_logo_alt' ) ); ?>" loading="lazy" decoding="async">
				</a>
				<p class="site-footer__tagline" data-figma-node="7073:71"><?php echo cdc_rich( cdc_mod( 'cdc_rodape_texto' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em cdc_rich(). ?></p>
			</div>

			<?php $cdc_rodape_grupo( 'end', $cdc_rodape_grupos['end'], $cdc_rodape_links ); ?>
		</div>

		<div class="site-footer__bottom" data-figma-node="7073:91">
			<p class="site-footer__copy" data-figma-node="7073:92"><?php echo esc_html( cdc_mod( 'cdc_rodape_copyright' ) ); ?></p>
			<p class="site-footer__credits" data-figma-node="7073:93">
				<span data-figma-node="7073:94"><?php echo esc_html( cdc_mod( 'cdc_rodape_credito' ) ); ?></span>
				<?php if ( $cdc_rodape_credito_link ) : ?>
					<a href="<?php echo esc_url( $cdc_rodape_credito_link ); ?>" target="_blank" rel="noopener">
				<?php endif; ?>
				<img class="site-footer__credits-logo" data-figma-node="7073:95" src="<?php echo esc_url( cdc_asset( 'images/footer-criado-por.svg' ) ); ?>" width="20" height="16" alt="<?php echo esc_attr( $cdc_rodape_credito_nome ); ?>" loading="lazy" decoding="async">
				<?php if ( $cdc_rodape_credito_link ) : ?>
					</a>
				<?php endif; ?>
			</p>
		</div>
	</div>

	<div class="site-footer__pattern" data-figma-node="7073:111" aria-hidden="true">
		<img src="<?php echo esc_url( cdc_asset( 'images/footer-pattern.svg' ) ); ?>" width="1440" height="21.25" alt="" loading="lazy" decoding="async">
	</div>
</footer>
