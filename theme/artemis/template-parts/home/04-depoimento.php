<?php
/**
 * Depoimento — Figma 8186:1160. Markup espelha partials/04-depoimento.html.
 * Conteúdo: Customizer (art_depoimento_eyebrow) + CPT art_depoimento.
 */

defined( 'ABSPATH' ) || exit;

$artemis_depoimento = artemis_depoimento_get();
if ( ! $artemis_depoimento ) {
	return;
}
$artemis_dep_nome    = get_the_title( $artemis_depoimento );
$artemis_dep_cargo   = get_post_meta( $artemis_depoimento->ID, '_art_depoimento_cargo', true );
$artemis_dep_empresa = get_post_meta( $artemis_depoimento->ID, '_art_depoimento_empresa', true );
$artemis_dep_foto    = get_post_thumbnail_id( $artemis_depoimento );
?>
<section class="depoimento" aria-labelledby="depoimento-title">
	<div class="container depoimento__inner">
		<div class="depoimento__globe" aria-hidden="true" data-globe="<?php echo esc_url( artemis_asset( 'data/globe-points.json' ) ); ?>">
			<canvas class="depoimento__globe-canvas" width="935" height="443"></canvas>
			<noscript><img src="<?php echo esc_url( artemis_asset( 'images/04-depoimento/globe.svg' ) ); ?>" width="935" height="443" alt="" loading="lazy" decoding="async"></noscript>
		</div>

		<h2 class="eyebrow depoimento__eyebrow" id="depoimento-title"><?php echo esc_html( artemis_mod( 'art_depoimento_eyebrow' ) ); ?></h2>

		<figure class="depoimento__card">
			<blockquote class="depoimento__quote">
				<p class="section-title"><?php echo esc_html( artemis_depoimento_citacao( $artemis_depoimento ) ); ?></p>
			</blockquote>
			<figcaption class="depoimento__author">
				<?php if ( $artemis_dep_foto ) : ?>
					<?php /* translators: %s: nome do autor do depoimento. */ ?>
					<img class="depoimento__avatar" src="<?php echo esc_url( wp_get_attachment_image_url( $artemis_dep_foto, 'thumbnail' ) ); ?>" width="51" height="51" alt="<?php echo esc_attr( sprintf( __( 'Foto de %s', 'artemis' ), $artemis_dep_nome ) ); ?>" loading="lazy">
				<?php endif; ?>
				<div class="depoimento__author-text">
					<p class="depoimento__name"><strong><?php echo esc_html( $artemis_dep_nome . ( $artemis_dep_cargo ? ',' : '' ) ); ?></strong><?php echo $artemis_dep_cargo ? ' ' . esc_html( $artemis_dep_cargo ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput -- escapado no ternário. ?></p>
					<?php if ( $artemis_dep_empresa ) : ?>
						<p class="depoimento__company"><?php echo esc_html( $artemis_dep_empresa ); ?></p>
					<?php endif; ?>
				</div>
			</figcaption>
		</figure>
	</div>
</section>
