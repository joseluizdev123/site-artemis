<?php
/**
 * Post (artigo do blog) — Figma 8331:321 · mobile 8673:5776.
 * Template: single.php → template-parts/post/NN-slug.php + newsletter · espelha
 * blog/anatomia-do-wcontrol-como-se-valida-um-documento-de-mil-paginas/index.html.
 *
 * Post nativo       título (8331:344) · categoria + data (8331:1123) · autor (8331:1169)
 *                   conteúdo do editor de blocos = texto do artigo (8436:1612): parágrafo, título, lista, imagem
 *                   estilos de bloco: título "Destaque" (40px, fora do índice — 8331:1185) · imagem "Recorte do layout" (859×600)
 * Post meta         _art_post_lead → abertura do topo (8331:345); vazia usa o resumo
 *                   _art_post_numeros → bloco "Números" (8331:1248), inserido no texto com [artemis_numeros]
 *                   _art_post_cta_title / _art_post_cta_text / _art_post_cta_off → chamada final (8435:1493)
 * Customizer        art_post_toc_title · art_post_related_title · art_post_cta_* (padrões da chamada final)
 * Índice            "Neste artigo" (8331:1264) = títulos H2 do texto, exceto os de estilo "Destaque" (ids automáticos)
 * Continue lendo    3 posts da mesma categoria (completa com os mais recentes) — card .post-card
 */

defined( 'ABSPATH' ) || exit;

/* --------------------------------------------------------------------------
   Customizer
   -------------------------------------------------------------------------- */
add_filter( 'artemis_customizer_defaults', function ( $d ) {
	return $d + array(
		'art_post_toc_title'     => 'Neste artigo',
		'art_post_related_title' => 'Continue lendo',
		'art_post_cta_title'     => 'Sua operação recebe documento de fornecedor em volume?',
		'art_post_cta_text'      => 'O diagnóstico começa com um dia dentro da operação, com as pessoas que decidem. Você sai com o mapa dos processos que mais consomem hora sênior e o custo estimado de cada um.',
		'art_post_cta_btn2'      => 'Ver os estudos de impacto',
		'art_post_cta_btn2_url'  => '/#estudos',
	);
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$s['artemis_post'] = array(
		__( 'Blog — Página do artigo', 'artemis' ),
		array(
			'art_post_toc_title'     => array( __( 'Título do índice (lista os títulos H2 do texto)', 'artemis' ), 'text' ),
			'art_post_related_title' => array( __( 'Título dos artigos relacionados', 'artemis' ), 'text' ),
			'art_post_cta_title'     => array( __( 'Chamada final — título padrão (cada artigo pode trocar)', 'artemis' ), 'text' ),
			'art_post_cta_text'      => array( __( 'Chamada final — texto padrão', 'artemis' ), 'textarea' ),
			'art_post_cta_btn2'      => array( __( 'Chamada final — botão secundário', 'artemis' ), 'text' ),
			'art_post_cta_btn2_url'  => array( __( 'Chamada final — link do botão secundário', 'artemis' ), 'text' ),
		),
	);
	return $s;
} );

/* No celular o header do artigo tem o selo "BLOG" e 64 de altura (8693:2102): body.page-post ajusta o --header-h. */
add_filter( 'body_class', function ( $classes ) {
	if ( is_singular( 'post' ) ) {
		$classes[] = 'page-post';
	}
	return $classes;
} );

/* --------------------------------------------------------------------------
   Estilos de bloco do editor (mesmas classes do HTML estático)
   -------------------------------------------------------------------------- */
add_action( 'init', function () {
	if ( ! function_exists( 'register_block_style' ) ) {
		return;
	}
	register_block_style( 'core/heading', array(
		'name'  => 'destaque',
		'label' => __( 'Destaque (fora do índice)', 'artemis' ),
	) );
	register_block_style( 'core/image', array(
		'name'  => 'recorte',
		'label' => __( 'Recorte do layout', 'artemis' ),
	) );
} );

/* Imagem "Recorte do layout" entra com recorte de baixo para cima e assenta (motion.js: data-wipe + data-wipe-zoom),
   como a figura do estático (8331:1213). */
add_filter( 'render_block_core/image', function ( $html, $block ) {
	$class = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
	if ( ! preg_match( '/\bis-style-recorte\b/', $class ) ) {
		return $html;
	}
	$tags = new WP_HTML_Tag_Processor( $html );
	if ( $tags->next_tag( 'figure' ) && null === $tags->get_attribute( 'data-wipe' ) ) {
		$tags->set_attribute( 'data-wipe', 'up' );
		$tags->set_attribute( 'data-wipe-zoom', true );
	}
	return $tags->get_updated_html();
}, 10, 2 );

/* --------------------------------------------------------------------------
   Meta do artigo + caixa "Detalhes do artigo"
   -------------------------------------------------------------------------- */

/** Campos de texto do artigo: chave => [rótulo, tipo]. */
function artemis_post_fields() {
	return array(
		'_art_post_lead'     => array( __( 'Abertura (texto abaixo do título; vazio usa o resumo)', 'artemis' ), 'textarea' ),
		'_art_post_cta_title' => array( __( 'Chamada final — título (vazio usa o padrão do Personalizar)', 'artemis' ), 'text' ),
		'_art_post_cta_text'  => array( __( 'Chamada final — texto (vazio usa o padrão)', 'artemis' ), 'textarea' ),
	);
}

/** Números do artigo: rótulo + 3 itens (prefixo, valor, sufixo, descrição). */
function artemis_post_numeros( $post = null ) {
	$post = get_post( $post );
	$data = $post ? get_post_meta( $post->ID, '_art_post_numeros', true ) : array();
	$data = is_array( $data ) ? $data : array();
	$out  = array(
		'label' => isset( $data['label'] ) ? (string) $data['label'] : '',
		'items' => array(),
	);
	for ( $i = 0; $i < 3; $i++ ) {
		$row            = isset( $data['items'][ $i ] ) && is_array( $data['items'][ $i ] ) ? $data['items'][ $i ] : array();
		$out['items'][] = array(
			'pre'   => isset( $row['pre'] ) ? (string) $row['pre'] : '',
			'valor' => isset( $row['valor'] ) ? (string) $row['valor'] : '',
			'suf'   => isset( $row['suf'] ) ? (string) $row['suf'] : '',
			'desc'  => isset( $row['desc'] ) ? (string) $row['desc'] : '',
		);
	}
	return $out;
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'art_post_detalhes', __( 'Detalhes do artigo', 'artemis' ), function ( $post ) {
		wp_nonce_field( 'art_post_detalhes', 'art_post_detalhes_nonce' );
		echo '<p>';
		foreach ( artemis_post_fields() as $key => $field ) {
			$value = (string) get_post_meta( $post->ID, $key, true );
			printf( '<label for="%1$s"><strong>%2$s</strong></label><br>', esc_attr( $key ), esc_html( $field[0] ) );
			if ( 'textarea' === $field[1] ) {
				printf( '<textarea id="%1$s" name="%1$s" rows="3" class="widefat">%2$s</textarea><br><br>', esc_attr( $key ), esc_textarea( $value ) );
			} else {
				printf( '<input type="text" id="%1$s" name="%1$s" value="%2$s" class="widefat"><br><br>', esc_attr( $key ), esc_attr( $value ) );
			}
		}
		printf(
			'<label><input type="checkbox" name="_art_post_cta_off" value="1" %s> %s</label></p>',
			checked( '1', (string) get_post_meta( $post->ID, '_art_post_cta_off', true ), false ),
			esc_html__( 'Esconder a chamada final neste artigo', 'artemis' )
		);

		$num = artemis_post_numeros( $post );
		echo '<hr><p><strong>' . esc_html__( 'Números', 'artemis' ) . '</strong> — ' . esc_html__( 'aparecem onde o bloco "Shortcode" com [artemis_numeros] estiver no texto.', 'artemis' ) . '</p>';
		printf( '<p><label>%s<br><input type="text" name="art_num[label]" value="%s" class="widefat"></label></p>', esc_html__( 'Rótulo (ex.: Números)', 'artemis' ), esc_attr( $num['label'] ) );
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Prefixo (menor)', 'artemis' ) . '</th><th>' . esc_html__( 'Valor', 'artemis' ) . '</th><th>' . esc_html__( 'Sufixo (menor)', 'artemis' ) . '</th><th>' . esc_html__( 'Descrição', 'artemis' ) . '</th></tr></thead><tbody>';
		foreach ( $num['items'] as $i => $row ) {
			echo '<tr>';
			foreach ( array( 'pre', 'valor', 'suf', 'desc' ) as $k ) {
				printf( '<td><input type="text" name="art_num[items][%d][%s]" value="%s" class="widefat"></td>', (int) $i, esc_attr( $k ), esc_attr( $row[ $k ] ) );
			}
			echo '</tr>';
		}
		echo '</tbody></table>';
	}, 'post', 'normal', 'high' );
} );

add_action( 'save_post_post', function ( $post_id ) {
	if ( ! isset( $_POST['art_post_detalhes_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['art_post_detalhes_nonce'] ), 'art_post_detalhes' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( artemis_post_fields() as $key => $field ) {
		$raw   = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$value = 'textarea' === $field[1] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
	if ( ! empty( $_POST['_art_post_cta_off'] ) ) {
		update_post_meta( $post_id, '_art_post_cta_off', '1' );
	} else {
		delete_post_meta( $post_id, '_art_post_cta_off' );
	}

	$raw   = isset( $_POST['art_num'] ) && is_array( $_POST['art_num'] ) ? wp_unslash( $_POST['art_num'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$clean = array(
		'label' => isset( $raw['label'] ) ? sanitize_text_field( $raw['label'] ) : '',
		'items' => array(),
	);
	for ( $i = 0; $i < 3; $i++ ) {
		$row = isset( $raw['items'][ $i ] ) && is_array( $raw['items'][ $i ] ) ? $raw['items'][ $i ] : array();
		$clean['items'][] = array_map( 'sanitize_text_field', array(
			'pre'   => isset( $row['pre'] ) ? $row['pre'] : '',
			'valor' => isset( $row['valor'] ) ? $row['valor'] : '',
			'suf'   => isset( $row['suf'] ) ? $row['suf'] : '',
			'desc'  => isset( $row['desc'] ) ? $row['desc'] : '',
		) );
	}
	update_post_meta( $post_id, '_art_post_numeros', $clean );
} );

/* --------------------------------------------------------------------------
   [artemis_numeros] — Statistics 8331:1248 (mesma marcação do estático). Entrada (motion.js): rótulo em fade e os
   valores contam um por vez (240 ms entre itens, 800 ms por contagem), cada descrição em fade depois do seu valor.
   -------------------------------------------------------------------------- */
add_shortcode( 'artemis_numeros', function () {
	$num   = artemis_post_numeros();
	$items = array_filter( $num['items'], function ( $row ) {
		return '' !== $row['valor'];
	} );
	if ( ! $items ) {
		return '';
	}
	$html = '<div class="post-numeros" data-reveal-group data-stagger="240">';
	if ( '' !== $num['label'] ) {
		$html .= '<p class="post-numeros__label eyebrow" data-reveal="fade">' . esc_html( $num['label'] ) . '</p>';
	}
	$html .= '<dl class="post-numeros__grid">';
	foreach ( $items as $row ) {
		$valor = esc_html( $row['valor'] );
		if ( '' !== $row['pre'] ) {
			$valor = '<small>' . esc_html( $row['pre'] ) . '</small> ' . $valor;
		}
		if ( '' !== $row['suf'] ) {
			$valor .= ' <small>' . esc_html( $row['suf'] ) . '</small>';
		}
		$html .= '<div class="post-numeros__item"><dt class="post-numeros__value" data-count data-count-duration="800">' . $valor . '</dt>';
		$html .= '<dd class="post-numeros__desc" data-reveal="fade">' . esc_html( $row['desc'] ) . '</dd></div>';
	}
	return $html . '</dl></div>';
} );

/* --------------------------------------------------------------------------
   Texto + índice
   -------------------------------------------------------------------------- */

/**
 * Texto do artigo já filtrado (the_content), com id em todo H2 que não tiver (e entrada por linhas, data-split), e o índice:
 * array( 'html' => string, 'toc' => array( array( texto, id ) ) ). O índice pula títulos "Destaque".
 */
function artemis_post_content( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return array( 'html' => '', 'toc' => array() );
	}
	$html = apply_filters( 'the_content', get_the_content( null, false, $post ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
	$html = str_replace( ']]>', ']]&gt;', $html );
	$toc  = array();
	$used = array();
	$html = preg_replace_callback( '#<h2(\s[^>]*)?>(.*?)</h2>#is', function ( $m ) use ( &$toc, &$used ) {
		$attrs = isset( $m[1] ) ? $m[1] : '';
		$text  = trim( wp_strip_all_tags( $m[2] ) );
		if ( preg_match( '/\sid=(["\'])(.*?)\1/i', $attrs, $id ) ) {
			$anchor = $id[2];
		} else {
			$base   = sanitize_title( $text );
			$base   = '' !== $base ? $base : 'secao';
			$anchor = $base;
			for ( $n = 2; isset( $used[ $anchor ] ); $n++ ) {
				$anchor = $base . '-' . $n;
			}
			$attrs .= ' id="' . esc_attr( $anchor ) . '"';
		}
		$used[ $anchor ] = true;
		if ( ! preg_match( '/\sdata-split=/i', $attrs ) ) {
			$attrs .= ' data-split="lines"';
		}
		if ( '' !== $text && ! preg_match( '/\bis-style-destaque\b/', $attrs ) ) {
			$toc[] = array( $text, $anchor );
		}
		return '<h2' . $attrs . '>' . $m[2] . '</h2>';
	}, $html );
	return array( 'html' => $html, 'toc' => $toc );
}

/** Abertura do topo (8331:345): meta _art_post_lead; sem ela, o resumo. */
function artemis_post_lead( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	$lead = (string) get_post_meta( $post->ID, '_art_post_lead', true );
	return '' !== $lead ? $lead : ( has_excerpt( $post ) ? wp_strip_all_tags( $post->post_excerpt ) : '' );
}

/** Chamada final (8435:1493): título e texto do artigo ou os padrões do Personalizar; null se escondida. */
function artemis_post_cta( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || '1' === (string) get_post_meta( $post->ID, '_art_post_cta_off', true ) ) {
		return null;
	}
	$title = (string) get_post_meta( $post->ID, '_art_post_cta_title', true );
	$text  = (string) get_post_meta( $post->ID, '_art_post_cta_text', true );
	return array(
		'title' => '' !== $title ? $title : (string) artemis_mod( 'art_post_cta_title' ),
		'text'  => '' !== $text ? $text : (string) artemis_mod( 'art_post_cta_text' ),
	);
}

/** "Continue lendo": até $limit posts da mesma categoria (mais recentes), completando com os mais recentes do blog. */
function artemis_post_related( $post = null, $limit = 3 ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}
	$base = array(
		'post_type'           => 'post',
		'orderby'             => 'date',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
	);
	$cats    = wp_get_post_categories( $post->ID );
	$related = $cats ? get_posts( $base + array(
		'numberposts'  => $limit,
		'category__in' => $cats,
		'post__not_in' => array( $post->ID ),
	) ) : array();
	if ( count( $related ) < $limit ) {
		$skip    = array_merge( array( $post->ID ), wp_list_pluck( $related, 'ID' ) );
		$related = array_merge( $related, get_posts( $base + array(
			'numberposts'  => $limit - count( $related ),
			'post__not_in' => $skip,
		) ) );
	}
	return $related;
}

/* --------------------------------------------------------------------------
   Seed — texto do Figma nos posts de exemplo (o Figma usa o mesmo artigo em todos os cards).
   Só troca o conteúdo provisório do seed do Blog (1 parágrafo com o resumo); post editado não é tocado.
   -------------------------------------------------------------------------- */
add_filter( 'artemis_seed', function ( $log ) {
	$file = ARTEMIS_DIR . '/inc/seed/post-wcontrol.html';
	if ( ! is_readable( $file ) ) {
		return $log;
	}
	$img = artemis_seed_media( 'images/blog-03-categoria/capa-wcontrol.png', 'Capa — Anatomia do WControl' );
	$src = $img ? wp_get_attachment_image_url( $img, 'large' ) : artemis_asset( 'images/blog-03-categoria/capa-wcontrol.png' );
	$body = strtr( (string) file_get_contents( $file ), array( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		'{{IMG_ID}}'  => (string) (int) $img,
		'{{IMG_SRC}}' => esc_url( $src ),
	) );

	$posts = get_posts( array(
		'post_type'   => 'post',
		'post_status' => 'any',
		'numberposts' => -1,
		'meta_key'    => '_artemis_seed_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	) );
	$done = 0;
	foreach ( $posts as $p ) {
		$stub = "<!-- wp:paragraph -->\n<p>" . esc_html( $p->post_excerpt ) . "</p>\n<!-- /wp:paragraph -->";
		if ( trim( $p->post_content ) === $stub || '' === trim( $p->post_content ) ) {
			wp_update_post( array( 'ID' => $p->ID, 'post_content' => $body ) );
			++$done;
		}
		if ( '' === (string) get_post_meta( $p->ID, '_art_post_lead', true ) ) {
			update_post_meta( $p->ID, '_art_post_lead', 'Um certificado de segurança do trabalho com 1.240 páginas leva 40 minutos para ser conferido à mão, folha por folha. O WControl devolve o veredito em 90 segundos e aponta a página exata do problema. Este é o desenho por dentro — inclusive as duas versões que jogamos fora.' );
		}
		if ( ! get_post_meta( $p->ID, '_art_post_numeros', true ) ) {
			update_post_meta( $p->ID, '_art_post_numeros', array(
				'label' => 'Números',
				'items' => array(
					array( 'pre' => 'R$', 'valor' => '40.000', 'suf' => '/MÊS', 'desc' => 'economizados em um único tipo de documento' ),
					array( 'pre' => '', 'valor' => '97%', 'suf' => '', 'desc' => 'de assertividade medida contra gabarito humano' ),
					array( 'pre' => '', 'valor' => '1.240', 'suf' => '', 'desc' => 'páginas no maior documento processado até aqui' ),
				),
			) );
		}
	}
	if ( $done ) {
		$log[] = sprintf( 'Texto do artigo do Figma aplicado em %d post(s) de exemplo', $done );
	}
	return $log;
}, 30 );
