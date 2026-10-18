<?php
/**
 * Serviços — o que não fazemos (Figma 8348:2419 · mobile 8699:5244). Markup espelha partials/servicos-03-nao-fazemos.html.
 * Textos no Customizer (art_srv_nao_*); card sem título não aparece; nota vazia esconde as marcas.
 * Motion (data-* lidos por assets/js/motion.js): título por linhas → texto; cards um a um (X em pop, título e texto
 * sobem); faixa de traços abre da esquerda; marcas em pop → nota.
 */

defined( 'ABSPATH' ) || exit;

$artemis_note = (string) artemis_mod( 'art_srv_nao_note' );
?>
<section class="srv-nao" aria-labelledby="srv-nao-title">
	<div class="container srv-nao__inner">
		<div class="srv-nao__head" data-reveal-group data-stagger="200">
			<h2 class="section-title srv-nao__title" id="srv-nao-title" data-split="lines"><?php echo artemis_highlight( artemis_mod( 'art_srv_nao_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h2>
			<p class="body-lg" data-reveal="up"><?php echo esc_html( artemis_mod( 'art_srv_nao_lead' ) ); ?></p>
		</div>

		<div class="srv-nao__box">
			<ul class="srv-nao__grid" data-reveal-group data-stagger="90">
				<?php
				for ( $artemis_i = 1; $artemis_i <= 4; $artemis_i++ ) :
					$artemis_t = (string) artemis_mod( 'art_srv_nao_' . $artemis_i . '_title' );
					if ( '' === $artemis_t ) {
						continue;
					}
					?>
					<li class="srv-nao__card">
						<span class="srv-nao__icon" data-reveal="pop"><img src="<?php echo esc_url( artemis_asset( 'images/servicos-03-nao-fazemos/icon-x.svg' ) ); ?>" width="24" height="24" alt=""></span>
						<div class="srv-nao__text">
							<h3 class="srv-nao__card-title" data-reveal="up"><?php echo esc_html( $artemis_t ); ?></h3>
							<p class="srv-nao__card-text" data-reveal="up"><?php echo esc_html( artemis_mod( 'art_srv_nao_' . $artemis_i . '_text' ) ); ?></p>
						</div>
					</li>
				<?php endfor; ?>
			</ul>
			<div class="srv-nao__pattern" aria-hidden="true" data-wipe>
				<picture>
					<source media="(max-width: 767px)" srcset="<?php echo esc_url( artemis_asset( 'images/servicos-03-nao-fazemos/m-pattern.svg' ) ); ?>" width="341" height="32">
					<img src="<?php echo esc_url( artemis_asset( 'images/servicos-03-nao-fazemos/pattern.svg' ) ); ?>" width="1375" height="65" alt="" loading="lazy" decoding="async">
				</picture>
			</div>
		</div>

		<?php if ( '' !== $artemis_note ) : ?>
			<div class="srv-nao__note" data-reveal-group data-stagger="120">
				<span class="srv-nao__marks" data-reveal="pop">
					<img src="<?php echo esc_url( artemis_asset( 'images/servicos-03-nao-fazemos/marca-mktops.svg' ) ); ?>" width="64" height="64" alt="MktOps">
					<img src="<?php echo esc_url( artemis_asset( 'images/servicos-03-nao-fazemos/marca-seo.svg' ) ); ?>" width="64" height="64" alt="Artemis SEO">
				</span>
				<p class="srv-nao__note-text" data-reveal="up"><?php echo esc_html( $artemis_note ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>
