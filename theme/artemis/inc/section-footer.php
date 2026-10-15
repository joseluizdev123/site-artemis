<?php
/**
 * Footer (Figma 8165:2501) — markup em footer.php.
 * Conteúdo: Customizer art_footer_* + contato global (art_contact_email/whatsapp/address)
 * + redes art_social_* + menu "footer" (registrado em functions.php).
 */

defined( 'ABSPATH' ) || exit;

/** Redes sociais na ordem do Figma: chave => [rótulo, ícone]. */
function artemis_footer_socials() {
	return array(
		'art_social_instagram' => array( 'Instagram', 'instagram' ),
		'art_social_youtube'   => array( 'YouTube', 'youtube' ),
		'art_social_linkedin'  => array( 'LinkedIn', 'linkedin' ),
		'art_social_behance'   => array( 'Behance', 'behance' ),
		'art_social_dribbble'  => array( 'Dribbble', 'dribbble' ),
		'art_social_x'         => array( 'X (Twitter)', 'x' ),
	);
}

add_filter( 'artemis_customizer_defaults', function ( $d ) {
	$d = $d + array(
		// Contato global (MODEL.md: art_contact_*) — e-mail já existe em customizer.php.
		'art_contact_whatsapp'         => '+55 (48) 0000-0000',
		'art_contact_address'          => 'R. Fúlvio Aducci, 416 — Florianópolis, SC',
		// Rodapé
		'art_footer_newsletter_title'  => 'Automação aplicada à operação, direto no seu e-mail. {Publicamos quando temos algo concreto para dizer.}',
		'art_footer_newsletter_action' => '',
		'art_footer_newsletter_field'  => 'email',
		'art_footer_lines'             => "Artemis SEO | #\nMktOps | #",
		'art_footer_copyright'         => '© [ano] Studio Artemis. Todos os direitos reservados.',
		'art_footer_privacy_url'       => '/politica-de-privacidade',
		'art_footer_terms_url'         => '/termos-de-uso',
	);
	foreach ( array_keys( artemis_footer_socials() ) as $key ) {
		$d += array( $key => '#' );
	}
	return $d;
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	// Campos de contato global entram na seção "Contato" existente.
	if ( isset( $s['artemis_contact'] ) ) {
		$s['artemis_contact'][1] += array(
			'art_contact_whatsapp' => array( __( 'WhatsApp (como deve aparecer, ex.: +55 (48) 99999-9999)', 'artemis' ), 'text' ),
			'art_contact_address'  => array( __( 'Endereço', 'artemis' ), 'text' ),
		);
	}

	$s['artemis_footer'] = array(
		__( 'Rodapé', 'artemis' ),
		array(
			'art_footer_newsletter_title'  => array( __( 'Frase da newsletter — coloque {entre chaves} o trecho em cinza', 'artemis' ), 'textarea' ),
			'art_footer_newsletter_action' => array( __( 'Newsletter — endereço de envio do formulário (copie do Mailchimp, RD Station etc.). Vazio = formulário desativado', 'artemis' ), 'url' ),
			'art_footer_newsletter_field'  => array( __( 'Newsletter — nome do campo de e-mail pedido pelo serviço (ex.: email, EMAIL)', 'artemis' ), 'text' ),
			'art_footer_lines'             => array( __( '"Outras linhas" — um por linha, no formato: Nome | link', 'artemis' ), 'textarea' ),
			'art_footer_copyright'         => array( __( 'Copyright — [ano] vira o ano atual', 'artemis' ), 'text' ),
			'art_footer_privacy_url'       => array( __( 'Link "Política de privacidade" (usado se a página de privacidade do WordPress não estiver definida)', 'artemis' ), 'text' ),
			'art_footer_terms_url'         => array( __( 'Link "Termos de uso"', 'artemis' ), 'text' ),
		),
	);

	$social = array();
	foreach ( artemis_footer_socials() as $key => $info ) {
		/* translators: %s: nome da rede social. */
		$social[ $key ] = array( sprintf( __( '%s — link do perfil (vazio = esconde o ícone)', 'artemis' ), $info[0] ), 'text' );
	}
	$s['artemis_social'] = array( __( 'Redes sociais', 'artemis' ), $social );

	return $s;
} );

// Seed: cria o menu "Rodapé" com os links do Figma (8165:2352) e atribui ao local "footer", se ainda não houver.
add_filter( 'artemis_seed', function ( $log ) {
	$locations = get_nav_menu_locations();
	if ( ! empty( $locations['footer'] ) && wp_get_nav_menu_object( $locations['footer'] ) ) {
		return $log;
	}
	$menu    = wp_get_nav_menu_object( 'Rodapé' );
	$menu_id = $menu ? (int) $menu->term_id : wp_create_nav_menu( 'Rodapé' );
	if ( is_wp_error( $menu_id ) || ! $menu_id ) {
		$log[] = 'Falha ao criar o menu Rodapé.';
		return $log;
	}
	if ( ! $menu ) {
		$items = array(
			'Serviços'           => '/servicos',
			'Estudos de impacto' => '/#estudos',
			'Blog'               => '/blog',
			'Sobre'              => '/sobre',
			'Contato'            => '/contato',
		);
		$position = 1;
		foreach ( $items as $title => $path ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'    => $title,
				'menu-item-url'      => home_url( $path ),
				'menu-item-type'     => 'custom',
				'menu-item-status'   => 'publish',
				'menu-item-position' => $position++,
			) );
		}
	}
	$locations['footer'] = (int) $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
	$log[] = 'Menu do rodapé criado e atribuído.';
	return $log;
} );

/** "Outras linhas": textarea "Nome | link" → array de [nome, link]. */
function artemis_footer_lines() {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) artemis_mod( 'art_footer_lines' ) ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( '' === $parts[0] ) {
			continue;
		}
		$out[] = array( $parts[0], isset( $parts[1] ) && '' !== $parts[1] ? $parts[1] : '#' );
	}
	return $out;
}

/** Miolo do ícone social (8165:2379…2397) — dribbble e X têm o recorte do Figma. */
function artemis_footer_social_icon( $icon ) {
	$src = esc_url( artemis_asset( 'images/footer/' . $icon . '.svg' ) );
	if ( 'dribbble' === $icon ) {
		return '<span class="site-footer__glyph site-footer__glyph--dribbble"><img src="' . $src . '" width="20" height="22" alt=""></span>';
	}
	if ( 'x' === $icon ) {
		return '<span class="site-footer__glyph site-footer__glyph--x"><img src="' . $src . '" width="14" height="13" alt=""></span>';
	}
	return '<img src="' . $src . '" width="24" height="24" alt="">';
}

/** Menu "footer": <li><a class="site-footer__link"> sem submenus. */
class Artemis_Footer_Nav_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_lvl( &$output, $depth = 0, $args = null ) {}
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$current = in_array( 'current-menu-item', (array) $item->classes, true ) ? ' aria-current="page"' : '';
		$output .= sprintf( '<li><a class="site-footer__link" href="%s"%s>%s</a>', esc_url( $item->url ), $current, esc_html( $item->title ) );
	}
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}

/** Fallback enquanto o menu "footer" não foi criado (mesmos links do Figma). */
function artemis_footer_nav_fallback() {
	$links = array(
		'Serviços'           => home_url( '/servicos' ),
		'Estudos de impacto' => home_url( '/#estudos' ),
		'Blog'               => home_url( '/blog' ),
		'Sobre'              => home_url( '/sobre' ),
		'Contato'            => home_url( '/contato' ),
	);
	echo '<ul class="site-footer__links">';
	foreach ( $links as $label => $url ) {
		printf( '<li><a class="site-footer__link" href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

/** Link interno relativo ("/contato") vira absoluto do site; URLs completas e âncoras ficam como estão. */
function artemis_footer_url( $url ) {
	$url = trim( (string) $url );
	if ( '' !== $url && '/' === $url[0] && 0 !== strpos( $url, '//' ) ) {
		return home_url( $url );
	}
	return $url;
}
