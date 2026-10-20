<?php
/**
 * Template Name: Sobre nós
 *
 * Página Sobre nós. Espelha sobre.html (src/paginas/sobre): blocos Nossa história
 * e Pioneiros com o layout da seção Sobre da home, depois Entrega e Restaurantes.
 * Conteúdo: Páginas do site → Sobre nós (inc/pages/20-sobre.php).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cdc_blocos = array(
	'historia'  => array(
		'tag'   => 'h1',
		'selo'  => (bool) cdc_opt( 'sobre', 'historia_selo' ),
		'esp'   => '',
		'dados' => cdc_sobre_bloco( 'historia' ),
	),
	'pioneiros' => array(
		'tag'   => 'h2',
		'selo'  => false,
		'esp'   => cdc_sobre_especialista(),
		'dados' => cdc_sobre_bloco( 'pioneiros' ),
	),
);

// Selo: o mesmo da seção Sobre da home (Customizer).
$cdc_selo = cdc_image( 'cdc_sobre_selo' );

foreach ( $cdc_blocos as $cdc_id => $cdc_bloco ) :
	$cdc_foto = $cdc_bloco['dados']['foto'];
	?>
<section class="sobre sobre--pagina" id="<?php echo esc_attr( $cdc_id ); ?>" aria-labelledby="<?php echo esc_attr( $cdc_id . '-titulo' ); ?>">
	<div class="sobre__inner container">
		<figure class="sobre__media">
			<?php if ( $cdc_foto['url'] ) : ?>
				<img class="sobre__foto" src="<?php echo esc_url( $cdc_foto['url'] ); ?>"<?php echo $cdc_foto['w'] ? ' width="' . (int) $cdc_foto['w'] . '" height="' . (int) $cdc_foto['h'] . '"' : ''; ?><?php echo 'historia' === $cdc_id ? '' : ' loading="lazy"'; ?> decoding="async" alt="<?php echo esc_attr( $cdc_bloco['dados']['alt'] ); ?>">
			<?php endif; ?>
			<?php if ( $cdc_bloco['selo'] && $cdc_selo ) : ?>
				<div class="sobre__selo">
					<img src="<?php echo esc_url( $cdc_selo ); ?>" width="142" height="142" loading="lazy" decoding="async" alt="<?php echo esc_attr( cdc_mod( 'cdc_sobre_selo_alt' ) ); ?>">
				</div>
			<?php endif; ?>
		</figure>

		<div class="sobre__content">
			<div class="sobre__text">
				<<?php echo tag_escape( $cdc_bloco['tag'] ); ?> class="sobre__title" id="<?php echo esc_attr( $cdc_id . '-titulo' ); ?>"><?php echo esc_html( $cdc_bloco['dados']['titulo'] ); ?></<?php echo tag_escape( $cdc_bloco['tag'] ); ?>>
				<?php if ( $cdc_bloco['dados']['paragrafos'] ) : ?>
					<div class="sobre__body">
						<?php foreach ( $cdc_bloco['dados']['paragrafos'] as $cdc_paragrafo ) : ?>
							<p><?php echo cdc_rich( $cdc_paragrafo ); // phpcs:ignore WordPress.Security.EscapeOutput -- cdc_rich() escapa. ?></p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( '' !== $cdc_bloco['esp'] ) : ?>

				<div class="sobre__especialista">
					<div class="sobre__especialista-info">
						<p class="sobre__especialista-nome"><?php echo esc_html( $cdc_bloco['esp'] ); ?></p>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
	<?php
endforeach;

// Seções da home (mesmo conteúdo do Customizer), ligadas na option page.
if ( cdc_opt( 'sobre', 'mostrar_entrega' ) ) {
	get_template_part( 'template-parts/sections/08-entrega' );
}
if ( cdc_opt( 'sobre', 'mostrar_restaurantes' ) ) {
	get_template_part( 'template-parts/sections/09-restaurantes' );
}

get_footer();
