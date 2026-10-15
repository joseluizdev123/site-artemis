<?php
/**
 * Blog — seções de categoria (Figma 8314:1689 · Mobile 8673:3920), uma por categoria com posts.
 * Markup espelha partials/blog-03-categoria.html (repetido 5× no estático).
 * Ordem: term meta _art_ordem; posts: os 3 mais recentes da categoria, sem o destaque
 * (funções em inc/section-blog-03-categoria.php).
 */

defined( 'ABSPATH' ) || exit;

$artemis_cat_more = (string) artemis_mod( 'art_blog_cat_more' );

foreach ( artemis_blog_categories() as $artemis_cat ) :
	$artemis_cat_posts = artemis_blog_cat_posts( $artemis_cat );
	if ( ! $artemis_cat_posts ) {
		continue; // Só o destaque nesta categoria: a seção não aparece.
	}
	$artemis_cat_id = artemis_blog_cat_anchor( $artemis_cat ); // âncora das abas do hero (#slug)
	?>
	<section class="blog-cat" id="<?php echo esc_attr( $artemis_cat_id ); ?>" aria-labelledby="<?php echo esc_attr( $artemis_cat_id ); ?>-title">
		<div class="container blog-cat__inner">
			<?php // Main Description · 8314:1706 ?>
			<h2 class="blog-cat__title" id="<?php echo esc_attr( $artemis_cat_id ); ?>-title"><?php echo esc_html( $artemis_cat->name ); ?></h2>

			<?php // Content Container · 8325:2695 ?>
			<div class="blog-cat__content">
				<?php // Article Card · 8314:1709 — 3 colunas no desktop; no mobile (8673:3923) só os 2 primeiros ?>
				<div class="blog-cat__grid">
					<?php
					foreach ( $artemis_cat_posts as $artemis_cat_post ) {
						echo artemis_blog_card( $artemis_cat_post, array( 'term' => $artemis_cat ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_blog_card.
					}
					?>
				</div>

				<?php if ( '' !== $artemis_cat_more ) : // button · 8325:2696 ?>
					<?php
					/* translators: 1: rótulo do botão ("Ver todos os artigos"), 2: nome da categoria. */
					$artemis_cat_label = sprintf( __( '%1$s de %2$s', 'artemis' ), $artemis_cat_more, $artemis_cat->name );
					?>
					<a class="btn blog-cat__more" href="<?php echo esc_url( get_category_link( $artemis_cat ) ); ?>" aria-label="<?php echo esc_attr( $artemis_cat_label ); ?>"><span><?php echo esc_html( $artemis_cat_more ); ?></span></a>
				<?php endif; ?>
			</div>
		</div>
	</section>
<?php endforeach; ?>
