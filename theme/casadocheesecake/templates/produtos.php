<?php
/**
 * Template Name: Produtos
 *
 * Página Produtos. Espelha cardapio.html (src/paginas/cardapio): título e texto da
 * 1ª categoria, banner do desconto da home, uma seção por categoria (o card e o
 * carrossel da seção "Nossos cheesecakes" da home) e a seção Fatias.
 * Dados e regras: inc/pages/10-produtos.php.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cdc_pagina = cdc_produtos_pagina();
$cdc_intro  = $cdc_pagina['intro'];
$cdc_pedir  = cdc_mod( 'cdc_cardapio_botao_pedir' );
$cdc_h1     = $cdc_intro ? $cdc_intro->name : get_the_title();
$cdc_lead   = $cdc_intro ? $cdc_intro->description : '';
?>
<section class="cardapio cardapio--pagina cardapio--intro" id="produtos" aria-labelledby="produtos-titulo">
	<div class="container cardapio__inner">
		<header class="cardapio__head">
			<h1 class="cardapio__title" id="produtos-titulo"><?php echo esc_html( $cdc_h1 ); ?></h1>
			<?php if ( '' !== trim( $cdc_lead ) ) : ?>
			<p class="cardapio__lead"><?php echo cdc_rich( $cdc_lead ); // phpcs:ignore WordPress.Security.EscapeOutput -- cdc_rich() escapa. ?></p>
			<?php endif; ?>
		</header>
	</div>
</section>

<?php
// Banner amarelo do desconto (seção 02 da home) com a fatia da frente desta página.
if ( cdc_opt( 'produtos', 'banner_mostrar' ) ) {
	$cdc_banner_img  = cdc_opt_image( 'produtos', 'banner_fatia' );
	$cdc_fatia_fundo = array(
		'url' => $cdc_banner_img['url'],
		'alt' => cdc_opt( 'produtos', 'banner_fatia_alt' ),
	);
	require get_template_directory() . '/template-parts/sections/02-primeira-fatia.php';
}

foreach ( $cdc_pagina['secoes'] as $cdc_secao ) :
	$cdc_sid   = $cdc_secao['id'];
	$cdc_total = count( $cdc_secao['cards'] );
	?>
<section class="cardapio cardapio--pagina<?php echo $cdc_secao['sem_cabecalho'] ? ' cardapio--sem-cabecalho' : ''; ?>" id="<?php echo esc_attr( $cdc_sid ); ?>" aria-labelledby="<?php echo esc_attr( $cdc_secao['rotulo'] ); ?>">
	<div class="container cardapio__inner">
		<?php if ( ! $cdc_secao['sem_cabecalho'] ) : ?>
		<header class="cardapio__head">
			<h2 class="cardapio__title" id="<?php echo esc_attr( $cdc_sid . '-titulo' ); ?>"><?php echo esc_html( $cdc_secao['titulo'] ); ?></h2>
			<?php if ( '' !== trim( $cdc_secao['lead'] ) ) : ?>
			<p class="cardapio__lead"><?php echo cdc_rich( $cdc_secao['lead'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- cdc_rich() escapa. ?></p>
			<?php endif; ?>
		</header>
		<?php endif; ?>

		<div class="cardapio__body" data-cardapio-carousel>
			<ul class="cardapio__grid" id="<?php echo esc_attr( $cdc_sid . '-lista' ); ?>" role="list" data-cardapio-track>
				<?php foreach ( $cdc_secao['cards'] as $cdc_card ) : ?>
				<li class="cardapio__card">
					<div class="cardapio__media" style="--cardapio-bg: <?php echo esc_attr( $cdc_card['cor'] ); ?>">
						<?php if ( $cdc_card['img']['url'] ) : ?>
						<div class="cardapio__photo">
							<img class="cardapio__img cardapio__img--recorte" src="<?php echo esc_url( $cdc_card['img']['url'] ); ?>" alt="<?php echo esc_attr( $cdc_card['img']['alt'] ); ?>" width="<?php echo (int) $cdc_card['img']['w']; ?>" height="<?php echo (int) $cdc_card['img']['h']; ?>" loading="lazy" decoding="async">
						</div>
						<?php endif; ?>
						<?php if ( '' !== $cdc_card['tag'] ) : ?>
						<p class="cardapio__tag">
							<img class="cardapio__tag-icon" src="<?php echo esc_url( cdc_asset( 'images/03-cardapio-icon-mais-pedido.svg' ) ); ?>" alt="" width="16" height="16">
							<span class="cardapio__tag-text"><?php echo esc_html( $cdc_card['tag'] ); ?></span>
						</p>
						<?php endif; ?>
					</div>
					<div class="cardapio__content">
						<div class="cardapio__info">
							<h3 class="cardapio__name"><?php echo esc_html( $cdc_card['nome'] ); ?></h3>
							<?php if ( '' !== $cdc_card['desc'] ) : ?>
							<p class="cardapio__desc"><?php echo esc_html( $cdc_card['desc'] ); ?></p>
							<?php endif; ?>
						</div>
						<ul class="cardapio__prices" role="list">
							<?php
							foreach ( $cdc_card['linhas'] as $cdc_r => $cdc_linha ) :
								$cdc_link = '' !== $cdc_linha['link'] ? $cdc_linha['link'] : $cdc_card['link'];
								// aria-label com espaço comum (o nbsp antes do "·" é só para a quebra visual).
								$cdc_tam = str_replace( "\u{00A0}", ' ', $cdc_linha['tamanho'] );
								?>
							<li class="cardapio__row">
								<p class="cardapio__option">
									<span class="cardapio__price"><?php echo esc_html( $cdc_linha['preco'] ); ?></span>
									<?php if ( '' !== $cdc_linha['tamanho'] ) : ?>
									<span class="cardapio__size"><?php echo esc_html( $cdc_linha['tamanho'] ); ?></span>
									<?php endif; ?>
								</p>
								<a class="btn <?php echo 0 === $cdc_r ? 'btn--primary' : 'btn--secondary'; ?> cardapio__btn" href="<?php echo esc_url( $cdc_link ); ?>"<?php echo cdc_target_attr( $cdc_link ); // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-label="<?php echo esc_attr( trim( $cdc_pedir . ' ' . $cdc_card['nome'] . ( '' !== $cdc_tam ? ' — ' . $cdc_tam : '' ) ) ); ?>"><?php echo esc_html( $cdc_pedir ); ?></a>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</li>
				<?php endforeach; ?>
			</ul>

			<?php /* Carrossel de 1 produto por vez no mobile (mesmo JS da home). Sem JS fica [hidden]. */ ?>
			<div class="cardapio__controls" role="group" aria-label="Navegar pelos produtos" data-cardapio-controls hidden>
				<div class="cardapio__dots" data-cardapio-dots>
					<?php foreach ( $cdc_secao['cards'] as $cdc_i => $cdc_card ) : ?>
					<button class="cardapio__dot" type="button" aria-controls="<?php echo esc_attr( $cdc_sid . '-lista' ); ?>" aria-label="<?php echo esc_attr( sprintf( '%1$s, %2$d de %3$d', $cdc_card['nome'], $cdc_i + 1, $cdc_total ) ); ?>"<?php echo 0 === $cdc_i ? ' aria-current="true"' : ''; ?> data-cardapio-dot></button>
					<?php endforeach; ?>
				</div>
				<div class="cardapio__arrows">
					<button class="cardapio__nav cardapio__nav--prev" type="button" aria-controls="<?php echo esc_attr( $cdc_sid . '-lista' ); ?>" aria-label="Produto anterior" aria-disabled="true" data-cardapio-prev>
						<svg viewBox="0 0 20 20" width="20" height="20" fill="none" aria-hidden="true" focusable="false"><path d="M12.5 15 7.5 10l5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</button>
					<button class="cardapio__nav cardapio__nav--next" type="button" aria-controls="<?php echo esc_attr( $cdc_sid . '-lista' ); ?>" aria-label="Próximo produto" aria-disabled="false" data-cardapio-next>
						<svg viewBox="0 0 20 20" width="20" height="20" fill="none" aria-hidden="true" focusable="false"><path d="m7.5 15 5-5-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</button>
				</div>
				<p class="sr-only" aria-live="polite" aria-atomic="true" data-cardapio-status></p>
			</div>
		</div>
	</div>
</section>
<?php
endforeach;

get_footer();
