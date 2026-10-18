<?php
/**
 * O que fazemos — Figma 8183:302 (mobile 8635:1990, só CSS). Markup espelha partials/02-o-que-fazemos.html.
 * Conteúdo: Customizer (art_oqf_*) + CPT art_membro (fundadores).
 */

defined( 'ABSPATH' ) || exit;

$artemis_fundadores = artemis_oqf_fundadores();
?>
<section class="o-que-fazemos" aria-labelledby="o-que-fazemos-title">
	<div class="o-que-fazemos__inner container">
		<div class="o-que-fazemos__aside">
			<div class="o-que-fazemos__heading section-head" data-reveal-group>
				<p class="o-que-fazemos__eyebrow eyebrow eyebrow--400" data-reveal="fade"><?php echo esc_html( artemis_mod( 'art_oqf_eyebrow' ) ); ?></p>
				<h2 data-split="lines" class="o-que-fazemos__title section-title section-title--em-400" id="o-que-fazemos-title"><?php echo artemis_highlight( artemis_mod( 'art_oqf_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h2>
			</div>

			<?php if ( $artemis_fundadores ) : ?>
				<div class="o-que-fazemos__founders">
					<ul class="o-que-fazemos__avatars" aria-label="<?php esc_attr_e( 'Fundadores', 'artemis' ); ?>" data-reveal-group>
						<?php foreach ( $artemis_fundadores as $artemis_membro ) : ?>
							<?php echo artemis_oqf_avatar( $artemis_membro ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado na função. ?>
						<?php endforeach; ?>
					</ul>
					<p class="o-que-fazemos__caption"><?php echo wp_kses_post( artemis_oqf_lines( artemis_mod( 'art_oqf_founders_caption' ) ) ); ?></p>
				</div>
			<?php endif; ?>
		</div>

		<div class="o-que-fazemos__body body-lg" data-reveal-group="up" data-stagger="120">
			<?php foreach ( artemis_oqf_paragraphs( artemis_mod( 'art_oqf_body' ) ) as $artemis_paragrafo ) : ?>
				<p><?php echo wp_kses_post( $artemis_paragrafo ); ?></p>
			<?php endforeach; ?>
		</div>
	</div>
</section>
