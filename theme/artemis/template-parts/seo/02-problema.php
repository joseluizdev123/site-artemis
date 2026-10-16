<?php
/**
 * Artemis SEO — o problema (Figma 8393:1582 · mobile 8704:6597). Markup espelha partials/seo-02-problema.html.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="seo-problema" aria-labelledby="seo-problema-title">
	<div class="container seo-problema__inner">
		<div class="seo-problema__head">
			<h2 class="section-title" id="seo-problema-title"><?php echo esc_html( artemis_mod( 'art_seo_problema_title' ) ); ?></h2>
		</div>
		<div class="seo-problema__text">
			<?php foreach ( artemis_oqf_paragraphs( artemis_mod( 'art_seo_problema_text' ) ) as $artemis_p ) : ?>
				<p><?php echo $artemis_p; // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_oqf_paragraphs. ?></p>
			<?php endforeach; ?>
		</div>
	</div>
</section>
