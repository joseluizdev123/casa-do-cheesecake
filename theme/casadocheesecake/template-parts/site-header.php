<?php
/**
 * Header (Figma 7073:253 default / 7073:254 scroll) — espelha src/partials/header.html.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

$cdc_left  = cdc_menu_links(
	'header-left',
	array(
		array( 'label' => 'Nosso cheesecake', 'url' => cdc_anchor( '#cardapio' ) ),
		array( 'label' => 'Sabores', 'url' => cdc_anchor( '#sabores' ) ),
		array( 'label' => 'Pedir agora', 'url' => cdc_anchor( '#pedir' ) ),
	)
);
$cdc_right = cdc_menu_links(
	'header-right',
	array(
		array( 'label' => 'Sobre nós', 'url' => cdc_anchor( '#sobre' ) ),
		array( 'label' => 'Blog', 'url' => cdc_anchor( '#blog' ) ),
		array( 'label' => 'Contato', 'url' => cdc_anchor( '#contato' ) ),
	)
);
?>
<header class="site-header" data-site-header>
	<div class="site-header__bar" data-figma-node="7073:253">
	<div class="site-header__inner">
		<nav class="site-header__nav" aria-label="<?php esc_attr_e( 'Principal', 'casadocheesecake' ); ?>">
			<ul class="site-header__menu site-header__menu--left" role="list">
				<?php foreach ( $cdc_left as $cdc_link ) : ?>
					<li><a class="btn-link" href="<?php echo esc_url( $cdc_link['url'] ); ?>"<?php echo $cdc_link['target'] ? ' target="' . esc_attr( $cdc_link['target'] ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $cdc_link['label'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) . ' — página inicial' ); ?>">
			<img src="<?php echo esc_url( cdc_image( 'cdc_global_logo' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="584" height="590" fetchpriority="high">
		</a>
		<nav class="site-header__nav" aria-label="<?php esc_attr_e( 'Institucional', 'casadocheesecake' ); ?>">
			<ul class="site-header__menu site-header__menu--right" role="list">
				<?php foreach ( $cdc_right as $cdc_link ) : ?>
					<li><a class="btn-link" href="<?php echo esc_url( $cdc_link['url'] ); ?>"<?php echo $cdc_link['target'] ? ' target="' . esc_attr( $cdc_link['target'] ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $cdc_link['label'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<button class="site-header__toggle" type="button" aria-expanded="false" aria-controls="menu-mobile" data-menu-toggle>
			<span class="site-header__toggle-bars" aria-hidden="true"></span>
			<span class="sr-only"><?php esc_html_e( 'Abrir menu', 'casadocheesecake' ); ?></span>
		</button>
	</div>
	</div>
	<div class="site-header__drawer" id="menu-mobile" hidden data-menu-drawer>
		<nav class="mobile-nav" aria-label="<?php esc_attr_e( 'Menu principal', 'casadocheesecake' ); ?>">
			<ul class="mobile-nav__list" role="list">
				<li class="mobile-nav__item is-current"><a class="mobile-nav__link" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-current="page"><?php esc_html_e( 'Página inicial', 'casadocheesecake' ); ?></a></li>
				<?php foreach ( array_merge( $cdc_left, $cdc_right ) as $cdc_link ) : ?>
					<?php $cdc_is_sabores = false !== strpos( $cdc_link['url'], '#sabores' ); ?>
					<li class="mobile-nav__item">
						<a class="mobile-nav__link" href="<?php echo esc_url( $cdc_link['url'] ); ?>"<?php echo $cdc_link['target'] ? ' target="' . esc_attr( $cdc_link['target'] ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $cdc_link['label'] ); ?><?php if ( $cdc_is_sabores ) : ?><span class="mobile-nav__caret" aria-hidden="true"></span><?php endif; ?></a>
						<?php if ( $cdc_is_sabores ) : ?>
							<ul class="mobile-nav__sub" role="list">
								<?php foreach ( cdc_posts( 'cdc_cheesecake' ) as $cdc_sabor ) : ?>
									<li><a href="<?php echo esc_url( $cdc_link['url'] ); ?>"><?php echo esc_html( get_the_title( $cdc_sabor ) ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<a class="mobile-nav__cta" href="<?php echo esc_url( cdc_mod( 'cdc_contato_whatsapp' ) ); ?>" target="_blank" rel="noopener">
			<span class="mobile-nav__cta-icon" aria-hidden="true"></span>
			<span><?php esc_html_e( 'Fale conosco', 'casadocheesecake' ); ?></span>
		</a>
	</div>
</header>
<a class="whatsapp-fab" href="<?php echo esc_url( cdc_mod( 'cdc_contato_whatsapp' ) ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Falar no WhatsApp', 'casadocheesecake' ); ?>"></a>
