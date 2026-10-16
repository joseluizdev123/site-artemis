<?php
/**
 * Página Sobre (slug "sobre") — espelha sobre/index.html (Figma 8348:3076 · mobile 8689:1932).
 * Renderiza template-parts/sobre/NN-slug.php em ordem (mesma ordem de src/pages.json → "sobre").
 */
get_header();
?>
<main>
	<?php
	foreach ( artemis_glob( 'template-parts/sobre/*.php' ) as $artemis_part ) {
		get_template_part( 'template-parts/sobre/' . basename( $artemis_part, '.php' ) );
	}
	?>
</main>
<?php
get_footer();
