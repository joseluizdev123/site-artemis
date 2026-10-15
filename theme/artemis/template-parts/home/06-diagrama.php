<?php
/**
 * Diagram — Figma 8653:2286 · mobile 8653:2599. Markup espelha partials/06-diagrama.html.
 * Conteúdo: Customizer (art_diag_*). Posições, linhas e ícones são fixos (desenho do Figma).
 * Mobile: o diagrama rola na horizontal com barra de progresso (assets/js/sections/06-diagrama.js).
 */

defined( 'ABSPATH' ) || exit;

$artemis_diag_versions  = artemis_diagrama_versions();
$artemis_diag_lab_label = trim( (string) artemis_mod( 'art_diag_lab_label' ) );
$artemis_diag_img      = static function ( $file ) {
	return esc_url( artemis_asset( 'images/06-diagrama/' . $file ) );
};
?>
<section class="diagrama" aria-labelledby="diagrama-title">
	<div class="container diagrama__inner">
		<div class="diagrama__diagram" data-diagrama>
			<div class="diagrama__scroll" data-diagrama-scroll>
				<figure class="diagrama__chart" aria-label="<?php echo esc_attr( artemis_diagrama_label() ); ?>">
					<ol class="diagrama__phases">
						<li class="diagrama__phase"><span class="eyebrow diagrama__phase-title"><?php echo esc_html( artemis_mod( 'art_diag_phase_1' ) ); ?></span></li>
						<li class="diagrama__phase">
							<span class="eyebrow diagrama__phase-title"><?php echo esc_html( artemis_mod( 'art_diag_phase_2' ) ); ?></span>
							<span class="diagrama__lines diagrama__lines--mvp" aria-hidden="true"><img src="<?php echo $artemis_diag_img( 'lines-mvp.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput -- esc_url na closure. ?>" width="465" height="461" alt=""></span>
						</li>
						<li class="diagrama__phase">
							<span class="eyebrow diagrama__phase-title"><?php echo esc_html( artemis_mod( 'art_diag_phase_3' ) ); ?></span>
							<span class="diagrama__lines diagrama__lines--escala" aria-hidden="true"><img src="<?php echo $artemis_diag_img( 'lines-escala.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput -- esc_url na closure. ?>" width="434" height="461" alt=""></span>
						</li>
					</ol>

					<div class="diagrama__body">
						<div class="diagrama__content">
							<div class="diagrama__row diagrama__row--start">
								<div class="diagrama__item diagrama__item--light">
									<p class="diagrama__item-title diagrama__item-title--bold"><?php echo esc_html( artemis_mod( 'art_diag_step_1' ) ); ?></p>
									<span class="diagrama__badge"><span class="diagrama__badge-num">01</span></span>
								</div>
								<div class="diagrama__item diagrama__item--measure">
									<p class="diagrama__item-title"><?php echo esc_html( artemis_mod( 'art_diag_step_2' ) ); ?></p>
									<span class="diagrama__badge diagrama__badge--blue"><span class="diagrama__badge-num">02</span></span>
								</div>
							</div>

							<div class="diagrama__row">
								<div class="diagrama__item diagrama__item--adjust">
									<p class="diagrama__item-title"><?php echo esc_html( artemis_mod( 'art_diag_adjust' ) ); ?></p>
									<img class="diagrama__icon" src="<?php echo $artemis_diag_img( 'icon-refresh-ccw.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput -- esc_url na closure. ?>" width="24" height="24" alt="">
								</div>
							</div>

							<div class="diagrama__row">
								<div class="diagrama__item diagrama__item--error">
									<svg class="diagrama__dash" aria-hidden="true" focusable="false"><rect width="100%" height="100%" rx="17" ry="17"/></svg>
									<p class="diagrama__item-text">
										<span class="diagrama__item-label"><?php echo esc_html( artemis_mod( 'art_diag_error_label' ) ); ?></span>
										<span class="diagrama__item-title"><?php echo esc_html( artemis_mod( 'art_diag_error_title' ) ); ?></span>
									</p>
									<img class="diagrama__icon" src="<?php echo $artemis_diag_img( 'icon-trash.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput -- esc_url na closure. ?>" width="24" height="24" alt="">
								</div>
							</div>

							<div class="diagrama__row">
								<div class="diagrama__item diagrama__item--lab">
									<p class="diagrama__item-text">
										<?php if ( '' !== $artemis_diag_lab_label ) : ?>
											<span class="diagrama__item-label diagrama__item-label--lab"><?php echo esc_html( $artemis_diag_lab_label ); ?></span>
										<?php endif; ?>
										<span class="diagrama__item-title diagrama__item-title--bold"><?php echo esc_html( artemis_mod( 'art_diag_step_3' ) ); ?></span>
									</p>
									<span class="diagrama__badge"><span class="diagrama__badge-num">03</span></span>
								</div>
							</div>

							<?php if ( $artemis_diag_versions ) : ?>
								<div class="diagrama__row diagrama__row--end">
									<ul class="diagrama__versions" aria-label="<?php esc_attr_e( 'Versões', 'artemis' ); ?>">
										<?php foreach ( $artemis_diag_versions as $artemis_diag_version ) : ?>
											<li class="diagrama__item diagrama__item--version"><span class="diagrama__item-title"><?php echo esc_html( $artemis_diag_version ); ?></span></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>

							<span class="diagrama__lines diagrama__lines--poc" aria-hidden="true"><img src="<?php echo $artemis_diag_img( 'lines-poc.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput -- esc_url na closure. ?>" width="436" height="461" alt=""></span>
						</div>

						<ul class="diagrama__legend" aria-label="<?php esc_attr_e( 'Legenda', 'artemis' ); ?>">
							<li class="diagrama__legend-item diagrama__legend-item--start">
								<img src="<?php echo $artemis_diag_img( 'diamond-gabarito.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput -- esc_url na closure. ?>" width="24" height="24" alt="">
								<span class="diagrama__legend-text"><?php echo esc_html( artemis_mod( 'art_diag_legend_1' ) ); ?></span>
							</li>
							<li class="diagrama__legend-item diagrama__legend-item--end">
								<img src="<?php echo $artemis_diag_img( 'diamond-producao.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput -- esc_url na closure. ?>" width="24" height="24" alt="">
								<span class="diagrama__legend-text"><?php echo esc_html( artemis_mod( 'art_diag_legend_2' ) ); ?></span>
							</li>
						</ul>
					</div>
				</figure>
			</div>

			<div class="diagrama__progress" aria-hidden="true">
				<span class="diagrama__progress-fill"></span>
				<span class="diagrama__progress-rest"></span>
			</div>
		</div>

		<div class="diagrama__desc">
			<h2 class="section-title diagrama__title" id="diagrama-title"><?php echo artemis_highlight( artemis_mod( 'art_diag_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h2>
			<p class="diagrama__text"><?php echo esc_html( artemis_mod( 'art_diag_text' ) ); ?></p>
		</div>
	</div>
</section>
