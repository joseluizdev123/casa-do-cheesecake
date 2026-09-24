<?php
/**
 * Seção 07 — Depoimentos (Figma 7057:361) — espelha src/sections/07-depoimentos.html.
 *
 * Cards vêm do CPT cdc_depoimento (fallback: depoimentos do Figma). Os dots são
 * renderizados pelo nº real de páginas (3 cards por página) e o JS
 * (assets/js/sections/07-depoimentos.js) os recria em runtime.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

$cdc_items  = cdc_depoimentos_items();
$cdc_nota   = cdc_depoimentos_stars( cdc_mod( 'cdc_depo_nota' ) );
$cdc_star   = cdc_asset( 'images/07-depoimentos-star.svg' );
$cdc_google = cdc_asset( 'images/07-depoimentos-google.svg' );
$cdc_pages  = max( 1, (int) ceil( count( $cdc_items ) / 3 ) );

// Nodes Figma dos 3 cards do layout (marcação do diff numérico; cards extras ficam sem).
$cdc_nodes = array(
	array( 'card' => '7057:371', 'body' => '7057:372', 'head' => '7057:373', 'stars' => '7057:374', 'star' => array( '7057:375', '7057:377', '7057:379', '7057:381', '7057:383' ), 'google' => '7057:386', 'quote' => '7057:401', 'foot' => '7057:402', 'author' => '7057:403', 'name' => '7057:404', 'source' => '7057:405', 'date' => '7057:406' ),
	array( 'card' => '7057:407', 'body' => '7057:408', 'head' => '7057:409', 'stars' => '7057:410', 'star' => array( '7057:411', '7057:413', '7057:415', '7057:417', '7057:419' ), 'google' => '7057:422', 'quote' => '7057:437', 'foot' => '7057:438', 'author' => '7057:439', 'name' => '7057:440', 'source' => '7057:441', 'date' => '7057:442' ),
	array( 'card' => '7057:443', 'body' => '7057:444', 'head' => '7057:445', 'stars' => '7057:446', 'star' => array( '7057:447', '7057:449', '7057:451', '7057:453', '7057:455' ), 'google' => '7057:458', 'quote' => '7057:473', 'foot' => '7057:474', 'author' => '7057:475', 'name' => '7057:476', 'source' => '7057:477', 'date' => '7057:478' ),
);
$cdc_dot_nodes = array( '7057:486', '7057:487', '7057:488', '7057:489' );

/**
 * Atributo data-figma-node (vazio quando o node não existe no layout).
 *
 * @param string|null $id Node ID.
 * @return string
 */
$cdc_node = function ( $id ) {
	return $id ? ' data-figma-node="' . esc_attr( $id ) . '"' : '';
};
?>
<section class="depoimentos" id="depoimentos" data-figma-node="7057:361" aria-labelledby="depoimentos-titulo">
	<div class="container depoimentos__container">
		<header class="depoimentos__header" data-figma-node="7057:362">
			<h2 class="depoimentos__title" id="depoimentos-titulo" data-figma-node="7057:363"><?php echo esc_html( cdc_mod( 'cdc_depo_titulo' ) ); ?></h2>
			<div class="depoimentos__badge" data-figma-node="7057:364">
				<p class="depoimentos__rating" data-figma-node="7057:365">
					<span class="depoimentos__rating-stars" aria-hidden="true" data-figma-node="7057:366"><?php echo esc_html( str_repeat( '★', $cdc_nota ) ); ?></span>
					<span class="sr-only"><?php echo esc_html( sprintf( 'Nota %d de 5,', $cdc_nota ) ); ?></span>
					<span class="depoimentos__rating-count" data-figma-node="7057:367"><?php echo esc_html( cdc_mod( 'cdc_depo_contagem' ) ); ?></span>
				</p>
				<p class="depoimentos__badge-label" data-figma-node="7057:368"><?php echo esc_html( cdc_mod( 'cdc_depo_fonte' ) ); ?></p>
			</div>
		</header>

		<div class="depoimentos__carousel" data-figma-node="7057:369" data-depoimentos-carousel role="region" aria-roledescription="carrossel" aria-label="<?php esc_attr_e( 'Depoimentos de clientes', 'casadocheesecake' ); ?>">
			<div class="depoimentos__viewport" data-figma-node="7057:370">
				<ul class="depoimentos__track" id="depoimentos-lista" role="list" data-carousel-track>
					<?php
					foreach ( $cdc_items as $cdc_i => $cdc_item ) :
						$cdc_n     = isset( $cdc_nodes[ $cdc_i ] ) ? $cdc_nodes[ $cdc_i ] : array();
						$cdc_get   = function ( $key ) use ( $cdc_n ) {
							return isset( $cdc_n[ $key ] ) ? $cdc_n[ $key ] : null;
						};
						$cdc_stars = cdc_depoimentos_stars( $cdc_item['estrelas'] );
						?>
						<li class="depoimentos__card"<?php echo $cdc_node( $cdc_get( 'card' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
							<div class="depoimentos__card-body"<?php echo $cdc_node( $cdc_get( 'body' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
								<div class="depoimentos__card-head"<?php echo $cdc_node( $cdc_get( 'head' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
									<span class="depoimentos__stars" role="img" aria-label="<?php echo esc_attr( sprintf( '%d de 5 estrelas', $cdc_stars ) ); ?>"<?php echo $cdc_node( $cdc_get( 'stars' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
										<?php for ( $cdc_s = 0; $cdc_s < $cdc_stars; $cdc_s++ ) : ?>
											<img class="depoimentos__star" src="<?php echo esc_url( $cdc_star ); ?>" alt="" width="16" height="16"<?php echo $cdc_node( isset( $cdc_n['star'][ $cdc_s ] ) ? $cdc_n['star'][ $cdc_s ] : null ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
										<?php endfor; ?>
									</span>
									<span class="depoimentos__google" aria-hidden="true"<?php echo $cdc_node( $cdc_get( 'google' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
										<span class="depoimentos__google-mask">
											<span class="depoimentos__google-art"><img src="<?php echo esc_url( $cdc_google ); ?>" alt="" width="26" height="27"></span>
										</span>
									</span>
								</div>
								<blockquote class="depoimentos__quote"<?php echo $cdc_node( $cdc_get( 'quote' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
									<p><?php echo wp_kses_post( cdc_rich( $cdc_item['texto'] ) ); ?></p>
								</blockquote>
							</div>
							<footer class="depoimentos__card-foot"<?php echo $cdc_node( $cdc_get( 'foot' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
								<div class="depoimentos__author"<?php echo $cdc_node( $cdc_get( 'author' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
									<p class="depoimentos__author-name"<?php echo $cdc_node( $cdc_get( 'name' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $cdc_item['nome'] ); ?></p>
									<?php if ( '' !== $cdc_item['fonte'] ) : ?>
										<p class="depoimentos__source"<?php echo $cdc_node( $cdc_get( 'source' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $cdc_item['fonte'] ); ?></p>
									<?php endif; ?>
								</div>
								<?php if ( '' !== $cdc_item['data'] ) : ?>
									<p class="depoimentos__date"<?php echo $cdc_node( $cdc_get( 'date' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $cdc_item['data'] ); ?></p>
								<?php endif; ?>
							</footer>
						</li>
					<?php endforeach; ?>
				</ul>
				<button class="depoimentos__nav depoimentos__nav--prev" type="button" aria-controls="depoimentos-lista" aria-label="<?php esc_attr_e( 'Depoimentos anteriores', 'casadocheesecake' ); ?>" data-carousel-prev data-figma-node="7057:482">
					<img src="<?php echo esc_url( cdc_asset( 'images/07-depoimentos-chevron-left.svg' ) ); ?>" alt="" width="20" height="20" data-figma-node="7057:483">
				</button>
				<button class="depoimentos__nav depoimentos__nav--next" type="button" aria-controls="depoimentos-lista" aria-label="<?php esc_attr_e( 'Próximos depoimentos', 'casadocheesecake' ); ?>" data-carousel-next data-figma-node="7057:479">
					<img src="<?php echo esc_url( cdc_asset( 'images/07-depoimentos-chevron-right.svg' ) ); ?>" alt="" width="20" height="20" data-figma-node="7057:480">
				</button>
			</div>
			<div class="depoimentos__dots" data-carousel-dots data-figma-node="7057:485">
				<?php for ( $cdc_p = 0; $cdc_p < $cdc_pages; $cdc_p++ ) : ?>
					<span class="depoimentos__dot<?php echo 0 === $cdc_p ? ' is-active' : ''; ?>" aria-hidden="true"<?php echo $cdc_node( isset( $cdc_dot_nodes[ $cdc_p ] ) ? $cdc_dot_nodes[ $cdc_p ] : null ); // phpcs:ignore WordPress.Security.EscapeOutput ?>></span>
				<?php endfor; ?>
			</div>
		</div>
	</div>
</section>
