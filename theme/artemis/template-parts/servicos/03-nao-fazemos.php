<?php
/**
 * Serviços — o que não fazemos (Figma 8348:2419 · mobile 8699:5244). Markup espelha partials/servicos-03-nao-fazemos.html.
 * Textos no Customizer (art_srv_nao_*); card sem título não aparece; nota vazia esconde as marcas.
 */

defined( 'ABSPATH' ) || exit;

$artemis_note = (string) artemis_mod( 'art_srv_nao_note' );
?>
<section class="srv-nao" aria-labelledby="srv-nao-title">
	<div class="container srv-nao__inner">
		<div class="srv-nao__head">
			<h2 class="section-title srv-nao__title" id="srv-nao-title"><?php echo artemis_highlight( artemis_mod( 'art_srv_nao_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h2>
			<p class="body-lg"><?php echo esc_html( artemis_mod( 'art_srv_nao_lead' ) ); ?></p>
		</div>

		<div class="srv-nao__box">
			<ul class="srv-nao__grid">
				<?php
				for ( $artemis_i = 1; $artemis_i <= 4; $artemis_i++ ) :
					$artemis_t = (string) artemis_mod( 'art_srv_nao_' . $artemis_i . '_title' );
					if ( '' === $artemis_t ) {
						continue;
					}
					?>
					<li class="srv-nao__card">
						<span class="srv-nao__icon"><img src="<?php echo esc_url( artemis_asset( 'images/servicos-03-nao-fazemos/icon-x.svg' ) ); ?>" width="24" height="24" alt=""></span>
						<div class="srv-nao__text">
							<h3 class="srv-nao__card-title"><?php echo esc_html( $artemis_t ); ?></h3>
							<p class="srv-nao__card-text"><?php echo esc_html( artemis_mod( 'art_srv_nao_' . $artemis_i . '_text' ) ); ?></p>
						</div>
					</li>
				<?php endfor; ?>
			</ul>
			<div class="srv-nao__pattern" aria-hidden="true">
				<picture>
					<source media="(max-width: 767px)" srcset="<?php echo esc_url( artemis_asset( 'images/servicos-03-nao-fazemos/m-pattern.svg' ) ); ?>" width="341" height="32">
					<img src="<?php echo esc_url( artemis_asset( 'images/servicos-03-nao-fazemos/pattern.svg' ) ); ?>" width="1375" height="65" alt="" loading="lazy" decoding="async">
				</picture>
			</div>
		</div>

		<?php if ( '' !== $artemis_note ) : ?>
			<div class="srv-nao__note">
				<span class="srv-nao__marks">
					<img src="<?php echo esc_url( artemis_asset( 'images/servicos-03-nao-fazemos/marca-mktops.svg' ) ); ?>" width="64" height="64" alt="MktOps">
					<img src="<?php echo esc_url( artemis_asset( 'images/servicos-03-nao-fazemos/marca-seo.svg' ) ); ?>" width="64" height="64" alt="Artemis SEO">
				</span>
				<p class="srv-nao__note-text"><?php echo esc_html( $artemis_note ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>
