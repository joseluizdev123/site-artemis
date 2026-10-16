<?php
/**
 * Sobre — foto da equipe + "Por que existimos" (Figma 8497:2188 · mobile 8689:1946). Markup espelha partials/sobre-02-porque.html.
 * Foto = imagem destacada da página Sobre; sem ela, assets/images/sobre/equipe.jpg.
 */

defined( 'ABSPATH' ) || exit;

$artemis_thumb = get_post_thumbnail_id();
?>
<section class="sobre-porque" aria-labelledby="sobre-porque-title">
	<div class="container sobre-porque__inner">
		<figure class="sobre-porque__photo">
			<?php
			if ( $artemis_thumb ) {
				echo wp_get_attachment_image( $artemis_thumb, 'full', false, array( 'sizes' => '(max-width: 1439px) calc(100vw - 32px), 1376px', 'decoding' => 'async' ) );
			} else {
				?>
				<picture>
					<source media="(max-width: 767px)" srcset="<?php echo esc_url( artemis_asset( 'images/sobre/m-equipe.jpg' ) ); ?>" width="686" height="458">
					<img src="<?php echo esc_url( artemis_asset( 'images/sobre/equipe.jpg' ) ); ?>" width="2752" height="1140" alt="<?php esc_attr_e( 'Os sócios da Artemis lado a lado, de camisa preta', 'artemis' ); ?>" decoding="async">
				</picture>
			<?php } ?>
		</figure>
		<div class="sobre-split">
			<h2 class="section-title sobre-porque__title" id="sobre-porque-title"><?php echo artemis_highlight( artemis_mod( 'art_sobre_porque_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_highlight. ?></h2>
			<div class="sobre-split__text"><?php echo artemis_sobre_paragraphs( 'art_sobre_porque_text' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado em artemis_oqf_paragraphs. ?></div>
		</div>
	</div>
</section>
