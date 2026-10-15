<?php
/**
 * Estudos de impacto — Figma 8195:1063 · Mobile 8635:2366. Markup espelha partials/07-estudos.html.
 * Conteúdo: Customizer (art_estudos_*) + CPT art_estudo (inc/section-07-estudos.php).
 */

defined( 'ABSPATH' ) || exit;

$artemis_estudos = artemis_estudos_get();
if ( ! $artemis_estudos ) {
	return;
}
$artemis_estudos_total = count( $artemis_estudos );
?>
<section class="estudos" id="estudos" aria-labelledby="estudos-title">
	<div class="container estudos__inner">
		<div class="section-head">
			<p class="eyebrow"><?php echo esc_html( artemis_mod( 'art_estudos_eyebrow' ) ); ?></p>
			<h2 class="section-title estudos__heading" id="estudos-title"><?php echo artemis_highlight( artemis_mod( 'art_estudos_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h2>
		</div>

		<div class="estudos__carousel<?php echo 1 === $artemis_estudos_total ? ' is-single' : ''; ?>" role="region" aria-roledescription="<?php esc_attr_e( 'carrossel', 'artemis' ); ?>" aria-label="<?php echo esc_attr( artemis_mod( 'art_estudos_eyebrow' ) ); ?>" style="--estudos-step:1;--estudos-total:<?php echo (int) $artemis_estudos_total; ?>" data-estudos>
			<div class="estudos__slides" id="estudos-slides" aria-live="polite" data-estudos-slides>
				<?php
				foreach ( $artemis_estudos as $artemis_i => $artemis_estudo ) :
					$artemis_active    = 0 === $artemis_i;
					$artemis_meta_line = artemis_estudos_meta_line( $artemis_estudo );
					$artemis_categoria = (string) get_post_meta( $artemis_estudo->ID, '_art_estudo_categoria', true );
					$artemis_status    = (string) get_post_meta( $artemis_estudo->ID, '_art_estudo_status', true );
					$artemis_rodape    = '' !== $artemis_categoria || '' !== $artemis_status;
					$artemis_shot      = artemis_estudos_shot( $artemis_estudo );
					/* translators: 1: posição do slide, 2: total de slides. */
					$artemis_posicao = sprintf( __( '%1$d de %2$d', 'artemis' ), $artemis_i + 1, $artemis_estudos_total );
					?>
					<article class="estudos__card<?php echo $artemis_active ? ' is-active' : ''; ?>" role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( $artemis_posicao ); ?>"<?php echo $artemis_active ? '' : ' aria-hidden="true" inert'; ?>>
						<div class="estudos__text<?php echo $artemis_rodape ? '' : ' estudos__text--center'; ?>">
							<?php if ( '' !== $artemis_meta_line ) : ?>
								<p class="estudos__meta"><?php echo esc_html( $artemis_meta_line ); ?></p>
							<?php endif; ?>
							<div class="estudos__content">
								<h3 class="estudos__title"><?php echo esc_html( get_the_title( $artemis_estudo ) ); ?></h3>
								<div class="estudos__body"><?php echo artemis_estudos_body( $artemis_estudo ); // phpcs:ignore WordPress.Security.EscapeOutput -- wp_kses_post na função. ?></div>
							</div>
							<?php if ( $artemis_rodape ) : ?>
								<p class="estudos__tags eyebrow">
									<?php if ( '' !== $artemis_categoria ) : ?>
										<span class="estudos__tag"><?php echo esc_html( $artemis_categoria ); ?></span>
									<?php endif; ?>
									<?php if ( '' !== $artemis_status ) : ?>
										<span class="estudos__status"><span class="estudos__status-label"><?php echo esc_html( $artemis_status ); ?></span></span>
									<?php endif; ?>
								</p>
							<?php endif; ?>
						</div>
						<?php // Mobile (8635:3470): faixa horizontal com outra arte do padrão (8635:3471). ?>
						<div class="estudos__pattern" aria-hidden="true"><picture><source media="(max-width: 767px)" srcset="<?php echo esc_url( artemis_asset( 'images/07-estudos/m-pattern.svg' ) ); ?>"><img src="<?php echo esc_url( artemis_asset( 'images/pattern-diagonal.svg' ) ); ?>" alt=""></picture></div>
						<figure class="estudos__media">
							<?php echo artemis_estudos_bg( $artemis_estudo ); // phpcs:ignore WordPress.Security.EscapeOutput -- wp_get_attachment_image. ?>
							<?php if ( '' !== $artemis_shot ) : // Sem imagem destacada: só a pintura, sem o quadro branco vazio. ?>
								<div class="estudos__shot"><?php echo $artemis_shot; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_get_attachment_image. ?></div>
							<?php endif; ?>
						</figure>
					</article>
				<?php endforeach; ?>
			</div>

			<div class="estudos__controls">
				<div class="estudos__progress" aria-hidden="true">
					<span class="estudos__progress-fill"></span>
					<span class="estudos__progress-rest"></span>
				</div>
				<div class="estudos__nav">
					<button class="estudos__arrow" type="button" aria-controls="estudos-slides" aria-label="<?php esc_attr_e( 'Estudo anterior', 'artemis' ); ?>" data-estudos-prev>
						<img src="<?php echo esc_url( artemis_asset( 'images/07-estudos/chevron-left.svg' ) ); ?>" width="16" height="16" alt="">
					</button>
					<button class="estudos__arrow" type="button" aria-controls="estudos-slides" aria-label="<?php esc_attr_e( 'Próximo estudo', 'artemis' ); ?>" data-estudos-next>
						<img src="<?php echo esc_url( artemis_asset( 'images/07-estudos/chevron-right.svg' ) ); ?>" width="16" height="16" alt="">
					</button>
				</div>
			</div>
		</div>
	</div>
</section>
