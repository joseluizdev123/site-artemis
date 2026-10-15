<?php
/**
 * Hero — Figma 8160:165 · mobile 8635:1586. Markup espelha index.html.
 * Conteúdo: Customizer (art_hero_*) + CPT art_cliente.
 */

defined( 'ABSPATH' ) || exit;

// Padrão de traços (8160:174): [arquivo, largura do slot, largura da linha, ajuste x do desenho (--dx)]
$artemis_dash_start = array( array( 0, 610.5 ), array( 1, 610.5 ), array( 2, 610.5 ), array( 3, 702 ), array( 4, 702 ), array( 5, 1038 ) );
$artemis_dash_end   = array( array( 6, 519, 516.75, 0 ), array( 7, 610.5, 605.25, 0 ), array( 8, 610.5, 605.25, 0 ), array( 9, 610.5, 516, -1 ), array( 10, 428.25, 339, -0.5 ), array( 11, 522, 251.25, -0.5 ) );
$artemis_clientes   = artemis_get_clientes();
// Hachura do mobile (8635:1905–1987): 83 traços; estes índices usam o traço de 22px (8635:1910, 1918, 1919, 1965, 1967, 1972, 1974)
$artemis_hatch_narrow = array( 5, 13, 14, 60, 62, 67, 69 );
?>
<section class="hero container" aria-labelledby="hero-title">
	<div class="hero__main">
		<div class="hero__row">
			<div class="hero__text">
				<div class="hero__heading">
					<p class="hero__eyebrow"><?php echo esc_html( artemis_mod( 'art_hero_eyebrow' ) ); ?></p>
					<h1 class="hero__title" id="hero-title"><?php echo artemis_highlight( artemis_mod( 'art_hero_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h1>
				</div>
				<p class="hero__lead"><?php echo esc_html( artemis_mod( 'art_hero_lead' ) ); ?></p>
			</div>
			<div class="hero__actions">
				<a class="btn btn--primary" href="<?php echo esc_url( artemis_mod( 'art_contact_cta_url' ) ); ?>"><?php echo esc_html( artemis_mod( 'art_hero_cta1_label' ) ); ?></a>
				<a class="btn" href="<?php echo esc_url( artemis_mod( 'art_hero_cta2_url' ) ); ?>"><?php echo esc_html( artemis_mod( 'art_hero_cta2_label' ) ); ?></a>
			</div>
		</div>

		<div class="hero__pattern" aria-hidden="true" data-dash-pattern>
			<div class="hero__pattern-group hero__pattern-group--start">
				<?php foreach ( $artemis_dash_start as $row ) : ?>
					<div class="hero__dash-row" style="width:<?php echo esc_attr( $row[1] ); ?>px"><img src="<?php echo esc_url( artemis_asset( "images/hero/dash-row-{$row[0]}.svg" ) ); ?>" alt=""></div>
				<?php endforeach; ?>
			</div>
			<div class="hero__pattern-group hero__pattern-group--end">
				<?php foreach ( $artemis_dash_end as $row ) : ?>
					<div class="hero__dash-slot" style="width:<?php echo esc_attr( $row[1] ); ?>px"><div class="hero__dash-row" style="width:<?php echo esc_attr( $row[2] ); ?>px<?php echo $row[3] ? ';--dx:' . esc_attr( $row[3] ) . 'px' : ''; ?>"><img src="<?php echo esc_url( artemis_asset( "images/hero/dash-row-{$row[0]}.svg" ) ); ?>" alt=""></div></div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<?php if ( $artemis_clientes ) : ?>
		<div class="clients">
			<ul class="clients__grid" aria-label="<?php esc_attr_e( 'Clientes', 'artemis' ); ?>">
				<?php foreach ( $artemis_clientes as $cliente ) : ?>
					<?php $artemis_logo_m = artemis_cliente_mobile_style( $cliente ); // --mw/--mh = tamanho do logo no mobile ?>
					<li class="clients__cell"<?php echo $artemis_logo_m ? ' style="' . esc_attr( $artemis_logo_m ) . '"' : ''; ?>><?php echo artemis_cliente_logo( $cliente ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado na função. ?></li>
				<?php endforeach; ?>
				<li class="clients__pattern" aria-hidden="true">
					<img src="<?php echo esc_url( artemis_asset( 'images/pattern-diagonal.svg' ) ); ?>" alt="">
					<span class="clients__hatch"><?php for ( $i = 0; $i < 83; $i++ ) { echo in_array( $i, $artemis_hatch_narrow, true ) ? '<i class="clients__hatch-line--narrow"></i>' : '<i></i>'; } ?></span>
				</li>
			</ul>
			<p class="clients__caption"><?php echo esc_html( artemis_mod( 'art_hero_clients_caption' ) ); ?></p>
		</div>
	<?php endif; ?>
</section>
