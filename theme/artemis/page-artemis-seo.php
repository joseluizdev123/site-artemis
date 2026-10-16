<?php
/**
 * Página Artemis SEO (slug "artemis-seo") — espelha artemis-seo/index.html (Figma 8348:4008 · mobile 8704:6450).
 * Renderiza template-parts/seo/NN-slug.php em ordem (mesma ordem de src/pages.json → "seo").
 */
get_header();
?>
<main>
	<?php
	foreach ( artemis_glob( 'template-parts/seo/*.php' ) as $artemis_part ) {
		get_template_part( 'template-parts/seo/' . basename( $artemis_part, '.php' ) );
	}
	?>
</main>
<?php
get_footer();
