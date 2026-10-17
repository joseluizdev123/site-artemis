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
		<figure class="mk-hero__diagram" role="img" aria-label="<?php esc_attr_e( 'Diagrama do MktOps: Skills, MCP, Pipelines e Infra conectados à operação no centro', 'artemis' ); ?>">
			<img class="mk-hero__floor" src="<?php echo esc_url( artemis_asset( 'images/mktops/hero-padrao.svg' ) ); ?>" width="1184" height="290" alt="" decoding="async">
			<?php get_template_part( 'template-parts/mktops/svg/diagrama' ); // diagrama em camadas, animado por mktops-01-hero.js. ?>
		</figure>
	</div>
</section>
