<?php
/**
 * A Casa do Cheesecake — setup do tema.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

define( 'CDC_VERSION', '1.0.0' );
define( 'CDC_DIR', get_template_directory() );
define( 'CDC_URI', get_template_directory_uri() );

require_once CDC_DIR . '/inc/helpers.php';
require_once CDC_DIR . '/inc/fields.php';
require_once CDC_DIR . '/inc/cpt.php';
require_once CDC_DIR . '/inc/customizer.php';
require_once CDC_DIR . '/inc/menus.php';
require_once CDC_DIR . '/inc/seeder.php';

// Um arquivo por seção da home: registra Customizer, campos extras de CPT e seed.
foreach ( cdc_sorted_glob( CDC_DIR . '/inc/sections/*.php' ) as $cdc_section_file ) {
	require_once $cdc_section_file;
}

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'casadocheesecake', CDC_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
} );

add_action( 'wp_enqueue_scripts', function () {
	$css = array( 'tokens', 'base', 'components' );
	foreach ( $css as $handle ) {
		wp_enqueue_style( "cdc-$handle", CDC_URI . "/assets/css/$handle.css", array(), cdc_asset_version( "css/$handle.css" ) );
	}
	foreach ( cdc_sorted_glob( CDC_DIR . '/assets/css/sections/*.css' ) as $file ) {
		$slug = basename( $file, '.css' );
		wp_enqueue_style( "cdc-section-$slug", CDC_URI . "/assets/css/sections/$slug.css", array( 'cdc-components' ), cdc_asset_version( "css/sections/$slug.css" ) );
	}

	wp_enqueue_script( 'cdc-main', CDC_URI . '/assets/js/main.js', array(), cdc_asset_version( 'js/main.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	foreach ( cdc_sorted_glob( CDC_DIR . '/assets/js/sections/*.js' ) as $file ) {
		$slug = basename( $file, '.js' );
		wp_enqueue_script( "cdc-section-$slug", CDC_URI . "/assets/js/sections/$slug.js", array(), cdc_asset_version( "js/sections/$slug.js" ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	}
} );

// Preload das fontes self-hosted (mesmo comportamento do index.html estático).
add_action( 'wp_head', function () {
	foreach ( array( 'anton-latin', 'archivo-latin' ) as $font ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( CDC_URI . "/assets/fonts/$font.woff2" ) );
	}
}, 1 );
