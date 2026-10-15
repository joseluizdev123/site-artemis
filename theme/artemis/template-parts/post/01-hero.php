<?php
/**
 * Post — topo do artigo (Figma 8331:338 · mobile 8673:5779). Markup espelha partials/post-01-hero.html.
 * Categoria (link para a seção dela no Blog) · data · título · abertura (_art_post_lead / resumo) · autor.
 */

defined( 'ABSPATH' ) || exit;

$artemis_post = get_post();
$artemis_term = artemis_blog_post_term( $artemis_post );
$artemis_lead = artemis_post_lead( $artemis_post );
?>
<section class="post-hero" aria-labelledby="post-title">
	<div class="container">
		<div class="post-hero__article">
			<div class="post-hero__heading">
				<p class="post-card__meta">
					<?php if ( $artemis_term ) : ?>
						<a class="post-card__cat post-hero__cat" href="<?php echo esc_url( artemis_blog_url() . '#' . artemis_blog_cat_anchor( $artemis_term ) ); ?>"><?php echo esc_html( $artemis_term->name ); ?></a>
						<span class="post-card__dot" aria-hidden="true">·</span>
					<?php endif; ?>
					<time class="post-card__date" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $artemis_post ) ); ?>"><?php echo esc_html( artemis_blog_date( $artemis_post ) ); ?></time>
				</p>
				<h1 class="post-hero__title" id="post-title"><?php echo esc_html( get_the_title( $artemis_post ) ); ?></h1>
			</div>
			<?php if ( '' !== $artemis_lead ) : ?>
				<p class="post-hero__lead body-lg"><?php echo esc_html( $artemis_lead ); ?></p>
			<?php endif; ?>
			<?php echo artemis_blog_author( $artemis_post ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_blog_author(). ?>
		</div>
	</div>
</section>
