<?php
/**
 * Seção 01 — Hero (Figma 8160:165 · mobile 8635:1586).
 * Conteúdo: Customizer art_hero_* + CPT art_cliente (inc/cpt-cliente.php).
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'artemis_customizer_defaults', function ( $d ) {
	return $d + array(
		'art_hero_eyebrow'         => 'Do canteiro de obra ao chão de fábrica, do comércio exterior à frota.',
		'art_hero_title'           => 'Inteligência artificial para {cada decisão} que a sua operação repete.',
		'art_hero_lead'            => 'Construímos agentes e automações com IA para o trabalho que consome horas de colaboradores sêniores. Cada sistema é medido contra gabarito humano antes de entrar em produção.',
		'art_hero_cta1_label'      => 'Agendar diagnóstico',
		'art_hero_cta2_label'      => 'Ver os estudos de impacto',
		'art_hero_cta2_url'        => '#estudos',
		'art_hero_clients_caption' => 'Operações que hoje rodam decisão automatizada por inteligência artificial em produção.',
	);
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$s['artemis_hero'] = array(
		__( 'Home — 01 Hero', 'artemis' ),
		array(
			'art_hero_eyebrow'         => array( __( 'Frase de abertura (itálico)', 'artemis' ), 'text' ),
			'art_hero_title'           => array( __( 'Título — coloque {entre chaves} o trecho em cinza', 'artemis' ), 'textarea' ),
			'art_hero_lead'            => array( __( 'Subtítulo', 'artemis' ), 'textarea' ),
			'art_hero_cta1_label'      => array( __( 'Botão principal — texto', 'artemis' ), 'text' ),
			'art_hero_cta2_label'      => array( __( 'Botão secundário — texto', 'artemis' ), 'text' ),
			'art_hero_cta2_url'        => array( __( 'Botão secundário — link', 'artemis' ), 'text' ),
			'art_hero_clients_caption' => array( __( 'Legenda abaixo dos logos', 'artemis' ), 'text' ),
		),
	);
	return $s;
} );

/*
 * Tamanho do logo no celular (Figma Mobile 8635:1869) — complementa os campos de inc/cpt-cliente.php.
 * Vazio = tamanho do desktop × 0,69 (proporção média do Figma).
 */
function artemis_cliente_mobile_fields() {
	return array(
		'_art_cliente_mw' => __( 'Largura do logo no celular (px)', 'artemis' ),
		'_art_cliente_mh' => __( 'Altura do logo no celular (px)', 'artemis' ),
	);
}

add_action( 'init', function () {
	foreach ( array_keys( artemis_cliente_mobile_fields() ) as $key ) {
		register_post_meta( 'art_cliente', $key, array(
			'type'          => 'number',
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		) );
	}
} );

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'art_cliente_mobile', __( 'Logo no celular', 'artemis' ), function ( $post ) {
		wp_nonce_field( 'art_cliente_mobile', 'art_cliente_mobile_nonce' );
		foreach ( artemis_cliente_mobile_fields() as $key => $label ) {
			$id = ltrim( $key, '_' );
			printf(
				'<p><label for="%1$s">%2$s</label><br><input type="number" step="any" min="0" class="widefat" id="%1$s" name="%1$s" value="%3$s"></p>',
				esc_attr( $id ),
				esc_html( $label ),
				esc_attr( get_post_meta( $post->ID, $key, true ) )
			);
		}
		echo '<p class="description">' . esc_html__( 'Em branco, usa o tamanho do desktop reduzido a 69%.', 'artemis' ) . '</p>';
	}, 'art_cliente', 'side' );
} );

add_action( 'save_post_art_cliente', function ( $post_id ) {
	if ( ! isset( $_POST['art_cliente_mobile_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['art_cliente_mobile_nonce'] ), 'art_cliente_mobile' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( array_keys( artemis_cliente_mobile_fields() ) as $key ) {
		$id  = ltrim( $key, '_' );
		$raw = isset( $_POST[ $id ] ) ? sanitize_text_field( wp_unslash( $_POST[ $id ] ) ) : '';
		update_post_meta( $post_id, $key, '' === $raw ? '' : abs( (float) $raw ) );
	}
} );

/**
 * Variáveis do <li> com o tamanho do logo no celular — "--mw:115;--mh:20" (px sem unidade),
 * lidas pelo bloco ≤767 de 01-hero.css. Igual ao index.html estático.
 */
function artemis_cliente_mobile_style( $post ) {
	$size = array();
	foreach ( array( 'w', 'h' ) as $axis ) {
		$value = (float) get_post_meta( $post->ID, "_art_cliente_m{$axis}", true );
		if ( ! $value ) {
			$value = round( (float) get_post_meta( $post->ID, "_art_cliente_{$axis}", true ) * 0.69, 3 );
		}
		if ( $value > 0 ) {
			$size[] = "--m{$axis}:" . $value;
		}
	}
	return implode( ';', $size );
}

// Clientes — tamanhos exatos do Figma (desktop 8160:446 · mobile 8635:1869).
add_filter( 'artemis_seed', function ( $log ) {
	$clientes = array(
		// nome, logo, largura, altura, ajuste x, máscara branca, largura mobile, altura mobile
		array( 'Athié Wohnrath', 'images/client-athie-wohnrath.png', 166, 29, 0, false, 115, 20 ),
		array( 'Gascat', 'images/client-gascat.png', 120, 36, 0, false, 81, 24 ),
		array( 'JRG Corp', 'images/client-jrgcorp.png', 152, 33, 0, false, 109, 24 ),
		array( 'Marine Telematics', 'images/client-marine-telematics.svg', 211.2, 17.424, 0, false, 145.456, 12 ),
		array( 'Orsegups', 'images/client-orsegups-mask.png', 143.201, 36.352, -2.98, true, 94.542, 24 ),
	);
	foreach ( $clientes as $order => $c ) {
		$id = artemis_seed_post( array(
			'post_type' => 'art_cliente',
			'title'     => $c[0],
			'order'     => $order,
			'image'     => $c[1],
			'meta'      => array(
				'_art_cliente_w'        => $c[2],
				'_art_cliente_h'        => $c[3],
				'_art_cliente_offset_x' => $c[4],
				'_art_cliente_mask'     => $c[5],
			),
		), $log );
		// Tamanho mobile também em clientes já semeados antes (só preenche o que está vazio)
		if ( $id ) {
			foreach ( array( '_art_cliente_mw' => $c[6], '_art_cliente_mh' => $c[7] ) as $key => $value ) {
				if ( '' === get_post_meta( $id, $key, true ) ) {
					update_post_meta( $id, $key, $value );
				}
			}
		}
	}
	return $log;
} );
