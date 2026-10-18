<?php
/**
 * Post — "Continue lendo" (Figma 8331:1587 · mobile 8673:6757). Markup espelha partials/post-03-relacionados.html:
 * grade .blog-cat + artemis_blog_card() (inc/section-blog-03-categoria.php). Sem outros posts, a seção não aparece.
 */

defined( 'ABSPATH' ) || exit;

$artemis_related = artemis_post_related();
if ( ! $artemis_related ) {
	return;
}
?>
<section class="blog-cat blog-cat--related" aria-labelledby="post-related-title">
	<div class="container blog-cat__inner">
		<h2 class="blog-cat__title" id="post-related-title" data-split="lines"><?php echo esc_html( artemis_mod( 'art_post_related_title' ) ); ?></h2>
		<div class="blog-cat__grid" data-reveal-group="up">
			<?php
			foreach ( $artemis_related as $artemis_rel ) {
				echo artemis_blog_card( $artemis_rel ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_blog_card().
			}
			?>
		</div>
	</div>
</section>
