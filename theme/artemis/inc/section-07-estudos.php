<?php
/**
 * Seção 07 — Estudos de impacto (Figma 8195:1063).
 * Conteúdo: Customizer art_estudos_* + CPT art_estudo (um post por slide do carrossel 8201:3770).
 *
 * CPT art_estudo
 *   título          → manchete (8201:3601)
 *   conteúdo        → parágrafos (8201:3602)
 *   imagem destacada → captura de tela do sistema (8201:3690)
 *   meta            → cliente, sistema (8201:3603) · categoria, status (8201:3596)
 *                     fundo (pintura 8201:3689) · zoom da captura (Gascat 8201:3580 = 138,69%)
 *   menu_order      → posição no carrossel, editada na caixa "Dados do estudo" (o editor de blocos não mostra "Ordem");
 *                     "Estudo N" sai da posição entre os publicados — não há número digitado
 */

defined( 'ABSPATH' ) || exit;

/* --------------------------------------------------------------------------
   Customizer
   -------------------------------------------------------------------------- */
add_filter( 'artemis_customizer_defaults', function ( $d ) {
	return $d + array(
		'art_estudos_eyebrow' => 'Estudos de impacto',
		'art_estudos_title'   => 'O que já está {em produção}',
	);
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$s['artemis_estudos'] = array(
		__( 'Home — 07 Estudos de impacto', 'artemis' ),
		array(
			'art_estudos_eyebrow' => array( __( 'Frase de abertura (itálico)', 'artemis' ), 'text' ),
			'art_estudos_title'   => array( __( 'Título — coloque {entre chaves} o trecho em cinza', 'artemis' ), 'text' ),
		),
	);
	return $s;
} );

/* --------------------------------------------------------------------------
   CPT art_estudo
   -------------------------------------------------------------------------- */
add_action( 'init', function () {
	register_post_type( 'art_estudo', array(
		'labels'       => array(
			'name'                  => __( 'Estudos de impacto', 'artemis' ),
			'singular_name'         => __( 'Estudo de impacto', 'artemis' ),
			'menu_name'             => __( 'Estudos', 'artemis' ),
			'add_new'               => __( 'Adicionar estudo', 'artemis' ),
			'add_new_item'          => __( 'Adicionar estudo', 'artemis' ),
			'edit_item'             => __( 'Editar estudo', 'artemis' ),
			'all_items'             => __( 'Todos os estudos', 'artemis' ),
			'featured_image'        => __( 'Captura de tela do sistema', 'artemis' ),
			'set_featured_image'    => __( 'Definir captura de tela', 'artemis' ),
			'remove_featured_image' => __( 'Remover captura de tela', 'artemis' ),
			'use_featured_image'    => __( 'Usar como captura de tela', 'artemis' ),
			'item_published'        => __( 'Estudo publicado.', 'artemis' ),
			'item_published_privately' => __( 'Estudo publicado como privado.', 'artemis' ),
			'item_reverted_to_draft' => __( 'Estudo voltou a ser rascunho.', 'artemis' ),
			'item_scheduled'        => __( 'Estudo agendado.', 'artemis' ),
			'item_updated'          => __( 'Estudo atualizado.', 'artemis' ),
		),
		'public'       => false,
		'show_ui'      => true,
		'menu_icon'    => 'dashicons-chart-line',
		'supports'     => array( 'title', 'editor', 'thumbnail' ), // posição: campo próprio na caixa "Dados do estudo"
		'show_in_rest' => true,
	) );

	foreach ( artemis_estudos_fields() as $key => $field ) {
		register_post_meta( 'art_estudo', $key, array(
			'type'          => $field['type'],
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		) );
	}
} );

/** Campos do estudo: chave meta => config. */
function artemis_estudos_fields() {
	return array(
		'_art_estudo_cliente'   => array( 'type' => 'string', 'label' => __( 'Cliente (ex.: Athié Wohnrath) — opcional', 'artemis' ), 'input' => 'text' ),
		'_art_estudo_sistema'   => array( 'type' => 'string', 'label' => __( 'Nome do sistema (ex.: AW Control) — opcional', 'artemis' ), 'input' => 'text' ),
		'_art_estudo_categoria' => array( 'type' => 'string', 'label' => __( 'Categoria no rodapé (ex.: Validação de documentos)', 'artemis' ), 'input' => 'text' ),
		'_art_estudo_status'    => array( 'type' => 'string', 'label' => __( 'Status no rodapé (ex.: Em produção)', 'artemis' ), 'input' => 'text' ),
		'_art_estudo_fundo'     => array( 'type' => 'integer', 'label' => __( 'Pintura de fundo (atrás da captura de tela)', 'artemis' ), 'input' => 'image' ),
		'_art_estudo_zoom'      => array( 'type' => 'number', 'label' => __( 'Zoom da captura de tela em % (vazio ou 100 = altura do quadro)', 'artemis' ), 'input' => 'number' ),
	);
}

add_filter( 'enter_title_here', function ( $text, $post ) {
	return 'art_estudo' === $post->post_type ? __( 'Manchete do estudo (ex.: R$ 40 mil por mês em um único tipo de documento)', 'artemis' ) : $text;
}, 10, 2 );

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'art_estudo_meta', __( 'Dados do estudo', 'artemis' ), 'artemis_estudos_meta_box', 'art_estudo', 'normal', 'high' );
} );

/** Meta box nativo — posição no carrossel, campos de texto + seletor de imagem da Mídia para a pintura de fundo. */
function artemis_estudos_meta_box( $post ) {
	wp_nonce_field( 'art_estudo_meta', 'art_estudo_nonce' );
	$ids      = artemis_estudos_ordered_ids();
	$index    = array_search( (int) $post->ID, $ids, true );
	$position = false === $index ? count( $ids ) + 1 : $index + 1;
	// _orig = o valor mostrado: o editor de blocos reenvia a caixa a cada "Atualizar"; só move se a pessoa mudou o número
	printf(
		'<p><label for="art_estudo_posicao">%1$s</label><br><input type="number" min="1" max="%2$d" step="1" class="small-text" id="art_estudo_posicao" name="art_estudo_posicao" value="%3$d"><input type="hidden" name="art_estudo_posicao_orig" value="%3$d"> <span class="description">%4$s</span></p>',
		esc_html__( 'Posição no carrossel (1 = primeiro)', 'artemis' ),
		false === $index ? count( $ids ) + 1 : count( $ids ),
		(int) $position,
		esc_html__( 'Os outros estudos andam uma casa para abrir espaço. Rascunhos guardam o lugar, mas não aparecem no site: lá o card mostra "Estudo 1, 2, 3…" contando só os publicados (coluna "No site" em Todos os estudos).', 'artemis' )
	);
	foreach ( artemis_estudos_fields() as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		$id    = ltrim( $key, '_' );
		if ( 'image' === $field['input'] ) {
			$src = $value ? wp_get_attachment_image_url( (int) $value, 'medium' ) : '';
			printf(
				'<p><strong>%1$s</strong><br><span data-artemis-preview="%2$s">%3$s</span><input type="hidden" id="%2$s" name="%2$s" value="%4$s"><br><button type="button" class="button" data-artemis-media="%2$s">%5$s</button> <button type="button" class="button-link" data-artemis-media-clear="%2$s">%6$s</button></p>',
				esc_html( $field['label'] ),
				esc_attr( $id ),
				$src ? '<img src="' . esc_url( $src ) . '" alt="" style="display:block;max-width:240px;height:auto;margin:8px 0">' : '',
				esc_attr( $value ),
				esc_html__( 'Escolher imagem', 'artemis' ),
				esc_html__( 'Remover', 'artemis' )
			);
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
	echo '<p class="description">' . esc_html__( 'O título do post é a manchete do estudo e o editor guarda os parágrafos. A imagem destacada é a captura de tela do sistema. Sem categoria e status, o texto aparece centralizado no card. Só estudos publicados aparecem no site.', 'artemis' ) . '</p>';
}

// Seletor da Mídia (wp.media) só na tela de edição de estudos.
add_action( 'admin_enqueue_scripts', function () {
	$screen = get_current_screen();
	if ( ! $screen || 'art_estudo' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
	wp_register_script( 'artemis-estudo-admin', false, array( 'media-views' ), ARTEMIS_VERSION, true );
	wp_enqueue_script( 'artemis-estudo-admin' );
	wp_add_inline_script( 'artemis-estudo-admin', <<<'JS'
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
    img.style.cssText = 'display:block;max-width:240px;height:auto;margin:8px 0';
    input.value = att.id;
    box.innerHTML = '';
    box.appendChild(img);
  });
  frame.open();
});
JS
	);
} );

add_action( 'save_post_art_estudo', function ( $post_id ) {
	if ( ! isset( $_POST['art_estudo_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['art_estudo_nonce'] ), 'art_estudo_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( artemis_estudos_fields() as $key => $field ) {
		$id  = ltrim( $key, '_' );
		$raw = isset( $_POST[ $id ] ) ? trim( (string) wp_unslash( $_POST[ $id ] ) ) : '';
		switch ( $field['type'] ) {
			case 'integer':
				$value = '' === $raw ? '' : absint( $raw );
				break;
			case 'number':
				$value = '' === $raw ? '' : (float) $raw;
				break;
			default:
				$value = sanitize_text_field( $raw );
		}
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
	$posicao = isset( $_POST['art_estudo_posicao'] ) && is_scalar( $_POST['art_estudo_posicao'] ) ? trim( (string) wp_unslash( $_POST['art_estudo_posicao'] ) ) : '';
	$antes   = isset( $_POST['art_estudo_posicao_orig'] ) && is_scalar( $_POST['art_estudo_posicao_orig'] ) ? trim( (string) wp_unslash( $_POST['art_estudo_posicao_orig'] ) ) : '';
	if ( ctype_digit( $posicao ) && (int) $posicao >= 1 && $posicao !== $antes ) { // só número inteiro, e só se a pessoa mudou
		artemis_estudos_move( $post_id, (int) $posicao );
	}
} );

/* --------------------------------------------------------------------------
   Posição no carrossel (menu_order)
   -------------------------------------------------------------------------- */

/** Status que ocupam posição (tudo que aparece em "Todos os estudos", menos a Lixeira). */
function artemis_estudos_statuses() {
	return array( 'publish', 'future', 'draft', 'pending', 'private' );
}

/** IDs na ordem do carrossel (mesma ordenação de artemis_estudos_get), incluindo os não publicados. */
function artemis_estudos_ordered_ids() {
	return array_map( 'intval', get_posts( array(
		'post_type'        => 'art_estudo',
		'post_status'      => artemis_estudos_statuses(),
		'posts_per_page'   => -1,
		'fields'           => 'ids',
		'orderby'          => array(
			'menu_order' => 'ASC',
			'date'       => 'ASC',
			'ID'         => 'ASC',
		),
		'suppress_filters' => true,
	) ) );
}

/** Põe o estudo na posição pedida (1 = primeiro) e renumera os outros 0, 1, 2… sem disparar save_post de novo. */
function artemis_estudos_move( $post_id, $position ) {
	global $wpdb;
	if ( $position < 1 ) {
		return;
	}
	$ids = array_values( array_diff( artemis_estudos_ordered_ids(), array( (int) $post_id ) ) );
	array_splice( $ids, min( $position - 1, count( $ids ) ), 0, array( (int) $post_id ) );
	foreach ( $ids as $order => $id ) {
		if ( (int) get_post_field( 'menu_order', $id, 'raw' ) !== $order ) {
			$wpdb->update( $wpdb->posts, array( 'menu_order' => $order ), array( 'ID' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			clean_post_cache( $id );
		}
	}
}

// Estudo novo (Adicionar estudo, REST, importação) entra no fim, não empatado com o 1º. Quem passa menu_order (o seed) manda.
add_filter( 'wp_insert_post_data', function ( $data, $postarr, $unsanitized_postarr = array(), $update = false ) {
	if ( 'art_estudo' !== $data['post_type'] || $update || ! empty( $postarr['ID'] ) || isset( $unsanitized_postarr['menu_order'] ) ) {
		return $data;
	}
	global $wpdb;
	$max = $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		"SELECT MAX(menu_order) FROM {$wpdb->posts} WHERE post_type = %s AND post_status NOT IN ('trash', 'auto-draft', 'inherit')",
		'art_estudo'
	) );
	$data['menu_order'] = null === $max ? 0 : (int) $max + 1;
	return $data;
}, 10, 4 );

// "Todos os estudos" na ordem do carrossel, com a coluna Posição.
add_action( 'pre_get_posts', function ( $query ) {
	if ( is_admin() && $query->is_main_query() && 'art_estudo' === $query->get( 'post_type' ) && ! isset( $_GET['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$query->set( 'orderby', array(
			'menu_order' => 'ASC',
			'date'       => 'ASC',
			'ID'         => 'ASC',
		) );
	}
} );

add_filter( 'manage_art_estudo_posts_columns', function ( $columns ) {
	$out = array();
	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$out['art_posicao'] = __( 'Posição', 'artemis' );
		}
		$out[ $key ] = $label;
		if ( 'title' === $key ) {
			$out['art_no_site'] = __( 'No site', 'artemis' );
		}
	}
	return $out;
} );

add_action( 'manage_art_estudo_posts_custom_column', function ( $column, $post_id ) {
	static $ids = null, $pub = null;
	if ( 'art_posicao' === $column ) {
		if ( null === $ids ) {
			$ids = artemis_estudos_ordered_ids();
		}
		$index = array_search( (int) $post_id, $ids, true );
		echo false === $index ? '—' : (int) $index + 1;
	} elseif ( 'art_no_site' === $column ) {
		if ( null === $pub ) {
			$pub = array_map( 'intval', wp_list_pluck( artemis_estudos_get(), 'ID' ) ); // o que a Home mostra, na ordem
		}
		$index = array_search( (int) $post_id, $pub, true );
		if ( false !== $index ) {
			/* translators: %d: número do estudo no card. */
			echo esc_html( sprintf( __( 'Estudo %d', 'artemis' ), $index + 1 ) );
		} else {
			echo esc_html( 'publish' === get_post_status( $post_id ) ? __( 'Não aparece (limite de 12)', 'artemis' ) : __( 'Não aparece (não publicado)', 'artemis' ) );
		}
	}
}, 10, 2 );

add_action( 'admin_head', function () {
	$screen = get_current_screen();
	if ( $screen && 'edit-art_estudo' === $screen->id ) {
		echo '<style>.column-art_posicao{width:5.5em}.column-art_no_site{width:12em}</style>';
	}
} );

/* --------------------------------------------------------------------------
   Helpers do template-part (template-parts/home/07-estudos.php)
   -------------------------------------------------------------------------- */

/** Estudos publicados, na ordem do menu_order. */
function artemis_estudos_get() {
	return get_posts( array(
		'post_type'      => 'art_estudo',
		'posts_per_page' => 12,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'ASC',
			'ID'         => 'ASC',
		),
	) );
}

/** "Estudo 1 · Athié Wohnrath · AW Control" (8201:3603) — número = posição do slide; texto puro, escapar na saída. */
function artemis_estudos_meta_line( $post, $numero ) {
	/* translators: %d: número do estudo (posição no carrossel). */
	$parts = array( sprintf( __( 'Estudo %d', 'artemis' ), (int) $numero ) );
	foreach ( array( '_art_estudo_cliente', '_art_estudo_sistema' ) as $key ) {
		$value = (string) get_post_meta( $post->ID, $key, true );
		if ( '' !== $value ) {
			$parts[] = $value;
		}
	}
	return implode( ' · ', $parts );
}

/** Parágrafos do estudo (8201:3602), já escapados. */
function artemis_estudos_body( $post ) {
	$content = $post->post_content;
	$html    = has_blocks( $content ) ? do_blocks( $content ) : wpautop( $content );
	return wp_kses_post( $html );
}

/** Pintura de fundo (8201:3689), já escapada. */
function artemis_estudos_bg( $post ) {
	$fundo = (int) get_post_meta( $post->ID, '_art_estudo_fundo', true );
	if ( ! $fundo ) {
		return '';
	}
	return wp_get_attachment_image( $fundo, 'full', false, array(
		'class'    => 'estudos__bg',
		'alt'      => '',
		'loading'  => 'lazy',
		'decoding' => 'async',
	) );
}

/** Captura de tela (imagem destacada) com zoom opcional (--shot-zoom), já escapada. */
function artemis_estudos_shot( $post ) {
	$thumb = get_post_thumbnail_id( $post );
	if ( ! $thumb ) {
		return '';
	}
	$alt = trim( (string) get_post_meta( $thumb, '_wp_attachment_image_alt', true ) );
	if ( '' === $alt ) {
		/* translators: %s: manchete do estudo. */
		$alt = sprintf( __( 'Tela do sistema — %s', 'artemis' ), get_the_title( $post ) );
	}
	$attr = array(
		'class'    => 'estudos__shot-img',
		'alt'      => $alt,
		'loading'  => 'lazy',
		'decoding' => 'async',
	);
	$zoom = (float) get_post_meta( $post->ID, '_art_estudo_zoom', true );
	if ( $zoom > 0 && 100.0 !== $zoom ) {
		$attr['style'] = '--shot-zoom:' . rtrim( rtrim( sprintf( '%.4F', $zoom ), '0' ), '.' ) . '%'; // %F ignora o locale (138.69, nunca 138,69).
	}
	return wp_get_attachment_image( $thumb, 'full', false, $attr );
}

/* --------------------------------------------------------------------------
   Seed — os 4 estudos do Figma (variantes do componente 8201:3769)
   -------------------------------------------------------------------------- */
add_filter( 'artemis_seed', function ( $log ) {
	$estudos = array(
		array(
			'slug'       => 'aw-control',
			'cliente'    => 'Athié Wohnrath',
			'sistema'    => 'AW Control',
			'titulo'     => 'R$ 40 mil por mês em um único tipo de documento',
			'paragrafos' => array(
				'A Athie Wohnrath é a maior incorporadora de prédios corporativos da América Latina. Uma área inteira validava documentos de fornecedores — certificados de segurança, olerite, cartão de ponto, programa de gerenciamento de risco — alguns com mais de mil páginas, com validação cruzada entre eles.',
				'O sistema hoje faz essa validação com 97% de assertividade, medida contra gabarito humano. Detecta assinatura fraudada comparando a imagem, valida assinatura digital contra a API do Gov.br e roda pipeline próprio para documentos de mil páginas.',
				'R$ 40 mil por mês economizados, e ainda nem todos os tipos de documento estão rodando.',
			),
			'categoria'  => 'Validação de documentos',
			'status'     => 'Em produção',
			'zoom'       => 0,
			'alt'        => 'Tela do AW Control: estatísticas de validação de documentos',
		),
		array(
			'slug'       => 'supply-map',
			'cliente'    => 'Athié Wohnrath',
			'sistema'    => 'Supply MEP',
			'titulo'     => "3 horas de engenheiro viraram 25\u{00A0}minutos",
			'paragrafos' => array(
				'Para orçar uma obra, o engenheiro recebe a planta arquitetônica e conta manualmente equipamentos, dutos e cabos. Erro de contagem vira orçamento errado: prejuízo de um lado, perda de competitividade do outro.',
				'O Supply Map corta o desenho em partes com sobreposição, analisa cada uma, deduplica por coordenada e identifica tipo e dimensão de cada duto. Cada item vem com grau de confiança: alta quando a tag estava clara, baixa quando o sistema precisou supor a partir dos elementos vizinhos. O engenheiro clica na tag e vê onde ela está no desenho.',
				'25 minutos por planta, contra 3 horas de engenheiro. A validação cruzada ainda pega o projetista que esqueceu de listar um equipamento na tabela.',
			),
			'categoria'  => 'Quantitativos de planta',
			'status'     => 'MVP → escala',
			'zoom'       => 0,
			'alt'        => 'Tela do Supply MEP: projeto em revisão com linha do tempo e processamentos',
		),
		array(
			'slug'       => 'gascat',
			'cliente'    => 'Gascat',
			'sistema'    => '',
			'titulo'     => '“Isso aqui está tomando mais decisão do que a nossa engenharia”',
			'paragrafos' => array(
				'A Gascat fabrica válvulas industriais há mais de 30 anos. São 30 famílias de produto, mais de 10 parâmetros por família e mais de 10 possibilidades por parâmetro. Um engenheiro formado leva dois anos para dominar a lógica do catálogo.',
				'A codificação era manual. Trocar uma letra transformava bronze em inox. R$ 12 milhões em estoque parado vieram desses erros, e uma válvula de R$ 50 mil voltou de navio da Escócia depois de estourar e parar a produção de um cliente.',
				'O pedido inicial foi “diminuir estoque com IA”. O diagnóstico mostrou que o problema estava na codificação, na origem. Hoje o sistema tem base de peças com descrição — que antes não existia em lugar nenhum além da cabeça das pessoas —, condicionais que bloqueiam combinação impossível e diagnóstico por família. A frase do título é do Celso, dono e engenheiro que criou a maior parte dos produtos.',
			),
			'categoria'  => 'Validação de documentos',
			'status'     => 'Em produção',
			'zoom'       => 138.69,
			'alt'        => 'Tela do sistema da Gascat: nova codificação com análise por IA',
		),
		array(
			'slug'       => 'estudo-4',
			'cliente'    => '',
			'sistema'    => '',
			'titulo'     => 'Um quarto sistema está em MVP numa multinacional de alimentos, e roda sem nenhuma IA em produção:',
			'paragrafos' => array(
				'lógica determinística pura. O que ele precisava era da regra de fracionamento de carga escrita corretamente, coisa que até então era estimativa de quem carregava o caminhão.',
			),
			'categoria'  => '',
			'status'     => '',
			'zoom'       => 0,
			'alt'        => 'Tela do sistema de fracionamento de carga: painel de cargas',
		),
	);

	foreach ( $estudos as $order => $e ) {
		$content = '';
		foreach ( $e['paragrafos'] as $paragrafo ) {
			$content .= "<!-- wp:paragraph -->\n<p>" . esc_html( $paragrafo ) . "</p>\n<!-- /wp:paragraph -->\n\n";
		}

		$meta = array(
			'_art_estudo_fundo' => artemis_seed_media( 'images/07-estudos/bg-' . $e['slug'] . '.png', 'Fundo — estudo ' . ( $order + 1 ) ),
		);
		foreach ( array( 'cliente', 'sistema', 'categoria', 'status' ) as $campo ) {
			if ( '' !== $e[ $campo ] ) {
				$meta[ '_art_estudo_' . $campo ] = $e[ $campo ];
			}
		}
		if ( $e['zoom'] ) {
			$meta['_art_estudo_zoom'] = $e['zoom'];
		}

		$id = artemis_seed_post( array(
			'post_type' => 'art_estudo',
			'title'     => $e['titulo'],
			'content'   => trim( $content ),
			'order'     => $order,
			'image'     => 'images/07-estudos/shot-' . $e['slug'] . '.png',
			'meta'      => $meta,
		), $log );

		$thumb = $id ? get_post_thumbnail_id( $id ) : 0;
		if ( $thumb && '' === (string) get_post_meta( $thumb, '_wp_attachment_image_alt', true ) ) {
			update_post_meta( $thumb, '_wp_attachment_image_alt', $e['alt'] );
		}
	}
	return $log;
} );
