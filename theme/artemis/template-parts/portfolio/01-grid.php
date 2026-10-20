<?php
/**
 * Portfólio — coluna "Navegue por aqui" (filtro por categoria) + grade de logos. Markup espelha partials/portfolio-01-grid.html.
 * Projetos: CPT art_projeto · categorias: art_projeto_cat (inc/section-portfolio.php) · WhatsApp: Dados do site.
 */

defined( 'ABSPATH' ) || exit;

$artemis_projetos = artemis_get_projetos();
$artemis_cats     = artemis_get_projeto_cats();
$artemis_wa       = artemis_whatsapp_url( artemis_mod( 'art_contact_whatsapp' ) );
$artemis_check    = '<svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2.5 6.2l2.3 2.3 4.7-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$artemis_filters  = array( 'todos' => __( 'Todos', 'artemis' ) );
foreach ( $artemis_cats as $artemis_cat ) {
	$artemis_filters[ $artemis_cat->slug ] = $artemis_cat->name;
}
?>
<section class="pf" aria-labelledby="pf-title" data-portfolio>
	<h1 class="pf__sr" id="pf-title"><?php echo esc_html( get_the_title() . ' — Studio Artemis' ); ?></h1>
	<aside class="pf-side" aria-labelledby="pf-nav" data-pf-side>
		<h2 class="pf-side__title" id="pf-nav" data-split="lines" data-motion-start="load"><?php echo esc_html( artemis_mod( 'art_pf_title' ) ); ?></h2>
		<button class="pf-dd" type="button" aria-expanded="false" aria-controls="pf-filtros" data-pf-dd><span data-pf-dd-label><?php echo esc_html( sprintf( __( 'Categoria: %s', 'artemis' ), __( 'Todos', 'artemis' ) ) ); ?></span><svg class="pf-dd__chev" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
		<ul class="pf-side__list" id="pf-filtros" data-reveal-group="up" data-motion-start="load" data-motion-delay="150">
			<?php foreach ( $artemis_filters as $artemis_slug => $artemis_label ) : ?>
				<li><button class="pf-chk" type="button" aria-pressed="<?php echo 'todos' === $artemis_slug ? 'true' : 'false'; ?>" data-pf-filter="<?php echo esc_attr( $artemis_slug ); ?>"><span class="pf-chk__box" aria-hidden="true"><?php echo $artemis_check; // phpcs:ignore WordPress.Security.EscapeOutput -- SVG fixo. ?></span><span><?php echo esc_html( $artemis_label ); ?></span></button></li>
			<?php endforeach; ?>
		</ul>
	</aside>
	<div class="pf-main">
		<p class="pf__sr" role="status" data-pf-status></p>
		<ul class="pf-grid" data-reveal-group="up" data-motion-start="load" data-motion-delay="200">
			<?php foreach ( $artemis_projetos as $artemis_i => $artemis_p ) : ?>
				<?php
				$artemis_name  = get_the_title( $artemis_p );
				$artemis_url   = (string) get_post_meta( $artemis_p->ID, '_art_projeto_url', true );
				$artemis_terms = wp_get_post_terms( $artemis_p->ID, 'art_projeto_cat', array( 'fields' => 'slugs' ) );
				$artemis_img   = wp_get_attachment_image_src( get_post_thumbnail_id( $artemis_p ), 'full' );
				$artemis_img_t = $artemis_img ? sprintf(
					'<img class="pf-tile__img" src="%1$s" width="%2$d" height="%3$d" alt="%4$s"%5$s decoding="async">',
					esc_url( $artemis_img[0] ),
					(int) $artemis_img[1],
					(int) $artemis_img[2],
					$artemis_url ? '' : esc_attr( $artemis_name ),
					$artemis_i < 8 ? '' : ' loading="lazy"'
				) : '';
				?>
				<li data-pf-cat="<?php echo esc_attr( implode( ' ', is_wp_error( $artemis_terms ) ? array() : $artemis_terms ) ); ?>">
					<?php if ( $artemis_url ) : ?>
						<a class="pf-tile" href="<?php echo esc_url( $artemis_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $artemis_name ); ?>"><?php echo $artemis_img_t; // phpcs:ignore WordPress.Security.EscapeOutput -- escapado acima. ?><span class="pf-tile__name" aria-hidden="true"><?php echo esc_html( $artemis_name ); ?></span></a>
					<?php else : ?>
						<div class="pf-tile"><?php echo $artemis_img_t; // phpcs:ignore WordPress.Security.EscapeOutput -- escapado acima. ?><?php echo $artemis_img_t ? '' : '<span class="pf-tile__ph">' . esc_html( $artemis_name ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<?php if ( $artemis_wa ) : ?>
	<a class="pf-wa" href="<?php echo esc_url( $artemis_wa ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Falar no WhatsApp', 'artemis' ); ?>"><svg width="44" height="44" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg></a>
<?php endif; ?>
