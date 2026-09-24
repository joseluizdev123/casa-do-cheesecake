<?php
/**
 * Home: renderiza template-parts/sections/*.php na ordem NN- (espelha src/sections/ do estático).
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

get_header();

foreach ( cdc_sorted_glob( CDC_DIR . '/template-parts/sections/*.php' ) as $cdc_section ) {
	get_template_part( 'template-parts/sections/' . basename( $cdc_section, '.php' ) );
}

get_footer();
