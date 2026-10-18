<?php
/**
 * Sobre — quem está por trás, versão B: fotos na rolagem + bio (Figma 8355:3485 · mobile 8689:2027).
 * Markup espelha partials/sobre-06-socios.html; comportamento em assets/js/sections/sobre-06-socios.js.
 * Bio = texto do editor do sócio (CPT art_membro).
 */

defined( 'ABSPATH' ) || exit;

$artemis_membros = artemis_sobre_membros();
if ( ! $artemis_membros ) {
	return;
}
$artemis_note = (string) artemis_mod( 'art_sobre_note' );
?>
<section class="sobre-socios" aria-labelledby="sobre-socios-title">
	<div class="container sobre-socios__inner">
		<div class="sobre-socios__list" data-socios>
			<div class="sobre-socios__intro">
				<h2 class="section-title sobre-socios__title" id="sobre-socios-title" data-split="lines"><?php echo esc_html( artemis_mod( 'art_sobre_socios_title' ) ); ?></h2>
				<p class="sobre-socios__lead body-lg" data-reveal="up" data-motion-delay="150"><?php echo esc_html( artemis_mod( 'art_sobre_equipe_lead' ) ); ?></p>
			</div>
			<div class="sobre-socios__photos">
				<?php foreach ( $artemis_membros as $artemis_i => $artemis_m ) : ?>
					<figure class="sobre-socios__photo<?php echo 0 === $artemis_i ? ' is-active' : ''; ?>" data-socio="<?php echo (int) $artemis_i; ?>">
						<?php if ( $artemis_m['foto'] ) : ?>
							<img src="<?php echo esc_url( $artemis_m['foto'] ); ?>" width="811" height="1081" alt="<?php echo esc_attr( $artemis_m['nome'] ); ?>" loading="lazy" decoding="async">
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
			<div class="sobre-socios__info" aria-live="polite">
				<?php foreach ( $artemis_membros as $artemis_i => $artemis_m ) : ?>
					<article class="sobre-socios__member<?php echo 0 === $artemis_i ? ' is-active' : ''; ?>" data-socio="<?php echo (int) $artemis_i; ?>"<?php echo 0 === $artemis_i ? '' : ' aria-hidden="true"'; ?>>
						<div class="sobre-socios__text">
							<h3 class="sobre-socios__name"><?php echo esc_html( $artemis_m['nome'] ); ?></h3>
							<?php if ( '' !== $artemis_m['cargo'] ) : ?>
								<p class="sobre-socios__role"><?php echo esc_html( $artemis_m['cargo'] ); ?></p>
							<?php endif; ?>
						</div>
						<?php if ( '' !== $artemis_m['bio'] ) : ?>
							<div class="sobre-socios__bio"><?php echo wpautop( wp_kses_post( $artemis_m['bio'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- filtrado por wp_kses_post. ?></div>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
		<?php if ( '' !== $artemis_note ) : ?>
			<p class="sobre-note" data-reveal="up"><?php echo esc_html( $artemis_note ); ?></p>
		<?php endif; ?>
	</div>
</section>
