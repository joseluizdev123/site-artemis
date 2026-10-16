<?php
/**
 * Página Sobre — Figma 8348:3076 · mobile 8689:1932.
 * Template: page-sobre.php (página de slug "sobre") → template-parts/sobre/NN-slug.php. Espelha sobre/index.html.
 *
 * Página        imagem destacada = foto da equipe (8497:2193); sem ela, a do tema
 * Customizer    art_sobre_* — topo, "Por que existimos", "Três coisas" (3 itens), "O sistema audita",
 *               "Quem está por trás" (título, texto, nota), "Onde estamos" (texto, botão, endereço do mapa)
 *               Textos longos: linha em branco separa parágrafos.
 * CPT           art_membro (inc/section-02-o-que-fazemos.php): nome, _art_membro_cargo, bio = conteúdo,
 *               _art_membro_foto_sobre = retrato 3:4 desta página (sem ele, a imagem destacada)
 */

defined( 'ABSPATH' ) || exit;

/* --------------------------------------------------------------------------
   Customizer
   -------------------------------------------------------------------------- */
function artemis_sobre_tres_defaults() {
	return array(
		array( 'A regra nunca estava escrita', 'Na Gascat, a descrição das peças era cabeça das pessoas, não existia em lugar nenhum. Na Athie, os critérios de validação estavam num prompt monolítico que ninguém tinha decomposto. Reconhecer duto rígido de flexível numa planta era olho de engenheiro. O trabalho real está aí.' ),
		array( 'O erro já tem preço', 'Não entramos por produtividade abstrata. Entramos onde o erro tem número: R$ 12 milhões de estoque parado, R$ 40 mil por mês de retrabalho, uma válvula voltando de navio. Se ninguém consegue dizer quanto custa o erro, o projeto ainda não está pronto para começar.' ),
		array( 'O pedido inicial nunca é o problema', 'Um cliente pediu para implementar uma ferramenta de automação e recebeu dois sistemas de decisão. Outro pediu para diminuir estoque e descobriu que o problema era codificação na origem. Um terceiro pediu para automatizar uma planilha e saiu com a roteirização inteira redesenhada. O pedido é sintoma. O diagnóstico encontra a causa.' ),
	);
}

add_filter( 'artemis_customizer_defaults', function ( $d ) {
	$add = array(
		'art_sobre_title'          => 'Uma empresa que automatiza a própria operação {antes de automatizar a sua.}',
		'art_sobre_lead'           => 'Artemis. Inteligência artificial e automações para indústria, construção e operação B2B.',
		'art_sobre_porque_title'   => 'Por que {existimos}',
		'art_sobre_porque_text'    => "A maior parte do que se vende como inteligência artificial hoje é uma de duas coisas: uma licença de software que promete resolver um problema genérico, ou um projeto de consultoria que termina em apresentação.\n\nExiste um espaço entre os dois. É onde mora a decisão técnica que a sua operação toma dezenas de vezes por dia, importante demais para um software de prateleira e específica demais para caber num projeto de escopo fechado. É nesse espaço que trabalhamos.",
		'art_sobre_tres_title'     => 'Três coisas que se repetem {em todo projeto}',
		'art_sobre_audita_title'   => 'O sistema audita a sua {operação de graça}',
		'art_sobre_audita_text'    => "Quando a IA erra com confiança alta, quase sempre não foi ela. Foi o humano supondo algo que não estava escrito na especificação.\n\nNa Gascat, esses casos viraram correção de processo. Na Athie, a empresa criou um playbook interno obrigando projetistas terceirizados a marcar dimensão em todos os dutos. Ninguém pediu; ela decidiu sozinha depois de ver o que o sistema apontava.\n\nIsso normalmente é entregável de consultoria de processo. Aqui sai como consequência.",
		'art_sobre_equipe_title'   => 'Quem está {por trás}',
		'art_sobre_equipe_lead'    => 'Fundada por Hélio Costa Jr. em Florianópolis, com sócios que operam as áreas de engenharia, delivery e comercial.',
		'art_sobre_socios_title'   => 'Quem está por trás',
		'art_sobre_note'           => 'A empresa opera outras duas linhas, com públicos e problemas diferentes: Artemis SEO, para captação orgânica, e MktOps, que instala capacidade de marketing com IA dentro do cliente.',
		'art_sobre_onde_title'     => 'Onde estamos',
		'art_sobre_onde_text'      => 'R. Fúlvio Aducci, 416 — Florianópolis, SC. Atendimento remoto em todo o Brasil, presencial quando o diagnóstico exigir.',
		'art_sobre_onde_cta'       => 'Falar com a gente',
		'art_sobre_onde_url'       => '/contato',
		'art_sobre_onde_mapa'      => 'R. Fúlvio Aducci, 416 - Florianópolis, SC',
	);
	foreach ( artemis_sobre_tres_defaults() as $i => $item ) {
		$add[ 'art_sobre_tres_' . ( $i + 1 ) . '_title' ] = $item[0];
		$add[ 'art_sobre_tres_' . ( $i + 1 ) . '_text' ]  = $item[1];
	}
	return $d + $add;
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$hl = __( '— coloque {entre chaves} o trecho em cinza', 'artemis' );
	$s['artemis_sobre_topo'] = array(
		__( 'Sobre — Topo, "Por que existimos" e "O sistema audita"', 'artemis' ),
		array(
			'art_sobre_title'        => array( __( 'Título do topo ', 'artemis' ) . $hl, 'textarea' ),
			'art_sobre_lead'         => array( __( 'Texto do topo', 'artemis' ), 'textarea' ),
			'art_sobre_porque_title' => array( __( '"Por que existimos" — título ', 'artemis' ) . $hl, 'text' ),
			'art_sobre_porque_text'  => array( __( '"Por que existimos" — texto (linha em branco separa parágrafos). A foto é a imagem destacada da página Sobre.', 'artemis' ), 'textarea' ),
			'art_sobre_audita_title' => array( __( '"O sistema audita" — título ', 'artemis' ) . $hl, 'text' ),
			'art_sobre_audita_text'  => array( __( '"O sistema audita" — texto (linha em branco separa parágrafos)', 'artemis' ), 'textarea' ),
		),
	);
	$tres = array( 'art_sobre_tres_title' => array( __( 'Título ', 'artemis' ) . $hl, 'text' ) );
	for ( $i = 1; $i <= 3; $i++ ) {
		/* translators: %d: número do item */
		$tres[ 'art_sobre_tres_' . $i . '_title' ] = array( sprintf( __( 'Item %d — título', 'artemis' ), $i ), 'text' );
		/* translators: %d: número do item */
		$tres[ 'art_sobre_tres_' . $i . '_text' ] = array( sprintf( __( 'Item %d — texto', 'artemis' ), $i ), 'textarea' );
	}
	$s['artemis_sobre_tres'] = array( __( 'Sobre — Três coisas que se repetem', 'artemis' ), $tres );
	$s['artemis_sobre_equipe'] = array(
		__( 'Sobre — Quem está por trás', 'artemis' ),
		array(
			'art_sobre_equipe_title' => array( __( 'Título dos cards ', 'artemis' ) . $hl, 'text' ),
			'art_sobre_equipe_lead'  => array( __( 'Texto', 'artemis' ), 'textarea' ),
			'art_sobre_socios_title' => array( __( 'Título da seção com as bios', 'artemis' ), 'text' ),
			'art_sobre_note'         => array( __( 'Nota abaixo das duas seções (vazio esconde)', 'artemis' ), 'textarea' ),
		),
	);
	$s['artemis_sobre_onde'] = array(
		__( 'Sobre — Onde estamos', 'artemis' ),
		array(
			'art_sobre_onde_title' => array( __( 'Título', 'artemis' ), 'text' ),
			'art_sobre_onde_text'  => array( __( 'Texto', 'artemis' ), 'textarea' ),
			'art_sobre_onde_cta'   => array( __( 'Botão — texto', 'artemis' ), 'text' ),
			'art_sobre_onde_url'   => array( __( 'Botão — link', 'artemis' ), 'text' ),
			'art_sobre_onde_mapa'  => array( __( 'Endereço buscado no mapa (Google Maps)', 'artemis' ), 'text' ),
		),
	);
	return $s;
} );

/* --------------------------------------------------------------------------
   Equipe — retrato da página Sobre (_art_membro_foto_sobre)
   -------------------------------------------------------------------------- */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'art_membro_foto_sobre', __( 'Retrato na página Sobre', 'artemis' ), function ( $post ) {
		wp_nonce_field( 'art_membro_foto_sobre', 'art_membro_foto_sobre_nonce' );
		$value = (int) get_post_meta( $post->ID, '_art_membro_foto_sobre', true );
		$src   = $value ? wp_get_attachment_image_url( $value, 'medium' ) : '';
		printf(
			'<p><span data-artemis-preview="art_membro_foto_sobre">%1$s</span><input type="hidden" id="art_membro_foto_sobre" name="art_membro_foto_sobre" value="%2$s"><button type="button" class="button" data-artemis-media="art_membro_foto_sobre">%3$s</button> <button type="button" class="button-link" data-artemis-media-clear="art_membro_foto_sobre">%4$s</button></p>',
			$src ? '<img src="' . esc_url( $src ) . '" alt="" style="display:block;max-width:100%;height:auto;margin:0 0 8px">' : '',
			esc_attr( $value ? (string) $value : '' ),
			esc_html__( 'Escolher imagem', 'artemis' ),
			esc_html__( 'Remover', 'artemis' )
		);
		echo '<p class="description">' . esc_html__( 'Vertical 3:4 (ex.: 811 × 1081 px). Vazio usa a imagem destacada. A bio da página Sobre é o texto do editor.', 'artemis' ) . '</p>';
		printf(
			'<p><label for="art_membro_ordem_sobre">%s</label><br><input type="number" min="0" id="art_membro_ordem_sobre" name="art_membro_ordem_sobre" value="%s" class="small-text"></p>',
			esc_html__( 'Ordem na página Sobre (menor primeiro; vazio vai para o fim)', 'artemis' ),
			esc_attr( (string) get_post_meta( $post->ID, '_art_membro_ordem_sobre', true ) )
		);
	}, 'art_membro', 'side' );
} );

add_action( 'admin_enqueue_scripts', function () {
	$screen = get_current_screen();
	if ( ! $screen || 'art_membro' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
	wp_register_script( 'artemis-membro-admin', false, array( 'media-views' ), ARTEMIS_VERSION, true );
	wp_enqueue_script( 'artemis-membro-admin' );
	wp_add_inline_script( 'artemis-membro-admin', <<<'JS'
document.addEventListener('click', function (e) {
  var pick = e.target.closest('[data-artemis-media]');
  var clear = e.target.closest('[data-artemis-media-clear]');
  if (!pick && !clear) return;
  e.preventDefault();
  var id = (pick || clear).getAttribute(pick ? 'data-artemis-media' : 'data-artemis-media-clear');
  var input = document.getElementById(id);
  var box = document.querySelector('[data-artemis-preview="' + id + '"]');
  if (clear) { input.value = ''; box.innerHTML = ''; return; }
  var frame = wp.media({ title: pick.textContent, library: { type: 'image' }, multiple: false });
  frame.on('select', function () {
    var att = frame.state().get('selection').first().toJSON();
    var img = document.createElement('img');
    img.src = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
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

add_action( 'save_post_art_membro', function ( $post_id ) {
	if ( ! isset( $_POST['art_membro_foto_sobre_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['art_membro_foto_sobre_nonce'] ), 'art_membro_foto_sobre' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	// Vazio grava 0 (removido de propósito): o seeder só preenche quando a chave nem existe.
	update_post_meta( $post_id, '_art_membro_foto_sobre', isset( $_POST['art_membro_foto_sobre'] ) ? absint( wp_unslash( $_POST['art_membro_foto_sobre'] ) ) : 0 );
	$ordem = isset( $_POST['art_membro_ordem_sobre'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['art_membro_ordem_sobre'] ) ) ) : '';
	if ( '' === $ordem ) {
		delete_post_meta( $post_id, '_art_membro_ordem_sobre' );
	} else {
		update_post_meta( $post_id, '_art_membro_ordem_sobre', absint( $ordem ) );
	}
} );

/** Sócios publicados, na ordem; cada um: [post, foto_url, nome, cargo, bio_html]. */
function artemis_sobre_membros() {
	$out = array();
	foreach ( get_posts( array( 'post_type' => 'art_membro', 'numberposts' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'ASC' ) ) ) as $p ) {
		$foto = (int) get_post_meta( $p->ID, '_art_membro_foto_sobre', true );
		$url  = $foto ? wp_get_attachment_image_url( $foto, 'large' ) : get_the_post_thumbnail_url( $p, 'large' );
		$out[] = array(
			'post'  => $p,
			'foto'  => $url ? $url : '',
			'nome'  => get_the_title( $p ),
			'cargo' => (string) get_post_meta( $p->ID, '_art_membro_cargo', true ),
			'bio'   => trim( (string) $p->post_content ),
			'ordem' => '' === (string) get_post_meta( $p->ID, '_art_membro_ordem_sobre', true ) ? PHP_INT_MAX : (int) get_post_meta( $p->ID, '_art_membro_ordem_sobre', true ),
		);
	}
	usort( $out, function ( $a, $b ) {
		return $a['ordem'] <=> $b['ordem'];
	} );
	return $out;
}

/** Texto do Customizer em parágrafos (<p>…</p>), já escapado. */
function artemis_sobre_paragraphs( $key ) {
	$html = '';
	foreach ( artemis_oqf_paragraphs( artemis_mod( $key ) ) as $para ) {
		$html .= '<p>' . $para . '</p>';
	}
	return $html;
}

/* --------------------------------------------------------------------------
   Seed — página Sobre (com a foto da equipe) e os retratos dos sócios
   -------------------------------------------------------------------------- */
add_filter( 'artemis_seed', function ( $log ) {
	$page = get_page_by_path( 'sobre' );
	if ( ! $page ) {
		$id   = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Sobre', 'post_name' => 'sobre' ) );
		$page = $id && ! is_wp_error( $id ) ? get_post( $id ) : null;
		$log[] = $page ? 'Página criada: Sobre (/sobre)' : 'Falha ao criar a página Sobre';
	}
	if ( $page && ! has_post_thumbnail( $page ) ) {
		$foto = artemis_seed_media( 'images/sobre/equipe.jpg', 'Equipe Artemis' );
		if ( $foto ) {
			set_post_thumbnail( $page, $foto );
		}
	}
	// Retratos do Figma (8355:3493 · 3499 · 3511 · 3505). Hélio e Julio conferidos; Enzo e Willy a confirmar.
	$retratos = array( 'Helio' => 'socio-1', 'Julio' => 'socio-2', 'Enzo' => 'socio-3', 'Willy' => 'socio-4' );
	$ordem    = 0;
	foreach ( $retratos as $nome => $arquivo ) {
		++$ordem;
		$membro = get_posts( array( 'post_type' => 'art_membro', 'title' => $nome, 'numberposts' => 1, 'post_status' => 'any' ) );
		if ( ! $membro ) {
			continue;
		}
		if ( ! metadata_exists( 'post', $membro[0]->ID, '_art_membro_ordem_sobre' ) ) {
			update_post_meta( $membro[0]->ID, '_art_membro_ordem_sobre', $ordem );
		}
		if ( metadata_exists( 'post', $membro[0]->ID, '_art_membro_foto_sobre' ) ) {
			continue;
		}
		$media = artemis_seed_media( 'images/sobre/' . $arquivo . '.jpg', $nome . ' (Sobre)' );
		if ( $media ) {
			update_post_meta( $membro[0]->ID, '_art_membro_foto_sobre', $media );
		}
	}
	return $log;
}, 20 );
