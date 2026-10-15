<?php
/**
 * Stats / Números — Figma 8486:1798 (mobile 8635:2371, só CSS). Markup espelha partials/08-stats.html.
 * Conteúdo: Customizer (art_stat_{1..4}_*), ver inc/section-08-stats.php.
 */

defined( 'ABSPATH' ) || exit;

$artemis_stats_ellipse_a = artemis_asset( 'images/08-stats/ellipse-container-a.svg' );
$artemis_stats_ellipse_b = artemis_asset( 'images/08-stats/ellipse-container-b.svg' );
?>
<section class="stats" aria-label="<?php esc_attr_e( 'Resultados em números', 'artemis' ); ?>">
	<div class="stats__band stats__band--top" aria-hidden="true">
		<div class="container">
			<div class="stats__pattern">
				<img class="stats__ellipses stats__ellipses--mirror" src="<?php echo esc_url( $artemis_stats_ellipse_a ); ?>" width="686" height="62" alt="" loading="lazy" decoding="async">
				<img class="stats__ellipses" src="<?php echo esc_url( $artemis_stats_ellipse_b ); ?>" width="686" height="62" alt="" loading="lazy" decoding="async">
			</div>
		</div>
	</div>

	<div class="container">
		<ul class="stats__list">
			<?php foreach ( array_keys( artemis_stats_defaults() ) as $artemis_stat ) : ?>
				<li class="stats__item">
					<p class="stats__value"><?php echo artemis_stats_value( $artemis_stat ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_stats_value. ?></p>
					<p class="stats__label body-lg"><?php echo esc_html( artemis_mod( "art_stat_{$artemis_stat}_legenda" ) ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<div class="stats__band stats__band--bottom" aria-hidden="true">
		<div class="container">
			<div class="stats__pattern stats__pattern--flip">
				<img class="stats__ellipses stats__ellipses--mirror" src="<?php echo esc_url( $artemis_stats_ellipse_a ); ?>" width="686" height="62" alt="" loading="lazy" decoding="async">
				<img class="stats__ellipses" src="<?php echo esc_url( $artemis_stats_ellipse_b ); ?>" width="686" height="62" alt="" loading="lazy" decoding="async">
			</div>
		</div>
	</div>
</section>
