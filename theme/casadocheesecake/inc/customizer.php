<?php
/**
 * Customizer: registro declarativo por seção.
 *
 * Cada inc/sections/NN-slug.php declara sua seção:
 *   add_filter( 'cdc_customizer_sections', function ( $sections ) {
 *       $sections['cdc_hero'] = array(
 *           'title'    => 'Hero',
 *           'priority' => 10,
 *           'fields'   => array(
 *               'cdc_hero_titulo' => array( 'label' => 'Título', 'type' => 'textarea', 'default' => '…' ),
 *               'cdc_hero_imagem' => array( 'label' => 'Imagem', 'type' => 'image', 'default' => 'images/hero-bg.jpg' ),
 *           ),
 *       );
 *       return $sections;
 *   } );
 *
 * Tipos: text, textarea, url, image (default = caminho relativo a assets/).
 * O mesmo registro alimenta cdc_mod()/cdc_image() com os defaults do Figma.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registro de seções do Customizer.
 *
 * @return array
 */
function cdc_customizer_sections() {
	static $sections = null;
	if ( null === $sections ) {
		$sections = apply_filters( 'cdc_customizer_sections', array() );
	}
	return $sections;
}

/**
 * Definição de um campo pela chave.
 *
 * @param string $key Chave do theme_mod.
 * @return array|null
 */
function cdc_customizer_field( $key ) {
	static $index = null;
	if ( null === $index ) {
		$index = array();
		foreach ( cdc_customizer_sections() as $section ) {
			foreach ( $section['fields'] as $k => $f ) {
				$index[ $k ] = $f;
			}
		}
	}
	return isset( $index[ $key ] ) ? $index[ $key ] : null;
}

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_panel(
		'cdc_home',
		array(
			'title'    => __( 'Casa do Cheesecake', 'casadocheesecake' ),
			'priority' => 30,
		)
	);

	foreach ( cdc_customizer_sections() as $section_id => $section ) {
		$wp_customize->add_section(
			$section_id,
			array(
				'title'    => $section['title'],
				'panel'    => 'cdc_home',
				'priority' => isset( $section['priority'] ) ? $section['priority'] : 100,
			)
		);
		foreach ( $section['fields'] as $key => $field ) {
			$type = isset( $field['type'] ) ? $field['type'] : 'text';
			$wp_customize->add_setting(
				$key,
				array(
					'default'           => 'image' === $type ? '' : ( isset( $field['default'] ) ? $field['default'] : '' ),
					'sanitize_callback' => cdc_customizer_sanitizer( $type ),
					'transport'         => 'refresh',
				)
			);
			if ( 'image' === $type ) {
				$wp_customize->add_control(
					new WP_Customize_Media_Control(
						$wp_customize,
						$key,
						array(
							'label'       => $field['label'],
							'section'     => $section_id,
							'mime_type'   => 'image',
							'description' => isset( $field['help'] ) ? $field['help'] : '',
						)
					)
				);
			} else {
				$wp_customize->add_control(
					$key,
					array(
						'label'       => $field['label'],
						'section'     => $section_id,
						'type'        => 'textarea' === $type ? 'textarea' : ( 'url' === $type ? 'url' : 'text' ),
						'description' => isset( $field['help'] ) ? $field['help'] : '',
					)
				);
			}
		}
	}
} );

/**
 * Sanitizer por tipo de campo.
 *
 * @param string $type Tipo.
 * @return callable
 */
function cdc_customizer_sanitizer( $type ) {
	switch ( $type ) {
		case 'textarea':
			return 'sanitize_textarea_field';
		case 'url':
			return 'esc_url_raw';
		case 'image':
			return 'absint';
		default:
			return 'sanitize_text_field';
	}
}
