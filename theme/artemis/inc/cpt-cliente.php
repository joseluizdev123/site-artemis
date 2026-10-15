<?php
/**
 * CPT art_cliente — logos da faixa "Customer Logos" (Figma 8160:446).
 * Campos: título (nome), featured image (logo), menu_order (ordem), meta _art_cliente_url.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'art_cliente', array(
		'labels'       => array(
			'name'               => __( 'Clientes', 'artemis' ),
			'singular_name'      => __( 'Cliente', 'artemis' ),
			'add_new_item'       => __( 'Adicionar cliente', 'artemis' ),
			'edit_item'          => __( 'Editar cliente', 'artemis' ),
			'featured_image'     => __( 'Logo', 'artemis' ),
			'set_featured_image' => __( 'Definir logo', 'artemis' ),
		),
		'public'       => false,
		'show_ui'      => true,
		'menu_icon'    => 'dashicons-building',
		'supports'     => array( 'title', 'thumbnail', 'page-attributes' ),
		'show_in_rest' => true,
	) );

	foreach ( artemis_cliente_fields() as $key => $field ) {
		register_post_meta( 'art_cliente', $key, array(
			'type'          => $field['type'],
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		) );
	}
} );

/** Campos do cliente: chave meta => config. */
function artemis_cliente_fields() {
	return array(
		'_art_cliente_url'      => array( 'type' => 'string', 'label' => __( 'Site do cliente (opcional)', 'artemis' ), 'input' => 'url' ),
		'_art_cliente_w'        => array( 'type' => 'number', 'label' => __( 'Largura do logo (px)', 'artemis' ), 'input' => 'number' ),
		'_art_cliente_h'        => array( 'type' => 'number', 'label' => __( 'Altura do logo (px)', 'artemis' ), 'input' => 'number' ),
		'_art_cliente_offset_x' => array( 'type' => 'number', 'label' => __( 'Ajuste óptico horizontal (px, pode ser negativo)', 'artemis' ), 'input' => 'number' ),
		'_art_cliente_mask'     => array( 'type' => 'boolean', 'label' => __( 'Pintar o logo de branco (para logos coloridos)', 'artemis' ), 'input' => 'checkbox' ),
	);
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'art_cliente_meta', __( 'Dados do cliente', 'artemis' ), function ( $post ) {
		wp_nonce_field( 'art_cliente_meta', 'art_cliente_nonce' );
		foreach ( artemis_cliente_fields() as $key => $field ) {
			$value = get_post_meta( $post->ID, $key, true );
			$id    = ltrim( $key, '_' );
			if ( 'checkbox' === $field['input'] ) {
				printf( '<p><label><input type="checkbox" name="%1$s" value="1"%2$s> %3$s</label></p>', esc_attr( $id ), checked( (bool) $value, true, false ), esc_html( $field['label'] ) );
				continue;
			}
			printf(
				'<p><label for="%1$s">%2$s</label><br><input type="%3$s" step="any" class="widefat" id="%1$s" name="%1$s" value="%4$s"></p>',
				esc_attr( $id ),
				esc_html( $field['label'] ),
				esc_attr( $field['input'] ),
				esc_attr( $value )
			);
		}
		echo '<p class="description">' . esc_html__( 'Use a imagem destacada como logo (fundo transparente, versão clara). A ordem segue o campo "Ordem".', 'artemis' ) . '</p>';
	}, 'art_cliente', 'normal' );
} );

add_action( 'save_post_art_cliente', function ( $post_id ) {
	if ( ! isset( $_POST['art_cliente_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['art_cliente_nonce'] ), 'art_cliente_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( artemis_cliente_fields() as $key => $field ) {
		$id  = ltrim( $key, '_' );
		$raw = isset( $_POST[ $id ] ) ? wp_unslash( $_POST[ $id ] ) : '';
		switch ( $field['type'] ) {
			case 'boolean':
				$value = '' !== $raw;
				break;
			case 'number':
				$value = '' === $raw ? '' : (float) $raw;
				break;
			default:
				$value = esc_url_raw( $raw );
		}
		update_post_meta( $post_id, $key, $value );
	}
} );

/**
 * Markup de um logo — idêntico ao index.html estático.
 */
function artemis_cliente_logo( $post ) {
	$thumb = get_post_thumbnail_id( $post );
	if ( ! $thumb ) {
		return '';
	}
	$src    = wp_get_attachment_image_url( $thumb, 'full' );
	$w      = (float) get_post_meta( $post->ID, '_art_cliente_w', true );
	$h      = (float) get_post_meta( $post->ID, '_art_cliente_h', true );
	$offset = (float) get_post_meta( $post->ID, '_art_cliente_offset_x', true );
	$style  = ( $w ? "width:{$w}px;" : '' ) . ( $h ? "height:{$h}px;" : '' ) . ( $offset ? "transform:translateX({$offset}px);" : '' );
	$name   = get_the_title( $post );

	if ( get_post_meta( $post->ID, '_art_cliente_mask', true ) ) {
		return sprintf(
			'<span class="clients__logo clients__logo--mask" role="img" aria-label="%s" style="--logo:url(%s);%s"></span>',
			esc_attr( $name ),
			esc_url( $src ),
			esc_attr( $style )
		);
	}
	return sprintf(
		'<img class="clients__logo" src="%s" width="%d" height="%d" style="%s" alt="%s" loading="lazy">',
		esc_url( $src ),
		(int) $w,
		(int) $h,
		esc_attr( $style ),
		esc_attr( $name )
	);
}
