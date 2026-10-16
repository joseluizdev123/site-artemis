<?php
/**
 * Serviços — topo (Figma 8340:2096 · mobile 8699:4923). Markup espelha partials/servicos-01-hero.html.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="srv-hero" aria-labelledby="srv-hero-title">
	<div class="container srv-hero__inner">
		<div class="srv-hero__article">
			<h1 class="srv-hero__title" id="srv-hero-title"><?php echo artemis_highlight( artemis_mod( 'art_srv_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h1>
			<p class="srv-hero__lead"><?php echo esc_html( artemis_mod( 'art_srv_lead' ) ); ?></p>
		</div>
		<a class="btn btn--primary srv-hero__cta" href="<?php echo esc_url( artemis_mod( 'art_contact_cta_url' ) ); ?>"><?php echo esc_html( artemis_mod( 'art_srv_cta' ) ); ?></a>
	</div>
</section>
