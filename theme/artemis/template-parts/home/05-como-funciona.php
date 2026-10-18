<?php
/**
 * Como funciona — Figma 8307:3380. Markup espelha partials/05-como-funciona.html.
 * Conteúdo: Customizer (art_cf_*) — ver inc/section-05-como-funciona.php.
 */

defined( 'ABSPATH' ) || exit;

$artemis_cf_paragraphs = artemis_como_funciona_paragraphs( artemis_mod( 'art_cf_text' ) );
$artemis_cf_steps      = artemis_como_funciona_steps();
$artemis_cf_last       = count( $artemis_cf_steps ) - 1;
?>
<section class="como-funciona" id="como-funciona" aria-labelledby="como-funciona-title">
	<div class="container">
		<div class="como-funciona__blocks">
			<div class="como-funciona__intro" data-reveal-group>
				<p class="eyebrow" data-reveal="fade"><?php echo esc_html( artemis_mod( 'art_cf_eyebrow' ) ); ?></p>
				<div class="como-funciona__text">
					<h2 class="section-title section-title--em-400" id="como-funciona-title" data-split="lines"><?php echo artemis_highlight( artemis_mod( 'art_cf_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h2>
					<div class="como-funciona__desc">
						<?php foreach ( $artemis_cf_paragraphs as $artemis_cf_paragraph ) : ?>
							<p class="body-lg" data-reveal="up"><?php echo esc_html( $artemis_cf_paragraph ); ?></p>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<?php if ( $artemis_cf_steps ) : ?>
				<div class="como-funciona__main">
					<p class="eyebrow" data-reveal="fade"><?php echo esc_html( artemis_mod( 'art_cf_steps_eyebrow' ) ); ?></p>
					<ol class="como-funciona__steps">
						<?php foreach ( $artemis_cf_steps as $artemis_cf_index => $artemis_cf_step ) : ?>
							<li class="como-funciona__step">
								<?php if ( $artemis_cf_index < $artemis_cf_last ) : ?>
									<img class="como-funciona__marker" src="<?php echo esc_url( artemis_asset( 'images/05-como-funciona/step-spacer.svg' ) ); ?>" width="4" height="48" alt="">
								<?php endif; ?>
								<span class="como-funciona__num"><?php echo esc_html( sprintf( '%02d', $artemis_cf_index + 1 ) ); ?></span>
								<div class="como-funciona__body">
									<h3 class="como-funciona__step-title"><?php echo esc_html( $artemis_cf_step['title'] ); ?></h3>
									<?php if ( '' !== $artemis_cf_step['text'] ) : ?>
										<p class="como-funciona__step-text"><?php echo esc_html( $artemis_cf_step['text'] ); ?></p>
									<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ol>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
