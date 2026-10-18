<?php
/**
 * MktOps — o que é instalado (Figma 8502:4262 · mobile 8695:3463). Markup espelha partials/mktops-03-instalado.html:
 * item 1 grande à esquerda, 2–5 em 2×2; "templates" e "guia" (4 e 5) com altura fixa no celular.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="mk-inst" aria-labelledby="mk-inst-title">
	<div class="container mk-inst__inner">
		<h2 class="section-title" id="mk-inst-title" data-split="lines"><?php echo artemis_highlight( artemis_mod( 'art_mk_inst_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h2>
		<div class="mk-inst__body">
			<ul class="mk-inst__grid" data-reveal-group>
				<?php foreach ( artemis_mk_items() as $artemis_item ) : ?>
					<?php
					$artemis_cls = 'mk-inst__item';
					if ( 1 === $artemis_item['n'] ) {
						$artemis_cls .= ' mk-inst__item--main';
					} elseif ( $artemis_item['n'] >= 4 ) {
						$artemis_cls .= ' mk-inst__item--fixed';
					}
					?>
					<li class="<?php echo esc_attr( $artemis_cls ); ?>">
						<span class="mk-inst__file" aria-hidden="true" data-reveal="pop"><img src="<?php echo esc_url( artemis_asset( 'images/mktops/arquivo-' . $artemis_item['file'] . '.svg' ) ); ?>" width="142" height="155" alt=""></span>
						<p class="mk-inst__text" data-reveal="up"><?php echo artemis_highlight( $artemis_item['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
			<div class="mk-inst__community" data-loop>
				<p class="mk-inst__text" data-reveal="up"><?php echo esc_html( artemis_mod( 'art_mk_comunidade' ) ); ?></p>
				<?php get_template_part( 'template-parts/mktops/svg/comunidade' ); // luz verde nas arestas de cada peça enquanto a faixa está na tela (mktops-03-instalado.js). ?>
			</div>
		</div>
	</div>
</section>
