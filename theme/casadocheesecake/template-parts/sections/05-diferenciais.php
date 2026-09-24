<?php
/**
 * Seção 05 — Diferenciais (Figma 7057:330) — espelha src/sections/05-diferenciais.html.
 *
 * Cabeçalho: Customizer (cdc_dif_titulo, cdc_dif_texto).
 * Cards: CPT cdc_diferencial (título, texto, tag, destaque, imagem destacada);
 * enquanto o CPT estiver vazio, usa cdc_diferenciais_defaults() (copy do Figma).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

// Nodes Figma dos 3 cards do layout (marcação para o diff numérico).
$cdc_nodes = array(
	array( 'card' => '7057:335', 'body' => '7057:336', 'title' => '7057:337', 'text' => '7057:338', 'media' => '7057:1414', 'tag' => '7057:1494', 'tag_icon' => '7057:1495', 'tag_text' => '7057:1499' ),
	array( 'card' => '7057:341', 'body' => '7057:342', 'title' => '7057:343', 'text' => '7057:344', 'media' => '7057:1438' ),
	array( 'card' => '7057:345', 'body' => '7057:346', 'title' => '7057:347', 'text' => '7057:348', 'media' => '7057:1440' ),
);
$cdc_node  = function ( $i, $key ) use ( $cdc_nodes ) {
	return isset( $cdc_nodes[ $i ][ $key ] ) ? ' data-figma-node="' . esc_attr( $cdc_nodes[ $i ][ $key ] ) . '"' : '';
};

$cdc_defaults = cdc_diferenciais_defaults();
$cdc_alts     = wp_list_pluck( $cdc_defaults, 'alt', 'title' );
$cdc_items    = array();
$cdc_img_attr = array(
	'class'    => 'diferenciais__img',
	'loading'  => 'lazy',
	'decoding' => 'async',
	'sizes'    => '(min-width: 1440px) 390px, 27vw',
);

$cdc_posts = cdc_posts( 'cdc_diferencial' );
if ( $cdc_posts ) {
	foreach ( $cdc_posts as $cdc_post ) {
		$cdc_title    = get_the_title( $cdc_post );
		$cdc_thumb_id = get_post_thumbnail_id( $cdc_post );
		$cdc_img      = '';
		if ( $cdc_thumb_id ) {
			$cdc_alt = (string) get_post_meta( $cdc_thumb_id, '_wp_attachment_image_alt', true );
			if ( '' === $cdc_alt ) {
				$cdc_alt = isset( $cdc_alts[ $cdc_title ] ) ? $cdc_alts[ $cdc_title ] : $cdc_title;
			}
			$cdc_img = wp_get_attachment_image( $cdc_thumb_id, 'large', false, array_merge( $cdc_img_attr, array( 'alt' => $cdc_alt ) ) );
		}
		$cdc_items[] = array(
			'title'    => $cdc_title,
			'texto'    => (string) cdc_meta( $cdc_post->ID, 'texto' ),
			'tag'      => (string) cdc_meta( $cdc_post->ID, 'tag' ),
			'destaque' => '1' === (string) cdc_meta( $cdc_post->ID, 'destaque' ),
			'img'      => $cdc_img,
		);
	}
} else {
	foreach ( $cdc_defaults as $cdc_item ) {
		$cdc_path = CDC_DIR . '/assets/' . $cdc_item['featured'];
		$cdc_size = file_exists( $cdc_path ) ? getimagesize( $cdc_path ) : false;
		$cdc_items[] = array(
			'title'    => $cdc_item['title'],
			'texto'    => $cdc_item['texto'],
			'tag'      => $cdc_item['tag'],
			'destaque' => '1' === $cdc_item['destaque'],
			'img'      => sprintf(
				'<img class="diferenciais__img" src="%1$s" alt="%2$s"%3$s loading="lazy" decoding="async">',
				esc_url( cdc_asset( $cdc_item['featured'] ) ),
				esc_attr( $cdc_item['alt'] ),
				$cdc_size ? sprintf( ' width="%d" height="%d"', (int) $cdc_size[0], (int) $cdc_size[1] ) : ''
			),
		);
	}
}
?>
<section class="diferenciais" id="diferenciais" data-figma-node="7057:330" aria-labelledby="diferenciais-titulo">
	<div class="container diferenciais__inner">
		<header class="diferenciais__head" data-figma-node="7057:331">
			<h2 class="diferenciais__title" id="diferenciais-titulo" data-figma-node="7057:332"><?php echo esc_html( cdc_mod( 'cdc_dif_titulo' ) ); ?></h2>
			<p class="diferenciais__lead" data-figma-node="7057:333"><?php echo cdc_rich( cdc_mod( 'cdc_dif_texto' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em cdc_rich(). ?></p>
		</header>

		<?php if ( $cdc_items ) : ?>
			<ul class="diferenciais__list" role="list" data-figma-node="7057:334">
				<?php foreach ( $cdc_items as $cdc_i => $cdc_item ) : ?>
					<li class="diferenciais__card<?php echo $cdc_item['destaque'] ? ' diferenciais__card--destaque' : ''; ?>"<?php echo $cdc_node( $cdc_i, 'card' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<div class="diferenciais__body"<?php echo $cdc_node( $cdc_i, 'body' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
							<h3 class="diferenciais__card-title"<?php echo $cdc_node( $cdc_i, 'title' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $cdc_item['title'] ); ?></h3>
							<?php if ( '' !== $cdc_item['texto'] ) : ?>
								<p class="diferenciais__card-text"<?php echo $cdc_node( $cdc_i, 'text' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo cdc_rich( $cdc_item['texto'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em cdc_rich(). ?></p>
							<?php endif; ?>
						</div>
						<?php if ( $cdc_item['img'] ) : ?>
							<figure class="diferenciais__media"<?php echo $cdc_node( $cdc_i, 'media' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
								<?php echo $cdc_item['img']; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_get_attachment_image() / sprintf com esc_url + esc_attr. ?>
								<?php if ( '' !== $cdc_item['tag'] ) : ?>
									<p class="diferenciais__tag"<?php echo $cdc_node( $cdc_i, 'tag' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
										<img class="diferenciais__tag-icon" src="<?php echo esc_url( cdc_asset( 'images/05-diferenciais-icon-sorriso.svg' ) ); ?>" alt="" width="16" height="16"<?php echo $cdc_node( $cdc_i, 'tag_icon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
										<span class="diferenciais__tag-text"<?php echo $cdc_node( $cdc_i, 'tag_text' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $cdc_item['tag'] ); ?></span>
									</p>
								<?php endif; ?>
							</figure>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
