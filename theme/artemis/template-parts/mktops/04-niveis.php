<?php
/**
 * MktOps — os três níveis (Figma 8502:3101 · mobile 8695:3548). Markup espelha partials/mktops-04-niveis.html.
 * Planos do Customizer (art_mk_plano{1..3}_*); o desenho de cada cabeçalho segue a posição do plano (solo, squad, enterprise).
 */

defined( 'ABSPATH' ) || exit;

$artemis_decos = array(
	'solo'       => array( 82, 126 ),
	'squad'      => array( 107, 126 ),
	'enterprise' => array( 144, 112 ),
);
?>
<section class="mk-niveis" aria-labelledby="mk-niveis-title">
	<div class="container mk-niveis__inner">
		<h2 class="section-title" id="mk-niveis-title" data-split="lines"><?php echo esc_html( artemis_mod( 'art_mk_niveis_title' ) ); ?></h2>
		<div class="mk-niveis__grid">
			<?php foreach ( artemis_mk_planos() as $artemis_plano ) : ?>
				<?php
				$artemis_id   = 'mk-plano-' . $artemis_plano['key'];
				$artemis_deco = $artemis_decos[ $artemis_plano['key'] ];
				?>
				<article class="mk-plano<?php echo 2 === $artemis_plano['n'] ? ' mk-plano--mid' : ''; ?>" aria-labelledby="<?php echo esc_attr( $artemis_id ); ?>">
					<header class="mk-plano__head">
						<?php if ( '' !== $artemis_plano['tag'] ) : ?>
							<div class="mk-plano__title">
								<h3 class="mk-plano__name" id="<?php echo esc_attr( $artemis_id ); ?>"><?php echo esc_html( $artemis_plano['name'] ); ?></h3>
								<span class="mk-plano__tag"><?php echo esc_html( $artemis_plano['tag'] ); ?></span>
							</div>
						<?php else : ?>
							<h3 class="mk-plano__name" id="<?php echo esc_attr( $artemis_id ); ?>"><?php echo esc_html( $artemis_plano['name'] ); ?></h3>
						<?php endif; ?>
						<img class="mk-plano__deco mk-plano__deco--<?php echo esc_attr( $artemis_plano['key'] ); ?>" src="<?php echo esc_url( artemis_asset( 'images/mktops/nivel-' . $artemis_plano['key'] . '.svg' ) ); ?>" width="<?php echo (int) $artemis_deco[0]; ?>" height="<?php echo (int) $artemis_deco[1]; ?>" alt="" aria-hidden="true">
					</header>
					<dl class="mk-plano__rows">
						<?php foreach ( $artemis_plano['rows'] as $artemis_row ) : ?>
							<div class="mk-plano__row"><dt><?php echo esc_html( $artemis_row[0] ); ?></dt><dd><?php echo esc_html( $artemis_row[1] ); ?></dd></div>
						<?php endforeach; ?>
					</dl>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
