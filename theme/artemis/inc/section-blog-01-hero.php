<?php
/**
 * Blog — hero + abas de categoria (Figma 8325:2675 · mobile 8673:3847).
 * Template: template-parts/blog/01-hero.php · espelha partials/blog-01-hero.html.
 *
 * Customizer        art_blog_eyebrow (8325:2678) · art_blog_title (8325:2679, {entre chaves} = trecho em cinza)
 *                   art_blog_text (8325:2680)
 * Categoria nativa  abas (8325:2725): as mesmas categorias que viram seção abaixo, na mesma ordem
 *                   (_art_ordem) e com a mesma âncora (#slug) — funções de inc/section-blog-03-categoria.php
 * Banner            linhas + luzes decorativas (assets/images/blog-01-hero, assets/js/sections/blog-01-hero.js)
 */

defined( 'ABSPATH' ) || exit;

/* --------------------------------------------------------------------------
   Customizer — textos (8325:2733)
   -------------------------------------------------------------------------- */
add_filter( 'artemis_customizer_defaults', function ( $d ) {
	return $d + array(
		'art_blog_eyebrow' => 'Blog',
		'art_blog_title'   => 'O que aprendemos colocando {inteligência artificial} em produção',
		'art_blog_text'    => 'Análises dos sistemas que construímos, guias de implementação e o registro honesto do que funciona e do que não funciona. Escrito por quem entrega, não por quem assiste.',
	);
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$s['artemis_blog_hero'] = array(
		__( 'Blog — Topo', 'artemis' ),
		array(
			'art_blog_eyebrow' => array( __( 'Sobretítulo', 'artemis' ), 'text' ),
			'art_blog_title'   => array( __( 'Título — coloque {entre chaves} o trecho em cinza', 'artemis' ), 'textarea' ),
			'art_blog_text'    => array( __( 'Texto de apoio', 'artemis' ), 'textarea' ),
		),
	);
	return $s;
} );

/* --------------------------------------------------------------------------
   Abas
   -------------------------------------------------------------------------- */

/**
 * Abas do hero: [ rótulo, âncora ] das categorias que aparecem como seção na página
 * (com posts além do destaque). Sem nenhuma, as 5 categorias do Figma.
 */
function artemis_blog_hero_tabs() {
	$tabs = array();
	if ( function_exists( 'artemis_blog_categories' ) ) {
		foreach ( artemis_blog_categories() as $term ) {
			if ( artemis_blog_cat_posts( $term ) ) {
				$tabs[] = array( $term->name, artemis_blog_cat_anchor( $term ) );
			}
		}
	}
	if ( ! $tabs ) {
		$tabs = array(
			array( 'Análises de caso', 'analises-de-caso' ),
			array( 'Implementação de IA', 'implementacao-de-ia' ),
			array( 'IA na operação', 'ia-na-operacao' ),
			array( 'SEO e AI Search', 'seo-e-ai-search' ),
			array( 'Marketing com IA', 'marketing-com-ia' ),
		);
	}
	return apply_filters( 'artemis_blog_hero_tabs', $tabs );
}
