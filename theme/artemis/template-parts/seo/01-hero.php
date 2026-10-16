<?php
/**
 * Artemis SEO — topo (Figma 8388:1923 · mobile 8704:6452). Markup espelha partials/seo-01-hero.html.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="seo-hero" aria-labelledby="seo-hero-title">
	<picture class="seo-hero__bg" aria-hidden="true">
		<source media="(max-width: 767px)" srcset="<?php echo esc_url( artemis_asset( 'images/seo/m-hero-fundo.jpg' ) ); ?>" width="750" height="1196">
		<img src="<?php echo esc_url( artemis_asset( 'images/seo/hero-fundo.jpg' ) ); ?>" width="2880" height="960" alt="" decoding="async">
	</picture>
	<div class="container seo-hero__inner">
		<div class="seo-hero__article">
			<div class="seo-hero__heading">
				<h1 class="seo-hero__title" id="seo-hero-title"><?php echo artemis_highlight( artemis_mod( 'art_seo_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h1>
				<p class="seo-hero__lead"><?php echo esc_html( artemis_mod( 'art_seo_lead' ) ); ?></p>
			</div>
			<div class="seo-hero__actions">
				<a class="btn btn--primary" href="<?php echo esc_url( artemis_mod( 'art_seo_btn1_url' ) ); ?>"><?php echo esc_html( artemis_mod( 'art_seo_btn1' ) ); ?></a>
				<?php if ( '' !== (string) artemis_mod( 'art_seo_btn2' ) ) : ?>
					<a class="btn" href="<?php echo esc_url( artemis_mod( 'art_seo_btn2_url' ) ); ?>"><?php echo esc_html( artemis_mod( 'art_seo_btn2' ) ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<picture class="seo-hero__mockup">
			<source media="(max-width: 767px)" srcset="<?php echo esc_url( artemis_asset( 'images/seo/m-mockup.png' ) ); ?>" width="686" height="1338">
			<img src="<?php echo esc_url( artemis_asset( 'images/seo/mockup.png' ) ); ?>" width="1814" height="1088" alt="<?php esc_attr_e( 'Conversa num assistente de IA: à pergunta “Como reduzir parada não planejada em linha de produção?”, a resposta cita seusite.com.br como fonte, ao lado de concorrentes', 'artemis' ); ?>" decoding="async">
		</picture>
	</div>
</section>
