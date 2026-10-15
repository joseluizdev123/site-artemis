<?php
/**
 * Seeder one-click: Aparência → Artemis: popular conteúdo.
 * Idempotente — busca por título/slug antes de criar.
 * Cada seção nova da Home acrescenta seu bloco em artemis_seed().
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_theme_page( __( 'Artemis: popular conteúdo', 'artemis' ), __( 'Artemis: popular conteúdo', 'artemis' ), 'manage_options', 'artemis-seeder', 'artemis_seeder_page' );
} );

function artemis_seeder_page() {
	$log = array();
	if ( isset( $_POST['artemis_seed'] ) && check_admin_referer( 'artemis_seed' ) && current_user_can( 'manage_options' ) ) {
		$log = artemis_seed();
	}
	echo '<div class="wrap"><h1>' . esc_html__( 'Popular conteúdo inicial', 'artemis' ) . '</h1>';
	echo '<p>' . esc_html__( 'Cria a página Home, define-a como página inicial e importa clientes e imagens do Figma. Pode rodar mais de uma vez: itens existentes são mantidos.', 'artemis' ) . '</p>';
	echo '<form method="post">';
	wp_nonce_field( 'artemis_seed' );
	submit_button( __( 'Popular agora', 'artemis' ), 'primary', 'artemis_seed' );
	echo '</form>';
	if ( $log ) {
		echo '<ul style="list-style:disc;padding-left:20px">';
		foreach ( $log as $line ) {
			echo '<li>' . esc_html( $line ) . '</li>';
		}
		echo '</ul>';
	}
	echo '</div>';
}

/** Importa um arquivo de assets/ para a Mídia (uma vez só). */
function artemis_seed_media( $rel_path, $title ) {
	$existing = get_posts( array(
		'post_type'   => 'attachment',
		'meta_key'    => '_artemis_seed_src',
		'meta_value'  => $rel_path,
		'numberposts' => 1,
		'fields'      => 'ids',
	) );
	if ( $existing ) {
		return (int) $existing[0];
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = wp_tempnam( basename( $rel_path ) );
	copy( ARTEMIS_DIR . '/assets/' . $rel_path, $tmp );
	$id = media_handle_sideload( array( 'name' => basename( $rel_path ), 'tmp_name' => $tmp ), 0, $title );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return 0;
	}
	update_post_meta( $id, '_artemis_seed_src', $rel_path );
	return (int) $id;
}

function artemis_seed() {
	$log = array();

	// Home como página inicial.
	$home = get_page_by_path( 'home' );
	if ( ! $home ) {
		$home_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Home', 'post_name' => 'home' ) );
		$log[]   = 'Página Home criada.';
	} else {
		$home_id = $home->ID;
	}
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home_id );

	// Blog como página de posts (home.php · Figma 8294:1703).
	$blog = get_page_by_path( 'blog' );
	if ( ! $blog ) {
		$blog_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Blog', 'post_name' => 'blog' ) );
		$log[]   = 'Página Blog criada.';
	} else {
		$blog_id = $blog->ID;
	}
	update_option( 'page_for_posts', $blog_id );

	/**
	 * Cada inc/section-NN-slug.php popula o próprio conteúdo:
	 * add_filter( 'artemis_seed', function ( $log ) { ...; return $log; } );
	 */
	$log = apply_filters( 'artemis_seed', $log );

	$log[] = 'Concluído.';
	return $log;
}

/**
 * Cria um post de CPT se ainda não existir um com o mesmo título.
 * $args: post_type, title, content, excerpt, order, image (caminho em assets/), meta (array).
 * Retorna o ID (novo ou existente) e registra no log.
 */
function artemis_seed_post( array $args, array &$log ) {
	$found = get_posts( array(
		'post_type'   => $args['post_type'],
		'title'       => $args['title'],
		'numberposts' => 1,
		'post_status' => 'any',
		'fields'      => 'ids',
	) );
	if ( $found ) {
		return (int) $found[0];
	}
	$id = wp_insert_post( array(
		'post_type'    => $args['post_type'],
		'post_status'  => 'publish',
		'post_title'   => $args['title'],
		'post_content' => isset( $args['content'] ) ? $args['content'] : '',
		'post_excerpt' => isset( $args['excerpt'] ) ? $args['excerpt'] : '',
		'menu_order'   => isset( $args['order'] ) ? (int) $args['order'] : 0,
	) );
	if ( ! $id || is_wp_error( $id ) ) {
		$log[] = 'Falha ao criar ' . $args['title'];
		return 0;
	}
	if ( ! empty( $args['image'] ) ) {
		$media = artemis_seed_media( $args['image'], $args['title'] );
		if ( $media ) {
			set_post_thumbnail( $id, $media );
		}
	}
	if ( ! empty( $args['meta'] ) ) {
		foreach ( $args['meta'] as $key => $value ) {
			update_post_meta( $id, $key, $value );
		}
	}
	$log[] = sprintf( '%s criado: %s', $args['post_type'], $args['title'] );
	return (int) $id;
}
