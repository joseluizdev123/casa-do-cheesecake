<?php
/**
 * Blog — página de posts (Configurações → Leitura). Espelha blog.html /
 * src/paginas/blog: 01 topo (título, texto e botões das categorias), 02 artigo
 * em destaque (bloco "Produção própria" da home) e uma seção por categoria com
 * os últimos artigos (cards da seção 05) e "Ver todos os artigos".
 *
 * Textos, categorias e rótulos: Páginas do site → Blog. Artigos: Posts (o post
 * fixado é o destaque e não se repete nos cards).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

$cdc_destaque = cdc_blog_destaque();
$cdc_limite   = cdc_blog_por_categoria();
$cdc_secoes   = array();
foreach ( cdc_blog_categorias() as $cdc_term ) {
	$cdc_posts = cdc_blog_posts_categoria( $cdc_term->term_id, $cdc_limite, $cdc_destaque ? array( $cdc_destaque->ID ) : array() );
	if ( $cdc_posts ) {
		$cdc_secoes[] = array(
			'id'     => cdc_blog_section_id( $cdc_term ),
			'titulo' => $cdc_term->name,
			'lead'   => trim( wp_strip_all_tags( $cdc_term->description ) ),
			'posts'  => $cdc_posts,
			'link'   => (string) get_category_link( $cdc_term ),
		);
	}
}

get_header();
?>
<section class="diferenciais blog-topo" id="blog" aria-labelledby="blog-titulo">
	<div class="container diferenciais__inner">
		<header class="diferenciais__head">
			<h1 class="diferenciais__title" id="blog-titulo"><?php echo esc_html( cdc_opt( 'blog', 'titulo' ) ); ?></h1>
			<p class="diferenciais__lead"><?php echo cdc_rich( cdc_opt( 'blog', 'texto' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em cdc_rich(). ?></p>
		</header>
		<?php if ( $cdc_secoes ) : ?>
			<nav aria-label="<?php esc_attr_e( 'Categorias do blog', 'casadocheesecake' ); ?>">
				<ul class="blog-topo__categorias" role="list">
					<?php foreach ( $cdc_secoes as $cdc_secao ) : ?>
						<li><a class="btn-link" href="#<?php echo esc_attr( $cdc_secao['id'] ); ?>"><?php echo esc_html( $cdc_secao['titulo'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>
	</div>
</section>
<?php
if ( $cdc_destaque ) :
	$cdc_destaque_cat   = cdc_blog_post_categoria( $cdc_destaque );
	$cdc_destaque_date  = cdc_blog_date( $cdc_destaque );
	$cdc_destaque_title = get_the_title( $cdc_destaque );
	$cdc_destaque_texto = cdc_blog_excerpt( $cdc_destaque );
	$cdc_destaque_nome  = wp_strip_all_tags( html_entity_decode( $cdc_destaque_title, ENT_QUOTES, 'UTF-8' ) );
	$cdc_destaque_ler   = (string) cdc_opt( 'blog', 'ler' );
	// Nome acessível começa com o texto visível do botão (WCAG 2.5.3); o padrão é o do estático.
	$cdc_destaque_aria  = 'Ler artigo' === $cdc_destaque_ler
		/* translators: %s: título do artigo. */
		? sprintf( __( 'Ler o artigo: %s', 'casadocheesecake' ), $cdc_destaque_nome )
		: $cdc_destaque_ler . ': ' . $cdc_destaque_nome;
	$cdc_destaque_img   = cdc_blog_image(
		$cdc_destaque,
		array(
			'class'    => 'sobre__foto',
			'loading'  => false,
			'decoding' => 'async',
			'sizes'    => '(min-width: 1024px) 50vw, 100vw',
		)
	);
	?>
<section class="sobre blog-destaque" id="destaque" aria-labelledby="destaque-titulo">
	<div class="sobre__inner container">
		<figure class="sobre__media">
			<?php echo $cdc_destaque_img; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_get_attachment_image() / cdc_blog_image() escapado. ?>
			<?php
			// Etiqueta: salva vazia = sem etiqueta (nunca salva = texto do layout).
			$cdc_blog_vals = cdc_opt_values( 'blog' );
			$cdc_tag       = isset( $cdc_blog_vals['destaque_tag'] ) ? trim( (string) $cdc_blog_vals['destaque_tag'] ) : (string) cdc_opt( 'blog', 'destaque_tag' );
			?>
			<?php if ( '' !== $cdc_tag ) : ?>
				<p class="diferenciais__tag blog-tag"><span class="diferenciais__tag-text"><?php echo esc_html( $cdc_tag ); ?></span></p>
			<?php endif; ?>
		</figure>

		<div class="sobre__content">
			<div class="sobre__text">
				<p class="blog-meta"><?php echo $cdc_destaque_cat ? esc_html( $cdc_destaque_cat->name ) . ' · ' : ''; ?><time datetime="<?php echo esc_attr( $cdc_destaque_date['iso'] ); ?>"><?php echo esc_html( $cdc_destaque_date['label'] ); ?></time></p>
				<h2 class="sobre__title" id="destaque-titulo"><?php echo esc_html( $cdc_destaque_title ); ?></h2>
				<?php if ( '' !== $cdc_destaque_texto ) : ?>
					<div class="sobre__body">
						<p><?php echo esc_html( $cdc_destaque_texto ); ?></p>
					</div>
				<?php endif; ?>
			</div>
			<a class="btn btn--primary" href="<?php echo esc_url( get_permalink( $cdc_destaque ) ); ?>" aria-label="<?php echo esc_attr( $cdc_destaque_aria ); ?>"><?php echo esc_html( cdc_opt( 'blog', 'ler' ) ); ?></a>
		</div>
	</div>
</section>
	<?php
endif;

foreach ( $cdc_secoes as $cdc_secao ) {
	get_template_part( 'template-parts/blog/categoria', null, $cdc_secao );
}

get_footer();
