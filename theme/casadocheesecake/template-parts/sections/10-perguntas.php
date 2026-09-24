<?php
/**
 * Seção 10 — Perguntas frequentes (Figma 7057:763).
 * Espelha src/sections/10-perguntas.html; perguntas do CPT cdc_faq, textos do Customizer
 * (inc/sections/10-perguntas.php). Emite JSON-LD FAQPage gerado das mesmas perguntas.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

$cdc_faq_items = cdc_faq_items();

// Node IDs do Figma por posição (item, pergunta, círculo, ícone visível) — só marcação de QA.
$cdc_faq_nodes = array(
	array( '7057:766', '7057:768', '7057:814', '7057:822' ),
	array( '7057:786', '7057:789', '7057:825', '7057:850' ),
	array( '7057:792', '7057:795', '7057:856', '7057:857' ),
	array( '7057:797', '7057:800', '7057:853', '7057:854' ),
	array( '7057:802', '7057:805', '7057:862', '7057:863' ),
	array( '7057:808', '7057:811', '7057:859', '7057:860' ),
	array( '7057:1021', '7057:1024', '7057:1027', '7057:1028' ),
	array( '7057:1030', '7057:1033', '7057:1036', '7057:1037' ),
);

$cdc_faq_link = cdc_mod( 'cdc_faq_botao_link' );
if ( '' === $cdc_faq_link ) {
	$cdc_faq_link = cdc_mod( 'cdc_contato_whatsapp' );
}

/**
 * Atributo data-figma-node opcional.
 *
 * @param string $node Node ID ou ''.
 * @return string
 */
$cdc_faq_node = function ( $node ) {
	return $node ? ' data-figma-node="' . esc_attr( $node ) . '"' : '';
};

$cdc_icon_minus = cdc_asset( 'images/10-perguntas-icon-minus.svg' );
$cdc_icon_plus  = cdc_asset( 'images/10-perguntas-icon-plus.svg' );
?>
<section class="faq" id="perguntas" data-figma-node="7057:763" aria-labelledby="faq-titulo" data-faq>
	<div class="faq__inner container">
		<div class="faq__header" data-figma-node="7057:764">
			<h2 class="faq__title u-display" id="faq-titulo" data-figma-node="7057:765"><?php echo esc_html( cdc_mod( 'cdc_faq_titulo' ) ); ?></h2>
		</div>
		<div class="faq__content" data-figma-node="7057:791">
			<?php
			foreach ( $cdc_faq_items as $cdc_i => $cdc_item ) :
				$cdc_n     = $cdc_i + 1;
				$cdc_open  = 0 === $cdc_i;
				$cdc_nodes = isset( $cdc_faq_nodes[ $cdc_i ] ) ? $cdc_faq_nodes[ $cdc_i ] : array( '', '', '', '' );
				?>
				<div class="faq__item<?php echo $cdc_open ? ' is-open' : ''; ?>"<?php echo $cdc_faq_node( $cdc_nodes[0] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado no closure. ?>>
					<div class="faq__row"<?php echo $cdc_open ? $cdc_faq_node( '7057:767' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<h3 class="faq__heading">
							<button class="faq__trigger" type="button" id="faq-pergunta-<?php echo (int) $cdc_n; ?>" aria-expanded="<?php echo $cdc_open ? 'true' : 'false'; ?>" aria-controls="faq-resposta-<?php echo (int) $cdc_n; ?>">
								<span class="faq__question"<?php echo $cdc_faq_node( $cdc_nodes[1] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $cdc_item['pergunta'] ); ?></span>
								<span class="faq__icon"<?php echo $cdc_faq_node( $cdc_nodes[2] ); // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-hidden="true">
									<img class="faq__icon-img faq__icon-img--minus"<?php echo $cdc_open ? $cdc_faq_node( $cdc_nodes[3] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?> src="<?php echo esc_url( $cdc_icon_minus ); ?>" alt="" width="20" height="20" loading="lazy" decoding="async">
									<img class="faq__icon-img faq__icon-img--plus"<?php echo $cdc_open ? '' : $cdc_faq_node( $cdc_nodes[3] ); // phpcs:ignore WordPress.Security.EscapeOutput ?> src="<?php echo esc_url( $cdc_icon_plus ); ?>" alt="" width="20" height="20" loading="lazy" decoding="async">
								</span>
							</button>
						</h3>
						<div class="faq__answer" id="faq-resposta-<?php echo (int) $cdc_n; ?>" role="region" aria-labelledby="faq-pergunta-<?php echo (int) $cdc_n; ?>">
							<div class="faq__answer-inner">
								<p class="faq__answer-text"<?php echo $cdc_open ? $cdc_faq_node( '7057:769' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo wp_kses_post( cdc_rich( $cdc_item['resposta'] ) ); ?></p>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
			<div class="faq__footer" data-figma-node="7057:893">
				<p class="faq__footer-text" data-figma-node="7057:894"><?php echo esc_html( cdc_mod( 'cdc_faq_rodape_texto' ) ); ?></p>
				<a class="btn btn--secondary" data-figma-node="7079:404" href="<?php echo esc_url( $cdc_faq_link ); ?>" target="_blank" rel="noopener"><?php echo esc_html( cdc_mod( 'cdc_faq_botao_texto' ) ); ?></a>
			</div>
		</div>
	</div>
	<?php
	// Dados estruturados FAQPage (mesmas perguntas/respostas exibidas).
	$cdc_faq_schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => array(),
	);
	foreach ( $cdc_faq_items as $cdc_item ) {
		$cdc_faq_schema['mainEntity'][] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( $cdc_item['pergunta'] ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => trim( str_replace( '**', '', wp_strip_all_tags( $cdc_item['resposta'] ) ) ),
			),
		);
	}
	?>
	<script type="application/ld+json"><?php echo wp_json_encode( $cdc_faq_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP ); // phpcs:ignore WordPress.Security.EscapeOutput -- JSON com < > & escapados. ?></script>
</section>
