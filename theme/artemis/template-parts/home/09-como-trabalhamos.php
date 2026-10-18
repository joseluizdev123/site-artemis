<?php
/**
 * Como trabalhamos — Figma 8490:2191. Markup espelha partials/09-como-trabalhamos.html.
 * Conteúdo: Customizer (art_ct_*), ver inc/section-09-como-trabalhamos.php.
 */

defined( 'ABSPATH' ) || exit;

$artemis_ct_narrow  = artemis_como_trabalhamos_narrow_strokes();
$artemis_ct_wide    = artemis_como_trabalhamos_img( 'diagonal-24.svg' );
$artemis_ct_slim    = artemis_como_trabalhamos_img( 'diagonal-22.svg' );
$artemis_ct_tick    = artemis_como_trabalhamos_img( 'tick.svg' );
// Text block do card "Run:API" (8490:2599): [tipo da linha, larguras]; null = linha que preenche o resto.
$artemis_ct_lines   = array(
	array( 'single', array( 63 ) ),
	array( 'indent', array( 50, 'full' ) ),
	array( 'row', array( 22, null, 24.734, 16 ) ),
	array( 'row', array( 52 ) ),
	array( 'indent', array( 55, 111 ) ),
	array( 'row', array( 22, null, 52 ) ),
	array( 'indent', array( 50 ) ),
	array( 'row', array( 22, null, 24.734, 16 ) ),
	array( 'row', array( 52 ) ),
	array( 'row', array( 22, null, 52 ) ),
);

/** Uma linha do text block: largura fixa (--w), 'full' (100%) ou null (preenche). */
$artemis_ct_bar = function ( $w ) {
	if ( null === $w ) {
		return '<span class="is-fill"></span>';
	}
	if ( 'full' === $w ) {
		return '<span></span>';
	}
	return '<span style="--w:' . esc_attr( $w ) . 'px"></span>';
};
?>
<section class="como-trabalhamos" aria-labelledby="como-trabalhamos-title">
	<div class="como-trabalhamos__inner container">
		<div class="como-trabalhamos__head section-head" data-reveal-group>
			<p class="como-trabalhamos__eyebrow eyebrow" data-reveal="fade"><?php echo esc_html( artemis_mod( 'art_ct_eyebrow' ) ); ?></p>
			<h2 data-split="lines" class="como-trabalhamos__title section-title section-title--em-400" id="como-trabalhamos-title"><?php echo artemis_highlight( artemis_mod( 'art_ct_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h2>
		</div>

		<div class="como-trabalhamos__frame">
			<ul class="como-trabalhamos__rules" data-reveal-group data-stagger="120">
				<?php foreach ( artemis_como_trabalhamos_rules() as $artemis_ct_pos => $artemis_ct_rule ) : ?>
					<li class="como-trabalhamos__rule">
						<?php if ( 1 === $artemis_ct_pos ) : ?>
							<div class="como-trabalhamos__illustration" aria-hidden="true" data-rise data-loop>
								<img class="como-trabalhamos__base" src="<?php echo esc_url( artemis_como_trabalhamos_img( 'base-outline.svg' ) ); ?>" alt="">
								<div class="como-trabalhamos__iso como-trabalhamos__iso--top como-trabalhamos__iso--code">
									<div class="como-trabalhamos__card como-trabalhamos__card--code">
										<p class="como-trabalhamos__code-label">&lt;/&gt; Run:API</p>
										<div class="como-trabalhamos__lines">
											<?php foreach ( $artemis_ct_lines as $artemis_ct_line ) : ?>
												<?php if ( 'single' === $artemis_ct_line[0] ) : ?>
													<?php echo $artemis_ct_bar( $artemis_ct_line[1][0] ); // phpcs:ignore WordPress.Security.EscapeOutput -- markup fixo, largura escapada. ?>
												<?php else : ?>
													<div class="como-trabalhamos__lines-<?php echo esc_attr( $artemis_ct_line[0] ); ?>"><?php echo implode( '', array_map( $artemis_ct_bar, $artemis_ct_line[1] ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- markup fixo, larguras escapadas. ?></div>
												<?php endif; ?>
											<?php endforeach; ?>
										</div>
									</div>
								</div>
								<div class="como-trabalhamos__iso como-trabalhamos__iso--side como-trabalhamos__iso--tick-code"><span class="como-trabalhamos__tick"><img src="<?php echo esc_url( $artemis_ct_tick ); ?>" alt=""></span></div>
								<img class="como-trabalhamos__screen-outline como-trabalhamos__lift" src="<?php echo esc_url( artemis_como_trabalhamos_img( 'screen-outline.svg' ) ); ?>" alt="">
								<div class="como-trabalhamos__iso como-trabalhamos__iso--top como-trabalhamos__iso--screen como-trabalhamos__lift">
									<div class="como-trabalhamos__card como-trabalhamos__card--screen">
										<div class="como-trabalhamos__screen-row">
											<div class="como-trabalhamos__screen-pattern"><img src="<?php echo esc_url( artemis_como_trabalhamos_img( 'screen-pattern.svg' ) ); ?>" alt=""></div>
											<div class="como-trabalhamos__screen-ui">
												<span class="como-trabalhamos__screen-header"></span>
												<div class="como-trabalhamos__screen-buttons"><span></span><span></span><span></span></div>
												<div class="como-trabalhamos__screen-lower"><span></span><span></span></div>
											</div>
										</div>
									</div>
								</div>
								<div class="como-trabalhamos__iso como-trabalhamos__iso--side como-trabalhamos__iso--tick-screen como-trabalhamos__lift"><span class="como-trabalhamos__tick"><img src="<?php echo esc_url( $artemis_ct_tick ); ?>" alt=""></span></div>
								<div class="como-trabalhamos__iso como-trabalhamos__iso--side como-trabalhamos__iso--line"><span class="como-trabalhamos__dashed"><img src="<?php echo esc_url( artemis_como_trabalhamos_img( 'dashed-line.svg' ) ); ?>" alt=""></span></div>
								<div class="como-trabalhamos__iso como-trabalhamos__iso--side como-trabalhamos__iso--status">
									<div class="como-trabalhamos__status">
										<img src="<?php echo esc_url( artemis_como_trabalhamos_img( 'dashed-circle.svg' ) ); ?>" alt="">
										<p class="como-trabalhamos__status-label"><span><?php esc_html_e( 'Validação', 'artemis' ); ?></span><span><?php esc_html_e( 'Pendente', 'artemis' ); ?></span></p>
									</div>
								</div>
							</div>
						<?php endif; ?>
						<div class="como-trabalhamos__rule-body" data-reveal="up">
							<h3 class="como-trabalhamos__rule-title"><?php echo esc_html( $artemis_ct_rule[0] ); ?></h3>
							<p class="como-trabalhamos__rule-text"><?php echo esc_html( $artemis_ct_rule[1] ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>

			<div class="como-trabalhamos__pattern" aria-hidden="true" data-wipe>
				<?php for ( $artemis_ct_i = 0; $artemis_ct_i < 83; $artemis_ct_i++ ) : ?>
					<?php if ( in_array( $artemis_ct_i, $artemis_ct_narrow, true ) ) : ?>
						<span class="como-trabalhamos__stroke como-trabalhamos__stroke--narrow"><img src="<?php echo esc_url( $artemis_ct_slim ); ?>" alt=""></span>
					<?php else : ?>
						<span class="como-trabalhamos__stroke"><img src="<?php echo esc_url( $artemis_ct_wide ); ?>" alt=""></span>
					<?php endif; ?>
				<?php endfor; ?>
			</div>
		</div>
	</div>
</section>
