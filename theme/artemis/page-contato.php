<?php
/**
 * Página Contato (slug "contato") — espelha contato/index.html (Figma 8348:3835 · mobile 8693:2761).
 * Renderiza template-parts/contato/NN-slug.php em ordem (mesma ordem de src/pages.json → "contato").
 */
get_header();
?>
<main>
	<?php
	foreach ( artemis_glob( 'template-parts/contato/*.php' ) as $artemis_part ) {
		get_template_part( 'template-parts/contato/' . basename( $artemis_part, '.php' ) );
	}
	?>
</main>
<?php
get_footer();
