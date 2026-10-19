<?php
/**
 * Footer — Figma 8165:2501. Markup espelha partials/footer.html.
 * Conteúdo: Customizer (art_footer_*) + Dados do site (art_contact_*, art_social_* — inc/options.php) + menu "footer" (inc/section-footer.php).
 */

defined( 'ABSPATH' ) || exit;

$artemis_email      = (string) artemis_mod( 'art_contact_email' );
$artemis_whatsapp   = (string) artemis_mod( 'art_contact_whatsapp' );
$artemis_address    = (string) artemis_mod( 'art_contact_address' );
$artemis_nl_action  = trim( (string) artemis_mod( 'art_footer_newsletter_action' ) );
$artemis_nl_field   = preg_replace( '/[^A-Za-z0-9_\-\[\]]/', '', (string) artemis_mod( 'art_footer_newsletter_field' ) ); // mantém maiúsculas (ex.: EMAIL do Mailchimp)
$artemis_nl_field   = $artemis_nl_field ? $artemis_nl_field : 'email';
$artemis_privacy    = get_privacy_policy_url();
$artemis_privacy    = $artemis_privacy ? $artemis_privacy : artemis_footer_url( artemis_mod( 'art_footer_privacy_url' ) );
$artemis_copyright  = str_replace( '[ano]', wp_date( 'Y' ), (string) artemis_mod( 'art_footer_copyright' ) );
$artemis_diag_short = array( 5, 13, 14, 60, 62, 67, 69 ); // vetores 22×64 do Pattern (8193:605, 613, 614, 660, 662, 667, 669)
?>
<footer class="site-footer">
	<div class="container site-footer__content">
		<div class="site-footer__top">
			<div class="site-footer__intro">
				<div class="site-footer__brand">
					<a class="site-footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Studio Artemis — página inicial', 'artemis' ); ?>"><img src="<?php echo esc_url( artemis_asset( 'images/footer/logo-lockup.svg' ) ); ?>" width="137" height="32" alt="Artemis"></a>
					<p class="site-footer__title"><?php echo artemis_highlight( artemis_mod( 'art_footer_newsletter_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></p>
				</div>
				<form class="site-footer__form" action="<?php echo $artemis_nl_action ? esc_url( $artemis_nl_action ) : '#'; ?>" method="post" data-newsletter data-unconfigured="<?php esc_attr_e( 'Newsletter ainda não configurada.', 'artemis' ); ?>"<?php echo $artemis_nl_action ? ' target="_blank"' : ''; ?>>
					<label class="site-footer__sr" for="footer-email"><?php esc_html_e( 'Seu endereço de e-mail', 'artemis' ); ?></label>
					<input class="site-footer__input" id="footer-email" type="email" name="<?php echo esc_attr( $artemis_nl_field ); ?>" placeholder="<?php esc_attr_e( 'Seu endereço de e-mail', 'artemis' ); ?>" autocomplete="email" required>
					<button class="btn btn--primary site-footer__submit" type="submit"><?php esc_html_e( 'Enviar', 'artemis' ); ?></button>
					<p class="site-footer__form-msg" role="status" aria-live="polite" hidden></p>
				</form>
			</div>

			<div class="site-footer__cols">
				<nav class="site-footer__col" aria-labelledby="footer-nav-title">
					<h2 class="site-footer__heading" id="footer-nav-title"><?php esc_html_e( 'Artemis', 'artemis' ); ?></h2>
					<?php
					wp_nav_menu( array(
						'theme_location' => 'footer',
						'container'      => false,
						'items_wrap'     => '<ul class="site-footer__links">%3$s</ul>',
						'depth'          => 1,
						'fallback_cb'    => 'artemis_footer_nav_fallback',
						'walker'         => new Artemis_Footer_Nav_Walker(),
					) );
					?>
				</nav>
				<?php $artemis_lines = artemis_footer_lines(); ?>
				<?php if ( $artemis_lines ) : ?>
					<nav class="site-footer__col" aria-labelledby="footer-lines-title">
						<h2 class="site-footer__heading" id="footer-lines-title"><?php esc_html_e( 'Outras linhas', 'artemis' ); ?></h2>
						<ul class="site-footer__links">
							<?php foreach ( $artemis_lines as $artemis_line ) : ?>
								<li><a class="site-footer__link" href="<?php echo esc_url( artemis_footer_url( $artemis_line[1] ) ); ?>"><?php echo esc_html( $artemis_line[0] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</nav>
				<?php endif; ?>
				<address class="site-footer__contact">
					<?php if ( $artemis_email ) : ?>
						<div class="site-footer__contact-item">
							<p class="site-footer__label"><?php esc_html_e( 'Fale conosco', 'artemis' ); ?></p>
							<a class="site-footer__value site-footer__value--nowrap" href="<?php echo esc_url( 'mailto:' . antispambot( $artemis_email, 1 ) ); ?>"><?php echo esc_html( antispambot( $artemis_email ) ); ?></a>
						</div>
					<?php endif; ?>
					<?php if ( $artemis_whatsapp ) : ?>
						<div class="site-footer__contact-item">
							<p class="site-footer__label"><?php esc_html_e( 'WhatsApp', 'artemis' ); ?></p>
							<a class="site-footer__value" href="<?php echo esc_url( artemis_whatsapp_url( $artemis_whatsapp ) ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $artemis_whatsapp ); ?></a>
						</div>
					<?php endif; ?>
					<?php if ( $artemis_address ) : ?>
						<div class="site-footer__contact-item">
							<p class="site-footer__label"><?php esc_html_e( 'Endereço', 'artemis' ); ?></p>
							<a class="site-footer__value" href="<?php echo esc_url( 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $artemis_address ) ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $artemis_address ); ?></a>
						</div>
					<?php endif; ?>
				</address>
			</div>
		</div>

		<div class="site-footer__middle">
			<div class="site-footer__social">
				<p class="site-footer__label"><?php esc_html_e( 'Social', 'artemis' ); ?></p>
				<ul class="site-footer__icons">
					<?php
					foreach ( artemis_footer_socials() as $artemis_key => $artemis_social ) :
						$artemis_social_url = trim( (string) artemis_mod( $artemis_key ) );
						if ( '' === $artemis_social_url ) {
							continue;
						}
						?>
						<li><a class="site-footer__icon" href="<?php echo esc_url( $artemis_social_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $artemis_social[0] ); ?>"><?php echo artemis_footer_social_icon( $artemis_social[1] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado na função. ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<a class="site-footer__totop" href="#" data-back-to-top>
				<span class="site-footer__totop-label"><?php esc_html_e( 'Voltar ao topo', 'artemis' ); ?></span>
				<span class="site-footer__icon"><img src="<?php echo esc_url( artemis_asset( 'images/footer/chevron-up.svg' ) ); ?>" width="24" height="24" alt=""></span>
			</a>
		</div>

		<div class="site-footer__legal">
			<p><?php echo esc_html( $artemis_copyright ); ?></p>
			<ul class="site-footer__legal-links">
				<li><a class="site-footer__link" href="<?php echo esc_url( $artemis_privacy ); ?>"><?php esc_html_e( 'Política de privacidade', 'artemis' ); ?></a></li>
				<li><a class="site-footer__link" href="<?php echo esc_url( artemis_footer_url( artemis_mod( 'art_footer_terms_url' ) ) ); ?>"><?php esc_html_e( 'Termos de uso', 'artemis' ); ?></a></li>
			</ul>
		</div>
	</div>

	<div class="site-footer__pattern" aria-hidden="true">
		<div class="site-footer__pattern-track">
			<?php for ( $artemis_d = 0; $artemis_d < 83; $artemis_d++ ) : ?>
				<?php if ( in_array( $artemis_d, $artemis_diag_short, true ) ) : ?>
					<img class="site-footer__diag site-footer__diag--22" src="<?php echo esc_url( artemis_asset( 'images/footer/diagonal-22.svg' ) ); ?>" width="23" height="64" alt="">
				<?php else : ?>
					<img class="site-footer__diag" src="<?php echo esc_url( artemis_asset( 'images/footer/diagonal-24.svg' ) ); ?>" width="25" height="64" alt="">
				<?php endif; ?>
			<?php endfor; ?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
