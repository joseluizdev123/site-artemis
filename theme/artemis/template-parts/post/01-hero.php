<?php
/**
 * Post — topo do artigo (Figma 8331:338 · mobile 8673:5779). Markup espelha partials/post-01-hero.html.
 * Categoria (link para a seção dela no Blog) · data · título · abertura (_art_post_lead / resumo) · autor.
 * Entrada ao carregar (motion.js): meta em fade, título por linhas, abertura e autor sobem, em sequência.
 */

defined( 'ABSPATH' ) || exit;

$artemis_post = get_post();
$artemis_term = artemis_blog_post_term( $artemis_post );
$artemis_lead = artemis_post_lead( $artemis_post );

// Autor (componente do Blog): sobe por último na entrada do topo
$artemis_author = new WP_HTML_Tag_Processor( artemis_blog_author( $artemis_post ) );
if ( $artemis_author->next_tag( 'div' ) ) {
	$artemis_author->set_attribute( 'data-reveal', 'up' );
}
?>
<section class="post-hero" aria-labelledby="post-title">
	<div class="container">
		<div class="post-hero__article" data-reveal-group data-motion-start="load">
			<div class="post-hero__heading">
				<p class="post-card__meta" data-reveal="fade">
					<?php if ( $artemis_term ) : ?>
						<a class="post-card__cat post-hero__cat" data-hover="line" href="<?php echo esc_url( artemis_blog_url() . '#' . artemis_blog_cat_anchor( $artemis_term ) ); ?>"><?php echo esc_html( $artemis_term->name ); ?></a>
						<span class="post-card__dot" aria-hidden="true">·</span>
					<?php endif; ?>
					<time class="post-card__date" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $artemis_post ) ); ?>"><?php echo esc_html( artemis_blog_date( $artemis_post ) ); ?></time>
				</p>
				<h1 class="post-hero__title" id="post-title" data-split="lines"><?php echo esc_html( get_the_title( $artemis_post ) ); ?></h1>
			</div>
			<?php if ( '' !== $artemis_lead ) : ?>
				<p class="post-hero__lead body-lg" data-reveal="up"><?php echo esc_html( $artemis_lead ); ?></p>
			<?php endif; ?>
			<?php echo $artemis_author->get_updated_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_blog_author(). ?>
		</div>
	</div>
</section>
