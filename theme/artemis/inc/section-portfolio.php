<?php
/**
 * Página Portfólio (slug "portfolio") — layout antigo (logos): canvas "Portfólio Artemis — nova página",
 * quadros Desktop-copia (1440) e Mobile-antigo (390). Espelha portfolio/index.html.
 * Fora do menu principal: o link fica no rodapé, em "Outras linhas" (art_footer_lines).
 * Template: page-portfolio.php → header-portfolio.php (header próprio, sem menu) + template-parts/portfolio/01-grid.php
 *           + footer-portfolio.php (sem o rodapé do site, como no quadro).
 *
 * CPT          art_projeto — nome (título), logo (imagem destacada, 1156×842 como no portfólio antigo),
 *              link (_art_projeto_url; vazio = logo sem link), posição (Ordem)
 * Taxonomia    art_projeto_cat — Site, Onepage, Blog (aprovadas pelo Studio em 2026-10-19, sem Plataforma: o oithe fica só
 *              em "Todos"); vira o filtro "Navegue por aqui" (só as categorias com projeto)
 * Customizer   art_pf_title ("Navegue por aqui") · art_pf_cta / art_pf_cta_url ("Comece um projeto")
 * Dados do site  WhatsApp (art_contact_whatsapp) → botão flutuante; vazio esconde
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'art_projeto', array(
		'labels'       => array(
			'name'               => __( 'Portfólio', 'artemis' ),
			'singular_name'      => __( 'Projeto', 'artemis' ),
			'add_new_item'       => __( 'Adicionar projeto', 'artemis' ),
			'edit_item'          => __( 'Editar projeto', 'artemis' ),
			'all_items'          => __( 'Projetos', 'artemis' ),
			'featured_image'     => __( 'Logo', 'artemis' ),
			'set_featured_image' => __( 'Definir logo', 'artemis' ),
		),
		'public'       => false,
		'show_ui'      => true,
		'menu_icon'    => 'dashicons-portfolio',
		'supports'     => array( 'title', 'thumbnail', 'page-attributes' ),
		'show_in_rest' => true,
	) );

	register_taxonomy( 'art_projeto_cat', 'art_projeto', array(
		'labels'            => array(
			'name'          => __( 'Categorias do portfólio', 'artemis' ),
			'singular_name' => __( 'Categoria', 'artemis' ),
			'add_new_item'  => __( 'Adicionar categoria', 'artemis' ),
		),
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'hierarchical'      => true, // caixas de seleção no editor
		'show_in_rest'      => true,
	) );

	register_post_meta( 'art_projeto', '_art_projeto_url', array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
	) );
} );

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'art_projeto_meta', __( 'Dados do projeto', 'artemis' ), function ( $post ) {
		wp_nonce_field( 'art_projeto_meta', 'art_projeto_nonce' );
		printf(
			'<p><label for="art_projeto_url">%1$s</label><br><input type="url" class="widefat" id="art_projeto_url" name="art_projeto_url" value="%2$s" placeholder="https://"></p>',
			esc_html__( 'Link do projeto (abre em nova aba; vazio = logo sem link)', 'artemis' ),
			esc_attr( get_post_meta( $post->ID, '_art_projeto_url', true ) )
		);
		echo '<p class="description">' . esc_html__( 'Logo: imagem destacada, de preferência 1156×842 com o logo centralizado (a grade corta um pouco das laterais). Categorias: caixa "Categorias do portfólio". Posição na grade: campo "Ordem".', 'artemis' ) . '</p>';
	}, 'art_projeto', 'normal' );
} );

add_action( 'save_post_art_projeto', function ( $post_id ) {
	if ( ! isset( $_POST['art_projeto_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['art_projeto_nonce'] ), 'art_projeto_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$url = isset( $_POST['art_projeto_url'] ) ? esc_url_raw( wp_unslash( $_POST['art_projeto_url'] ) ) : '';
	update_post_meta( $post_id, '_art_projeto_url', $url );
} );

/** Projetos publicados, na ordem do campo "Ordem". */
function artemis_get_projetos() {
	return get_posts( array(
		'post_type'      => 'art_projeto',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	) );
}

/** Categorias com pelo menos um projeto, na ordem de criação (Site, Onepage, Blog). */
function artemis_get_projeto_cats() {
	$terms = get_terms( array(
		'taxonomy'   => 'art_projeto_cat',
		'hide_empty' => true,
		'orderby'    => 'term_id',
	) );
	return is_wp_error( $terms ) ? array() : $terms;
}

/* O header do Portfólio não é fixo: sem o padding-top do <main> (portfolio-header.css usa .page-portfolio). */
add_filter( 'body_class', function ( $classes ) {
	if ( is_page( 'portfolio' ) ) {
		$classes[] = 'page-portfolio';
	}
	return $classes;
} );

add_filter( 'artemis_customizer_defaults', function ( $d ) {
	return $d + array(
		'art_pf_title'   => 'Navegue por aqui',
		'art_pf_cta'     => 'Comece um projeto',
		'art_pf_cta_url' => '/contato',
	);
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$s['artemis_portfolio'] = array(
		__( 'Portfólio', 'artemis' ),
		array(
			'art_pf_title'   => array( __( 'Título da coluna do filtro', 'artemis' ), 'text' ),
			'art_pf_cta'     => array( __( 'Link do topo — texto', 'artemis' ), 'text' ),
			'art_pf_cta_url' => array( __( 'Link do topo — endereço', 'artemis' ), 'text' ),
		),
	);
	return $s;
} );

/** Projetos na ordem do quadro: [nome, logo, link, categorias]. Logos e links do portfólio antigo
 *  (studioartemis.co/portfolio-artemis); categorias aprovadas pelo Studio em 2026-10-19. */
function artemis_portfolio_seed_items() {
	return array(
		array( 'Expo Usipa', 'expo-usipa', 'https://expousipa.com', array( 'site' ) ),
		array( 'ARV Incorporação & Tecnologia', 'arv', 'https://arvinc.com.br', array( 'site' ) ),
		array( 'CashWay', 'cashway', 'https://cashway.io', array( 'site' ) ),
		array( 'Grupo CMD', 'grupo-cmd', 'https://institucional.grupocmd.com.br', array( 'site' ) ),
		array( '3Neuron', '3neuron', 'https://3neuron.com', array( 'site', 'blog' ) ),
		array( 'Mais Previdência', 'mais-previdencia', 'https://maisprevidencia.com.br', array( 'site' ) ),
		array( 'Instacont', 'instacont', 'https://instacont.com.br', array( 'site' ) ),
		array( 'FORZA BR', 'forza-br', 'https://forzabr.com.br', array( 'site' ) ),
		array( 'SysMiddle', 'sysmiddle', 'https://sysmiddle.com.br', array( 'site' ) ),
		array( 'Voxy', 'voxy', 'https://voxy.com', array( 'site', 'blog' ) ),
		array( 'ESGreen', 'esgreen', 'https://esgreen.com.br', array( 'site' ) ),
		array( 'Bolsa de terras', 'bolsa-de-terras', 'https://bolsadeterras.com.br', array( 'site', 'blog' ) ),
		array( 'Zeros à Direita', 'zeros-a-direita', 'https://zerosadireita.com', array( 'onepage' ) ),
		array( 'ebrain', 'ebrain', 'https://www.ebrain.clinic', array( 'onepage' ) ),
		array( 'Do Telematics', 'do-telematics', 'https://dotelematics.com', array( 'site' ) ),
		array( 'FaleUp', 'faleup', 'https://www.faleup.com.br', array( 'onepage' ) ),
		array( 'Lebensart – Porsche', 'lebensart', 'https://testeskatz.artemisdigital.net', array( 'site' ) ),
		array( 'oithe', 'oithe', 'https://www.figma.com/proto/DM3eSolLKRDnMDaHOD16RM/OITHE---Sistema-Web-e-Mobile?page-id=7117%3A1772&node-id=7118-1877&viewport=2919%2C354%2C0.46&t=SPQty4qoWcJkBhoZ-1&scaling=scale-down&content-scaling=fixed&starting-point-node-id=7118%3A1877&show-proto-sidebar=1', array() ),
		array( 'BID Consultoria', 'bid-consultoria', 'https://bidconsultoria.com', array( 'site' ) ),
		array( 'Nivéro', 'nivero', 'https://nivero.co', array( 'site' ) ),
		array( 'Intacta', 'intacta', 'https://intactaengenharia.com.br', array( 'site' ) ),
		array( 'Global Supply', 'global-supply', 'https://consultoriaglobalsupply.com.br', array( 'site', 'blog' ) ),
		array( 'Lorenza Franceschi', 'lorenza-franceschi', 'https://lf.arq.br', array( 'site' ) ),
	);
}

add_filter( 'artemis_seed', function ( $log ) {
	if ( ! get_page_by_path( 'portfolio' ) ) {
		$id    = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Portfólio', 'post_name' => 'portfolio' ) );
		$log[] = $id && ! is_wp_error( $id ) ? 'Página criada: Portfólio (/portfolio)' : 'Falha ao criar a página Portfólio';
	}

	// Categorias na ordem do filtro (a listagem ordena por ID).
	foreach ( array( 'site' => 'Site', 'onepage' => 'Onepage', 'blog' => 'Blog' ) as $slug => $name ) {
		if ( ! term_exists( $slug, 'art_projeto_cat' ) ) {
			wp_insert_term( $name, 'art_projeto_cat', array( 'slug' => $slug ) );
		}
	}

	foreach ( artemis_portfolio_seed_items() as $i => $item ) {
		$exists = get_posts( array( 'post_type' => 'art_projeto', 'title' => $item[0], 'numberposts' => 1, 'post_status' => 'any', 'fields' => 'ids' ) );
		if ( $exists ) {
			continue; // mantém o que já foi editado
		}
		$id = artemis_seed_post( array(
			'post_type' => 'art_projeto',
			'title'     => $item[0],
			'order'     => $i + 1,
			'image'     => 'images/portfolio/' . $item[1] . '.webp',
			'meta'      => array( '_art_projeto_url' => $item[2] ),
		), $log );
		if ( $id ) {
			wp_set_object_terms( $id, $item[3], 'art_projeto_cat' );
		}
	}

	// Rodapé já personalizado antes do Portfólio existir: acrescenta o link em "Outras linhas".
	$lines = get_theme_mod( 'art_footer_lines', null );
	if ( null !== $lines && false === strpos( (string) $lines, '/portfolio' ) ) {
		set_theme_mod( 'art_footer_lines', rtrim( (string) $lines ) . "\nPortfólio | /portfolio/" );
		$log[] = 'Rodapé: link "Portfólio" acrescentado em Outras linhas.';
	}
	return $log;
} );
