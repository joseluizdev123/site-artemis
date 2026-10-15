<?php
/**
 * Blog — artigo em destaque (Figma 8314:1380 · mobile 8673:3901).
 * Template: template-parts/blog/02-destaque.php · espelha partials/blog-02-destaque.html.
 *
 * Post nativo       o fixado mais recente; sem fixados, o mais recente (filtro artemis_blog_featured_id)
 *                   imagem destacada (8314:1572) · categoria + data (8314:1681) · título (8314:1571)
 *                   resumo = linha fina (8314:1660)
 * Usuário (autor)   nome de exibição (8314:1663) · user meta art_cargo (8314:1665) · get_avatar / art_avatar (8314:1675)
 * Customizer        art_blog_destaque_tag → selo "Em destaque" (8314:2646)
 *
 * Meta, autor e linha fina reaproveitam artemis_blog_post_meta() / artemis_blog_author() / artemis_blog_excerpt()
 * de inc/section-blog-03-categoria.php (prefixo BEM blog-destaque); os fallbacks abaixo geram a mesma marcação.
 * Os posts, a capa e a foto do autor vêm do seed da seção de categorias (mesmos arquivos do Figma).
 */

defined( 'ABSPATH' ) || exit;

/* --------------------------------------------------------------------------
   Customizer — selo (8314:2646)
   -------------------------------------------------------------------------- */
add_filter( 'artemis_customizer_defaults', function ( $d ) {
	return $d + array(
		'art_blog_destaque_tag' => 'Em destaque',
	);
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$s['artemis_blog_destaque'] = array(
		__( 'Blog — Artigo em destaque', 'artemis' ),
		array(
			'art_blog_destaque_tag' => array( __( 'Selo acima do artigo (vazio esconde). O artigo é o post fixado mais recente.', 'artemis' ), 'text' ),
		),
	);
	return $s;
} );

/* --------------------------------------------------------------------------
   Consulta
   -------------------------------------------------------------------------- */

/** Post em destaque: o fixado mais recente; sem fixados, o mais recente. Mesma regra que as categorias excluem. */
function artemis_blog_destaque_post() {
	if ( function_exists( 'artemis_blog_cat_featured_id' ) ) {
		$id = artemis_blog_cat_featured_id();
	} else {
		$args   = array(
			'post_type'   => 'post',
			'numberposts' => 1,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'fields'      => 'ids',
		);
		$sticky = array_filter( array_map( 'absint', (array) get_option( 'sticky_posts', array() ) ) );
		$found  = $sticky ? get_posts( $args + array( 'post__in' => $sticky ) ) : array();
		if ( ! $found ) {
			$found = get_posts( $args );
		}
		$id = (int) apply_filters( 'artemis_blog_featured_id', $found ? (int) $found[0] : 0 );
	}
	$post = $id ? get_post( $id ) : null;
	return ( $post && 'post' === $post->post_type && 'publish' === $post->post_status ) ? $post : null;
}

/* --------------------------------------------------------------------------
   Partes do card (já escapadas)
   -------------------------------------------------------------------------- */

/** Imagem destacada (Article Image 8314:1572): 4:3 em cover; metade do card no desktop, largura total ≤1023. */
function artemis_blog_destaque_image( $post ) {
	$thumb = get_post_thumbnail_id( $post );
	if ( ! $thumb ) {
		return '';
	}
	return (string) wp_get_attachment_image( $thumb, 'large', false, array(
		'class'    => 'blog-destaque__img',
		'sizes'    => '(max-width: 1023px) calc(100vw - 64px), (max-width: 1439px) 45vw, 616px',
		'decoding' => 'async',
	) );
}

/** Linha "Categoria · 13 de março, 2026" (Meta Information 8314:1681). */
function artemis_blog_destaque_meta( $post ) {
	if ( function_exists( 'artemis_blog_post_meta' ) ) {
		return artemis_blog_post_meta( $post, null, 'blog-destaque' );
	}
	$cats = get_the_category( $post->ID );
	$html = '<p class="blog-destaque__meta">';
	if ( $cats ) {
		$html .= '<span class="blog-destaque__cat">' . esc_html( $cats[0]->name ) . '</span>';
		$html .= '<span class="blog-destaque__dot" aria-hidden="true">·</span>';
	}
	$html .= '<time class="blog-destaque__date" datetime="' . esc_attr( get_the_date( 'Y-m-d', $post ) ) . '">' . esc_html( get_the_date( 'j \d\e F, Y', $post ) ) . '</time>';
	return $html . '</p>';
}

/** Autor: foto + nome + cargo (Author Info 8314:1664). */
function artemis_blog_destaque_author( $post ) {
	if ( function_exists( 'artemis_blog_author' ) ) {
		return artemis_blog_author( $post, 'blog-destaque' );
	}
	$author = (int) $post->post_author;
	if ( ! $author ) {
		return '';
	}
	$cargo  = (string) get_user_meta( $author, 'art_cargo', true );
	$avatar = get_avatar( $author, 56, '', '', array( 'class' => 'blog-destaque__avatar' ) );

	$html  = '<div class="blog-destaque__author">' . ( $avatar ? $avatar : '' );
	$html .= '<p class="blog-destaque__author-details">';
	$html .= '<span class="blog-destaque__author-name">' . esc_html( get_the_author_meta( 'display_name', $author ) ) . '</span>';
	if ( '' !== $cargo ) {
		$html .= '<span class="blog-destaque__author-role">' . esc_html( $cargo ) . '</span>';
	}
	return $html . '</p></div>';
}

/** Linha fina (Description 8314:1660): o resumo do post; sem resumo, o começo do texto. Texto puro. */
function artemis_blog_destaque_excerpt( $post ) {
	if ( function_exists( 'artemis_blog_excerpt' ) ) {
		return artemis_blog_excerpt( $post );
	}
	if ( has_excerpt( $post ) ) {
		return trim( wp_strip_all_tags( $post->post_excerpt ) );
	}
	return wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 18, '…' );
}

/* --------------------------------------------------------------------------
   Seed — não cria posts (a seção de categorias cria e fixa o destaque).
   Só garante, no post fixado do seed, a capa do Figma e o texto alternativo do estático.
   -------------------------------------------------------------------------- */
add_filter( 'artemis_seed', function ( $log ) {
	$ids = get_posts( array(
		'post_type'   => 'post',
		'post_status' => 'any',
		'meta_key'    => '_artemis_seed_key',
		'meta_value'  => 'blog-destaque',
		'numberposts' => 1,
		'fields'      => 'ids',
	) );
	if ( ! $ids ) {
		return $log;
	}
	$post_id = (int) $ids[0];
	if ( ! has_post_thumbnail( $post_id ) ) {
		$capa = artemis_seed_media( 'images/blog-02-destaque/capa-wcontrol.png', 'Capa — Anatomia do WControl' );
		if ( $capa ) {
			set_post_thumbnail( $post_id, $capa );
			$log[] = 'Capa do artigo em destaque definida.';
		}
	}
	// Texto alternativo só em capa importada pelo seed e ainda sem alt.
	$thumb = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb && get_post_meta( $thumb, '_artemis_seed_src', true ) && '' === (string) get_post_meta( $thumb, '_wp_attachment_image_alt', true ) ) {
		update_post_meta( $thumb, '_wp_attachment_image_alt', 'Tela de estatísticas do WControl: documentos aprovados, reprovados e em processamento' );
	}
	return $log;
}, 20 ); // depois do seed das categorias (prioridade 10), que cria o post fixado.
