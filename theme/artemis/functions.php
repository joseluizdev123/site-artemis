<?php
/**
 * Studio Artemis — setup do tema.
 *
 * Estrutura modular por seção da Home (espelha partials/NN-slug.html do estático):
 *   inc/section-NN-slug.php           → CPT, Customizer (filtros) e seed da seção
 *   template-parts/home/NN-slug.php   → markup da seção
 *   assets/css/sections/NN-slug.css   → estilos da seção
 *   assets/js/sections/NN-slug.js     → comportamento da seção (opcional)
 */

defined( 'ABSPATH' ) || exit;

define( 'ARTEMIS_VERSION', '0.2.0' );
define( 'ARTEMIS_DIR', get_template_directory() );
define( 'ARTEMIS_URI', get_template_directory_uri() );

require_once ARTEMIS_DIR . '/inc/helpers.php';
require_once ARTEMIS_DIR . '/inc/customizer.php';
require_once ARTEMIS_DIR . '/inc/options.php';
require_once ARTEMIS_DIR . '/inc/seeder.php';
require_once ARTEMIS_DIR . '/inc/cpt-cliente.php';

foreach ( artemis_glob( 'inc/section-*.php' ) as $artemis_section_file ) {
	require_once $artemis_section_file;
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	register_nav_menus( array(
		'primary' => __( 'Menu principal', 'artemis' ),
		'footer'  => __( 'Menu do rodapé', 'artemis' ),
	) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'artemis-fonts', 'https://fonts.googleapis.com/css2?family=Cabin:wdth,wght@75..100,400..700&family=Exo:wght@400..700&family=Instrument+Serif:ital@0;1&display=swap', array(), null );
	wp_enqueue_style( 'artemis-main', ARTEMIS_URI . '/assets/css/main.css', array( 'artemis-fonts' ), (string) filemtime( ARTEMIS_DIR . '/assets/css/main.css' ) );
	wp_enqueue_style( 'artemis-motion', ARTEMIS_URI . '/assets/css/motion.css', array( 'artemis-main' ), (string) filemtime( ARTEMIS_DIR . '/assets/css/motion.css' ) );
	foreach ( artemis_glob( 'assets/css/sections/*.css' ) as $file ) {
		$slug = basename( $file, '.css' );
		wp_enqueue_style( 'artemis-' . $slug, ARTEMIS_URI . '/assets/css/sections/' . $slug . '.css', array( 'artemis-main' ), (string) filemtime( $file ) );
	}

	wp_enqueue_script( 'artemis-main', ARTEMIS_URI . '/assets/js/main.js', array(), (string) filemtime( ARTEMIS_DIR . '/assets/js/main.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	// Motion (assets/js/motion.js + assets/css/motion.css): entradas e microinterações do site inteiro
	wp_enqueue_script( 'artemis-motion', ARTEMIS_URI . '/assets/js/motion.js', array( 'artemis-main' ), (string) filemtime( ARTEMIS_DIR . '/assets/js/motion.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	foreach ( artemis_glob( 'assets/js/sections/*.js' ) as $file ) {
		$slug = basename( $file, '.js' );
		wp_enqueue_script( 'artemis-' . $slug, ARTEMIS_URI . '/assets/js/sections/' . $slug . '.js', array( 'artemis-main' ), (string) filemtime( $file ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
} );

/* Motion: liga html.motion-on antes da 1ª pintura (só com movimento liberado); volta sozinho se o motion.js não carregar */
add_action( 'wp_head', function () {
	echo "<script>(function(d){if(matchMedia('(prefers-reduced-motion: no-preference)').matches&&'IntersectionObserver' in window){d.classList.add('motion-on');setTimeout(function(){if(!window.ArtemisMotion)d.classList.remove('motion-on')},4000)}})(document.documentElement)</script>
";
}, 1 );

/* Favicon: a marca "A" (assets/images/favicon.svg) enquanto o Ícone do site não é definido no Personalizar. */
add_action( 'wp_head', function () {
	if ( ! has_site_icon() ) {
		printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "
", esc_url( artemis_asset( 'images/favicon.svg' ) ) );
	}
} );

add_filter( 'wp_resource_hints', function ( $urls, $relation ) {
	if ( 'preconnect' === $relation ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}, 10, 2 );
