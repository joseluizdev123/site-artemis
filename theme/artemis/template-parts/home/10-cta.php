<?php
/**
 * Call to action — Figma 8160:1628. Markup espelha partials/10-cta.html.
 * Conteúdo: Customizer (art_cta_*) + art_contact_cta_url.
 */

defined( 'ABSPATH' ) || exit;

$artemis_cta_status = artemis_cta_status_items();

// Padrão de traços (8160:1729): [arquivo, largura do slot (0 = 100%), largura da linha, inset esquerdo, inset direito]
$artemis_cta_dash_start = array( array( 0, 610.5 ), array( 1, 610.5 ), array( 2, 610.5 ), array( 3, 702 ), array( 4, 702 ), array( 5, 1038 ) );
$artemis_cta_dash_end   = array(
	array( 6, 519, 516.75, '', '' ),
	array( 7, 610.5, 605.25, '', '' ),
	array( 8, 610.5, 605.25, '', '' ),
	array( 9, 610.5, 516, '-0.19%', '' ),
	array( 10, 428.25, 339, '-0.15%', '-0.15%' ),
	array( 11, 522, 251.25, '-0.2%', '-0.2%' ),
);
?>
<section class="cta" id="diagnostico" aria-labelledby="cta-title">
	<?php if ( $artemis_cta_status ) : ?>
		<div class="cta__status">
			<div class="container cta__status-inner">
				<?php foreach ( $artemis_cta_status as $artemis_i => $artemis_item ) : ?>
					<?php if ( 0 === $artemis_i ) : ?>
						<p class="cta__status-item">
							<span class="cta__led" aria-hidden="true"></span>
							<span class="cta__status-text"><?php echo esc_html( $artemis_item ); ?></span>
						</p>
					<?php else : ?>
						<?php echo artemis_cta_signal(); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado na função. ?>
						<p class="cta__status-text"><?php echo esc_html( $artemis_item ); ?></p>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="container cta__main">
		<div class="cta__note-row">
			<p class="eyebrow cta__note"><?php echo esc_html( artemis_mod( 'art_cta_note' ) ); ?></p>
		</div>

		<div class="cta__content">
			<div class="cta__body">
				<div class="cta__heading">
					<h2 class="cta__title" id="cta-title"><?php echo esc_html( artemis_mod( 'art_cta_title' ) ); ?></h2>
					<p class="body-lg cta__text"><?php echo esc_html( artemis_mod( 'art_cta_text' ) ); ?></p>
				</div>
				<a class="btn btn--primary cta__button" href="<?php echo esc_url( artemis_mod( 'art_contact_cta_url' ) ); ?>"><?php echo esc_html( artemis_mod( 'art_contact_cta_label' ) ); ?></a>
			</div>
		</div>

		<div class="cta__pattern" aria-hidden="true" data-cta-pattern>
			<div class="cta__pattern-group cta__pattern-group--start">
				<?php foreach ( $artemis_cta_dash_start as $artemis_row ) : ?>
					<div class="cta__dash-slot"><div class="cta__dash-row" style="width:<?php echo esc_attr( $artemis_row[1] ); ?>px"><img src="<?php echo esc_url( artemis_asset( "images/10-cta/dash-row-{$artemis_row[0]}.svg" ) ); ?>" alt=""></div></div>
				<?php endforeach; ?>
			</div>
			<div class="cta__pattern-group cta__pattern-group--end">
				<?php
				foreach ( $artemis_cta_dash_end as $artemis_row ) :
					$artemis_style = 'width:' . $artemis_row[2] . 'px' . ( $artemis_row[3] ? ';--l:' . $artemis_row[3] : '' ) . ( $artemis_row[4] ? ';--r:' . $artemis_row[4] : '' );
					?>
					<div class="cta__dash-slot" style="width:<?php echo esc_attr( $artemis_row[1] ); ?>px"><div class="cta__dash-row" style="<?php echo esc_attr( $artemis_style ); ?>"><img src="<?php echo esc_url( artemis_asset( "images/10-cta/dash-row-{$artemis_row[0]}.svg" ) ); ?>" alt=""></div></div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
