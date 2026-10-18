<?php
/**
 * Serviços — as etapas (Figma "Tabs" 8573:1797 · mobile 8709:5717…). Markup espelha partials/servicos-02-etapas.html:
 * cabeçalho + painel por etapa (CPT art_etapa, na ordem) e a ilustração no fim. Comportamento em
 * assets/js/sections/servicos-02-etapas.js. O CSS posiciona até 3 etapas como no Figma.
 */

defined( 'ABSPATH' ) || exit;

$artemis_etapas = artemis_get_etapas();
if ( ! $artemis_etapas ) {
	return;
}
$artemis_total = count( $artemis_etapas );
?>
<section class="srv-steps" aria-label="<?php esc_attr_e( 'Etapas do serviço', 'artemis' ); ?>">
	<div class="srv-steps__grid" data-steps data-active="1">
		<?php
		foreach ( $artemis_etapas as $artemis_i => $artemis_etapa ) :
			$artemis_n  = $artemis_i + 1;
			$artemis_on = 1 === $artemis_n;
			?>
			<div class="srv-step__head" style="--step:<?php echo (int) $artemis_n; ?>">
				<h2 class="srv-step__heading"><button class="srv-step__tab" type="button" id="srv-tab-<?php echo (int) $artemis_n; ?>" aria-expanded="<?php echo $artemis_on ? 'true' : 'false'; ?>" aria-controls="srv-panel-<?php echo (int) $artemis_n; ?>">
					<?php /* translators: %d: número da etapa */ ?>
					<span class="srv-step__tag"><?php echo esc_html( sprintf( __( 'Etapa %d', 'artemis' ), $artemis_n ) ); ?></span>
					<span class="srv-step__title"><?php echo esc_html( get_the_title( $artemis_etapa ) ); ?></span>
				</button></h2>
				<?php if ( $artemis_n > 1 ) : ?>
					<span class="srv-step__bar" aria-hidden="true"><i></i></span>
				<?php endif; ?>
				<?php if ( $artemis_n === $artemis_total && $artemis_n > 1 ) : ?>
					<span class="srv-step__bar srv-step__bar--end" aria-hidden="true"><i></i></span>
				<?php endif; ?>
			</div>
			<div class="srv-step__panel" id="srv-panel-<?php echo (int) $artemis_n; ?>" role="region" aria-labelledby="srv-tab-<?php echo (int) $artemis_n; ?>" style="--step:<?php echo (int) $artemis_n; ?>"<?php echo $artemis_on ? '' : ' hidden'; ?>>
				<?php foreach ( artemis_etapa_blocks( $artemis_etapa ) as $artemis_block ) : ?>
					<div class="srv-info">
						<img class="srv-info__icon" src="<?php echo esc_url( artemis_asset( 'images/servicos-02-etapas/icon-' . $artemis_block[0] . '.svg' ) ); ?>" width="24" height="24" alt="">
						<div class="srv-info__content">
							<h3 class="srv-info__label"><?php echo esc_html( $artemis_block[1] ); ?></h3>
							<?php if ( $artemis_block[3] ) : ?>
								<ul class="srv-info__list">
									<?php foreach ( $artemis_block[3] as $artemis_item ) : ?>
										<li><?php echo esc_html( $artemis_item ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php else : ?>
								<p class="srv-info__text"><?php echo esc_html( $artemis_block[2] ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>

		<?php /* Ilustração em 4 camadas do tamanho do quadro (desktop 296×593 · celular 375×421): monta com o scroll e flutua, como a da Home */ ?>
		<div class="srv-steps__art" aria-hidden="true">
			<div class="srv-art" data-rise data-loop>
				<?php foreach ( array( 'base' => 'base', 'screen' => 'tela', 'line' => 'tracejado', 'badge' => 'selo' ) as $artemis_layer => $artemis_file ) : ?>
					<picture class="srv-art__layer srv-art__layer--<?php echo esc_attr( $artemis_layer ); ?>"><source media="(max-width: 767px)" srcset="<?php echo esc_url( artemis_asset( 'images/servicos-02-etapas/m-ilustra-' . $artemis_file . '.svg' ) ); ?>" width="375" height="421"><img src="<?php echo esc_url( artemis_asset( 'images/servicos-02-etapas/ilustra-' . $artemis_file . '.svg' ) ); ?>" width="296" height="593" alt="" decoding="async"></picture>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
