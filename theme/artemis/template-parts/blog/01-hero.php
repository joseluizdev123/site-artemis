<?php
/**
 * Blog — hero + abas de categoria (Figma 8325:2675 · mobile 8673:3847).
 * Markup espelha partials/blog-01-hero.html. Textos no Customizer (art_blog_*), abas = categorias
 * com seção na página (artemis_blog_hero_tabs() em inc/section-blog-01-hero.php).
 */

defined( 'ABSPATH' ) || exit;

$artemis_blog_eyebrow = (string) artemis_mod( 'art_blog_eyebrow' );
?>
<section class="blog-hero" aria-labelledby="blog-hero-title">
	<div class="container">
		<?php // Main Container · 8325:2732 ?>
		<div class="blog-hero__box">
			<div class="blog-hero__intro">
				<div class="blog-hero__text">
					<div class="blog-hero__heading">
						<?php if ( $artemis_blog_eyebrow ) : ?>
							<p class="blog-hero__eyebrow eyebrow"><?php echo esc_html( $artemis_blog_eyebrow ); ?></p>
						<?php endif; ?>
						<h1 class="blog-hero__title" id="blog-hero-title"><?php echo artemis_highlight( artemis_mod( 'art_blog_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h1>
					</div>
					<p class="blog-hero__lead body-lg"><?php echo esc_html( artemis_mod( 'art_blog_text' ) ); ?></p>
				</div>
			</div>

			<?php // Banner · 8512:5733 — linhas convergentes; as luzes correm pelas linhas (assets/js/sections/blog-01-hero.js) ?>
			<div class="blog-hero__banner" aria-hidden="true" data-blog-hero-banner>
				<div class="blog-hero__lines">
					<div class="blog-hero__curves">
						<picture>
							<source media="(max-width: 767px)" srcset="<?php echo esc_url( artemis_asset( 'images/blog-01-hero/m-thin-curved-lines.svg' ) ); ?>">
							<img src="<?php echo esc_url( artemis_asset( 'images/blog-01-hero/thin-curved-lines.svg' ) ); ?>" width="1657" height="194" alt="">
						</picture>
					</div>
					<div class="blog-hero__curves blog-hero__curves--flip">
						<picture>
							<source media="(max-width: 767px)" srcset="<?php echo esc_url( artemis_asset( 'images/blog-01-hero/m-thick-curved-lines.svg' ) ); ?>">
							<img src="<?php echo esc_url( artemis_asset( 'images/blog-01-hero/thick-curved-lines.svg' ) ); ?>" width="1657" height="194" alt="">
						</picture>
					</div>
				</div>
				<?php // Light · 8512:5748 — --x/--y desktop, --mx/--my mobile (px, relativos ao grupo) ?>
				<div class="blog-hero__light">
					<i class="blog-hero__pulse" style="--x:739;--y:160;--mx:95.414;--my:68.459"></i>
					<i class="blog-hero__pulse" style="--x:1117;--y:160;--mx:35.938;--my:68.459"></i>
					<i class="blog-hero__pulse" style="--x:85;--y:160;--mx:36.37;--my:68.459"></i>
					<i class="blog-hero__pulse" style="--x:0;--y:321;--mx:0;--my:137.349"></i>
					<i class="blog-hero__pulse" style="--x:171.8265;--y:340.7705;--mx:73.516;--my:145.812;--r:-9.99deg"></i>
					<i class="blog-hero__pulse" style="--x:207.9445;--y:249.79;--mx:88.9765;--my:106.879;--r:-6.42deg"></i>
					<i class="blog-hero__pulse" style="--x:198;--y:128;--mx:84.72;--my:54.769"></i>
					<i class="blog-hero__pulse" style="--x:100;--y:48;--mx:42.79;--my:20.54"></i>
					<i class="blog-hero__pulse" style="--x:30;--y:0;--mx:12.84;--my:0"></i>
					<i class="blog-hero__pulse" style="--x:1337;--y:160;--mx:130.078;--my:68.459"></i>
				</div>
			</div>

			<?php // Button Container · 8325:2725 — menu âncora: cada aba leva à seção da categoria (#slug) ?>
			<nav class="blog-hero__tabs" aria-label="<?php esc_attr_e( 'Categorias do blog', 'artemis' ); ?>">
				<?php foreach ( artemis_blog_hero_tabs() as $artemis_tab ) : ?>
					<a class="blog-hero__tab" href="#<?php echo esc_attr( $artemis_tab[1] ); ?>"><span class="blog-hero__tab-label"><?php echo esc_html( $artemis_tab[0] ); ?></span></a>
				<?php endforeach; ?>
			</nav>
		</div>
	</div>
</section>
