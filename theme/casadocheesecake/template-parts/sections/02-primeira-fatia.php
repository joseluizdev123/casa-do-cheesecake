<?php
/**
 * Seção 02 — Primeira fatia (Figma "Call to action" 7057:164).
 * Espelha src/sections/02-primeira-fatia.html (mesmas classes e data-figma-node).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

$cdc_fatia_link = cdc_mod( 'cdc_fatia_botao_link' );
if ( ! $cdc_fatia_link ) {
	$cdc_fatia_link = cdc_mod( 'cdc_contato_whatsapp' );
}
$cdc_fatia_externo = (bool) preg_match( '#^https?://#', $cdc_fatia_link );
?>
<section class="primeira-fatia" id="primeira-fatia" data-figma-node="7057:164" aria-labelledby="primeira-fatia-titulo">
	<div class="container">
		<div class="primeira-fatia__box" data-figma-node="7057:165">
			<h2 class="primeira-fatia__title u-display" id="primeira-fatia-titulo" data-figma-node="7057:166"><?php echo cdc_rich( cdc_mod( 'cdc_fatia_titulo' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em cdc_rich(). ?></h2>

			<div class="primeira-fatia__media">
				<div class="primeira-fatia__images" data-figma-node="7057:172">
					<img class="primeira-fatia__img primeira-fatia__img--foreground" data-figma-node="7057:173" src="<?php echo esc_url( cdc_image( 'cdc_fatia_imagem_frente' ) ); ?>" width="206" height="191" decoding="async" alt="<?php echo esc_attr( cdc_mod( 'cdc_fatia_imagem_frente_alt' ) ); ?>">
					<img class="primeira-fatia__img primeira-fatia__img--background" data-figma-node="7057:174" src="<?php echo esc_url( cdc_image( 'cdc_fatia_imagem_fundo' ) ); ?>" width="305" height="305" decoding="async" alt="<?php echo esc_attr( cdc_mod( 'cdc_fatia_imagem_fundo_alt' ) ); ?>">
				</div>
			</div>

			<div class="primeira-fatia__content" data-figma-node="7057:167">
				<p class="primeira-fatia__subtitle" data-figma-node="7057:168"><?php echo esc_html( cdc_mod( 'cdc_fatia_subtitulo' ) ); ?></p>
				<p class="primeira-fatia__text" data-figma-node="7057:169"><?php echo str_replace( ' · ', '&nbsp;· ', cdc_rich( cdc_mod( 'cdc_fatia_texto' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em cdc_rich(); o nbsp prende cada " · " à palavra anterior (nenhuma linha começa com o ponto). ?></p>
				<a class="btn btn--primary" href="<?php echo esc_url( $cdc_fatia_link ); ?>"<?php echo $cdc_fatia_externo ? ' target="_blank" rel="noopener"' : ''; ?> data-figma-node="7073:338"><?php echo esc_html( cdc_mod( 'cdc_fatia_botao' ) ); ?></a>
			</div>
		</div>
	</div>
</section>
