<?php
/**
 * Seção 04 · Sabores (Figma 7057:329) — espelha src/sections/04-sabores.html.
 *
 * Abas = posts de cdc_cheesecake (título + imagem_fatia + cor_fundo); a troca é feita por
 * assets/js/sections/04-sabores.js. Textos e link vêm do Customizer (inc/sections/04-sabores.php).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

$cdc_sabores = cdc_sabores_items();
if ( ! $cdc_sabores ) {
	return;
}

$cdc_sabores_primeiro = $cdc_sabores[0];
$cdc_sabores_escuro   = 'escuro' === $cdc_sabores_primeiro['tema'];
$cdc_sabores_btn      = 'btn-link' . ( $cdc_sabores_escuro ? ' btn-link--dark' : '' ) . ' sabores__aba';
$cdc_sabores_url      = (string) cdc_mod( 'cdc_sabores_link_url' );
if ( 0 === strpos( $cdc_sabores_url, '#' ) ) {
	$cdc_sabores_url = cdc_anchor( $cdc_sabores_url ); // âncora funciona também fora da home
}
$cdc_sabores_alt      = static function ( $titulo ) {
	$nome = function_exists( 'mb_strtolower' ) ? mb_strtolower( $titulo, 'UTF-8' ) : $titulo;
	/* translators: %s: nome do sabor */
	return sprintf( __( 'Fatia de cheesecake de %s', 'casadocheesecake' ), $nome );
};
?>
<section class="sabores<?php echo $cdc_sabores_escuro ? '' : ' sabores--claro'; ?>" id="sabores" data-figma-node="7057:329" data-sabores aria-labelledby="sabores-titulo"<?php echo 'var(--color-red-700)' === $cdc_sabores_primeiro['bg'] ? '' : ' style="--sabores-bg: ' . esc_attr( $cdc_sabores_primeiro['bg'] ) . '"'; ?>>
  <div class="sabores__selo" data-figma-node="I7057:329;7057:520">
    <span class="sabores__decoracao sabores__decoracao--esq" data-figma-node="I7057:329;7057:521" aria-hidden="true">
      <img class="sabores__decoracao-img sabores__decoracao-img--escuro" src="<?php echo esc_url( cdc_asset( 'images/04-sabores-decoration-left-cream.svg' ) ); ?>" alt="" width="41" height="68">
      <img class="sabores__decoracao-img sabores__decoracao-img--claro" src="<?php echo esc_url( cdc_asset( 'images/04-sabores-decoration-left-red.svg' ) ); ?>" alt="" width="41" height="68">
    </span>
    <p class="sabores__selo-texto" data-figma-node="I7057:329;7057:537">
      <span class="sabores__selo-rotulo" data-figma-node="I7057:329;7057:538"><?php echo esc_html( cdc_mod( 'cdc_sabores_selo_rotulo' ) ); ?></span>
      <span class="sabores__selo-ano" data-figma-node="I7057:329;7057:539"><?php echo esc_html( cdc_mod( 'cdc_sabores_selo_ano' ) ); ?></span>
    </p>
    <span class="sabores__decoracao sabores__decoracao--dir" data-figma-node="I7057:329;7057:540" aria-hidden="true">
      <img class="sabores__decoracao-img sabores__decoracao-img--escuro" src="<?php echo esc_url( cdc_asset( 'images/04-sabores-decoration-right-cream.svg' ) ); ?>" alt="" width="41" height="68">
      <img class="sabores__decoracao-img sabores__decoracao-img--claro" src="<?php echo esc_url( cdc_asset( 'images/04-sabores-decoration-right-red.svg' ) ); ?>" alt="" width="41" height="68">
    </span>
  </div>

  <div class="sabores__palco" data-figma-node="I7057:329;7057:556">
    <h2 class="sabores__titulo" id="sabores-titulo" data-figma-node="I7057:329;7057:557"><?php echo esc_html( cdc_mod( 'cdc_sabores_titulo' ) ); ?></h2>
    <div class="sabores__fatia" data-figma-node="I7057:329;7086:582">
<?php foreach ( $cdc_sabores as $cdc_i => $cdc_sabor ) : ?>
      <div class="sabores__painel" role="tabpanel" id="<?php echo esc_attr( 'sabores-painel-' . $cdc_sabor['slug'] ); ?>" aria-labelledby="<?php echo esc_attr( 'sabores-aba-' . $cdc_sabor['slug'] ); ?>"<?php echo $cdc_i ? ' hidden' : ''; ?>>
        <img src="<?php echo esc_url( $cdc_sabor['imagem'] ); ?>" alt="<?php echo esc_attr( $cdc_sabores_alt( $cdc_sabor['titulo'] ) ); ?>" width="546" height="516"<?php echo $cdc_i ? ' loading="lazy"' : ''; ?>>
      </div>
<?php endforeach; ?>
    </div>
  </div>

  <div class="sabores__nav" data-figma-node="I7057:329;7057:560">
    <div class="sabores__abas" role="tablist" aria-label="<?php esc_attr_e( 'Sabores de cheesecake', 'casadocheesecake' ); ?>">
<?php
	// Nodes Figma das 4 abas do layout (só para o diff numérico; abas extras ficam sem).
	$cdc_sabores_nodes = array( 'I7057:329;7082:438', 'I7057:329;7082:445', 'I7057:329;7082:451', 'I7057:329;7082:457' );
foreach ( $cdc_sabores as $cdc_i => $cdc_sabor ) :
	?>
      <button class="<?php echo esc_attr( $cdc_sabores_btn ); ?>" type="button" role="tab" id="<?php echo esc_attr( 'sabores-aba-' . $cdc_sabor['slug'] ); ?>" aria-controls="<?php echo esc_attr( 'sabores-painel-' . $cdc_sabor['slug'] ); ?>" aria-selected="<?php echo $cdc_i ? 'false' : 'true'; ?>"<?php echo $cdc_i ? ' tabindex="-1"' : ''; ?> data-bg="<?php echo esc_attr( $cdc_sabor['bg'] ); ?>" data-tema="<?php echo esc_attr( $cdc_sabor['tema'] ); ?>"<?php echo isset( $cdc_sabores_nodes[ $cdc_i ] ) ? ' data-figma-node="' . esc_attr( $cdc_sabores_nodes[ $cdc_i ] ) . '"' : ''; ?>><?php echo esc_html( $cdc_sabor['titulo'] ); ?></button>
<?php endforeach; ?>
    </div>
    <a class="<?php echo esc_attr( $cdc_sabores_btn ); ?>" href="<?php echo esc_url( $cdc_sabores_url ); ?>" data-figma-node="I7057:329;7082:463"><?php echo esc_html( cdc_mod( 'cdc_sabores_link_rotulo' ) ); ?></a>
  </div>
</section>
