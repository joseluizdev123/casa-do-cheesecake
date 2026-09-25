<?php
/**
 * Header (Figma 7073:253 default / 7073:254 scroll) — espelha src/partials/header.html.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

$cdc_pedido = cdc_anchor_or_url( (string) cdc_mod( 'cdc_contato_cardapio' ) );
$cdc_blog   = cdc_anchor_or_url( (string) cdc_mod( 'cdc_contato_blog' ) );
$cdc_left   = cdc_menu_links(
	'header-left',
	array(
		array( 'label' => 'Nosso cheesecake', 'url' => cdc_anchor( '#cardapio' ) ),
		array( 'label' => 'Sabores', 'url' => cdc_anchor( '#sabores' ) ),
		array( 'label' => 'Pedir agora', 'url' => $cdc_pedido, 'target' => cdc_is_external( $cdc_pedido ) ? '_blank' : '' ),
	)
);
$cdc_right  = cdc_menu_links(
	'header-right',
	array(
		array( 'label' => 'Sobre nós', 'url' => cdc_anchor( '#sobre' ) ),
		array( 'label' => 'Blog', 'url' => $cdc_blog, 'target' => cdc_is_external( $cdc_blog ) ? '_blank' : '' ),
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
				<?php foreach ( array_merge( $cdc_left, $cdc_right ) as $cdc_link ) : ?>
					<?php
					if ( $cdc_link['url'] === $cdc_pedido ) {
						continue; // "Pedir agora" vira o botão principal no pé do menu.
					}
					$cdc_sabores = false !== strpos( $cdc_link['url'], '#sabores' ) ? cdc_posts( 'cdc_cheesecake' ) : array();
					?>
					<li class="mobile-nav__item<?php echo $cdc_sabores ? ' mobile-nav__item--sabores' : ''; ?>">
						<a class="mobile-nav__link" href="<?php echo esc_url( $cdc_link['url'] ); ?>"<?php echo $cdc_link['target'] ? ' target="' . esc_attr( $cdc_link['target'] ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $cdc_link['label'] ); ?></a>
						<?php if ( $cdc_sabores ) : ?>
							<ul class="mobile-nav__chips" role="list" aria-label="<?php esc_attr_e( 'Sabores', 'casadocheesecake' ); ?>">
								<?php foreach ( $cdc_sabores as $cdc_sabor ) : ?>
									<?php $cdc_cor = sanitize_hex_color( (string) cdc_meta( $cdc_sabor->ID, 'cor_fundo' ) ); ?>
									<li><a class="mobile-nav__chip" href="<?php echo esc_url( $cdc_link['url'] ); ?>" data-sabor="<?php echo esc_attr( sanitize_title( $cdc_sabor->post_title ) ); ?>"<?php echo $cdc_cor ? ' style="--chip: ' . esc_attr( $cdc_cor ) . '"' : ''; ?>><?php echo esc_html( get_the_title( $cdc_sabor ) ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<div class="mobile-nav__actions">
			<a class="btn btn--primary mobile-nav__btn" href="<?php echo esc_url( $cdc_pedido ); ?>"<?php echo cdc_target_attr( $cdc_pedido ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php esc_html_e( 'Pedir agora', 'casadocheesecake' ); ?></a>
			<a class="btn btn--secondary mobile-nav__btn" href="<?php echo esc_url( cdc_mod( 'cdc_contato_whatsapp' ) ); ?>" target="_blank" rel="noopener"><span class="mobile-nav__wa" aria-hidden="true"></span><?php esc_html_e( 'Falar no WhatsApp', 'casadocheesecake' ); ?></a>
		</div>
		<?php
		$cdc_tel     = (string) cdc_mod( 'cdc_contato_telefone' );
		$cdc_tel_num = preg_replace( '/\D+/', '', $cdc_tel );
		?>
		<p class="mobile-nav__contact">
			<?php if ( $cdc_tel_num ) : ?>
				<a href="<?php echo esc_url( 'tel:+55' . $cdc_tel_num ); ?>"><?php echo esc_html( $cdc_tel ); ?></a>
			<?php endif; ?>
			<a href="<?php echo esc_url( cdc_mod( 'cdc_contato_instagram' ) ); ?>" target="_blank" rel="noopener">Instagram</a>
			<a href="<?php echo esc_url( cdc_mod( 'cdc_contato_facebook' ) ); ?>" target="_blank" rel="noopener">Facebook</a>
		</p>
	</div>
</header>
<a class="whatsapp-fab" href="<?php echo esc_url( cdc_mod( 'cdc_contato_whatsapp' ) ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Falar no WhatsApp', 'casadocheesecake' ); ?>"></a>
