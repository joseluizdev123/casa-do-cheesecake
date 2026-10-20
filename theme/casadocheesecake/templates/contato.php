<?php
/**
 * Template Name: Contato
 *
 * Página Contato. Espelha src/paginas/contato/01-fale-conosco.html (bloco "Produção
 * própria" da home com os canais nas linhas de preço do cardápio) + a seção 10
 * (Perguntas frequentes) da home. Conteúdo: Páginas do site → Contato e o CPT
 * "Canais de contato" (inc/pages/30-contato.php).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cdc_contato_fotos      = cdc_opt_gallery( 'contato', 'fotos' );
$cdc_contato_paragrafos = cdc_paragraphs( cdc_opt( 'contato', 'texto' ) );
$cdc_contato_canais     = cdc_contato_canais();
$cdc_contato_selo       = '1' === cdc_opt( 'contato', 'selo' ) ? cdc_image( 'cdc_sobre_selo' ) : '';
?>
<section class="sobre contato" id="fale-conosco" aria-labelledby="fale-conosco-titulo">
	<div class="sobre__inner container">
		<figure class="sobre__media contato__fotos" aria-roledescription="carrossel" aria-label="<?php esc_attr_e( 'Fotos dos nossos cheesecakes', 'casadocheesecake' ); ?>" data-contato-fotos>
			<?php
			foreach ( $cdc_contato_fotos as $cdc_i => $cdc_foto ) :
				$cdc_primeira = 0 === $cdc_i;
				?>
				<img class="sobre__foto contato__slide<?php echo $cdc_primeira ? ' is-active' : ''; ?>" src="<?php echo esc_url( $cdc_foto['url'] ); ?>"<?php echo $cdc_foto['w'] ? ' width="' . esc_attr( $cdc_foto['w'] ) . '" height="' . esc_attr( $cdc_foto['h'] ) . '"' : ''; ?><?php echo $cdc_primeira ? '' : ' loading="lazy"'; ?> decoding="async" alt="<?php echo esc_attr( cdc_contato_foto_alt( $cdc_foto ) ); ?>"<?php echo $cdc_primeira ? '' : ' aria-hidden="true"'; ?> data-contato-slide>
			<?php endforeach; ?>
			<?php if ( $cdc_contato_selo ) : ?>
				<div class="sobre__selo">
					<img src="<?php echo esc_url( $cdc_contato_selo ); ?>" width="142" height="142" loading="lazy" decoding="async" alt="<?php echo esc_attr( cdc_mod( 'cdc_sobre_selo_alt' ) ); ?>">
				</div>
			<?php endif; ?>
			<?php // Setas: mesmo visual das dos depoimentos (07). ?>
			<div class="contato__setas">
				<button class="contato__seta" type="button" aria-label="<?php esc_attr_e( 'Foto anterior', 'casadocheesecake' ); ?>" data-contato-prev>
					<img src="<?php echo esc_url( cdc_asset( 'images/07-depoimentos-chevron-left.svg' ) ); ?>" alt="" width="20" height="20">
				</button>
				<button class="contato__seta" type="button" aria-label="<?php esc_attr_e( 'Próxima foto', 'casadocheesecake' ); ?>" data-contato-next>
					<img src="<?php echo esc_url( cdc_asset( 'images/07-depoimentos-chevron-right.svg' ) ); ?>" alt="" width="20" height="20">
				</button>
			</div>
			<p class="sr-only" aria-live="polite" aria-atomic="true" data-contato-status></p>
		</figure>

		<div class="sobre__content">
			<div class="sobre__text">
				<h1 class="sobre__title" id="fale-conosco-titulo"><?php echo esc_html( cdc_opt( 'contato', 'titulo' ) ); ?></h1>
				<?php if ( $cdc_contato_paragrafos ) : ?>
					<div class="sobre__body">
						<?php foreach ( $cdc_contato_paragrafos as $cdc_paragrafo ) : ?>
							<p><?php echo wp_kses_post( cdc_rich( $cdc_paragrafo ) ); ?></p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $cdc_contato_canais ) : ?>
				<ul class="cardapio__prices contato__canais" role="list">
					<?php foreach ( $cdc_contato_canais as $cdc_i => $cdc_canal ) : ?>
						<li class="cardapio__row">
							<p class="cardapio__option">
								<span class="cardapio__price"><?php echo esc_html( $cdc_canal['title'] ); ?></span>
								<?php if ( '' !== $cdc_canal['contato'] ) : ?>
									<span class="cardapio__size"><?php echo cdc_contato_texto( $cdc_canal['contato'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado na função. ?></span>
								<?php endif; ?>
							</p>
							<?php if ( '' !== $cdc_canal['link'] && '' !== $cdc_canal['botao'] ) : ?>
								<a class="btn <?php echo 0 === $cdc_i ? 'btn--primary' : 'btn--secondary'; ?> cardapio__btn" href="<?php echo esc_url( $cdc_canal['link'] ); ?>"<?php echo cdc_target_attr( $cdc_canal['link'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- atributos fixos. ?> aria-label="<?php echo esc_attr( $cdc_canal['botao'] . ' — ' . $cdc_canal['title'] ); ?>"><?php echo esc_html( $cdc_canal['botao'] ); ?></a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php
if ( '1' === cdc_opt( 'contato', 'perguntas' ) ) {
	get_template_part( 'template-parts/sections/10-perguntas' );
}

get_footer();
