<?php
/**
 * Post — conteúdo (Figma 8331:1178 · mobile 8673:5795). Markup espelha partials/post-02-conteudo.html.
 * Índice "Neste artigo" = H2 do texto (sem os "Destaque"), texto do editor de blocos e chamada final.
 */

defined( 'ABSPATH' ) || exit;

$artemis_content = artemis_post_content();
$artemis_cta     = artemis_post_cta();
$artemis_toc_t   = (string) artemis_mod( 'art_post_toc_title' );
?>
<section class="post-content">
	<div class="container post-content__inner">
		<?php if ( $artemis_content['toc'] ) : ?>
			<aside class="post-toc" aria-labelledby="post-toc-title">
				<nav class="post-toc__nav" aria-labelledby="post-toc-title">
					<p class="post-toc__title" id="post-toc-title"><?php echo esc_html( $artemis_toc_t ); ?></p>
					<ol class="post-toc__list">
						<?php foreach ( $artemis_content['toc'] as $artemis_item ) : ?>
							<li><a class="post-toc__link" data-hover="line" href="#<?php echo esc_attr( $artemis_item[1] ); ?>"><?php echo esc_html( $artemis_item[0] ); ?></a></li>
						<?php endforeach; ?>
					</ol>
				</nav>
			</aside>
		<?php endif; ?>

		<div class="post-content__main">
			<div class="post-body">
				<?php echo $artemis_content['html']; // phpcs:ignore WordPress.Security.EscapeOutput -- saída de the_content. ?>
			</div>

			<?php if ( $artemis_cta ) : ?>
				<aside class="post-cta" aria-labelledby="post-cta-title">
					<div class="post-cta__content">
						<h2 class="post-cta__title" id="post-cta-title"><?php echo esc_html( $artemis_cta['title'] ); ?></h2>
						<?php if ( '' !== $artemis_cta['text'] ) : ?>
							<p class="post-cta__text body-lg"><?php echo esc_html( $artemis_cta['text'] ); ?></p>
						<?php endif; ?>
					</div>
					<div class="post-cta__actions">
						<a class="btn btn--primary" href="<?php echo esc_url( artemis_mod( 'art_contact_cta_url' ) ); ?>"><?php echo esc_html( artemis_mod( 'art_contact_cta_label' ) ); ?></a>
						<?php if ( '' !== (string) artemis_mod( 'art_post_cta_btn2' ) ) : ?>
							<a class="btn" href="<?php echo esc_url( artemis_mod( 'art_post_cta_btn2_url' ) ); ?>"><?php echo esc_html( artemis_mod( 'art_post_cta_btn2' ) ); ?></a>
						<?php endif; ?>
					</div>
				</aside>
			<?php endif; ?>
		</div>
	</div>
</section>
