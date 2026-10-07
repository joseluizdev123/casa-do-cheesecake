<?php
/**
 * Seção 02 — Primeira fatia (Figma "Call to action" 7057:164).
 * Espelha src/sections/02-primeira-fatia.html (mesmas classes e data-figma-node).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

// A seção é repetida abaixo do cardápio (03b) com textos e fotos próprios:
// lá o id ganha sufixo e os campos vêm do prefixo cdc_fatia2_.
$cdc_fatia_id  = isset( $cdc_fatia_id ) ? $cdc_fatia_id : 'primeira-fatia';
$cdc_fatia_key = isset( $cdc_fatia_key ) ? $cdc_fatia_key : 'cdc_fatia';
$cdc_fatia_img = $cdc_fatia_key . '_imagem';
$cdc_fatia_sub = trim( cdc_mod( $cdc_fatia_key . '_subtitulo' ) );
$cdc_fatia_txt = trim( cdc_mod( $cdc_fatia_key . '_texto' ) );

$cdc_fatia_link = cdc_mod( $cdc_fatia_key . '_botao_link' );
// Só o banner do desconto cai no WhatsApp global; o da fatia (03b) sem link
// mostra o botão sem levar a lugar nenhum.
if ( ! $cdc_fatia_link && 'cdc_fatia' === $cdc_fatia_key ) {
	$cdc_fatia_link = cdc_mod( 'cdc_contato_whatsapp' );
}
$cdc_fatia_externo = cdc_is_external( $cdc_fatia_link );
?>
<section class="primeira-fatia" id="<?php echo esc_attr( $cdc_fatia_id ); ?>" data-figma-node="7057:164" aria-labelledby="<?php echo esc_attr( $cdc_fatia_id ); ?>-titulo">
	<div class="container">
		<div class="primeira-fatia__box" data-figma-node="7057:165">
			<h2 class="primeira-fatia__title u-display" id="<?php echo esc_attr( $cdc_fatia_id ); ?>-titulo" data-figma-node="7057:166"><?php echo cdc_rich( cdc_mod( $cdc_fatia_key . '_titulo' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em cdc_rich(). ?></h2>

			<div class="primeira-fatia__media">
				<div class="primeira-fatia__images" data-figma-node="7057:172">
					<img class="primeira-fatia__img primeira-fatia__img--foreground" data-figma-node="7057:173" src="<?php echo esc_url( cdc_image( $cdc_fatia_img . '_frente' ) ); ?>" width="206" height="191" decoding="async" alt="<?php echo esc_attr( cdc_mod( $cdc_fatia_img . '_frente_alt' ) ); ?>">
					<img class="primeira-fatia__img primeira-fatia__img--background" data-figma-node="7057:174" src="<?php echo esc_url( cdc_image( $cdc_fatia_img . '_fundo' ) ); ?>" width="305" height="305" decoding="async" alt="<?php echo esc_attr( cdc_mod( $cdc_fatia_img . '_fundo_alt' ) ); ?>">
				</div>
			</div>

			<div class="primeira-fatia__content" data-figma-node="7057:167">
				<?php if ( '' !== $cdc_fatia_sub ) : ?>
					<p class="primeira-fatia__subtitle" data-figma-node="7057:168"><?php echo esc_html( $cdc_fatia_sub ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $cdc_fatia_txt ) : ?>
				<p class="primeira-fatia__text" data-figma-node="7057:169"><?php echo str_replace( ' · ', '&nbsp;· ', cdc_rich( $cdc_fatia_txt ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em cdc_rich(); o nbsp prende cada " · " à palavra anterior (nenhuma linha começa com o ponto). ?></p>
				<?php endif; ?>
				<?php if ( $cdc_fatia_link ) : ?>
					<a class="btn btn--primary" href="<?php echo esc_url( $cdc_fatia_link ); ?>"<?php echo $cdc_fatia_externo ? ' target="_blank" rel="noopener"' : ''; ?> data-figma-node="7073:338"><?php echo esc_html( cdc_mod( $cdc_fatia_key . '_botao' ) ); ?></a>
				<?php else : ?>
					<span class="btn btn--primary" data-figma-node="7073:338"><?php echo esc_html( cdc_mod( $cdc_fatia_key . '_botao' ) ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
