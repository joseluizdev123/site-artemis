<?php
/**
 * MktOps — o problema (Figma 8425:4174 · mobile 8695:3456). Markup espelha partials/mktops-02-problema.html.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="mk-problema" aria-labelledby="mk-problema-title">
	<div class="container mk-problema__inner">
		<div class="mk-problema__row">
			<h2 class="section-title mk-problema__title" id="mk-problema-title"><?php echo esc_html( artemis_mod( 'art_mk_problema_title' ) ); ?></h2>
			<div class="mk-problema__text">
				<?php foreach ( artemis_oqf_paragraphs( artemis_mod( 'art_mk_problema_text' ) ) as $artemis_p ) : ?>
					<p><?php echo $artemis_p; // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_oqf_paragraphs. ?></p>
				<?php endforeach; ?>
			</div>
		</div>
		<picture class="mk-problema__img">
			<source media="(max-width: 767px)" srcset="<?php echo esc_url( artemis_asset( 'images/mktops/m-problema.jpg' ) ); ?>" width="686" height="528">
			<img src="<?php echo esc_url( artemis_asset( 'images/mktops/problema.jpg' ) ); ?>" width="2752" height="1140" alt="<?php esc_attr_e( 'Notebook aberto numa mesa de trabalho, com outra pessoa digitando ao lado', 'artemis' ); ?>" loading="lazy" decoding="async">
		</picture>
	</div>
</section>
