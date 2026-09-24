<?php
/**
 * Seção 06 — Sobre (Figma 7057:349). Espelha src/sections/06-sobre.html.
 *
 * Conteúdo: Customizer (painel "Sobre (Uma cozinha, não uma embalagem)").
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

$cdc_sobre_paragrafos = cdc_lines( cdc_mod( 'cdc_sobre_texto' ) );
$cdc_sobre_selo       = cdc_image( 'cdc_sobre_selo' );
$cdc_sobre_esp_foto   = cdc_image( 'cdc_sobre_especialista_foto' );

// Dimensões intrínsecas da foto principal: as do anexo quando o cliente troca a
// imagem no Customizer; 1047 × 1400 só para o asset padrão.
$cdc_sobre_img_w  = 1047;
$cdc_sobre_img_h  = 1400;
$cdc_sobre_img_id = get_theme_mod( 'cdc_sobre_imagem' );
if ( $cdc_sobre_img_id && is_numeric( $cdc_sobre_img_id ) ) {
	$cdc_sobre_img_src = wp_get_attachment_image_src( (int) $cdc_sobre_img_id, 'full' );
	if ( $cdc_sobre_img_src && $cdc_sobre_img_src[1] && $cdc_sobre_img_src[2] ) {
		$cdc_sobre_img_w = (int) $cdc_sobre_img_src[1];
		$cdc_sobre_img_h = (int) $cdc_sobre_img_src[2];
	}
}
?>
<section class="sobre" id="sobre" data-figma-node="7057:349" aria-labelledby="sobre-titulo">
	<div class="sobre__inner container">
		<figure class="sobre__media" data-figma-node="7057:350">
			<img class="sobre__foto" src="<?php echo esc_url( cdc_image( 'cdc_sobre_imagem' ) ); ?>" width="<?php echo esc_attr( $cdc_sobre_img_w ); ?>" height="<?php echo esc_attr( $cdc_sobre_img_h ); ?>" loading="lazy" decoding="async" alt="<?php echo esc_attr( cdc_mod( 'cdc_sobre_imagem_alt' ) ); ?>">
			<?php if ( $cdc_sobre_selo ) : ?>
				<div class="sobre__selo" data-figma-node="7057:1481">
					<img src="<?php echo esc_url( $cdc_sobre_selo ); ?>" width="142" height="142" loading="lazy" decoding="async" alt="<?php echo esc_attr( cdc_mod( 'cdc_sobre_selo_alt' ) ); ?>">
				</div>
			<?php endif; ?>
		</figure>

		<div class="sobre__content" data-figma-node="7057:352">
			<div class="sobre__text" data-figma-node="7057:353">
				<h2 class="sobre__title" id="sobre-titulo" data-figma-node="7057:354"><?php echo esc_html( cdc_mod( 'cdc_sobre_titulo' ) ); ?></h2>
				<?php if ( $cdc_sobre_paragrafos ) : ?>
					<div class="sobre__body" data-figma-node="7057:355">
						<?php foreach ( $cdc_sobre_paragrafos as $cdc_paragrafo ) : ?>
							<p><?php echo wp_kses_post( cdc_rich( $cdc_paragrafo ) ); ?></p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="sobre__especialista" data-figma-node="7057:356">
				<?php if ( $cdc_sobre_esp_foto ) : ?>
					<img class="sobre__especialista-foto" data-figma-node="7057:357" src="<?php echo esc_url( $cdc_sobre_esp_foto ); ?>" width="100" height="100" loading="lazy" decoding="async" alt="<?php echo esc_attr( cdc_mod( 'cdc_sobre_especialista_alt' ) ); ?>">
				<?php endif; ?>
				<div class="sobre__especialista-info" data-figma-node="7057:358">
					<p class="sobre__especialista-nome" data-figma-node="7057:359"><?php echo esc_html( cdc_mod( 'cdc_sobre_especialista_nome' ) ); ?></p>
					<p class="sobre__especialista-desc" data-figma-node="7057:360"><?php echo wp_kses_post( cdc_rich( cdc_mod( 'cdc_sobre_especialista_texto' ) ) ); ?></p>
				</div>
			</div>
		</div>
	</div>
</section>
