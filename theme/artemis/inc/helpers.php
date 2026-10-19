<?php
/**
 * Utilitários do tema.
 */

defined( 'ABSPATH' ) || exit;

/** Arquivos do tema que casam com o padrão, em ordem alfabética (NN- define a ordem). */
function artemis_glob( $pattern ) {
	$files = glob( ARTEMIS_DIR . '/' . $pattern );
	if ( ! $files ) {
		return array();
	}
	sort( $files );
	return $files;
}

/** URL de um arquivo em assets/. */
function artemis_asset( $path ) {
	return ARTEMIS_URI . '/assets/' . ltrim( $path, '/' );
}

/**
 * Valor de conteúdo com default central definido em customizer.php.
 * Dados globais (contato, redes, link do CTA, destinatário do formulário) vêm da página "Dados do site" (inc/options.php);
 * enquanto ela não foi salva, vale o que estava no Personalizar (theme mod) ou o default.
 */
function artemis_mod( $key ) {
	if ( artemis_is_site_option( $key ) ) {
		$site = get_option( 'artemis_site' );
		if ( is_array( $site ) && array_key_exists( $key, $site ) ) {
			return $site[ $key ];
		}
	}
	$defaults = artemis_customizer_defaults();
	return get_theme_mod( $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

/** Link do WhatsApp a partir do número como aparece no site; sem código do país (+ ou 00), assume Brasil (+55). */
function artemis_whatsapp_url( $number ) {
	$number = trim( (string) $number );
	$digits = preg_replace( '/\D+/', '', $number );
	if ( '' === $digits ) {
		return '';
	}
	if ( '+' === substr( $number, 0, 1 ) || '00' === substr( $digits, 0, 2 ) ) { // código do país escrito: fica como está
		$digits = ltrim( $digits, '0' );
	} else {
		$digits = ltrim( $digits, '0' ); // 048… → 48…
		if ( strlen( $digits ) <= 11 ) { // DDD + número
			$digits = '55' . $digits;
		}
	}
	return 'https://wa.me/' . $digits;
}

/** Clientes publicados, na ordem do menu_order. */
function artemis_get_clientes() {
	return get_posts( array(
		'post_type'      => 'art_cliente',
		'posts_per_page' => 10,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	) );
}

/** Menu sem <li>: só <a>, como no Figma (Navigation Links). */
class Artemis_Flat_Nav_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_lvl( &$output, $depth = 0, $args = null ) {}
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = (array) $item->classes;
		$current = in_array( 'current-menu-item', $classes, true ) ? ' aria-current="page"' : ( in_array( 'current_page_parent', $classes, true ) ? ' aria-current="true"' : '' ); // "Blog" num artigo = seção atual
		$output .= sprintf( '<a href="%s"%s>%s</a>', esc_url( $item->url ), $current, esc_html( $item->title ) );
	}
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}

/** Área do Blog (página de posts, artigo, categoria): header na variante Blog (8340:2124) — selo "BLOG", link "Início", "Blog" atual. */
function artemis_is_blog() {
	return ( is_home() && ! is_front_page() ) || is_singular( 'post' ) || is_category();
}

/** URL do Blog: a página de posts definida em Configurações → Leitura, senão /blog. */
function artemis_blog_url() {
	$page = (int) get_option( 'page_for_posts' );
	return $page ? get_permalink( $page ) : home_url( '/blog' );
}

/** Fallback enquanto o menu "primary" não foi criado. */
function artemis_nav_fallback() {
	$links = array(
		'Serviços'           => home_url( '/servicos' ),
		'Estudos de impacto' => home_url( '/#estudos' ),
		'Blog'               => artemis_blog_url(),
		'Sobre'              => home_url( '/sobre' ),
		'MktOps'             => home_url( '/mktops' ),
	);
	foreach ( $links as $label => $url ) {
		$current = ( 'Blog' === $label && artemis_is_blog() ) ? ( is_home() ? ' aria-current="page"' : ' aria-current="true"' ) : '';
		if ( ( 'Serviços' === $label && is_page( 'servicos' ) ) || ( 'Sobre' === $label && is_page( 'sobre' ) ) || ( 'MktOps' === $label && is_page( 'mktops' ) ) ) {
			$current = ' aria-current="page"';
		}
		printf( '<a href="%s"%s>%s</a>', esc_url( $url ), $current, esc_html( $label ) );
	}
}
