<?php
/**
 * Newsletter (componente) — símbolo 8436:2153 · mobile 8673:4986. Markup espelha partials/newsletter.html.
 * Conteúdo: Customizer art_newsletter_* (inc/section-newsletter.php).
 */

defined( 'ABSPATH' ) || exit;

$artemis_nl_status      = artemis_blog_newsletter_status_items();
$artemis_nl_action      = trim( (string) artemis_mod( 'art_newsletter_action' ) );
$artemis_nl_placeholder = (string) artemis_mod( 'art_newsletter_placeholder' );
$artemis_nl_label       = '' !== trim( $artemis_nl_placeholder ) ? $artemis_nl_placeholder : __( 'Seu e-mail', 'artemis' );
$artemis_nl_eyebrow     = (string) artemis_mod( 'art_newsletter_eyebrow' );
$artemis_nl_text        = (string) artemis_mod( 'art_newsletter_text' );
?>
<section class="newsletter" id="newsletter" aria-labelledby="newsletter-title">
	<?php if ( $artemis_nl_status ) : ?>
		<div class="newsletter__status">
			<div class="container newsletter__status-inner">
				<?php foreach ( $artemis_nl_status as $artemis_i => $artemis_item ) : ?>
					<?php if ( 0 === $artemis_i ) : ?>
						<p class="newsletter__status-item">
							<span class="newsletter__led" aria-hidden="true"></span>
							<span class="newsletter__status-text"><?php echo esc_html( $artemis_item ); ?></span>
						</p>
					<?php else : ?>
						<?php echo artemis_blog_newsletter_signal(); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado na função. ?>
						<p class="newsletter__status-text"><?php echo esc_html( $artemis_item ); ?></p>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="container newsletter__main">
		<div class="newsletter__content">
			<div class="newsletter__heading">
				<?php if ( '' !== trim( $artemis_nl_eyebrow ) ) : ?>
					<p class="eyebrow"><?php echo esc_html( $artemis_nl_eyebrow ); ?></p>
				<?php endif; ?>
				<h2 class="section-title newsletter__title" id="newsletter-title"><?php echo artemis_highlight( artemis_mod( 'art_newsletter_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h2>
				<?php if ( '' !== trim( $artemis_nl_text ) ) : ?>
					<p class="body-lg"><?php echo esc_html( $artemis_nl_text ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<form class="newsletter__form" action="<?php echo $artemis_nl_action ? esc_url( $artemis_nl_action ) : '#'; ?>" method="post" data-newsletter-form data-unconfigured="<?php esc_attr_e( 'Newsletter ainda não configurada.', 'artemis' ); ?>"<?php echo $artemis_nl_action ? ' target="_blank" rel="noopener"' : ''; ?>>
			<label class="newsletter__sr" for="newsletter-email"><?php echo esc_html( $artemis_nl_label ); ?></label>
			<input class="newsletter__input" id="newsletter-email" type="email" name="<?php echo esc_attr( artemis_blog_newsletter_field() ); ?>" placeholder="<?php echo esc_attr( $artemis_nl_placeholder ); ?>" autocomplete="email" required>
			<button class="btn btn--primary newsletter__submit" type="submit"><span><?php echo esc_html( artemis_mod( 'art_newsletter_button' ) ); ?></span></button>
			<p class="newsletter__msg" role="status" aria-live="polite"></p>
		</form>
	</div>
</section>
