<?php
/**
 * Sobre — topo (Figma 8355:1920 · mobile 8689:1935). Markup espelha partials/sobre-01-hero.html.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="sobre-hero" aria-labelledby="sobre-hero-title">
	<img class="sobre-hero__circles" src="<?php echo esc_url( artemis_asset( 'images/sobre/circulos.svg' ) ); ?>" width="1920" height="522" alt="" aria-hidden="true" data-reveal="pop" data-motion-start="load" data-parallax="0.12" data-parallax-max="24">
	<div class="container sobre-hero__inner">
		<div class="sobre-hero__article">
			<h1 class="sobre-hero__title" id="sobre-hero-title" data-split="lines" data-motion-start="load" data-motion-delay="200"><?php echo artemis_highlight( artemis_mod( 'art_sobre_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h1>
			<p class="sobre-hero__lead" data-reveal="up" data-motion-start="load" data-motion-delay="500"><?php echo esc_html( artemis_mod( 'art_sobre_lead' ) ); ?></p>
		</div>
	</div>
</section>
