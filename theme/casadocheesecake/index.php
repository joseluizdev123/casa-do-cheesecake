<?php
/**
 * Fallback genérico (blog, páginas internas).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container" style="padding-block: var(--section-py);">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<h1 class="u-display"><?php the_title(); ?></h1>
				<div class="entry-content"><?php the_content(); ?></div>
			</article>
		<?php endwhile; ?>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nada encontrado.', 'casadocheesecake' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
