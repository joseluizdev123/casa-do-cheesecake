<?php
/**
 * Seção 11 — CTA final "Peça o seu cheesecake hoje" (Figma 7057:955).
 * Espelha src/sections/11-cta-final.html.
 *
 * Conteúdo: Customizer (painel "CTA final (Peça o seu cheesecake)") + links globais.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

$cdc_cta_pedir = trim( (string) cdc_mod( 'cdc_cta_botao_url' ) );
if ( '' === $cdc_cta_pedir ) {
	$cdc_cta_pedir = (string) cdc_mod( 'cdc_contato_cardapio' );
}
if ( 0 === strpos( $cdc_cta_pedir, '#' ) ) {
	$cdc_cta_pedir = cdc_anchor( $cdc_cta_pedir );
}

$cdc_cta_whats = trim( (string) cdc_mod( 'cdc_cta_whatsapp_url' ) );
if ( '' === $cdc_cta_whats ) {
	$cdc_cta_whats = (string) cdc_mod( 'cdc_contato_whatsapp' );
}
$cdc_cta_whats_externo = cdc_is_external( $cdc_cta_whats );

// Título: uma linha por linha do campo. O título usa white-space: pre-wrap, então
// o markup não pode ter quebras/indentação dentro do <h2> (espaços finais são mantidos).
$cdc_cta_titulo = implode(
	'<br>',
	array_map( 'esc_html', preg_split( '/\r\n|\r|\n/', (string) cdc_mod( 'cdc_cta_titulo' ) ) )
);
?>
<section class="cta-final" id="pedir" data-figma-node="7057:955" aria-labelledby="cta-final-titulo">
	<div class="cta-final__inner container">
		<div class="cta-final__head" data-figma-node="7057:992">
			<div class="cta-final__selo" data-figma-node="7057:1367">
				<img src="<?php echo esc_url( cdc_asset( 'images/11-cta-final-selo.svg' ) ); ?>" width="142" height="142" loading="lazy" decoding="async" alt="Yes, we have cheesecake. Desde 2004.">
			</div>
			<h2 class="cta-final__title" id="cta-final-titulo" data-figma-node="7057:993"><?php echo wp_kses( $cdc_cta_titulo, array( 'br' => array() ) ); ?></h2>
			<p class="cta-final__text" data-figma-node="7057:1009"><?php echo nl2br( esc_html( cdc_mod( 'cdc_cta_texto' ) ) ); ?></p>
			<img class="cta-final__fatia" data-figma-node="7057:994" src="<?php echo esc_url( cdc_image( 'cdc_cta_imagem' ) ); ?>" width="198" height="184" loading="lazy" decoding="async" alt="<?php echo esc_attr( cdc_mod( 'cdc_cta_imagem_alt' ) ); ?>">
		</div>

		<div class="cta-final__actions" data-figma-node="7057:1005">
			<a class="btn btn--primary-dark" data-figma-node="7079:408" href="<?php echo esc_url( $cdc_cta_pedir ); ?>"<?php echo cdc_target_attr( $cdc_cta_pedir ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( cdc_mod( 'cdc_cta_botao' ) ); ?></a>
			<p class="cta-final__ou" data-figma-node="7057:1006"><?php echo esc_html( cdc_mod( 'cdc_cta_ou' ) ); ?></p>
			<a class="btn-link btn-link--dark" data-figma-node="7079:412" href="<?php echo esc_url( $cdc_cta_whats ); ?>"<?php echo $cdc_cta_whats_externo ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( cdc_mod( 'cdc_cta_whatsapp' ) ); ?><?php if ( $cdc_cta_whats_externo ) : ?><span class="sr-only"> (abre em nova aba)</span><?php endif; ?></a>
		</div>
	</div>
</section>
