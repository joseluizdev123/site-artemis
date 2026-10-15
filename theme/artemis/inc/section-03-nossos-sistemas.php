<?php
/**
 * Seção 03 — Nossos sistemas (Figma 8294:500).
 * Conteúdo: Customizer art_sistemas_* (título, texto, legenda) + CPT art_sistema (cards 8294:507).
 * Card: título = título do post, descrição = resumo (excerpt), ilustração = imagem destacada, ordem = menu_order.
 * Mobile (8635:1999): ilustração própria opcional (_art_sistema_img_m) — o frame "ilustra" do celular
 * tem 343 e mostra mais da composição que o de 320 do desktop; vira <source> até 767px.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'art_sistema', array(
		'labels'       => array(
			'name'                  => __( 'Sistemas', 'artemis' ),
			'singular_name'         => __( 'Sistema', 'artemis' ),
			'menu_name'             => __( 'Sistemas', 'artemis' ),
			'add_new'               => __( 'Adicionar sistema', 'artemis' ),
			'add_new_item'          => __( 'Adicionar sistema', 'artemis' ),
			'edit_item'             => __( 'Editar sistema', 'artemis' ),
			'new_item'              => __( 'Novo sistema', 'artemis' ),
			'view_item'             => __( 'Ver sistema', 'artemis' ),
			'search_items'          => __( 'Buscar sistemas', 'artemis' ),
			'not_found'             => __( 'Nenhum sistema encontrado', 'artemis' ),
			'not_found_in_trash'    => __( 'Nenhum sistema na lixeira', 'artemis' ),
			'all_items'             => __( 'Todos os sistemas', 'artemis' ),
			'featured_image'        => __( 'Ilustração', 'artemis' ),
			'set_featured_image'    => __( 'Definir ilustração', 'artemis' ),
			'remove_featured_image' => __( 'Remover ilustração', 'artemis' ),
			'use_featured_image'    => __( 'Usar como ilustração', 'artemis' ),
		),
		'public'       => false,
		'show_ui'      => true,
		'menu_icon'    => 'dashicons-screenoptions',
		'supports'     => array( 'title', 'excerpt', 'thumbnail', 'page-attributes' ),
		'show_in_rest' => true,
	) );

	register_post_meta( 'art_sistema', '_art_sistema_img_m', array(
		'type'          => 'integer',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
	) );
} );

// Meta boxes nativos com rótulos para quem edita: o resumo vira "Descrição do card".
add_action( 'add_meta_boxes_art_sistema', function () {
	remove_meta_box( 'postexcerpt', 'art_sistema', 'normal' );
	add_meta_box( 'postexcerpt', __( 'Descrição do card', 'artemis' ), 'post_excerpt_meta_box', 'art_sistema', 'normal', 'high' );
	add_meta_box( 'art_sistema_img_m', __( 'Ilustração no celular', 'artemis' ), 'artemis_sistemas_img_m_box', 'art_sistema', 'side', 'low' );
	add_meta_box( 'art_sistema_ajuda', __( 'Como este card aparece no site', 'artemis' ), 'artemis_sistemas_help_box', 'art_sistema', 'side', 'low' );
} );

/** Seletor da Mídia para a ilustração do celular (opcional). */
function artemis_sistemas_img_m_box( $post ) {
	wp_nonce_field( 'art_sistema_img_m', 'art_sistema_img_m_nonce' );
	$value = (int) get_post_meta( $post->ID, '_art_sistema_img_m', true );
	$src   = $value ? wp_get_attachment_image_url( $value, 'medium' ) : '';
	printf(
		'<p><span data-artemis-preview="art_sistema_img_m">%1$s</span><input type="hidden" id="art_sistema_img_m" name="art_sistema_img_m" value="%2$s"><button type="button" class="button" data-artemis-media="art_sistema_img_m">%3$s</button> <button type="button" class="button-link" data-artemis-media-clear="art_sistema_img_m">%4$s</button></p>',
		$src ? '<img src="' . esc_url( $src ) . '" alt="" style="display:block;max-width:100%;height:auto;margin:0 0 8px">' : '',
		esc_attr( $value ? (string) $value : '' ),
		esc_html__( 'Escolher imagem', 'artemis' ),
		esc_html__( 'Remover', 'artemis' )
	);
	echo '<p class="description">' . esc_html__( 'Quadrada, 686 × 686 px. Aparece em telas de até 767 px; vazia, o celular usa a ilustração principal.', 'artemis' ) . '</p>';
}

// Seletor da Mídia (wp.media) só na tela de edição de sistemas.
add_action( 'admin_enqueue_scripts', function () {
	$screen = get_current_screen();
	if ( ! $screen || 'art_sistema' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
	wp_register_script( 'artemis-sistema-admin', false, array( 'media-views' ), ARTEMIS_VERSION, true );
	wp_enqueue_script( 'artemis-sistema-admin' );
	wp_add_inline_script( 'artemis-sistema-admin', <<<'JS'
document.addEventListener('click', function (e) {
  var pick = e.target.closest('[data-artemis-media]');
  var clear = e.target.closest('[data-artemis-media-clear]');
  if (!pick && !clear) return;
  e.preventDefault();
  var id = (pick || clear).getAttribute(pick ? 'data-artemis-media' : 'data-artemis-media-clear');
  var input = document.getElementById(id);
  var box = document.querySelector('[data-artemis-preview="' + id + '"]');
  if (clear) {
    input.value = '';
    box.innerHTML = '';
    return;
  }
  var frame = wp.media({ title: pick.textContent, library: { type: 'image' }, multiple: false });
  frame.on('select', function () {
    var att = frame.state().get('selection').first().toJSON();
    var url = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
    var img = document.createElement('img');
    img.src = url;
    img.alt = '';
    img.style.cssText = 'display:block;max-width:100%;height:auto;margin:0 0 8px';
    input.value = att.id;
    box.innerHTML = '';
    box.appendChild(img);
  });
  frame.open();
});
JS
	);
} );

add_action( 'save_post_art_sistema', function ( $post_id ) {
	if ( ! isset( $_POST['art_sistema_img_m_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['art_sistema_img_m_nonce'] ), 'art_sistema_img_m' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	// Vazio grava 0 (removida de propósito): o seeder só preenche quando a chave nem existe.
	$value = isset( $_POST['art_sistema_img_m'] ) ? absint( wp_unslash( $_POST['art_sistema_img_m'] ) ) : 0;
	update_post_meta( $post_id, '_art_sistema_img_m', $value );
} );

/** Instruções do card (somente leitura — não grava nada). */
function artemis_sistemas_help_box() {
	echo '<p>' . esc_html__( 'Título: nome do sistema, em uma linha curta.', 'artemis' ) . '</p>';
	echo '<p>' . esc_html__( 'Descrição do card: duas a cinco linhas sobre o que o sistema decide.', 'artemis' ) . '</p>';
	echo '<p>' . esc_html__( 'Ilustração: imagem quadrada de 640 × 640 px (aparece com 320 px de largura).', 'artemis' ) . '</p>';
	echo '<p>' . esc_html__( 'Ilustração no celular (opcional): 686 × 686 px, mesmo desenho com um pouco mais de área nas bordas.', 'artemis' ) . '</p>';
	echo '<p>' . esc_html__( 'Ordem: número no campo "Ordem" em Atributos — menor aparece primeiro.', 'artemis' ) . '</p>';
}

add_filter( 'artemis_customizer_defaults', function ( $d ) {
	return $d + array(
		'art_sistemas_title'   => 'O que nossos {sistemas decidem}',
		'art_sistemas_text'    => 'Uma construtora de prédios corporativos e uma fábrica de válvulas não têm nada em comum como negócio. Como problema, têm: as duas erram na codificação de item pelo mesmo motivo. Por isso trabalhamos por formato de decisão, não por ramo de atividade.',
		'art_sistemas_caption' => 'Se a sua operação tem uma dessas quatro, já resolvemos antes.',
	);
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$s['artemis_sistemas'] = array(
		__( 'Home — 03 Nossos sistemas', 'artemis' ),
		array(
			'art_sistemas_title'   => array( __( 'Título — coloque {entre chaves} o trecho em cinza', 'artemis' ), 'text' ),
			'art_sistemas_text'    => array( __( 'Texto à direita do título', 'artemis' ), 'textarea' ),
			'art_sistemas_caption' => array( __( 'Frase abaixo dos cards (itálico)', 'artemis' ), 'text' ),
		),
	);
	return $s;
} );

/** Sistemas publicados, na ordem do campo "Ordem" (menu_order). */
function artemis_sistemas_get_items() {
	return get_posts( array(
		'post_type'      => 'art_sistema',
		'posts_per_page' => 12,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'ASC',
		),
		'no_found_rows'  => true,
	) );
}

/**
 * Conteúdo do <picture class="sistemas__media"> — mesmo markup do partial estático:
 * <source> da ilustração do celular (até 767px, se houver) + img.sistemas__img (imagem destacada).
 */
function artemis_sistemas_image( $post ) {
	$thumb = get_post_thumbnail_id( $post );
	if ( ! $thumb ) {
		return '';
	}
	$source = '';
	$mobile = (int) get_post_meta( get_post( $post )->ID, '_art_sistema_img_m', true );
	$m_src  = $mobile ? wp_get_attachment_image_src( $mobile, 'full' ) : false;
	if ( $m_src ) {
		$srcset = wp_get_attachment_image_srcset( $mobile, 'full' );
		$source = sprintf(
			'<source media="(max-width: 767px)" srcset="%1$s" sizes="100vw" width="%2$d" height="%3$d">',
			$srcset ? esc_attr( $srcset ) : esc_url( $m_src[0] ),
			(int) $m_src[1],
			(int) $m_src[2]
		);
	}
	return $source . wp_get_attachment_image( $thumb, 'full', false, array(
		'class'    => 'sistemas__img',
		'sizes'    => '(max-width: 767px) 100vw, (max-width: 1199px) 50vw, 320px',
		'loading'  => 'lazy',
		'decoding' => 'async',
	) );
}

// Seed: os 4 sistemas do Figma (8294:507), ilustrações exportadas dos frames "ilustra"
// (desktop 8294:509… @2x e celular 8635:2008… @2x).
add_filter( 'artemis_seed', function ( $log ) {
	$sistemas = array(
		array(
			'Validação documental',
			'Valide documento de terceiro contra a regra do contrato, em escala. Compras, qualidade, RH, jurídico, financeiro — qualquer área que recebe documento de fornecedor.',
			'images/03-nossos-sistemas/ilustra-validacao-documental.png',
			'images/03-nossos-sistemas/m-ilustra-validacao-documental.png',
		),
		array(
			'Quantificação de projeto',
			'Extraia quantidade e tipo de item de um desenho técnico. Orçamento de obra, elétrica, hidráulica, metalurgia, projeto industrial.',
			'images/03-nossos-sistemas/ilustra-quantificacao-projeto.png',
			'images/03-nossos-sistemas/m-ilustra-quantificacao-projeto.png',
		),
		array(
			'Codificação de produto',
			'Codifique item configurável a partir dos parâmetros técnicos. Válvula, bomba, motor, painel, esquadria, redutor — qualquer catálogo com combinação.',
			'images/03-nossos-sistemas/ilustra-codificacao-produto.png',
			'images/03-nossos-sistemas/m-ilustra-codificacao-produto.png',
		),
		array(
			'Alocação e roteirização',
			'Distribua carga, rota e recurso dentro das restrições reais da operação. Logística, PCP, escala de equipe, frota, agendamento.',
			'images/03-nossos-sistemas/ilustra-alocacao-roteirizacao.png',
			'images/03-nossos-sistemas/m-ilustra-alocacao-roteirizacao.png',
		),
	);
	foreach ( $sistemas as $order => $s ) {
		$id = artemis_seed_post( array(
			'post_type' => 'art_sistema',
			'title'     => $s[0],
			'excerpt'   => $s[1],
			'order'     => $order,
			'image'     => $s[2],
		), $log );
		// Ilustração do celular: também completa sistemas criados antes dela existir.
		// Sem o arquivo no tema (assets ainda não sincronizados), tenta de novo na próxima execução.
		if ( $id && ! metadata_exists( 'post', $id, '_art_sistema_img_m' ) && file_exists( ARTEMIS_DIR . '/assets/' . $s[3] ) ) {
			$media = artemis_seed_media( $s[3], $s[0] . ' (celular)' );
			if ( $media ) {
				update_post_meta( $id, '_art_sistema_img_m', $media );
				$log[] = sprintf( 'Ilustração do celular definida: %s', $s[0] );
			}
		}
	}
	return $log;
} );

/*
 * Ilustração animada (cards [data-anim] — scroll + hover, assets/js/sections/03-nossos-sistemas.js).
 * Cada sistema pode usar uma das composições em camadas de template-parts/components/ilustra-<chave>.php
 * (estáticas em partials/ilustra-<chave>.html); vazio = imagem destacada, como antes.
 */
function artemis_sistemas_ilustras() {
	return array(
		''              => __( 'Nenhuma (usar a imagem destacada)', 'artemis' ),
		'validacao'     => __( 'Validação documental — laser lendo os documentos', 'artemis' ),
		'quantificacao' => __( 'Quantificação de projeto — pinos subindo na planta', 'artemis' ),
		'codificacao'   => __( 'Codificação de produto — barra carregando', 'artemis' ),
		'roteirizacao'  => __( 'Alocação e roteirização — carga percorrendo a rota', 'artemis' ),
	);
}

add_action( 'init', function () {
	register_post_meta( 'art_sistema', '_art_sistema_ilustra', array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'sanitize_callback' => 'sanitize_key',
		'auth_callback'     => function () {
			return current_user_can( 'edit_posts' );
		},
	) );
} );

add_action( 'add_meta_boxes_art_sistema', function () {
	add_meta_box( 'art_sistema_ilustra', __( 'Ilustração animada', 'artemis' ), function ( $post ) {
		wp_nonce_field( 'art_sistema_ilustra', 'art_sistema_ilustra_nonce' );
		$value = get_post_meta( $post->ID, '_art_sistema_ilustra', true );
		echo '<p><select name="art_sistema_ilustra" class="widefat">';
		foreach ( artemis_sistemas_ilustras() as $key => $label ) {
			printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $value, $key, false ), esc_html( $label ) );
		}
		echo '</select></p><p class="description">' . esc_html__( 'Com uma ilustração animada escolhida, a imagem destacada deixa de aparecer no site.', 'artemis' ) . '</p>';
	}, 'art_sistema', 'side', 'low' );
} );

add_action( 'save_post_art_sistema', function ( $post_id ) {
	if ( ! isset( $_POST['art_sistema_ilustra_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['art_sistema_ilustra_nonce'] ), 'art_sistema_ilustra' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$value = isset( $_POST['art_sistema_ilustra'] ) ? sanitize_key( wp_unslash( $_POST['art_sistema_ilustra'] ) ) : '';
	update_post_meta( $post_id, '_art_sistema_ilustra', array_key_exists( $value, artemis_sistemas_ilustras() ) ? $value : '' );
} );

/** Chave da ilustração animada do sistema, só se o template existir no tema; '' = usar a imagem. */
function artemis_sistemas_ilustra( $post ) {
	$key = sanitize_key( (string) get_post_meta( get_post( $post )->ID, '_art_sistema_ilustra', true ) );
	if ( '' === $key || ! array_key_exists( $key, artemis_sistemas_ilustras() ) ) {
		return '';
	}
	return file_exists( ARTEMIS_DIR . '/template-parts/components/ilustra-' . $key . '.php' ) ? $key : '';
}

// Seed: liga os 4 sistemas do Figma às ilustrações animadas (só preenche o que está vazio).
add_filter( 'artemis_seed', function ( $log ) {
	$map = array(
		'Validação documental'     => 'validacao',
		'Quantificação de projeto' => 'quantificacao',
		'Codificação de produto'   => 'codificacao',
		'Alocação e roteirização'  => 'roteirizacao',
	);
	foreach ( $map as $title => $key ) {
		$found = get_posts( array( 'post_type' => 'art_sistema', 'title' => $title, 'numberposts' => 1, 'post_status' => 'any', 'fields' => 'ids' ) );
		if ( $found && '' === (string) get_post_meta( $found[0], '_art_sistema_ilustra', true ) ) {
			update_post_meta( $found[0], '_art_sistema_ilustra', $key );
			$log[] = sprintf( 'Ilustração animada ligada: %s', $title );
		}
	}
	return $log;
}, 20 );
