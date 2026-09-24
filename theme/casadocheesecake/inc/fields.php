<?php
/**
 * Motor genérico de meta box (sem ACF).
 *
 * Campos são declarados por CPT via filtro:
 *   add_filter( 'cdc_post_fields', function ( $fields ) {
 *       $fields['cdc_cheesecake']['tag'] = array( 'label' => 'Tag', 'type' => 'text' );
 *       return $fields;
 *   } );
 *
 * Tipos: text, textarea, url, number, color, checkbox, image.
 * Meta salva como `_cdc_<chave>`.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registro completo de campos por post type.
 *
 * @return array<string, array<string, array>>
 */
function cdc_post_fields() {
	static $fields = null;
	if ( null === $fields ) {
		$fields = apply_filters( 'cdc_post_fields', array() );
	}
	return $fields;
}

add_action( 'add_meta_boxes', function ( $post_type ) {
	$all = cdc_post_fields();
	if ( empty( $all[ $post_type ] ) ) {
		return;
	}
	add_meta_box( 'cdc_fields', __( 'Conteúdo', 'casadocheesecake' ), 'cdc_render_meta_box', $post_type, 'normal', 'high' );
} );

/**
 * Renderiza o meta box.
 *
 * @param WP_Post $post Post atual.
 */
function cdc_render_meta_box( $post ) {
	$fields = cdc_post_fields()[ $post->post_type ];
	wp_nonce_field( 'cdc_save_fields', 'cdc_fields_nonce' );
	echo '<table class="form-table cdc-fields"><tbody>';
	foreach ( $fields as $key => $field ) {
		$name  = 'cdc_fields[' . esc_attr( $key ) . ']';
		$id    = 'cdc-field-' . esc_attr( $key );
		$value = get_post_meta( $post->ID, '_cdc_' . $key, true );
		$type  = isset( $field['type'] ) ? $field['type'] : 'text';
		echo '<tr><th scope="row"><label for="' . $id . '">' . esc_html( $field['label'] ) . '</label></th><td>';
		switch ( $type ) {
			case 'textarea':
				printf( '<textarea class="large-text" rows="%d" id="%s" name="%s">%s</textarea>', isset( $field['rows'] ) ? (int) $field['rows'] : 4, $id, $name, esc_textarea( $value ) );
				break;
			case 'checkbox':
				printf( '<input type="checkbox" id="%s" name="%s" value="1" %s>', $id, $name, checked( $value, '1', false ) );
				break;
			case 'image':
				$src = $value ? wp_get_attachment_image_url( (int) $value, 'thumbnail' ) : '';
				printf(
					'<div class="cdc-image-field"><img src="%1$s" alt="" style="max-width:120px;height:auto;%2$s"><input type="hidden" id="%3$s" name="%4$s" value="%5$s"> <button type="button" class="button cdc-image-pick">%6$s</button> <button type="button" class="button-link cdc-image-clear">%7$s</button></div>',
					esc_url( $src ),
					$src ? '' : 'display:none;',
					$id,
					$name,
					esc_attr( $value ),
					esc_html__( 'Escolher imagem', 'casadocheesecake' ),
					esc_html__( 'Remover', 'casadocheesecake' )
				);
				break;
			default:
				$input_type = in_array( $type, array( 'url', 'number', 'color' ), true ) ? $type : 'text';
				printf( '<input type="%s" class="%s" id="%s" name="%s" value="%s">', $input_type, 'color' === $type ? '' : 'regular-text', $id, $name, esc_attr( $value ) );
		}
		if ( ! empty( $field['help'] ) ) {
			echo '<p class="description">' . esc_html( $field['help'] ) . '</p>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

add_action( 'save_post', function ( $post_id, $post ) {
	if ( ! isset( $_POST['cdc_fields_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['cdc_fields_nonce'] ) ), 'cdc_save_fields' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$all = cdc_post_fields();
	if ( empty( $all[ $post->post_type ] ) ) {
		return;
	}
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitizado por campo abaixo.
	$input = isset( $_POST['cdc_fields'] ) ? (array) wp_unslash( $_POST['cdc_fields'] ) : array();
	foreach ( $all[ $post->post_type ] as $key => $field ) {
		$raw = isset( $input[ $key ] ) ? $input[ $key ] : '';
		update_post_meta( $post_id, '_cdc_' . $key, cdc_sanitize_field( $raw, isset( $field['type'] ) ? $field['type'] : 'text' ) );
	}
}, 10, 2 );

/**
 * Sanitiza por tipo.
 *
 * @param mixed  $raw  Valor bruto.
 * @param string $type Tipo do campo.
 * @return string
 */
function cdc_sanitize_field( $raw, $type ) {
	switch ( $type ) {
		case 'textarea':
			return sanitize_textarea_field( $raw );
		case 'url':
			return esc_url_raw( $raw );
		case 'number':
		case 'image':
			return '' === $raw ? '' : (string) absint( $raw );
		case 'color':
			return (string) sanitize_hex_color( $raw );
		case 'checkbox':
			return $raw ? '1' : '';
		default:
			return sanitize_text_field( $raw );
	}
}

// Media picker para campos de imagem.
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	wp_enqueue_media();
	wp_add_inline_script(
		'media-editor',
		"jQuery(function($){\n" .
		"  $(document).on('click','.cdc-image-pick',function(e){e.preventDefault();var w=$(this).closest('.cdc-image-field');var f=wp.media({multiple:false,library:{type:'image'}});f.on('select',function(){var a=f.state().get('selection').first().toJSON();w.find('input').val(a.id);w.find('img').attr('src',(a.sizes&&a.sizes.thumbnail?a.sizes.thumbnail.url:a.url)).show();});f.open();});\n" .
		"  $(document).on('click','.cdc-image-clear',function(e){e.preventDefault();var w=$(this).closest('.cdc-image-field');w.find('input').val('');w.find('img').attr('src','').hide();});\n" .
		'});'
	);
} );
