<?php
/**
 * Blog (página de posts) — espelha blog/index.html estático (Figma 8294:1703 · mobile 8673:3845).
 * Renderiza template-parts/blog/NN-slug.php em ordem (mesma ordem de src/pages.json) e,
 * no fim, o componente de newsletter (template-parts/components/newsletter.php, símbolo 8436:2153).
 */
get_header();
?>
<main>
	<?php
	foreach ( artemis_glob( 'template-parts/blog/*.php' ) as $artemis_part ) {
		get_template_part( 'template-parts/blog/' . basename( $artemis_part, '.php' ) );
	}
	get_template_part( 'template-parts/components/newsletter' );
	?>
</main>
<?php
get_footer();
