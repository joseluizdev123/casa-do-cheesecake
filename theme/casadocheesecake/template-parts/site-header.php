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
	<div class="site-header__bar">
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
	</div>
	</div>
</header>
