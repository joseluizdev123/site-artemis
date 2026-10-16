<?php
/**
 * Sobre — o sistema audita a sua operação (Figma 8355:1996 · mobile 8689:1992). Markup espelha partials/sobre-04-audita.html.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="sobre-audita" aria-labelledby="sobre-audita-title">
	<div class="container sobre-split sobre-audita__inner">
		<div class="sobre-audita__head">
			<h2 class="section-title sobre-audita__title" id="sobre-audita-title"><?php echo artemis_highlight( artemis_mod( 'art_sobre_audita_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h2>
		</div>
		<div class="sobre-split__text"><?php echo artemis_sobre_paragraphs( 'art_sobre_audita_text' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_oqf_paragraphs. ?></div>
	</div>
</section>
