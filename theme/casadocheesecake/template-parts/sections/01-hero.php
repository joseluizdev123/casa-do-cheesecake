<?php
/**
 * Seção 01 — Hero (Figma 7057:81). Espelha src/sections/01-hero.html.
 *
 * Conteúdo: Customizer (painel "Hero (topo da página)") + links globais.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

$cdc_cardapio = (string) cdc_mod( 'cdc_contato_cardapio' );
if ( 0 === strpos( $cdc_cardapio, '#' ) ) {
	$cdc_cardapio = cdc_anchor( $cdc_cardapio );
}

$cdc_info = array(
	array(
		'node'  => array( '7057:127', '7057:128', '7057:129', '7057:136' ),
		'icon'  => 'images/01-hero-icon-delivery.svg',
		'w'     => 24,
		'h'     => 24,
		'label' => cdc_mod( 'cdc_hero_info_1' ),
	),
	array(
		'node'  => array( '7057:137', '7057:138', '7057:139', '7057:145' ),
		'icon'  => 'images/01-hero-icon-slices.svg',
		'w'     => 24,
		'h'     => 18,
		'label' => cdc_mod( 'cdc_hero_info_2' ),
	),
	array(
		'node'  => array( '7057:146', '7057:147', '7057:148', '7057:156' ),
		'icon'  => 'images/01-hero-icon-reviews.svg',
		'w'     => 24,
		'h'     => 24,
		'label' => cdc_mod( 'cdc_hero_info_3' ),
	),
);
?>
<section class="hero" id="inicio" data-figma-node="7057:81" aria-labelledby="hero-titulo">
	<div class="hero__inner" data-figma-node="7057:82">
		<div class="hero__head container" data-figma-node="7057:83">
			<h1 class="hero__title" id="hero-titulo" data-figma-node="7057:84"><?php echo esc_html( cdc_mod( 'cdc_hero_titulo' ) ); ?></h1>
		</div>

		<div class="hero__media" data-figma-node="7057:85">
			<div class="hero__bg" data-figma-node="7057:86">
				<img class="hero__bg-img" src="<?php echo esc_url( cdc_image( 'cdc_hero_imagem' ) ); ?>" width="2048" height="1152" alt="<?php echo esc_attr( cdc_mod( 'cdc_hero_imagem_alt' ) ); ?>" fetchpriority="high">
			</div>
			<div class="hero__cutout" data-figma-node="7057:87" aria-hidden="true">
				<img class="hero__cutout-img" src="<?php echo esc_url( cdc_image( 'cdc_hero_recorte' ) ); ?>" width="1440" height="576" alt="" fetchpriority="high">
			</div>

			<div class="hero__since" data-figma-node="7057:90">
				<img class="hero__laurel hero__laurel--left" data-figma-node="7057:91" src="<?php echo esc_url( cdc_asset( 'images/01-hero-laurel-left.svg' ) ); ?>" width="41" height="68" alt="">
				<p class="hero__since-badge" data-figma-node="7057:107">
					<span class="hero__since-label" data-figma-node="7057:108"><?php echo esc_html( cdc_mod( 'cdc_hero_desde_rotulo' ) ); ?></span>
					<span class="hero__since-year" data-figma-node="7057:109"><?php echo esc_html( cdc_mod( 'cdc_hero_desde_ano' ) ); ?></span>
				</p>
				<img class="hero__laurel hero__laurel--right" data-figma-node="7057:110" src="<?php echo esc_url( cdc_asset( 'images/01-hero-laurel-right.svg' ) ); ?>" width="41" height="68" alt="">
			</div>

			<ul class="hero__info" role="list" data-figma-node="7057:126">
				<?php foreach ( $cdc_info as $cdc_item ) : ?>
					<?php
					if ( '' === trim( (string) $cdc_item['label'] ) ) {
						continue;
					}
					?>
					<li class="hero__info-item" data-figma-node="<?php echo esc_attr( $cdc_item['node'][0] ); ?>">
						<span class="hero__info-badge" data-figma-node="<?php echo esc_attr( $cdc_item['node'][1] ); ?>"><span class="hero__info-icon" data-figma-node="<?php echo esc_attr( $cdc_item['node'][2] ); ?>"><img src="<?php echo esc_url( cdc_asset( $cdc_item['icon'] ) ); ?>" width="<?php echo (int) $cdc_item['w']; ?>" height="<?php echo (int) $cdc_item['h']; ?>" alt=""></span></span>
						<span class="hero__info-text" data-figma-node="<?php echo esc_attr( $cdc_item['node'][3] ); ?>"><?php echo esc_html( $cdc_item['label'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="hero__footer container" data-figma-node="7057:157">
			<div class="hero__actions" data-figma-node="7057:158">
				<a class="btn btn--primary" data-figma-node="7073:331" href="<?php echo esc_url( $cdc_cardapio ); ?>"><?php echo esc_html( cdc_mod( 'cdc_hero_botao_1' ) ); ?></a>
				<a class="btn btn--secondary" data-figma-node="7073:335" href="<?php echo esc_url( cdc_mod( 'cdc_contato_whatsapp' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( cdc_mod( 'cdc_hero_botao_2' ) ); ?></a>
			</div>
			<p class="hero__text" data-figma-node="7057:163"><?php echo wp_kses_post( cdc_rich( cdc_mod( 'cdc_hero_texto' ) ) ); ?></p>
		</div>
	</div>
</section>
