<?php
/**
 * Seção 02 — O que fazemos (Figma 8183:302).
 * Conteúdo: Customizer art_oqf_* + CPT art_membro (Equipe — MODEL.md).
 * Fundadores (Founder Info 8213:357) = membros com "É fundador" marcado, na ordem do campo "Ordem".
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'artemis_customizer_defaults', function ( $d ) {
	return $d + array(
		'art_oqf_eyebrow'          => 'O que fazemos',
		'art_oqf_title'            => 'Existe uma decisão na sua operação {que nenhum sistema toma}',
		'art_oqf_body'             => implode( "\n\n", array(
			'Qual código essa peça recebe. Se o documento desse fornecedor está válido. Quantos dutos tem nessa planta e de que tipo. Como essa carga vai no caminhão. Se esse pedido pode ser liberado.',
			'São decisões de julgamento técnico, tomadas dezenas de vezes por dia, por gente cara. O critério existe, mas mora dentro de três ou quatro pessoas que o aprenderam em anos de operação. Não está no ERP, não está no procedimento, não está escrito em lugar nenhum.',
			'É por isso que o erro custa caro e ninguém consegue explicar direito por que ele aconteceu. E é por isso que, quando o especialista sai, o conhecimento vai junto.',
			'Automação de processo não resolve isso, porque não existe processo escrito para automatizar. **Nosso trabalho é extrair esse critério, escrevê-lo com rigor e transformá-lo em sistema de inteligência artificial que decide como o especialista decidiria. A parte difícil é a primeira. O software vem depois, e é a parte fácil.**',
		) ),
		'art_oqf_founders_caption' => "Fundadores\ndo Studio Artemis",
	);
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$s['artemis_oqf'] = array(
		__( 'Home — 02 O que fazemos', 'artemis' ),
		array(
			'art_oqf_eyebrow'          => array( __( 'Frase de abertura (itálico)', 'artemis' ), 'text' ),
			'art_oqf_title'            => array( __( 'Título — coloque {entre chaves} o trecho em cinza', 'artemis' ), 'textarea' ),
			'art_oqf_body'             => array( __( 'Texto — deixe uma linha em branco entre parágrafos; use **dois asteriscos** em volta do trecho em negrito', 'artemis' ), 'textarea' ),
			'art_oqf_founders_caption' => array( __( 'Legenda ao lado das fotos dos fundadores (Enter quebra a linha)', 'artemis' ), 'textarea' ),
		),
	);
	return $s;
} );

/* --------------------------------------------------------------------------
   CPT art_membro — Equipe (fotos, nome e cargo)
   -------------------------------------------------------------------------- */

add_action( 'init', function () {
	if ( post_type_exists( 'art_membro' ) ) {
		return;
	}
	register_post_type( 'art_membro', array(
		'labels'       => array(
			'name'                  => __( 'Equipe', 'artemis' ),
			'singular_name'         => __( 'Membro da equipe', 'artemis' ),
			'menu_name'             => __( 'Equipe', 'artemis' ),
			'all_items'             => __( 'Todos os membros', 'artemis' ),
			'add_new'               => __( 'Adicionar membro', 'artemis' ),
			'add_new_item'          => __( 'Adicionar membro da equipe', 'artemis' ),
			'edit_item'             => __( 'Editar membro da equipe', 'artemis' ),
			'new_item'              => __( 'Novo membro', 'artemis' ),
			'search_items'          => __( 'Buscar membros', 'artemis' ),
			'not_found'             => __( 'Nenhum membro encontrado', 'artemis' ),
			'featured_image'        => __( 'Foto', 'artemis' ),
			'set_featured_image'    => __( 'Definir foto', 'artemis' ),
			'remove_featured_image' => __( 'Remover foto', 'artemis' ),
			'use_featured_image'    => __( 'Usar como foto', 'artemis' ),
		),
		'public'       => false,
		'show_ui'      => true,
		'menu_icon'    => 'dashicons-groups',
		'supports'     => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'show_in_rest' => true,
	) );

	foreach ( artemis_oqf_membro_fields() as $key => $field ) {
		register_post_meta( 'art_membro', $key, array(
			'type'          => $field['type'],
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		) );
	}
} );

/** Campos do membro: chave meta => config. */
function artemis_oqf_membro_fields() {
	return array(
		'_art_membro_cargo'    => array( 'type' => 'string', 'label' => __( 'Cargo (ex.: Sócio-fundador)', 'artemis' ), 'input' => 'text' ),
		'_art_membro_fundador' => array( 'type' => 'boolean', 'label' => __( 'É fundador — a foto aparece na Home, seção "O que fazemos"', 'artemis' ), 'input' => 'checkbox' ),
	);
}

add_filter( 'enter_title_here', function ( $title, $post ) {
	return 'art_membro' === $post->post_type ? __( 'Nome', 'artemis' ) : $title;
}, 10, 2 );

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'art_membro_meta', __( 'Dados do membro', 'artemis' ), function ( $post ) {
		wp_nonce_field( 'art_membro_meta', 'art_membro_nonce' );
		foreach ( artemis_oqf_membro_fields() as $key => $field ) {
			$value = get_post_meta( $post->ID, $key, true );
			$id    = ltrim( $key, '_' );
			if ( 'checkbox' === $field['input'] ) {
				printf( '<p><label><input type="checkbox" name="%1$s" value="1"%2$s> %3$s</label></p>', esc_attr( $id ), checked( (bool) $value, true, false ), esc_html( $field['label'] ) );
				continue;
			}
			printf(
				'<p><label for="%1$s">%2$s</label><br><input type="%3$s" class="widefat" id="%1$s" name="%1$s" value="%4$s"></p>',
				esc_attr( $id ),
				esc_html( $field['label'] ),
				esc_attr( $field['input'] ),
				esc_attr( $value )
			);
		}
		echo '<p class="description">' . esc_html__( 'Use a imagem destacada como foto (quadrada, de preferência 400×400 px). A ordem segue o campo "Ordem".', 'artemis' ) . '</p>';
	}, 'art_membro', 'side' );
} );

add_action( 'save_post_art_membro', function ( $post_id ) {
	if ( ! isset( $_POST['art_membro_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['art_membro_nonce'] ), 'art_membro_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( artemis_oqf_membro_fields() as $key => $field ) {
		$id  = ltrim( $key, '_' );
		$raw = isset( $_POST[ $id ] ) ? wp_unslash( $_POST[ $id ] ) : '';
		if ( 'boolean' === $field['type'] ) {
			$value = '' !== $raw;
		} else {
			$value = sanitize_text_field( $raw );
		}
		update_post_meta( $post_id, $key, $value );
	}
} );

/* --------------------------------------------------------------------------
   Helpers de render (usados em template-parts/home/02-o-que-fazemos.php)
   -------------------------------------------------------------------------- */

/** Membros marcados como fundadores, na ordem do menu_order. */
function artemis_oqf_fundadores() {
	return get_posts( array(
		'post_type'      => 'art_membro',
		'posts_per_page' => 8,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'key'   => '_art_membro_fundador',
				'value' => '1',
			),
		),
	) );
}

/** Avatar de um fundador — idêntico ao partial estático (foto 66px com anel de 1px; nome no hover). */
function artemis_oqf_avatar( $post ) {
	$thumb = get_post_thumbnail_id( $post );
	if ( ! $thumb ) {
		return '';
	}
	return sprintf(
		'<li class="o-que-fazemos__avatar" tabindex="0"><img src="%s" width="66" height="66" alt=""><span class="o-que-fazemos__name">%s</span></li>',
		esc_url( wp_get_attachment_image_url( $thumb, 'thumbnail' ) ),
		esc_html( get_the_title( $post ) )
	);
}

/** Texto simples com quebras de linha viradas em <br>. */
function artemis_oqf_lines( $text ) {
	return preg_replace( '/\R/', '<br>', esc_html( trim( (string) $text ) ) );
}

/** Parágrafos do texto: linha em branco separa parágrafos; **trecho** vira <strong>. */
function artemis_oqf_paragraphs( $text ) {
	$out = array();
	foreach ( preg_split( '/\R\s*\R/', trim( (string) $text ) ) as $para ) {
		$para = trim( $para );
		if ( '' === $para ) {
			continue;
		}
		$out[] = preg_replace( '/\*\*(.+?)\*\*/s', '<strong>$1</strong>', artemis_oqf_lines( $para ) );
	}
	return $out;
}

// Fundadores — fotos do Figma (8213:358), na ordem Willy, Enzo, Julio, Helio.
add_filter( 'artemis_seed', function ( $log ) {
	$fundadores = array(
		'Willy' => 'willy',
		'Enzo'  => 'enzo',
		'Julio' => 'julio',
		'Helio' => 'helio',
	);
	$order = 0;
	foreach ( $fundadores as $nome => $arquivo ) {
		artemis_seed_post( array(
			'post_type' => 'art_membro',
			'title'     => $nome,
			'order'     => $order++,
			'image'     => 'images/02-o-que-fazemos/' . $arquivo . '.png',
			'meta'      => array(
				'_art_membro_cargo'    => 'Sócio-fundador',
				'_art_membro_fundador' => true,
			),
		), $log );
	}
	return $log;
} );
