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
 * Tipos: text, textarea, url, number, color, checkbox, image, gallery (várias
 * imagens, IDs separados por vírgula) e heading (só um título de grupo, sem valor).
 * Meta salva como `_cdc_<chave>`. O mesmo renderizador (cdc_field_input) serve
 * às option pages de inc/options.php.
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
	cdc_fields_table(
		$fields,
		static function ( $key ) use ( $post ) {
			return get_post_meta( $post->ID, '_cdc_' . $key, true );
		},
		'cdc_fields'
	);
}

/**
 * Tabela de campos (meta box e option pages).
 *
 * @param array    $fields   Definições.
 * @param callable $value_of Função ( $key ) → valor salvo.
 * @param string   $base     Nome base do array no POST (ex. 'cdc_fields').
 */
function cdc_fields_table( $fields, $value_of, $base ) {
	echo '<table class="form-table cdc-fields" role="presentation"><tbody>';
	foreach ( $fields as $key => $field ) {
		$type = isset( $field['type'] ) ? $field['type'] : 'text';
		if ( 'heading' === $type ) {
			echo '<tr><th colspan="2" style="padding-bottom:0"><h2 style="margin:16px 0 0">' . esc_html( $field['label'] ) . '</h2>';
			if ( ! empty( $field['help'] ) ) {
				echo '<p class="description" style="font-weight:400">' . esc_html( $field['help'] ) . '</p>';
			}
			echo '</th></tr>';
			continue;
		}
		$id = 'cdc-field-' . sanitize_html_class( $key );
		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';
		cdc_field_input( $field, $base . '[' . $key . ']', $id, call_user_func( $value_of, $key ) );
		if ( ! empty( $field['help'] ) ) {
			echo '<p class="description">' . esc_html( $field['help'] ) . '</p>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

/**
 * Um campo de formulário.
 *
 * @param array  $field Definição (type, rows…).
 * @param string $name  Atributo name.
 * @param string $id    Atributo id.
 * @param mixed  $value Valor salvo.
 */
function cdc_field_input( $field, $name, $id, $value ) {
	$type = isset( $field['type'] ) ? $field['type'] : 'text';
	switch ( $type ) {
		case 'textarea':
			printf( '<textarea class="large-text" rows="%d" id="%s" name="%s">%s</textarea>', isset( $field['rows'] ) ? (int) $field['rows'] : 4, esc_attr( $id ), esc_attr( $name ), esc_textarea( $value ) );
			break;
		case 'checkbox':
			// hidden antes do checkbox: desmarcado também é salvo (option pages guardam o array inteiro).
			printf( '<input type="hidden" name="%2$s" value=""><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s>', esc_attr( $id ), esc_attr( $name ), checked( $value, '1', false ) );
			break;
		case 'image':
			$src = $value ? wp_get_attachment_image_url( (int) $value, 'thumbnail' ) : '';
			printf(
				'<div class="cdc-image-field"><img src="%1$s" alt="" style="max-width:120px;height:auto;%2$s"><input type="hidden" id="%3$s" name="%4$s" value="%5$s"> <button type="button" class="button cdc-image-pick">%6$s</button> <button type="button" class="button-link cdc-image-clear">%7$s</button></div>',
				esc_url( $src ),
				$src ? '' : 'display:none;',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				esc_html__( 'Escolher imagem', 'casadocheesecake' ),
				esc_html__( 'Remover', 'casadocheesecake' )
			);
			break;
		case 'gallery':
			$thumbs = '';
			foreach ( cdc_ids( $value ) as $att ) {
				$src = wp_get_attachment_image_url( $att, 'thumbnail' );
				if ( $src ) {
					$thumbs .= '<img src="' . esc_url( $src ) . '" alt="" style="width:72px;height:72px;object-fit:cover;margin:0 6px 6px 0">';
				}
			}
			printf(
				'<div class="cdc-gallery-field"><div class="cdc-gallery-thumbs">%1$s</div><input type="hidden" id="%2$s" name="%3$s" value="%4$s"> <button type="button" class="button cdc-gallery-pick">%5$s</button> <button type="button" class="button-link cdc-gallery-clear">%6$s</button></div>',
				$thumbs, // phpcs:ignore WordPress.Security.EscapeOutput -- montado com esc_url acima.
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( implode( ',', cdc_ids( $value ) ) ),
				esc_html__( 'Escolher imagens', 'casadocheesecake' ),
				esc_html__( 'Limpar', 'casadocheesecake' )
			);
			break;
		default:
			$input_type = in_array( $type, array( 'url', 'number', 'color' ), true ) ? $type : 'text';
			printf( '<input type="%s" class="%s" id="%s" name="%s" value="%s">', esc_attr( $input_type ), 'color' === $type ? '' : 'regular-text', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ) );
	}
}

/**
 * Lista de IDs (attachments) a partir de "1,2,3" ou array.
 *
 * @param mixed $value Valor salvo.
 * @return int[]
 */
function cdc_ids( $value ) {
	$list = is_array( $value ) ? $value : explode( ',', (string) $value );
	return array_values( array_filter( array_map( 'absint', $list ) ) );
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
			return cdc_keep_percent( $raw, 'sanitize_textarea_field' );
		case 'url':
			return esc_url_raw( $raw );
		case 'number':
		case 'image':
			return '' === $raw ? '' : (string) absint( $raw );
		case 'gallery':
			return implode( ',', cdc_ids( $raw ) );
		case 'color':
			return (string) sanitize_hex_color( $raw );
		case 'checkbox':
			return $raw ? '1' : '';
		default:
			return cdc_keep_percent( $raw, 'sanitize_text_field' );
	}
}

/**
 * Roda um sanitizador do WP sem perder a codificação "%XX" dos links
 * (sanitize_text_field e sanitize_textarea_field apagam "%C3%A1").
 *
 * @param mixed    $raw Valor bruto.
 * @param callable $fn  Sanitizador.
 * @return string
 */
function cdc_keep_percent( $raw, $fn ) {
	$marca = "\u{E000}"; // caractere de uso privado: não aparece em texto normal
	$raw   = preg_replace( '/%([0-9A-Fa-f]{2})/', $marca . '$1', (string) $raw );
	return preg_replace( '/' . $marca . '([0-9A-Fa-f]{2})/u', '%$1', (string) call_user_func( $fn, $raw ) );
}

// Media picker para campos de imagem e galeria (posts e option pages "Páginas do site").
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && false === strpos( (string) $hook, 'cdc-pagina' ) ) {
		return;
	}
	wp_enqueue_media();
	wp_add_inline_script(
		'media-editor',
		"jQuery(function($){\n" .
		"  $(document).on('click','.cdc-image-pick',function(e){e.preventDefault();var w=$(this).closest('.cdc-image-field');var f=wp.media({multiple:false,library:{type:'image'}});f.on('select',function(){var a=f.state().get('selection').first().toJSON();w.find('input').val(a.id);w.find('img').attr('src',(a.sizes&&a.sizes.thumbnail?a.sizes.thumbnail.url:a.url)).show();});f.open();});\n" .
		"  $(document).on('click','.cdc-image-clear',function(e){e.preventDefault();var w=$(this).closest('.cdc-image-field');w.find('input').val('');w.find('img').attr('src','').hide();});\n" .
		"  $(document).on('click','.cdc-gallery-pick',function(e){e.preventDefault();var w=$(this).closest('.cdc-gallery-field');var inp=w.find('input');var f=wp.media({multiple:'add',library:{type:'image'}});f.on('open',function(){var sel=f.state().get('selection');(inp.val()?inp.val().split(','):[]).forEach(function(id){var a=wp.media.attachment(id);a.fetch();sel.add(a);});});f.on('select',function(){var ids=[],box=w.find('.cdc-gallery-thumbs').empty();f.state().get('selection').each(function(m){var a=m.toJSON();ids.push(a.id);$('<img alt=\"\" style=\"width:72px;height:72px;object-fit:cover;margin:0 6px 6px 0\">').attr('src',(a.sizes&&a.sizes.thumbnail?a.sizes.thumbnail.url:a.url)).appendTo(box);});inp.val(ids.join(','));});f.open();});\n" .
		"  $(document).on('click','.cdc-gallery-clear',function(e){e.preventDefault();var w=$(this).closest('.cdc-gallery-field');w.find('input').val('');w.find('.cdc-gallery-thumbs').empty();});\n" .
		'});'
	);
} );
