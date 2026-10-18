<?php
/**
 * Artemis SEO — duas formas de contratar (Figma 8396:3369 · mobile 8704:7347 + 8704:7536). Markup espelha partials/seo-03-formas.html:
 * desktop = acordeão (seo-03-formas.js); celular = os dois estados em sequência, como no Figma.
 */

defined( 'ABSPATH' ) || exit;

$artemis_opts  = artemis_seo_opts();
$artemis_title = artemis_highlight( artemis_mod( 'art_seo_formas_title' ) );

/** Detalhes da opção (texto + "Para quem"), já escapados. */
$artemis_body = function ( $o, $class, $id = '', $hidden = false ) {
	ob_start();
	?>
	<div class="<?php echo esc_attr( $class ); ?>"<?php echo $id ? ' id="' . esc_attr( $id ) . '"' : ''; ?><?php echo $hidden ? ' hidden' : ''; ?>>
		<p class="seo-opt__desc"><?php echo esc_html( $o['desc'] ); ?></p>
		<?php if ( '' !== $o['para'] ) : ?>
			<div class="seo-opt__aud">
				<img class="seo-opt__icon" src="<?php echo esc_url( artemis_asset( 'images/seo/' . $o['icon'] ) ); ?>" width="24" height="24" alt="">
				<div class="seo-opt__aud-text">
					<p class="seo-opt__aud-label"><?php esc_html_e( 'Para quem', 'artemis' ); ?></p>
					<p class="seo-opt__aud-desc"><?php echo esc_html( $o['para'] ); ?></p>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
};
?>
<section class="seo-formas" aria-labelledby="seo-formas-title">
	<div class="seo-formas__desk" data-formas data-active="1">
		<div class="seo-formas__left" data-reveal-group>
			<h2 class="section-title seo-formas__title" id="seo-formas-title" data-split="lines"><?php echo $artemis_title; // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h2>
			<?php foreach ( $artemis_opts as $artemis_o ) : ?>
				<div class="seo-opt seo-opt--<?php echo esc_attr( $artemis_o['cor'] ); ?>" style="--opt:<?php echo (int) $artemis_o['n']; ?>" data-reveal="up">
					<?php if ( $artemis_o['n'] > 1 ) : ?>
						<span class="seo-opt__bar" aria-hidden="true"><i></i></span>
					<?php endif; ?>
					<h3 class="seo-opt__heading"><button class="seo-opt__tab" type="button" id="seo-tab-<?php echo esc_attr( $artemis_o['key'] ); ?>" aria-expanded="<?php echo 1 === $artemis_o['n'] ? 'true' : 'false'; ?>" aria-controls="seo-opt-<?php echo esc_attr( $artemis_o['key'] ); ?>">
						<span class="seo-opt__name"><?php echo esc_html( $artemis_o['name'] ); ?></span>
						<?php if ( '' !== $artemis_o['tag'] ) : ?>
							<span class="seo-opt__tag"><?php echo esc_html( $artemis_o['tag'] ); ?></span>
						<?php endif; ?>
					</button></h3>
					<?php echo $artemis_body( $artemis_o, 'seo-opt__body', 'seo-opt-' . $artemis_o['key'], 1 !== $artemis_o['n'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado acima. ?>
					<?php if ( 2 === $artemis_o['n'] ) : ?>
						<span class="seo-opt__bar seo-opt__bar--end" aria-hidden="true"><i></i></span>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="seo-formas__media" data-wipe="up">
			<?php foreach ( $artemis_opts as $artemis_o ) : ?>
				<figure class="seo-formas__img<?php echo 1 === $artemis_o['n'] ? ' is-active' : ''; ?>" data-opt="<?php echo (int) $artemis_o['n']; ?>">
					<img src="<?php echo esc_url( artemis_asset( 'images/seo/' . $artemis_o['img'] ) ); ?>" width="1440" height="1364" alt="" loading="lazy" decoding="async">
				</figure>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="seo-formas__mob">
		<?php
		list( $artemis_o1, $artemis_o2 ) = $artemis_opts;
		?>
		<div class="seo-fm" data-reveal="up">
			<div class="seo-fm__text">
				<h2 class="section-title seo-formas__title"><?php echo $artemis_title; // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h2>
				<div class="seo-opt seo-opt--<?php echo esc_attr( $artemis_o1['cor'] ); ?> is-open">
					<p class="seo-opt__tab"><span class="seo-opt__name"><?php echo esc_html( $artemis_o1['name'] ); ?></span><?php echo '' !== $artemis_o1['tag'] ? '<span class="seo-opt__tag">' . esc_html( $artemis_o1['tag'] ) . '</span>' : ''; ?></p>
					<?php echo $artemis_body( $artemis_o1, 'seo-fm__body' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado acima. ?>
				</div>
			</div>
			<img class="seo-fm__img" src="<?php echo esc_url( artemis_asset( 'images/seo/' . $artemis_o1['mimg'] ) ); ?>" width="750" height="1094" alt="" loading="lazy" decoding="async">
		</div>
		<div class="seo-fm" data-reveal="up">
			<div class="seo-fm__text">
				<div class="seo-opt seo-opt--<?php echo esc_attr( $artemis_o1['cor'] ); ?>"><p class="seo-opt__tab"><span class="seo-opt__name"><?php echo esc_html( $artemis_o1['name'] ); ?></span></p></div>
				<div class="seo-opt seo-opt--<?php echo esc_attr( $artemis_o2['cor'] ); ?> is-open">
					<p class="seo-opt__tab"><span class="seo-opt__name"><?php echo esc_html( $artemis_o2['name'] ); ?></span><?php echo '' !== $artemis_o2['tag'] ? '<span class="seo-opt__tag">' . esc_html( $artemis_o2['tag'] ) . '</span>' : ''; ?></p>
					<?php echo $artemis_body( $artemis_o2, 'seo-fm__body' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado acima. ?>
				</div>
			</div>
			<img class="seo-fm__img" src="<?php echo esc_url( artemis_asset( 'images/seo/' . $artemis_o2['mimg'] ) ); ?>" width="750" height="1094" alt="" loading="lazy" decoding="async">
		</div>
	</div>
</section>
