<?php
/**
 * Header — Figma 8165:2338 (desktop) · 8635:3579 (mobile) · menu mobile 8662:2946.
 * Markup espelha partials/header.html. Menu: localização "primary" (barra desktop e gaveta mobile).
 * Variante Blog (8340:2124 · mobile 8673:4443) na página de posts: logo sem ™ + selo "BLOG",
 * link "Início" antes do menu e "Blog" com aria-current (o item do menu da página de posts já vem marcado).
 */

defined( 'ABSPATH' ) || exit;

$artemis_email    = (string) artemis_mod( 'art_contact_email' );
$artemis_cta_url  = artemis_mod( 'art_contact_cta_url' );
$artemis_nav_args = array(
	'theme_location' => 'primary',
	'container'      => false,
	'items_wrap'     => '%3$s',
	'depth'          => 1,
	'fallback_cb'    => 'artemis_nav_fallback',
);
$artemis_blog     = artemis_is_blog();
$artemis_post     = is_singular( 'post' ); // artigo: no celular o header tem o selo e 64 de altura (8693:2102)
$artemis_inicio   = $artemis_blog ? sprintf( '<a href="%s">%s</a>', esc_url( home_url( '/' ) ), esc_html__( 'Início', 'artemis' ) ) : '';
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

<header class="site-header<?php echo $artemis_blog ? ' site-header--blog' : ''; ?><?php echo $artemis_post ? ' site-header--tag-m' : ''; ?>" data-header>
	<div class="container site-header__bar">
		<div class="site-header__inner">
			<?php if ( $artemis_blog ) : ?>
				<div class="site-header__brand">
					<a class="site-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Studio Artemis — página inicial', 'artemis' ); ?>">
						<picture>
							<?php if ( ! $artemis_post ) : // Blog no celular = header da Home (logo com ™, sem selo) ?>
								<source media="(max-width: 767px)" srcset="<?php echo esc_url( artemis_asset( 'images/logo-artemis.svg' ) ); ?>" width="137" height="32">
							<?php endif; ?>
							<img src="<?php echo esc_url( artemis_asset( 'images/header/logo-blog.svg' ) ); ?>" width="125" height="32" alt="Artemis">
						</picture>
					</a>
					<span class="site-header__tag"><span><?php esc_html_e( 'Blog', 'artemis' ); ?></span></span>
				</div>
			<?php else : ?>
				<a class="site-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Studio Artemis — página inicial', 'artemis' ); ?>">
					<img src="<?php echo esc_url( artemis_asset( 'images/logo-artemis.svg' ) ); ?>" width="137" height="32" alt="Artemis">
				</a>
			<?php endif; ?>
			<div class="site-header__actions">
				<nav class="site-nav" aria-label="<?php esc_attr_e( 'Principal', 'artemis' ); ?>">
					<?php echo $artemis_inicio; // phpcs:ignore WordPress.Security.EscapeOutput -- escapado acima. ?>
					<?php wp_nav_menu( array_merge( $artemis_nav_args, array( 'walker' => new Artemis_Flat_Nav_Walker() ) ) ); ?>
				</nav>
				<span class="site-header__sep" aria-hidden="true"></span>
				<a class="site-header__cta" href="<?php echo esc_url( $artemis_cta_url ); ?>"<?php echo is_page( 'contato' ) ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Agendar diagnóstico', 'artemis' ); ?></a>
			</div>
		</div>
		<?php /* Mobile (≤767): botão "Menu" (8635:3577) abre a gaveta */ ?>
		<button class="site-header__toggle" type="button" aria-expanded="false" aria-controls="site-menu" aria-haspopup="dialog" data-menu-open>
			<span><?php esc_html_e( 'Menu', 'artemis' ); ?></span>
		</button>
	</div>

	<?php /* Menu mobile · 8662:2946 (325 de largura, altura da tela, entra da esquerda em overlay) */ ?>
	<div class="site-header__drawer" id="site-menu" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'artemis' ); ?>" data-menu>
		<div class="site-header__backdrop" data-menu-close></div>
		<div class="site-header__panel">
			<div class="site-header__panel-top">
				<div class="site-header__panel-head">
					<button class="site-header__close" type="button" data-menu-close><?php esc_html_e( 'Fechar', 'artemis' ); ?></button>
				</div>
				<nav class="site-header__menu" aria-label="<?php esc_attr_e( 'Principal', 'artemis' ); ?>">
					<?php echo $artemis_inicio; // phpcs:ignore WordPress.Security.EscapeOutput -- escapado acima. ?>
					<?php wp_nav_menu( array_merge( $artemis_nav_args, array( 'walker' => new Artemis_Flat_Nav_Walker() ) ) ); ?>
					<a href="<?php echo esc_url( $artemis_cta_url ); ?>"><?php esc_html_e( 'Agendar diagnóstico', 'artemis' ); ?></a>
				</nav>
			</div>
			<?php if ( $artemis_email ) : ?>
				<div class="site-header__contact">
					<p class="site-header__contact-label" aria-live="polite" data-copy-status data-copied="<?php esc_attr_e( 'E-mail copiado', 'artemis' ); ?>"><?php esc_html_e( 'Fale conosco', 'artemis' ); ?></p>
					<a class="site-header__contact-email" href="<?php echo esc_url( 'mailto:' . antispambot( $artemis_email, 1 ) ); ?>" data-copy-email><?php echo esc_html( antispambot( $artemis_email ) ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</header>
