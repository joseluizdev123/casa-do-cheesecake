<?php
/**
 * Card de artigo — card da seção 05 da home: título com link, resumo e foto com
 * a data na pílula. Espelha os <li> de src/paginas/blog/03-*.html.
 * Usado no blog (home.php), no arquivo da categoria e no "Leia também".
 *
 * $args: post (WP_Post), titulo_tag ('h3' padrão; 'h2' no arquivo da categoria).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

$cdc_card_post = isset( $args['post'] ) ? get_post( $args['post'] ) : null;
if ( ! $cdc_card_post ) {
	return;
}
$cdc_card_tag     = ( isset( $args['titulo_tag'] ) && 'h2' === $args['titulo_tag'] ) ? 'h2' : 'h3';
$cdc_card_date    = cdc_blog_date( $cdc_card_post );
$cdc_card_excerpt = cdc_blog_excerpt( $cdc_card_post );
$cdc_card_img     = cdc_blog_image(
	$cdc_card_post,
	array(
		'class'    => 'diferenciais__img',
		'loading'  => 'lazy',
		'decoding' => 'async',
		// 3 cards por linha (>= 1024), 2 colunas no tablet, largura total no mobile (como na home).
		'sizes'    => '(min-width: 1440px) 390px, (min-width: 1024px) 27vw, (min-width: 768px) 50vw, 100vw',
	)
);
?>
<li class="diferenciais__card blog-card">
	<div class="diferenciais__body">
		<<?php echo $cdc_card_tag; // phpcs:ignore WordPress.Security.EscapeOutput -- 'h2' ou 'h3'. ?> class="diferenciais__card-title"><a class="blog-card__link" href="<?php echo esc_url( get_permalink( $cdc_card_post ) ); ?>"><?php echo esc_html( get_the_title( $cdc_card_post ) ); ?></a></<?php echo $cdc_card_tag; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<?php if ( '' !== $cdc_card_excerpt ) : ?>
			<p class="diferenciais__card-text"><?php echo esc_html( $cdc_card_excerpt ); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( $cdc_card_img ) : ?>
		<figure class="diferenciais__media">
			<?php echo $cdc_card_img; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_get_attachment_image() / cdc_blog_image() escapado. ?>
			<p class="diferenciais__tag blog-tag"><time class="diferenciais__tag-text" datetime="<?php echo esc_attr( $cdc_card_date['iso'] ); ?>"><?php echo esc_html( $cdc_card_date['label'] ); ?></time></p>
		</figure>
	<?php endif; ?>
</li>
