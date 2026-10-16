<?php
/**
 * MktOps — topo (Figma 8425:3827 · mobile 8695:3271). Markup espelha partials/mktops-01-hero.html.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="mk-hero" aria-labelledby="mk-hero-title">
	<div class="container mk-hero__inner">
		<div class="mk-hero__article">
			<div class="mk-hero__heading">
				<h1 class="mk-hero__title" id="mk-hero-title"><?php echo artemis_highlight( artemis_mod( 'art_mk_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h1>
				<p class="mk-hero__lead"><?php echo esc_html( artemis_mod( 'art_mk_lead' ) ); ?></p>
			</div>
			<div class="mk-hero__actions">
				<a class="btn btn--primary" href="<?php echo esc_url( artemis_mod( 'art_mk_btn1_url' ) ); ?>"><?php echo esc_html( artemis_mod( 'art_mk_btn1' ) ); ?></a>
				<?php if ( '' !== (string) artemis_mod( 'art_mk_btn2' ) ) : ?>
					<a class="btn mk-hero__btn" href="<?php echo esc_url( artemis_mod( 'art_mk_btn2_url' ) ); ?>"><?php echo esc_html( artemis_mod( 'art_mk_btn2' ) ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<figure class="mk-hero__diagram">
			<picture>
				<source media="(max-width: 767px)" srcset="<?php echo esc_url( artemis_asset( 'images/mktops/m-hero-diagrama.svg' ) ); ?>" width="208" height="881">
				<img src="<?php echo esc_url( artemis_asset( 'images/mktops/hero-diagrama.svg' ) ); ?>" width="1184" height="290" alt="<?php esc_attr_e( 'Diagrama do MktOps: Skills, MCP, Pipelines e Infra conectados à operação no centro', 'artemis' ); ?>" decoding="async">
			</picture>
		</figure>
	</div>
</section>
