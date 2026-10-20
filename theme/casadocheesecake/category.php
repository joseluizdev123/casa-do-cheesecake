<?php
/**
 * Arquivo da categoria ("Ver todos os artigos" do blog): cabeçalho da seção 05
 * da home (nome + descrição da categoria) e todos os artigos nos mesmos cards do
 * blog, N por página (Páginas do site → Blog), com os botões da home na paginação.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;

$cdc_term  = get_queried_object();
$cdc_id    = $cdc_term instanceof WP_Term ? cdc_blog_section_id( $cdc_term ) : 'categoria';
$cdc_lead  = $cdc_term instanceof WP_Term ? trim( wp_strip_all_tags( $cdc_term->description ) ) : '';
$cdc_paged = max( 1, (int) get_query_var( 'paged' ) );
$cdc_pages = (int) $wp_query->max_num_pages;

get_header();
?>
<section class="diferenciais blog-categoria blog-arquivo" id="<?php echo esc_attr( $cdc_id ); ?>" aria-labelledby="<?php echo esc_attr( $cdc_id . '-titulo' ); ?>">
	<div class="container diferenciais__inner">
		<header class="diferenciais__head">
			<h1 class="diferenciais__title" id="<?php echo esc_attr( $cdc_id . '-titulo' ); ?>"><?php echo esc_html( single_cat_title( '', false ) ); ?></h1>
			<?php if ( '' !== $cdc_lead ) : ?>
				<p class="diferenciais__lead"><?php echo esc_html( $cdc_lead ); ?></p>
			<?php endif; ?>
		</header>
		<?php if ( have_posts() ) : ?>
			<ul class="diferenciais__list" role="list">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part(
						'template-parts/blog/card',
						null,
						array(
							'post'       => get_post(),
							'titulo_tag' => 'h2',
						)
					);
				}
				?>
			</ul>
			<?php if ( $cdc_pages > 1 ) : ?>
				<nav class="blog-categoria__mais blog-paginacao" aria-label="<?php esc_attr_e( 'Páginas de artigos', 'casadocheesecake' ); ?>">
					<?php if ( $cdc_paged < $cdc_pages ) : ?>
						<a class="btn btn--secondary" href="<?php echo esc_url( get_next_posts_page_link( $cdc_pages ) ); ?>"><?php echo esc_html( cdc_opt( 'blog', 'anteriores' ) ); ?></a>
					<?php endif; ?>
					<?php if ( $cdc_paged > 1 ) : ?>
						<a class="btn btn--secondary" href="<?php echo esc_url( get_previous_posts_page_link() ); ?>"><?php echo esc_html( cdc_opt( 'blog', 'recentes' ) ); ?></a>
					<?php endif; ?>
				</nav>
			<?php endif; ?>
		<?php else : ?>
			<p class="diferenciais__card-text"><?php esc_html_e( 'Ainda não há artigos nesta categoria.', 'casadocheesecake' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
