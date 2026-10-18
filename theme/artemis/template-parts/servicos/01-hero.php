<?php
/**
 * Serviços — topo (Figma 8340:2096 · mobile 8699:4923). Markup espelha partials/servicos-01-hero.html.
 * Motion ao carregar: título por linhas → texto → botão (atributos data-* lidos por assets/js/motion.js).
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="srv-hero" aria-labelledby="srv-hero-title">
	<div class="container srv-hero__inner">
		<div class="srv-hero__article">
			<h1 class="srv-hero__title" id="srv-hero-title" data-split="lines" data-motion-start="load"><?php echo artemis_highlight( artemis_mod( 'art_srv_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h1>
			<p class="srv-hero__lead" data-reveal="up" data-motion-start="load" data-motion-delay="350"><?php echo esc_html( artemis_mod( 'art_srv_lead' ) ); ?></p>
		</div>
		<a class="btn btn--primary srv-hero__cta" data-reveal="up" data-motion-start="load" data-motion-delay="500" href="<?php echo esc_url( artemis_mod( 'art_contact_cta_url' ) ); ?>"><?php echo esc_html( artemis_mod( 'art_srv_cta' ) ); ?></a>
	</div>
</section>
