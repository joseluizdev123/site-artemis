<?php
/**
 * Sobre — três coisas que se repetem (Figma 8355:1946 · mobile 8689:1956). Markup espelha partials/sobre-03-tres-coisas.html.
 * Itens sem título não aparecem; o número segue a posição (01, 02, 03).
 */

defined( 'ABSPATH' ) || exit;

$artemis_items = array();
for ( $artemis_i = 1; $artemis_i <= 3; $artemis_i++ ) {
	$artemis_t = (string) artemis_mod( 'art_sobre_tres_' . $artemis_i . '_title' );
	if ( '' !== $artemis_t ) {
		$artemis_items[] = array( $artemis_t, (string) artemis_mod( 'art_sobre_tres_' . $artemis_i . '_text' ) );
	}
}
?>
<section class="sobre-tres" aria-labelledby="sobre-tres-title">
	<div class="container">
		<div class="sobre-tres__inner">
			<h2 class="section-title sobre-tres__title" id="sobre-tres-title"><?php echo artemis_highlight( artemis_mod( 'art_sobre_tres_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h2>
			<ol class="sobre-tres__list">
				<?php foreach ( $artemis_items as $artemis_n => $artemis_item ) : ?>
					<li class="sobre-tres__item">
						<span class="sobre-tres__num"><?php echo esc_html( sprintf( '%02d', $artemis_n + 1 ) ); ?></span>
						<img class="sobre-tres__mark" src="<?php echo esc_url( artemis_asset( 'images/sobre/marcador.svg' ) ); ?>" width="4" height="48" alt="">
						<div class="sobre-tres__text">
							<h3 class="sobre-tres__item-title"><?php echo esc_html( $artemis_item[0] ); ?></h3>
							<p class="sobre-tres__item-text"><?php echo esc_html( $artemis_item[1] ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</section>
