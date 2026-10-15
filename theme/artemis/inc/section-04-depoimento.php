<?php
/**
 * Seção 04 — Depoimento / testmonial (Figma 8186:1160).
 * Conteúdo: Customizer art_depoimento_eyebrow + CPT art_depoimento.
 * CPT: título = nome, conteúdo = citação, imagem destacada = foto,
 * metas _art_depoimento_cargo e _art_depoimento_empresa.
 * A Home mostra o depoimento de menor "Ordem" (no empate, o mais recente).
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'artemis_customizer_defaults', function ( $d ) {
	return $d + array(
		'art_depoimento_eyebrow' => 'O que dizem quem já rodou',
	);
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$s['artemis_depoimento'] = array(
		__( 'Home — 04 Depoimento', 'artemis' ),
		array(
			'art_depoimento_eyebrow' => array( __( 'Frase à esquerda (itálico)', 'artemis' ), 'text' ),
		),
	);
	return $s;
} );

/** Campos do depoimento: chave meta => rótulo. */
function artemis_depoimento_fields() {
	return array(
		'_art_depoimento_cargo'   => __( 'Cargo — aparece depois do nome (ex.: Dono e engenheiro)', 'artemis' ),
		'_art_depoimento_empresa' => __( 'Empresa e contexto — linha cinza abaixo (ex.: Gascat, indústria de válvulas há mais de 30 anos)', 'artemis' ),
	);
}

add_action( 'init', function () {
	register_post_type( 'art_depoimento', array(
		'labels'       => array(
			'name'                  => __( 'Depoimentos', 'artemis' ),
			'singular_name'         => __( 'Depoimento', 'artemis' ),
			'add_new'               => __( 'Adicionar depoimento', 'artemis' ),
			'add_new_item'          => __( 'Adicionar depoimento', 'artemis' ),
			'edit_item'             => __( 'Editar depoimento', 'artemis' ),
			'new_item'              => __( 'Novo depoimento', 'artemis' ),
			'search_items'          => __( 'Buscar depoimentos', 'artemis' ),
			'not_found'             => __( 'Nenhum depoimento encontrado', 'artemis' ),
			'featured_image'        => __( 'Foto do autor', 'artemis' ),
			'set_featured_image'    => __( 'Definir foto do autor', 'artemis' ),
			'remove_featured_image' => __( 'Remover foto do autor', 'artemis' ),
			'use_featured_image'    => __( 'Usar como foto do autor', 'artemis' ),
		),
		'public'       => false,
		'show_ui'      => true,
		'menu_icon'    => 'dashicons-format-quote',
		'supports'     => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'show_in_rest' => true,
	) );

	foreach ( artemis_depoimento_fields() as $key => $label ) {
		register_post_meta( 'art_depoimento', $key, array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		) );
	}
} );

// Citação curta: editor clássico é mais simples para o cliente do que o de blocos.
add_filter( 'use_block_editor_for_post_type', function ( $use, $post_type ) {
	return 'art_depoimento' === $post_type ? false : $use;
}, 10, 2 );

add_filter( 'enter_title_here', function ( $text, $post ) {
	return 'art_depoimento' === $post->post_type ? __( 'Nome de quem deu o depoimento (ex.: Celso)', 'artemis' ) : $text;
}, 10, 2 );

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'art_depoimento_meta', __( 'Dados do autor', 'artemis' ), function ( $post ) {
		wp_nonce_field( 'art_depoimento_meta', 'art_depoimento_nonce' );
		foreach ( artemis_depoimento_fields() as $key => $label ) {
			$id = ltrim( $key, '_' );
			printf(
				'<p><label for="%1$s">%2$s</label><br><input type="text" class="widefat" id="%1$s" name="%1$s" value="%3$s"></p>',
				esc_attr( $id ),
				esc_html( $label ),
				esc_attr( get_post_meta( $post->ID, $key, true ) )
			);
		}
		echo '<p class="description">' . esc_html__( 'Escreva a frase no campo de texto principal, sem aspas: o site coloca as aspas sozinho. A foto do autor é a "Imagem destacada" (quadrada). Se houver mais de um depoimento, a Home mostra o de menor número no campo "Ordem".', 'artemis' ) . '</p>';
	}, 'art_depoimento', 'normal', 'high' );
} );

add_action( 'save_post_art_depoimento', function ( $post_id ) {
	if ( ! isset( $_POST['art_depoimento_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['art_depoimento_nonce'] ), 'art_depoimento_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( artemis_depoimento_fields() as $key => $label ) {
		$id = ltrim( $key, '_' );
		if ( isset( $_POST[ $id ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $id ] ) ) );
		}
	}
} );

/** Depoimento exibido na Home: menor "Ordem"; no empate, o mais recente. */
function artemis_depoimento_get() {
	$posts = get_posts( array(
		'post_type'      => 'art_depoimento',
		'posts_per_page' => 1,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
	) );
	return $posts ? $posts[0] : null;
}

/** Citação entre aspas curvas, com ponto final fora das aspas (como no Figma 8186:1340). */
function artemis_depoimento_citacao( $post ) {
	$texto = trim( wp_strip_all_tags( $post->post_content ) );
	$limpo = preg_replace( '/^[\s"“”]+|[\s"“”.]+$/u', '', $texto ); // tira aspas e ponto final digitados pelo cliente.
	if ( null !== $limpo ) {
		$texto = $limpo;
	}
	if ( '' === $texto ) {
		return '';
	}
	// Ponto final só quando a frase não termina em ?, ! ou reticências.
	return '“' . $texto . '”' . ( preg_match( '/[?!…]$/u', $texto ) ? '' : '.' );
}

// Depoimento do Figma (8186:1335).
add_filter( 'artemis_seed', function ( $log ) {
	artemis_seed_post( array(
		'post_type' => 'art_depoimento',
		'title'     => 'Celso',
		'content'   => 'Isso aqui está tomando mais decisão do que a nossa engenharia',
		'order'     => 0,
		'image'     => 'images/04-depoimento/avatar-celso.png',
		'meta'      => array(
			'_art_depoimento_cargo'   => 'Dono e engenheiro',
			'_art_depoimento_empresa' => 'Gascat, indústria de válvulas há mais de 30 anos',
		),
	), $log );
	return $log;
} );
