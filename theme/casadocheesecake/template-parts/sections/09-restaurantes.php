<?php
/**
 * Seção 09 — Restaurantes (Figma "Delivery" 7057:508).
 * Espelha src/sections/09-restaurantes.html (mesmas classes e data-figma-node).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

$cdc_rest_link = cdc_mod( 'cdc_rest_botao_link' );
if ( ! $cdc_rest_link ) {
	$cdc_rest_link = cdc_mod( 'cdc_contato_whatsapp' );
}
$cdc_rest_externo = (bool) preg_match( '#^https?://#', $cdc_rest_link );
?>
<section class="restaurantes" id="restaurantes" data-figma-node="7057:508" aria-labelledby="restaurantes-titulo">
	<div class="container">
		<div class="restaurantes__banner" data-figma-node="7057:509">
			<h2 class="restaurantes__title" id="restaurantes-titulo" data-figma-node="7057:510"><?php echo esc_html( cdc_mod( 'cdc_rest_titulo' ) ); ?></h2>
			<div class="restaurantes__media" data-figma-node="7099:227">
				<img class="restaurantes__image" data-figma-node="7057:515" src="<?php echo esc_url( cdc_image( 'cdc_rest_imagem' ) ); ?>" width="357" height="305" alt="<?php echo esc_attr( cdc_mod( 'cdc_rest_imagem_alt' ) ); ?>" loading="lazy">
				<?php if ( cdc_rest_selo_visivel() ) : ?>
					<img class="restaurantes__selo" data-figma-node="7099:240" src="<?php echo esc_url( cdc_asset( 'images/09-restaurantes-selo.svg' ) ); ?>" width="53" height="63" alt="" loading="lazy">
				<?php endif; ?>
			</div>
			<div class="restaurantes__content" data-figma-node="7057:511">
				<p class="restaurantes__text" data-figma-node="7057:512"><?php echo cdc_rich( cdc_mod( 'cdc_rest_texto' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em cdc_rich(). ?></p>
				<a class="btn btn--primary" data-figma-node="7079:401" href="<?php echo esc_url( $cdc_rest_link ); ?>"<?php echo $cdc_rest_externo ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( cdc_mod( 'cdc_rest_botao' ) ); ?></a>
			</div>
		</div>
	</div>
</section>
