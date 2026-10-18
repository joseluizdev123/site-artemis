<?php
/**
 * Sobre — onde estamos (Figma 8355:3565 · mobile 8689:2043). Markup espelha partials/sobre-07-onde.html.
 * Mapa: Google Maps (iframe, sem chave de API) buscando art_sobre_onde_mapa.
 */

defined( 'ABSPATH' ) || exit;

$artemis_q   = (string) artemis_mod( 'art_sobre_onde_mapa' );
$artemis_map = 'https://www.google.com/maps?q=' . rawurlencode( $artemis_q ) . '&z=17&output=embed';
?>
<section class="sobre-onde" aria-labelledby="sobre-onde-title">
	<div class="container sobre-onde__inner">
		<div class="sobre-onde__head">
			<img class="sobre-onde__shape" src="<?php echo esc_url( artemis_asset( 'images/sobre/forma.svg' ) ); ?>" width="270" height="270" alt="" aria-hidden="true" data-reveal="pop">
			<div class="sobre-onde__text">
				<h2 class="section-title sobre-onde__title" id="sobre-onde-title" data-split="lines"><?php echo esc_html( artemis_mod( 'art_sobre_onde_title' ) ); ?></h2>
				<p class="body-lg" data-reveal="up" data-motion-delay="150"><?php echo esc_html( artemis_mod( 'art_sobre_onde_text' ) ); ?></p>
			</div>
			<a class="btn btn--primary sobre-onde__cta" data-reveal="up" data-motion-delay="250" href="<?php echo esc_url( artemis_mod( 'art_sobre_onde_url' ) ); ?>"><?php echo esc_html( artemis_mod( 'art_sobre_onde_cta' ) ); ?></a>
		</div>
		<div class="sobre-onde__map">
			<?php if ( '' !== $artemis_q ) : ?>
				<?php /* translators: %s: endereço */ ?>
				<iframe class="sobre-onde__frame" src="<?php echo esc_url( $artemis_map ); ?>" width="1374" height="300" title="<?php echo esc_attr( sprintf( __( 'Mapa: %s', 'artemis' ), $artemis_q ) ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen data-wipe="up"></iframe>
			<?php endif; ?>
			<div class="sobre-onde__pattern" aria-hidden="true" data-wipe data-motion-delay="300">
				<picture>
					<source media="(max-width: 767px)" srcset="<?php echo esc_url( artemis_asset( 'images/sobre/m-pattern.svg' ) ); ?>" width="341" height="32">
					<img src="<?php echo esc_url( artemis_asset( 'images/sobre/pattern.svg' ) ); ?>" width="1375" height="65" alt="" loading="lazy" decoding="async">
				</picture>
			</div>
		</div>
	</div>
</section>
