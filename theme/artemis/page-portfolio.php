<?php
/**
 * Página Portfólio (slug "portfolio") — espelha portfolio/index.html (layout antigo, quadros Desktop-copia · Mobile-antigo).
 * Header próprio e sem rodapé; renderiza template-parts/portfolio/NN-slug.php em ordem (src/pages.json → "portfolio").
 */
get_header( 'portfolio' );
?>
<main>
	<?php
	foreach ( artemis_glob( 'template-parts/portfolio/*.php' ) as $artemis_part ) {
		get_template_part( 'template-parts/portfolio/' . basename( $artemis_part, '.php' ) );
	}
	?>
</main>
<?php
get_footer( 'portfolio' );
