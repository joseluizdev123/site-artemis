<?php
/**
 * Página Serviços (slug "servicos") — espelha servicos/index.html (Figma 8340:1700 · mobile 8699:4921).
 * Renderiza template-parts/servicos/NN-slug.php em ordem (mesma ordem de src/pages.json → "servicos")
 * e, no fim, a chamada final da Home (template-parts/home/10-cta.php).
 */
get_header();
?>
<main>
	<?php
	foreach ( artemis_glob( 'template-parts/servicos/*.php' ) as $artemis_part ) {
		get_template_part( 'template-parts/servicos/' . basename( $artemis_part, '.php' ) );
	}
	get_template_part( 'template-parts/home/10-cta' );
	?>
</main>
<?php
get_footer();
