<?php
/**
 * Página Serviços — Figma 8340:1700 · mobile 8699:4921.
 * Template: page-servicos.php (página de slug "servicos") → template-parts/servicos/NN-slug.php + chamada final da Home.
 * Espelha servicos/index.html.
 *
 * Customizer   art_srv_title ({entre chaves} = trecho em cinza) · art_srv_lead → topo (8340:2096); botão = Dados do site
 *              art_srv_nao_title · art_srv_nao_lead · art_srv_nao_N_title/_text (4 cards) · art_srv_nao_note → 8348:2419
 * CPT          art_etapa (ordem = menu_order; selo "Etapa N" pela posição) → as etapas (Tabs 8573:1797)
 *              título · meta _art_etapa_quem (um item por linha) · _art_etapa_b2_icon (arquivo|cifrao)
 *              · _art_etapa_b2_label · _art_etapa_b2_text · _art_etapa_duracao (vazio esconde o bloco)
 * Ilustração   fixa do tema (assets/images/servicos-02-etapas)
 */

defined( 'ABSPATH' ) || exit;

/* --------------------------------------------------------------------------
   Customizer
   -------------------------------------------------------------------------- */
function artemis_srv_nao_defaults() {
	return array(
		array( 'Não implementamos ERP nem CRM.', 'Trabalhamos em cima do que você já tem.' ),
		array( 'Não fazemos chatbot de atendimento.', 'O que automatizamos é decisão técnica, não conversa.' ),
		array( 'Não vendemos licença de software.', 'O que construímos é seu, adaptado ao seu processo.' ),
		array( 'Não começamos sem medir.', 'Se não dá para quantificar o custo do erro, ainda não é hora.' ),
	);
}

add_filter( 'artemis_customizer_defaults', function ( $d ) {
	$add = array(
		'art_srv_title'     => 'Implementação de inteligência artificial em três etapas. {Todas começam medindo.}',
		'art_srv_lead'      => 'Do diagnóstico ao sistema em produção, com a prova de assertividade em cada fase.',
		'art_srv_nao_title' => 'O que {não} fazemos',
		'art_srv_nao_lead'  => 'Ser específico sobre o que não fazemos economiza o tempo de todo mundo.',
		'art_srv_nao_note'  => 'Não somos agência de marketing. Captação orgânica é com a Artemis SEO; operação de marketing, com o MktOps.',
	);
	foreach ( artemis_srv_nao_defaults() as $i => $card ) {
		$add[ 'art_srv_nao_' . ( $i + 1 ) . '_title' ] = $card[0];
		$add[ 'art_srv_nao_' . ( $i + 1 ) . '_text' ]  = $card[1];
	}
	return $d + $add;
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$s['artemis_srv_hero'] = array(
		__( 'Serviços — Topo', 'artemis' ),
		array(
			'art_srv_title' => array( __( 'Título — coloque {entre chaves} o trecho em cinza', 'artemis' ), 'textarea' ),
			'art_srv_lead'  => array( __( 'Texto de apoio', 'artemis' ), 'textarea' ),
		),
	);
	$fields = array(
		'art_srv_nao_title' => array( __( 'Título — {entre chaves} o trecho em cinza', 'artemis' ), 'text' ),
		'art_srv_nao_lead'  => array( __( 'Texto de apoio', 'artemis' ), 'textarea' ),
	);
	for ( $i = 1; $i <= 4; $i++ ) {
		/* translators: %d: número do card */
		$fields[ 'art_srv_nao_' . $i . '_title' ] = array( sprintf( __( 'Card %d — título', 'artemis' ), $i ), 'text' );
		/* translators: %d: número do card */
		$fields[ 'art_srv_nao_' . $i . '_text' ] = array( sprintf( __( 'Card %d — texto', 'artemis' ), $i ), 'textarea' );
	}
	$fields['art_srv_nao_note'] = array( __( 'Nota ao lado das marcas Artemis SEO / MktOps', 'artemis' ), 'textarea' );
	$s['artemis_srv_nao'] = array( __( 'Serviços — O que não fazemos', 'artemis' ), $fields );
	return $s;
} );

/* Na página Serviços, a chamada final do celular é mais alta (8699:5366): body.page-servicos (10-cta--servicos.css). */
add_filter( 'body_class', function ( $classes ) {
	if ( is_page( 'servicos' ) ) {
		$classes[] = 'page-servicos';
	}
	return $classes;
} );

/* --------------------------------------------------------------------------
   CPT art_etapa + caixa "Detalhes da etapa"
   -------------------------------------------------------------------------- */
add_action( 'init', function () {
	register_post_type( 'art_etapa', array(
		'labels'       => array(
			'name'               => __( 'Etapas', 'artemis' ),
			'singular_name'      => __( 'Etapa', 'artemis' ),
			'menu_name'          => __( 'Etapas (Serviços)', 'artemis' ),
			'add_new'            => __( 'Adicionar etapa', 'artemis' ),
			'add_new_item'       => __( 'Adicionar etapa', 'artemis' ),
			'edit_item'          => __( 'Editar etapa', 'artemis' ),
			'new_item'           => __( 'Nova etapa', 'artemis' ),
			'search_items'       => __( 'Buscar etapas', 'artemis' ),
			'not_found'          => __( 'Nenhuma etapa encontrada', 'artemis' ),
			'not_found_in_trash' => __( 'Nenhuma etapa na lixeira', 'artemis' ),
			'all_items'          => __( 'Todas as etapas', 'artemis' ),
		),
		'public'       => false,
		'show_ui'      => true,
		'menu_icon'    => 'dashicons-editor-ol',
		'supports'     => array( 'title', 'page-attributes' ),
		'show_in_rest' => false,
	) );
} );

/** Campos da etapa: chave => [rótulo, tipo]. */
function artemis_etapa_fields() {
	return array(
		'_art_etapa_quem'     => array( __( '"Para quem" — um item por linha (todos aparecem com marcador)', 'artemis' ), 'textarea' ),
		'_art_etapa_b2_icon'  => array( __( '2º bloco — ícone', 'artemis' ), 'icon' ),
		'_art_etapa_b2_label' => array( __( '2º bloco — título (ex.: O que você leva, Como cobramos)', 'artemis' ), 'text' ),
		'_art_etapa_b2_text'  => array( __( '2º bloco — texto', 'artemis' ), 'textarea' ),
		'_art_etapa_duracao'  => array( __( 'Duração (vazio esconde o bloco)', 'artemis' ), 'text' ),
	);
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'art_etapa_detalhes', __( 'Detalhes da etapa', 'artemis' ), function ( $post ) {
		wp_nonce_field( 'art_etapa_detalhes', 'art_etapa_nonce' );
		echo '<p>' . esc_html__( 'O selo "Etapa N" segue a ordem (Atributos → Ordem).', 'artemis' ) . '</p>';
		foreach ( artemis_etapa_fields() as $key => $field ) {
			$value = (string) get_post_meta( $post->ID, $key, true );
			printf( '<p><label for="%1$s"><strong>%2$s</strong></label><br>', esc_attr( $key ), esc_html( $field[0] ) );
			if ( 'textarea' === $field[1] ) {
				printf( '<textarea id="%1$s" name="%1$s" rows="%3$d" class="widefat">%2$s</textarea></p>', esc_attr( $key ), esc_textarea( $value ), '_art_etapa_quem' === $key ? 7 : 2 );
			} elseif ( 'icon' === $field[1] ) {
				printf( '<select id="%s" name="%1$s">', esc_attr( $key ) );
				foreach ( array( 'arquivo' => __( 'Arquivo', 'artemis' ), 'cifrao' => __( 'Cifrão', 'artemis' ) ) as $opt => $label ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $opt ), selected( $value ? $value : 'arquivo', $opt, false ), esc_html( $label ) );
				}
				echo '</select></p>';
			} else {
				printf( '<input type="text" id="%1$s" name="%1$s" value="%2$s" class="widefat"></p>', esc_attr( $key ), esc_attr( $value ) );
			}
		}
	}, 'art_etapa', 'normal', 'high' );
} );

add_action( 'save_post_art_etapa', function ( $post_id ) {
	if ( ! isset( $_POST['art_etapa_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['art_etapa_nonce'] ), 'art_etapa_detalhes' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( artemis_etapa_fields() as $key => $field ) {
		$raw   = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$value = 'textarea' === $field[1] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		if ( 'icon' === $field[1] && ! in_array( $value, array( 'arquivo', 'cifrao' ), true ) ) {
			$value = 'arquivo';
		}
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
} );

/** Etapas publicadas, na ordem. */
function artemis_get_etapas() {
	return get_posts( array(
		'post_type'   => 'art_etapa',
		'numberposts' => -1,
		'orderby'     => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
	) );
}

/** Blocos do painel da etapa: [ícone, rótulo, texto|null, itens|null]. */
function artemis_etapa_blocks( $post ) {
	$items = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) get_post_meta( $post->ID, '_art_etapa_quem', true ) ) ) ) );
	$icon  = (string) get_post_meta( $post->ID, '_art_etapa_b2_icon', true );
	$blocks = array();
	if ( $items ) {
		$blocks[] = array( 'para-quem', __( 'Para quem', 'artemis' ), null, $items );
	}
	$label = (string) get_post_meta( $post->ID, '_art_etapa_b2_label', true );
	if ( '' !== $label ) {
		$blocks[] = array( 'cifrao' === $icon ? 'cifrao' : 'arquivo', $label, (string) get_post_meta( $post->ID, '_art_etapa_b2_text', true ), null );
	}
	$dur = (string) get_post_meta( $post->ID, '_art_etapa_duracao', true );
	if ( '' !== $dur ) {
		$blocks[] = array( 'calendario', __( 'Duração', 'artemis' ), $dur, null );
	}
	return $blocks;
}

/* --------------------------------------------------------------------------
   Seed — página Serviços + as 3 etapas do Figma
   -------------------------------------------------------------------------- */
add_filter( 'artemis_seed', function ( $log ) {
	if ( ! get_page_by_path( 'servicos' ) ) {
		$page = wp_insert_post( array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => 'Serviços',
			'post_name'   => 'servicos',
		) );
		$log[] = $page && ! is_wp_error( $page ) ? 'Página criada: Serviços (/servicos)' : 'Falha ao criar a página Serviços';
	}
	$etapas = array(
		array( 'Diagnóstico de operação', "Você sabe que existe hora sênior queimada em tarefa repetitiva, mas não sabe onde atacar primeiro.\nUm dia dentro da operação, com as pessoas que decidem\nMapeamento dos processos com maior custo de hora especializada\nQuantificação do custo mensal de cada um\nProtótipo funcionando no problema mais caro, no mesmo dia\nPlano de implementação por fase, com estimativa de retorno", 'arquivo', 'O que você leva', 'O mapa, a quantificação e o protótipo. Mesmo que não siga com a gente.', '4 a 6 semanas, com a imersão presencial no início.' ),
		array( 'Implementação: do protótipo à produção', "O problema já está identificado e quantificado. Falta construir e provar.\nConstrução da base de casos com gabarito humano\nDesenvolvimento dos agentes de IA e das automações que sustentam a decisão\nLaboratório de teste entregue como módulo seu\nIteração até o nível de assertividade que você definir\nEntrada em produção com monitoramento", 'cifrao', 'Como cobramos', 'Por projeto, com escopo e fase declarados.', '8 a 12 semanas, variando com a complexidade da regra.' ),
		array( 'Manutenção e evolução', "O sistema está em produção e precisa acompanhar a operação que muda.\nMonitoramento de assertividade em produção\nAjuste de critério quando a regra do negócio muda\nNovos módulos sobre a base já construída\nRelatório mensal de impacto", 'cifrao', 'Como cobramos', 'Mensalidade', '' ),
	);
	foreach ( $etapas as $order => $e ) {
		$id = artemis_seed_post( array(
			'post_type' => 'art_etapa',
			'title'     => $e[0],
			'order'     => $order,
		), $log );
		if ( $id && ! metadata_exists( 'post', $id, '_art_etapa_quem' ) ) {
			update_post_meta( $id, '_art_etapa_quem', $e[1] );
			update_post_meta( $id, '_art_etapa_b2_icon', $e[2] );
			update_post_meta( $id, '_art_etapa_b2_label', $e[3] );
			update_post_meta( $id, '_art_etapa_b2_text', $e[4] );
			if ( '' !== $e[5] ) {
				update_post_meta( $id, '_art_etapa_duracao', $e[5] );
			}
		}
	}
	return $log;
} );
