<?php
/**
 * Seção 03 — Cardápio "Nossos cheesecakes" (Figma 7057:175).
 * Espelha src/sections/03-cardapio.html; cards vêm do CPT cdc_cheesecake (ver inc/sections/03-cardapio.php).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

$cdc_items    = cdc_cardapio_items();
$cdc_total    = count( $cdc_items );
$cdc_nodes    = cdc_cardapio_figma_nodes();
$cdc_pedir    = cdc_mod( 'cdc_cardapio_botao_pedir' );
$cdc_cta_link = cdc_mod( 'cdc_cardapio_botao_link' );
$cdc_cta_link = cdc_cardapio_href( '' !== $cdc_cta_link ? $cdc_cta_link : cdc_mod( 'cdc_contato_cardapio' ) );
?>
<section class="cardapio" id="cardapio" data-figma-node="7057:175" aria-labelledby="cardapio-titulo">
	<div class="container cardapio__inner">
		<header class="cardapio__head" data-figma-node="7057:176">
			<h2 class="cardapio__title" id="cardapio-titulo" data-figma-node="7057:177"><?php echo esc_html( cdc_mod( 'cdc_cardapio_titulo' ) ); ?></h2>
			<p class="cardapio__lead" data-figma-node="7057:178"><?php echo cdc_rich( cdc_mod( 'cdc_cardapio_texto' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- cdc_rich() escapa. ?></p>
		</header>

		<div class="cardapio__body" data-figma-node="7057:179" data-cardapio-carousel>
			<?php if ( $cdc_items ) : ?>
			<ul class="cardapio__grid" id="cardapio-lista" role="list" data-figma-node="7057:180" data-cardapio-track>
				<?php
				foreach ( $cdc_items as $cdc_i => $cdc_item ) :
					$cdc_n  = isset( $cdc_nodes[ $cdc_i ] ) ? $cdc_nodes[ $cdc_i ] : array();
					$cdc_bg = '' !== $cdc_item['cor'] ? $cdc_item['cor'] : 'var(--color-red-700)';
					?>
				<li class="cardapio__card"<?php echo cdc_cardapio_node_attr( $cdc_n, 'card' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
					<div class="cardapio__media" style="--cardapio-bg: <?php echo esc_attr( $cdc_bg ); ?>"<?php echo cdc_cardapio_node_attr( $cdc_n, 'media' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<div class="cardapio__photo"<?php echo cdc_cardapio_node_attr( $cdc_n, 'photo' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
							<img class="cardapio__img cardapio__img--recorte" src="<?php echo esc_url( $cdc_item['image'] ); ?>" alt="<?php echo esc_attr( $cdc_item['alt'] ); ?>" width="<?php echo (int) $cdc_item['image_w']; ?>" height="<?php echo (int) $cdc_item['image_h']; ?>" loading="lazy" decoding="async">
						</div>
						<?php if ( '' !== $cdc_item['tag'] ) : ?>
						<p class="cardapio__tag"<?php echo cdc_cardapio_node_attr( $cdc_n, 'tag' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
							<img class="cardapio__tag-icon" src="<?php echo esc_url( cdc_asset( 'images/03-cardapio-icon-mais-pedido.svg' ) ); ?>" alt="" width="16" height="16"<?php echo cdc_cardapio_node_attr( $cdc_n, 'tag_icon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
							<span class="cardapio__tag-text"<?php echo cdc_cardapio_node_attr( $cdc_n, 'tag_text' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $cdc_item['tag'] ); ?></span>
						</p>
						<?php endif; ?>
					</div>
					<div class="cardapio__content"<?php echo cdc_cardapio_node_attr( $cdc_n, 'content' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<div class="cardapio__info"<?php echo cdc_cardapio_node_attr( $cdc_n, 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
							<h3 class="cardapio__name"<?php echo cdc_cardapio_node_attr( $cdc_n, 'name' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $cdc_item['title'] ); ?></h3>
							<?php if ( '' !== $cdc_item['descricao'] ) : ?>
							<p class="cardapio__desc"<?php echo cdc_cardapio_node_attr( $cdc_n, 'desc' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $cdc_item['descricao'] ); ?></p>
							<?php endif; ?>
						</div>
						<?php if ( $cdc_item['precos'] ) : ?>
						<ul class="cardapio__prices" role="list"<?php echo cdc_cardapio_node_attr( $cdc_n, 'prices' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
							<?php foreach ( $cdc_item['precos'] as $cdc_r => $cdc_opcao ) : ?>
							<li class="cardapio__row"<?php echo cdc_cardapio_node_attr( $cdc_n, 'rows', $cdc_r, 0 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
								<p class="cardapio__option"<?php echo cdc_cardapio_node_attr( $cdc_n, 'rows', $cdc_r, 1 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
									<span class="cardapio__price"<?php echo cdc_cardapio_node_attr( $cdc_n, 'rows', $cdc_r, 2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $cdc_opcao['preco'] ); ?></span>
									<?php if ( '' !== $cdc_opcao['tamanho'] ) : ?>
									<span class="cardapio__size"<?php echo cdc_cardapio_node_attr( $cdc_n, 'rows', $cdc_r, 3 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $cdc_opcao['tamanho'] ); ?></span>
									<?php endif; ?>
								</p>
								<a class="btn <?php echo 0 === $cdc_r ? 'btn--primary' : 'btn--secondary'; ?> cardapio__btn" href="<?php echo esc_url( $cdc_opcao['link'] ? $cdc_opcao['link'] : $cdc_item['link'] ); ?>"<?php echo cdc_target_attr( $cdc_opcao['link'] ? $cdc_opcao['link'] : $cdc_item['link'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-label="<?php echo esc_attr( trim( $cdc_pedir . ' ' . $cdc_item['title'] . ( '' !== $cdc_opcao['tamanho'] ? ' — ' . $cdc_opcao['tamanho'] : '' ) ) ); ?>"<?php echo cdc_cardapio_node_attr( $cdc_n, 'rows', $cdc_r, 4 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $cdc_pedir ); ?></a>
							</li>
							<?php endforeach; ?>
						</ul>
						<?php endif; ?>
					</div>
				</li>
				<?php endforeach; ?>
			</ul>

			<?php if ( $cdc_total > 1 ) : ?>
			<?php /* Carrossel de 1 sabor por vez (≤767px). Sem JS fica [hidden] e o trilho continua rolável; o CSS esconde tudo em ≥768px. */ ?>
			<div class="cardapio__controls" role="group" aria-label="Navegar pelos sabores" data-cardapio-controls hidden>
				<div class="cardapio__dots" data-cardapio-dots>
					<?php foreach ( $cdc_items as $cdc_i => $cdc_item ) : ?>
					<button class="cardapio__dot" type="button" aria-controls="cardapio-lista" aria-label="<?php echo esc_attr( sprintf( '%1$s, %2$d de %3$d', $cdc_item['title'], $cdc_i + 1, $cdc_total ) ); ?>"<?php echo 0 === $cdc_i ? ' aria-current="true"' : ''; ?> data-cardapio-dot></button>
					<?php endforeach; ?>
				</div>
				<div class="cardapio__arrows">
					<button class="cardapio__nav cardapio__nav--prev" type="button" aria-controls="cardapio-lista" aria-label="Sabor anterior" aria-disabled="true" data-cardapio-prev>
						<svg viewBox="0 0 20 20" width="20" height="20" fill="none" aria-hidden="true" focusable="false"><path d="M12.5 15 7.5 10l5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</button>
					<button class="cardapio__nav cardapio__nav--next" type="button" aria-controls="cardapio-lista" aria-label="Próximo sabor" aria-disabled="false" data-cardapio-next>
						<svg viewBox="0 0 20 20" width="20" height="20" fill="none" aria-hidden="true" focusable="false"><path d="m7.5 15 5-5-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</button>
				</div>
				<p class="sr-only" aria-live="polite" aria-atomic="true" data-cardapio-status></p>
			</div>
			<?php endif; ?>
			<?php endif; ?>

			<a class="btn btn--primary" href="<?php echo esc_url( $cdc_cta_link ); ?>"<?php echo cdc_target_attr( $cdc_cta_link ); // phpcs:ignore WordPress.Security.EscapeOutput ?> data-figma-node="7073:393"><?php echo esc_html( cdc_mod( 'cdc_cardapio_botao' ) ); ?></a>
		</div>
	</div>
</section>
