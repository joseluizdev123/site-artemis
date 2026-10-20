<?php
/**
 * Header do Portfólio (get_header( 'portfolio' )) — layout antigo: logo + "Comece um projeto", no fluxo e sem menu
 * (a página fica fora do menu oficial). Markup espelha partials/portfolio-header.html.
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="pf-header">
	<div class="pf-header__in">
		<a class="pf-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Studio Artemis — página inicial', 'artemis' ); ?>"><img src="<?php echo esc_url( artemis_asset( 'images/logo-artemis.svg' ) ); ?>" width="137" height="32" alt="Artemis"></a>
		<a class="pf-start" href="<?php echo esc_url( artemis_footer_url( artemis_mod( 'art_pf_cta_url' ) ) ); ?>"><span class="pf-start__dot" aria-hidden="true"></span><?php echo esc_html( artemis_mod( 'art_pf_cta' ) ); ?></a>
	</div>
</header>
