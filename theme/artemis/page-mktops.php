<?php
/**
 * Página MktOps (slug "mktops") — espelha mktops/index.html (Figma 8348:4181 · mobile 8695:3268).
 * Renderiza template-parts/mktops/NN-slug.php em ordem (mesma ordem de src/pages.json → "mktops").
 */
get_header();
?>
<main>
	<?php
	foreach ( artemis_glob( 'template-parts/mktops/*.php' ) as $artemis_part ) {
		get_template_part( 'template-parts/mktops/' . basename( $artemis_part, '.php' ) );
	}
	?>
</main>
<?php
get_footer();
