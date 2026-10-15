<?php
/**
 * Nossos sistemas — Figma 8294:500. Markup espelha partials/03-nossos-sistemas.html.
 * Mobile 8635:1999: <picture> troca a ilustração até 767px (_art_sistema_img_m).
 * Ilustração animada (_art_sistema_ilustra): template-parts/components/ilustra-<chave>.php no lugar da imagem.
 * Conteúdo: Customizer (art_sistemas_*) + CPT art_sistema (inc/section-03-nossos-sistemas.php).
 */

defined( 'ABSPATH' ) || exit;

$artemis_sistemas = artemis_sistemas_get_items();
$artemis_caption  = artemis_mod( 'art_sistemas_caption' );
?>
<section class="sistemas" aria-labelledby="sistemas-title">
	<div class="sistemas__inner container">
		<div class="sistemas__head">
			<div class="sistemas__title-wrap">
				<h2 class="sistemas__title section-title section-title--em-300" id="sistemas-title"><?php echo artemis_highlight( artemis_mod( 'art_sistemas_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h2>
			</div>
			<div class="sistemas__intro">
				<p class="sistemas__text body-lg"><?php echo esc_html( artemis_mod( 'art_sistemas_text' ) ); ?></p>
			</div>
		</div>

		<?php if ( $artemis_sistemas || $artemis_caption ) : ?>
			<div class="sistemas__cards">
				<?php if ( $artemis_sistemas ) : ?>
					<ul class="sistemas__list">
						<?php foreach ( $artemis_sistemas as $artemis_sistema ) : ?>
							<?php
							$artemis_anim = artemis_sistemas_ilustra( $artemis_sistema );
							$artemis_img  = $artemis_anim ? '' : artemis_sistemas_image( $artemis_sistema );
							?>
							<li class="sistemas__card"<?php echo $artemis_anim ? ' data-anim="' . esc_attr( $artemis_anim ) . '"' : ''; ?>>
								<?php if ( $artemis_anim ) : ?>
									<?php get_template_part( 'template-parts/components/ilustra-' . $artemis_anim ); ?>
								<?php elseif ( $artemis_img ) : ?>
									<picture class="sistemas__media"><?php echo $artemis_img; // phpcs:ignore WordPress.Security.EscapeOutput -- <source> escapado em artemis_sistemas_image + wp_get_attachment_image. ?></picture>
								<?php endif; ?>
								<div class="sistemas__body">
									<div class="sistemas__copy">
										<h3 class="sistemas__card-title"><?php echo esc_html( get_the_title( $artemis_sistema ) ); ?></h3>
										<?php if ( has_excerpt( $artemis_sistema ) ) : ?>
											<p class="sistemas__card-text"><?php echo esc_html( get_the_excerpt( $artemis_sistema ) ); ?></p>
										<?php endif; ?>
									</div>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( $artemis_caption ) : ?>
					<p class="sistemas__caption eyebrow"><?php echo esc_html( $artemis_caption ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
