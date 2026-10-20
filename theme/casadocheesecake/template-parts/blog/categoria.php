<?php
/**
 * Seção de uma categoria — cabeçalho e cards da seção 05 da home + "Ver todos os
 * artigos". Espelha src/paginas/blog/03-*.html. Usada no blog (home.php) e no
 * "Leia também" do artigo (single.php).
 *
 * $args: id, titulo, lead (texto puro), posts (WP_Post[]), link (página da
 * categoria; '' = sem botão).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

$cdc_cat_args = wp_parse_args(
	$args,
	array(
		'id'     => '',
		'titulo' => '',
		'lead'   => '',
		'posts'  => array(),
		'link'   => '',
	)
);
if ( ! $cdc_cat_args['posts'] ) {
	return;
}
$cdc_cat_id = sanitize_html_class( $cdc_cat_args['id'] );
?>
<section class="diferenciais blog-categoria" id="<?php echo esc_attr( $cdc_cat_id ); ?>" aria-labelledby="<?php echo esc_attr( $cdc_cat_id . '-titulo' ); ?>">
	<div class="container diferenciais__inner">
		<header class="diferenciais__head">
			<h2 class="diferenciais__title" id="<?php echo esc_attr( $cdc_cat_id . '-titulo' ); ?>"><?php echo esc_html( $cdc_cat_args['titulo'] ); ?></h2>
			<?php if ( '' !== trim( $cdc_cat_args['lead'] ) ) : ?>
				<p class="diferenciais__lead"><?php echo esc_html( $cdc_cat_args['lead'] ); ?></p>
			<?php endif; ?>
		</header>
		<ul class="diferenciais__list" role="list">
			<?php
			foreach ( $cdc_cat_args['posts'] as $cdc_cat_post ) {
				get_template_part( 'template-parts/blog/card', null, array( 'post' => $cdc_cat_post ) );
			}
			?>
		</ul>
		<?php if ( '' !== $cdc_cat_args['link'] ) : ?>
			<div class="blog-categoria__mais">
				<a class="btn btn--primary" href="<?php echo esc_url( $cdc_cat_args['link'] ); ?>"><?php echo esc_html( cdc_opt( 'blog', 'ver_todos' ) ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>
