<?php
/**
 * Seção 08 — Entrega (Figma 7057:490).
 * Espelha src/sections/08-entrega.html; conteúdo vem do Customizer (inc/sections/08-entrega.php).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

// Slots fixos do layout: modificador do ícone (geometria do Figma), tamanho intrínseco e node IDs.
$cdc_entrega_slots = array(
	1 => array(
		'mod'   => 'local',
		'w'     => 19,
		'h'     => 26,
		'nodes' => array( '7057:494', '7057:1287', '7057:1349', '7057:1315', '7057:495', '7057:496' ),
	),
	2 => array(
		'mod'   => 'agenda',
		'w'     => 27,
		'h'     => 26,
		'nodes' => array( '7057:497', '7057:1279', '7057:1325', '7057:1316', '7057:498', '7057:499' ),
	),
	3 => array(
		'mod'   => 'gelado',
		'w'     => 32,
		'h'     => 24,
		'nodes' => array( '7057:500', '7057:1303', '7057:1329', '7057:1317', '7057:501', '7057:502' ),
	),
	4 => array(
		'mod'   => 'ifood',
		'w'     => 32,
		'h'     => 32,
		'nodes' => array( '7057:503', '7057:1295', '7057:1356', '7057:1318', '7057:504', '7057:505' ),
	),
);

$cdc_entrega_link = cdc_mod( 'cdc_entrega_botao_link' );
if ( '' === $cdc_entrega_link ) {
	$cdc_entrega_link = cdc_mod( 'cdc_contato_cardapio' );
}
?>
<section class="entrega" id="entrega" data-figma-node="7057:490" aria-labelledby="entrega-titulo">
	<div class="entrega__inner container">
		<div class="entrega__header" data-figma-node="7057:491">
			<h2 class="entrega__title u-display" id="entrega-titulo" data-figma-node="7057:492"><?php echo esc_html( cdc_mod( 'cdc_entrega_titulo' ) ); ?></h2>
		</div>
		<div class="entrega__body" data-figma-node="7057:1348">
			<ul class="entrega__list" role="list" data-figma-node="7057:493">
				<?php
				foreach ( $cdc_entrega_slots as $cdc_n => $cdc_slot ) :
					list( $cdc_li, $cdc_badge, $cdc_icon, $cdc_text, $cdc_title, $cdc_desc ) = $cdc_slot['nodes'];
					$cdc_icon_url = cdc_image( "cdc_entrega_item{$cdc_n}_icone" );
					?>
					<li class="entrega__item" data-figma-node="<?php echo esc_attr( $cdc_li ); ?>">
						<span class="entrega__badge" data-figma-node="<?php echo esc_attr( $cdc_badge ); ?>">
							<span class="entrega__icon entrega__icon--<?php echo esc_attr( $cdc_slot['mod'] ); ?>" data-figma-node="<?php echo esc_attr( $cdc_icon ); ?>">
								<?php if ( $cdc_icon_url ) : ?>
									<img src="<?php echo esc_url( $cdc_icon_url ); ?>" alt="" width="<?php echo (int) $cdc_slot['w']; ?>" height="<?php echo (int) $cdc_slot['h']; ?>" loading="lazy" decoding="async">
								<?php endif; ?>
							</span>
						</span>
						<div class="entrega__text" data-figma-node="<?php echo esc_attr( $cdc_text ); ?>">
							<h3 class="entrega__item-title" data-figma-node="<?php echo esc_attr( $cdc_title ); ?>"><?php echo esc_html( cdc_mod( "cdc_entrega_item{$cdc_n}_titulo" ) ); ?></h3>
							<p class="entrega__item-text" data-figma-node="<?php echo esc_attr( $cdc_desc ); ?>"><?php echo cdc_rich( cdc_mod( "cdc_entrega_item{$cdc_n}_texto" ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- cdc_rich() escapa. ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
			<a class="btn btn--primary" href="<?php echo esc_url( $cdc_entrega_link ); ?>" data-figma-node="7073:396"><?php echo esc_html( cdc_mod( 'cdc_entrega_botao_rotulo' ) ); ?></a>
		</div>
	</div>
</section>
