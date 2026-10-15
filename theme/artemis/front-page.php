<?php
/**
 * Home — espelha index.html estático (Figma 8160:163).
 * Renderiza template-parts/home/NN-slug.php em ordem (mesma ordem de partials/).
 */
get_header();
?>
<main>
	<?php
	foreach ( artemis_glob( 'template-parts/home/*.php' ) as $artemis_part ) {
		get_template_part( 'template-parts/home/' . basename( $artemis_part, '.php' ) );
	}
	?>
</main>
<?php
get_footer();
