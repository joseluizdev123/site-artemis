<?php
/**
 * Artigo do blog — espelha blog/anatomia-do-wcontrol-como-se-valida-um-documento-de-mil-paginas/index.html
 * (Figma 8331:321 · mobile 8673:5776). Renderiza template-parts/post/NN-slug.php em ordem (mesma ordem de
 * src/pages.json → "post") e, no fim, o componente de newsletter (símbolo 8436:2153).
 * Outros tipos de post caem no index.php.
 */
if ( ! is_singular( 'post' ) ) {
	get_template_part( 'index' );
	return;
}
get_header();
?>
<main>
	<?php
	while ( have_posts() ) {
		the_post();
		foreach ( artemis_glob( 'template-parts/post/*.php' ) as $artemis_part ) {
			get_template_part( 'template-parts/post/' . basename( $artemis_part, '.php' ) );
		}
	}
	get_template_part( 'template-parts/components/newsletter' );
	?>
</main>
<?php
get_footer();
