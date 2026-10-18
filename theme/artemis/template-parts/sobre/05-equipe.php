<?php
/**
 * Sobre — quem está por trás, versão A: cards (Figma 8355:2000 · mobile 8689:1996). Markup espelha partials/sobre-05-equipe.html.
 * Sócios do CPT art_membro (ordem "Ordem na página Sobre"): retrato, nome e cargo.
 */

defined( 'ABSPATH' ) || exit;

$artemis_membros = artemis_sobre_membros();
if ( ! $artemis_membros ) {
	return;
}
$artemis_note = (string) artemis_mod( 'art_sobre_note' );
?>
<section class="sobre-equipe" aria-labelledby="sobre-equipe-title">
	<div class="container sobre-equipe__inner">
		<div class="sobre-equipe__head">
			<div class="sobre-equipe__title-box"><h2 class="section-title sobre-equipe__title" id="sobre-equipe-title" data-split="lines"><?php echo artemis_highlight( artemis_mod( 'art_sobre_equipe_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h2></div>
			<p class="sobre-equipe__lead body-lg" data-reveal="up" data-motion-delay="150"><?php echo esc_html( artemis_mod( 'art_sobre_equipe_lead' ) ); ?></p>
		</div>
		<ul class="sobre-equipe__grid">
			<?php foreach ( array_values( $artemis_membros ) as $artemis_i => $artemis_m ) : ?>
				<li class="sobre-card" data-reveal-group data-stagger="0" data-motion-delay="<?php echo (int) $artemis_i * 120; ?>">
					<?php if ( $artemis_m['foto'] ) : ?>
						<div class="sobre-card__media" data-wipe="up" data-wipe-zoom><img class="sobre-card__photo" src="<?php echo esc_url( $artemis_m['foto'] ); ?>" width="811" height="1081" alt="" loading="lazy" decoding="async"></div>
					<?php endif; ?>
					<div class="sobre-card__info">
						<div class="sobre-card__text">
							<h3 class="sobre-card__name" data-reveal="up" data-motion-delay="350"><?php echo esc_html( $artemis_m['nome'] ); ?></h3>
							<?php if ( '' !== $artemis_m['cargo'] ) : ?>
								<p class="sobre-card__role" data-reveal="up" data-motion-delay="450"><?php echo esc_html( $artemis_m['cargo'] ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php if ( '' !== $artemis_note ) : ?>
			<p class="sobre-note" data-reveal="fade"><?php echo esc_html( $artemis_note ); ?></p>
		<?php endif; ?>
	</div>
</section>
