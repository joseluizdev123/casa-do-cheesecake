<?php
/**
 * Página do artigo (post do blog). Só o design system da home: categoria · data
 * (como no destaque do blog), título em Anton, imagem destacada com raio 24 e o
 * conteúdo em Archivo (assets/css/paginas/artigo.css, tudo sob .artigo).
 * No fim, "Leia também": outros artigos da mesma categoria na seção/cards do blog.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$cdc_artigo_id   = get_the_ID();
	$cdc_artigo_cat  = cdc_blog_post_categoria( $cdc_artigo_id );
	$cdc_artigo_date = cdc_blog_date( $cdc_artigo_id );
	?>
<article class="artigo" id="artigo" aria-labelledby="artigo-titulo">
	<div class="container artigo__inner">
		<header class="artigo__head">
			<p class="blog-meta artigo__meta">
				<?php if ( $cdc_artigo_cat ) : ?>
					<a href="<?php echo esc_url( get_category_link( $cdc_artigo_cat ) ); ?>"><?php echo esc_html( $cdc_artigo_cat->name ); ?></a> ·
				<?php endif; ?>
				<time datetime="<?php echo esc_attr( $cdc_artigo_date['iso'] ); ?>"><?php echo esc_html( $cdc_artigo_date['label'] ); ?></time>
			</p>
			<h1 class="artigo__title" id="artigo-titulo"><?php echo esc_html( get_the_title() ); ?></h1>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="artigo__media">
				<?php
				the_post_thumbnail(
					'large',
					array(
						'class'         => 'artigo__img',
						'loading'       => false,
						'fetchpriority' => 'high',
						'decoding'      => 'async',
						'sizes'         => '(min-width: 864px) 800px, 100vw',
					)
				);
				?>
			</figure>
		<?php endif; ?>

		<div class="artigo__content">
			<?php
			the_content();
			wp_link_pages(
				array(
					'before' => '<nav class="artigo__paginas" aria-label="' . esc_attr__( 'Páginas do artigo', 'casadocheesecake' ) . '">',
					'after'  => '</nav>',
				)
			);
			?>
		</div>
	</div>
</article>
	<?php
	// Leia também: últimos da mesma categoria; completa com os mais recentes do blog.
	$cdc_rel_limite = cdc_blog_por_categoria();
	$cdc_rel        = $cdc_artigo_cat ? cdc_blog_posts_categoria( $cdc_artigo_cat->term_id, $cdc_rel_limite, array( $cdc_artigo_id ) ) : array();
	if ( count( $cdc_rel ) < $cdc_rel_limite ) {
		$cdc_rel = array_merge(
			$cdc_rel,
			get_posts(
				array(
					'post_type'           => 'post',
					'post_status'         => 'publish',
					'posts_per_page'      => $cdc_rel_limite - count( $cdc_rel ),
					'post__not_in'        => array_merge( array( $cdc_artigo_id ), wp_list_pluck( $cdc_rel, 'ID' ) ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
					'suppress_filters'    => false,
				)
			)
		);
	}
	get_template_part(
		'template-parts/blog/categoria',
		null,
		array(
			'id'     => 'leia-tambem',
			'titulo' => cdc_opt( 'blog', 'relacionados' ),
			'lead'   => $cdc_artigo_cat ? trim( wp_strip_all_tags( $cdc_artigo_cat->description ) ) : '',
			'posts'  => $cdc_rel,
			'link'   => $cdc_artigo_cat ? (string) get_category_link( $cdc_artigo_cat ) : cdc_page_url( 'blog' ),
		)
	);
endwhile;

get_footer();
