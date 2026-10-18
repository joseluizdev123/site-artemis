<?php
/**
 * Contato — contatos + formulário + ilustração (Figma 8370:1542 · mobile 8693:2763). Markup espelha partials/contato-01-form.html.
 * Envio: inc/section-contato.php (admin-post → e-mail) ou o endereço externo do Customizer.
 */

defined( 'ABSPATH' ) || exit;

$artemis_email    = (string) artemis_mod( 'art_contact_email' );
$artemis_whatsapp = (string) artemis_mod( 'art_contact_whatsapp' );
$artemis_address  = (string) artemis_mod( 'art_contact_address' );
$artemis_external = '' !== trim( (string) artemis_mod( 'art_contato_action' ) );
$artemis_status   = artemis_contato_status();
?>
<section class="contato" aria-labelledby="contato-title">
	<div class="container contato__grid">
		<ul class="contato-info" data-reveal-group="up">
			<?php if ( '' !== $artemis_email ) : ?>
				<li class="contato-info__item">
					<p class="contato-info__label"><?php esc_html_e( 'Fale conosco', 'artemis' ); ?></p>
					<a class="contato-info__value" href="<?php echo esc_url( 'mailto:' . antispambot( $artemis_email, 1 ) ); ?>"><span data-hover="line"><?php echo esc_html( antispambot( $artemis_email ) ); ?></span></a>
				</li>
			<?php endif; ?>
			<?php if ( '' !== $artemis_whatsapp ) : ?>
				<li class="contato-info__item">
					<p class="contato-info__label"><?php esc_html_e( 'WhatsApp', 'artemis' ); ?></p>
					<a class="contato-info__value" href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/\D+/', '', $artemis_whatsapp ) ); ?>" target="_blank" rel="noopener"><span data-hover="line"><?php echo esc_html( $artemis_whatsapp ); ?></span></a>
				</li>
			<?php endif; ?>
			<?php if ( '' !== $artemis_address ) : ?>
				<li class="contato-info__item">
					<p class="contato-info__label"><?php esc_html_e( 'Endereço', 'artemis' ); ?></p>
					<p class="contato-info__value"><?php echo esc_html( $artemis_address ); ?></p>
				</li>
			<?php endif; ?>
		</ul>

		<div class="contato__main">
			<div class="contato__head">
				<div class="contato__heading">
					<h1 class="contato__title" id="contato-title" data-split="lines" data-motion-start="load"><?php echo artemis_highlight( artemis_mod( 'art_contato_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h1>
					<p class="contato__lead" data-reveal="up" data-motion-start="load" data-motion-delay="120"><?php echo esc_html( artemis_mod( 'art_contato_lead' ) ); ?></p>
				</div>
				<img class="contato__arrow" src="<?php echo esc_url( artemis_asset( 'images/contato/seta.svg' ) ); ?>" width="8" height="33" alt="" aria-hidden="true" data-reveal="grow-y" data-origin="top" data-motion-start="load" data-motion-delay="300">
			</div>

			<form class="contato-form" id="contato-form" action="<?php echo esc_url( artemis_contato_action() ); ?>" method="post" data-contato-form data-reveal="up" data-motion-start="load" data-motion-delay="200">
				<?php if ( ! $artemis_external ) : ?>
					<input type="hidden" name="action" value="artemis_contato">
					<?php wp_nonce_field( 'artemis_contato', 'artemis_contato_nonce' ); ?>
					<p class="contato-form__trap" aria-hidden="true"><label>Site <input type="text" name="site" tabindex="-1" autocomplete="off"></label></p>
				<?php endif; ?>
				<div class="contato-form__fields">
					<div class="contato-form__field">
						<label class="contato-form__label" for="contato-nome"><?php esc_html_e( 'Nome', 'artemis' ); ?></label>
						<input class="contato-form__input" id="contato-nome" name="nome" type="text" placeholder="<?php esc_attr_e( 'Nome e sobrenome', 'artemis' ); ?>" autocomplete="name" required>
					</div>
					<div class="contato-form__field">
						<label class="contato-form__label" for="contato-email"><?php esc_html_e( 'E-mail corporativo', 'artemis' ); ?></label>
						<input class="contato-form__input" id="contato-email" name="email" type="email" placeholder="contato@exemplo.com" autocomplete="email" required>
					</div>
					<div class="contato-form__field">
						<label class="contato-form__label" for="contato-empresa"><?php esc_html_e( 'Empresa', 'artemis' ); ?></label>
						<input class="contato-form__input" id="contato-empresa" name="empresa" type="text" placeholder="<?php esc_attr_e( 'Nome da Empresa', 'artemis' ); ?>" autocomplete="organization">
					</div>
					<div class="contato-form__field">
						<label class="contato-form__label" for="contato-cargo"><?php esc_html_e( 'Cargo', 'artemis' ); ?></label>
						<input class="contato-form__input" id="contato-cargo" name="cargo" type="text" placeholder="<?php esc_attr_e( 'Seu cargo', 'artemis' ); ?>" autocomplete="organization-title">
					</div>
					<div class="contato-form__field">
						<label class="contato-form__label" for="contato-processo"><?php esc_html_e( 'Qual processo consome mais hora especializada hoje?', 'artemis' ); ?></label>
						<textarea class="contato-form__input contato-form__input--area" id="contato-processo" name="processo" placeholder="<?php esc_attr_e( 'Descreva', 'artemis' ); ?>" rows="3"></textarea>
					</div>
				</div>
				<div class="contato-form__submit">
					<button class="btn btn--primary contato-form__button" type="submit"><?php echo esc_html( artemis_mod( 'art_contato_button' ) ); ?></button>
					<p class="contato-form__note"><?php echo esc_html( artemis_mod( 'art_contato_note' ) ); ?></p>
				</div>
				<p class="contato-form__msg<?php echo esc_attr( $artemis_status[1] ); ?>" role="status" aria-live="polite"><?php echo esc_html( $artemis_status[0] ); ?></p>
			</form>
		</div>

		<picture class="contato__art" data-reveal="fade" data-motion-start="load" data-motion-delay="150">
			<source media="(max-width: 1023px)" srcset="<?php echo esc_url( artemis_asset( 'images/contato/m-ilustracao.jpg' ) ); ?>" width="32" height="1982">
			<img src="<?php echo esc_url( artemis_asset( 'images/contato/ilustracao.jpg' ) ); ?>" width="464" height="1902" alt="" aria-hidden="true" loading="lazy" decoding="async">
		</picture>
	</div>
</section>
