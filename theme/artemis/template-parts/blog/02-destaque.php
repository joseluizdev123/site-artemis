<?php
/**
 * Blog — artigo em destaque (Figma 8314:1380 · mobile 8673:3901). Markup espelha partials/blog-02-destaque.html.
 * Conteúdo: post fixado mais recente (fallback: o mais recente) + selo do Customizer — inc/section-blog-02-destaque.php.
 */

defined( 'ABSPATH' ) || exit;

$artemis_destaque = artemis_blog_destaque_post();
if ( ! $artemis_destaque ) {
	return;
}
$artemis_destaque_img     = artemis_blog_destaque_image( $artemis_destaque );
$artemis_destaque_tag     = trim( (string) artemis_mod( 'art_blog_destaque_tag' ) );
$artemis_destaque_excerpt = artemis_blog_destaque_excerpt( $artemis_destaque );
?>
<section class="blog-destaque" aria-labelledby="blog-destaque-title">
	<div class="container">
		<?php // Article Content 8314:1568 — card inteiro clicável (link do título esticado). ?>
		<article class="blog-destaque__card">
			<?php if ( '' !== $artemis_destaque_img ) : ?>
				<div class="blog-destaque__media"><?php echo $artemis_destaque_img; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_get_attachment_image. ?></div>
			<?php endif; ?>

			<div class="blog-destaque__text">
				<?php if ( '' !== $artemis_destaque_tag ) : ?>
					<p class="blog-destaque__tag"><span><?php echo esc_html( $artemis_destaque_tag ); ?></span></p>
				<?php endif; ?>

				<div class="blog-destaque__content">
					<div class="blog-destaque__head">
						<?php echo artemis_blog_destaque_meta( $artemis_destaque ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado na função. ?>
						<h2 class="blog-destaque__title" id="blog-destaque-title"><a class="blog-destaque__link" href="<?php echo esc_url( get_permalink( $artemis_destaque ) ); ?>"><?php echo esc_html( get_the_title( $artemis_destaque ) ); ?></a></h2>
					</div>
					<?php if ( '' !== $artemis_destaque_excerpt ) : ?>
						<p class="blog-destaque__excerpt"><?php echo esc_html( $artemis_destaque_excerpt ); ?></p>
					<?php endif; ?>
				</div>

				<?php echo artemis_blog_destaque_author( $artemis_destaque ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado na função. ?>
			</div>
		</article>
	</div>
</section>
