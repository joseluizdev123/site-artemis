<?php
/**
 * Página "Dados do site" — dados globais num lugar só, como uma options page (sem ACF: Settings API nativa).
 * Opção `artemis_site` (array). Lida por artemis_mod() (inc/helpers.php) com as mesmas chaves que antes viviam no
 * Personalizar: o que já foi salvo lá continua valendo até a primeira gravação aqui.
 * Quem edita: capability edit_pages (Editor e Administrador) — o Personalizar exige Administrador.
 */

defined( 'ABSPATH' ) || exit;

/** Grupos e campos da página: grupo => [título, descrição, [chave => [rótulo, tipo (email|tel|text|url|label|link), ajuda]]]. */
function artemis_site_fields() {
	$social = array();
	foreach ( function_exists( 'artemis_footer_socials' ) ? artemis_footer_socials() : array() as $key => $info ) {
		$social[ $key ] = array( $info[0], 'url', __( 'Link do perfil. Vazio esconde o ícone.', 'artemis' ) );
	}
	return array(
		'contato'    => array(
			__( 'Contato', 'artemis' ),
			__( 'Aparece no rodapé de todas as páginas e na página Contato. O e-mail também aparece no menu do celular.', 'artemis' ),
			array(
				'art_contact_email'    => array( __( 'E-mail', 'artemis' ), 'email', __( 'Vazio esconde o e-mail do site.', 'artemis' ) ),
				'art_contact_whatsapp' => array( __( 'WhatsApp', 'artemis' ), 'tel', __( 'Como deve aparecer no site, com DDD (ex.: +55 (48) 99999-9999). O link do WhatsApp é montado sozinho; sem o +55, o Brasil é assumido. Vazio esconde o item.', 'artemis' ) ),
				'art_contact_address'  => array( __( 'Endereço', 'artemis' ), 'text', __( 'No rodapé vira link para o Google Maps. O endereço da seção "Onde estamos", na página Sobre, é editado em Aparência → Personalizar.', 'artemis' ) ),
			),
		),
		'formulario' => array(
			__( 'Formulário de contato', 'artemis' ),
			'',
			array(
				'art_contato_to' => array( __( 'E-mail que recebe as mensagens', 'artemis' ), 'email', __( 'Vazio = o e-mail de contato acima.', 'artemis' ) ),
			),
		),
		'botao'      => array(
			__( 'Botão "Agendar diagnóstico"', 'artemis' ),
			'',
			array(
				'art_contact_cta_label' => array( __( 'Texto do botão', 'artemis' ), 'label', __( 'Um texto só para o site todo: topo do site, menu do celular, Home (topo e chamada final), Serviços e final dos artigos do blog. Até 20 caracteres e sem caixa alta, para caber no topo do site no tablet.', 'artemis' ) ),
				'art_contact_cta_url'   => array( __( 'Link do botão', 'artemis' ), 'link', __( 'Para onde o botão leva, nos mesmos lugares. Página do site começando com / (ex.: /contato) ou endereço completo com https://.', 'artemis' ) ),
			),
		),
		'redes'      => array( __( 'Redes sociais', 'artemis' ), __( 'Ícones do rodapé, na ordem abaixo.', 'artemis' ), $social ),
	);
}

/** Campos achatados: chave => [rótulo, tipo, ajuda]. */
function artemis_site_field_list() {
	$list = array();
	foreach ( artemis_site_fields() as $group ) {
		$list += $group[2];
	}
	return $list;
}

/** A chave pertence à página "Dados do site"? */
function artemis_is_site_option( $key ) {
	static $keys = null;
	if ( null === $keys ) {
		$list = array_fill_keys( array_keys( artemis_site_field_list() ), true );
		if ( ! function_exists( 'artemis_footer_socials' ) ) { // antes de inc/section-footer.php carregar: não guarda a lista incompleta
			return isset( $list[ $key ] );
		}
		$keys = $list;
	}
	return isset( $keys[ $key ] );
}

add_action( 'admin_init', function () {
	register_setting( 'artemis_site', 'artemis_site', array(
		'type'              => 'array',
		'sanitize_callback' => 'artemis_site_sanitize',
		'default'           => array(),
		'show_in_rest'      => false,
	) );
} );

// options.php exige manage_options por padrão; aqui basta poder editar páginas.
add_filter( 'option_page_capability_artemis_site', function () {
	return 'edit_pages';
} );

add_action( 'admin_menu', function () {
	add_menu_page( __( 'Dados do site', 'artemis' ), __( 'Dados do site', 'artemis' ), 'edit_pages', 'artemis-site', 'artemis_site_page', 'dashicons-id-alt', 3 );
} );

/** Aviso na página (fora do painel — WP-CLI, front — a função do WordPress não existe: o valor segue validado, só sem aviso). */
function artemis_site_notice( $setting, $code, $message ) {
	if ( function_exists( 'add_settings_error' ) ) {
		add_settings_error( $setting, $code, $message );
	}
}

/** Valida e limpa. Valor inválido não é gravado: fica o anterior e aparece um aviso. */
function artemis_site_sanitize( $input ) {
	static $done = null;
	if ( null !== $done && $input === $done ) { // 1ª gravação: o WordPress chama de novo com o valor já limpo (update_option → add_option)
		return $done;
	}
	$input = is_array( $input ) ? $input : array();
	$out   = array();
	foreach ( artemis_site_field_list() as $key => $field ) {
		$raw   = isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) ? trim( (string) $input[ $key ] ) : '';
		$error = '';
		switch ( $field[1] ) {
			case 'email':
				$value = sanitize_email( $raw );
				if ( '' !== $raw && ! is_email( $value ) ) {
					/* translators: %s: rótulo do campo. */
					$error = __( '"%s" não parece um e-mail válido.', 'artemis' );
				}
				break;
			case 'tel':
				$value = sanitize_text_field( $raw );
				if ( '' !== $value && strlen( preg_replace( '/\D+/', '', $value ) ) < 10 ) {
					/* translators: %s: rótulo do campo. */
					$error = __( '"%s" precisa do DDD e do número.', 'artemis' );
				}
				break;
			case 'url':
				$value = '' === $raw ? '' : esc_url_raw( $raw );
				if ( '' !== $raw && '' === $value ) {
					/* translators: %s: rótulo do campo. */
					$error = __( '"%s" não parece um link válido.', 'artemis' );
				}
				break;
			case 'label':
				$value = sanitize_text_field( $raw );
				if ( '' === $value || mb_strlen( $value ) > 20 ) {
					/* translators: %s: rótulo do campo. */
					$error = __( '"%s" precisa ter de 1 a 20 caracteres.', 'artemis' );
				}
				break;
			case 'link':
				$value = sanitize_text_field( $raw );
				if ( ! preg_match( '#^(/|\#|https?://[^\s/]+\.|mailto:|tel:)#i', $value ) ) {
					/* translators: %s: rótulo do campo. */
					$error = __( '"%s" precisa começar com / (página do site, ex.: /contato) ou com https://.', 'artemis' );
				}
				break;
			default:
				$value = sanitize_text_field( $raw );
		}
		if ( $error ) {
			artemis_site_notice( 'artemis_site', $key, sprintf( $error, $field[0] ) . ' ' . __( 'O valor anterior foi mantido.', 'artemis' ) );
			$value = (string) artemis_mod( $key );
		}
		$out[ $key ] = $value;
	}
	// Sem nenhum e-mail o formulário de contato não tem para onde enviar: volta o anterior (ou o padrão do tema).
	if ( '' === $out['art_contact_email'] && '' === $out['art_contato_to'] ) {
		$previous = (string) artemis_mod( 'art_contact_email' );
		$defaults = artemis_customizer_defaults();
		$out['art_contact_email'] = is_email( $previous ) ? $previous : $defaults['art_contact_email'];
		/* translators: %s: e-mail que ficou valendo. */
		artemis_site_notice( 'artemis_site', 'art_contact_email', sprintf( __( 'Preencha o e-mail de contato ou o que recebe as mensagens: sem nenhum dos dois o formulário não tem para onde enviar. O e-mail de contato ficou %s.', 'artemis' ), $out['art_contact_email'] ) );
	}
	$done = $out;
	return $out;
}

function artemis_site_page() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	$defaults = artemis_customizer_defaults();
	echo '<div class="wrap"><h1>' . esc_html__( 'Dados do site', 'artemis' ) . '</h1>';
	settings_errors(); // Página fora de Configurações: o WordPress não mostra os avisos sozinho.
	echo '<p>' . esc_html__( 'Informações que se repetem em várias páginas. Edite aqui uma vez e o site inteiro acompanha.', 'artemis' ) . '</p>';
	echo '<form method="post" action="options.php">';
	settings_fields( 'artemis_site' );
	foreach ( artemis_site_fields() as $group ) {
		echo '<h2 class="title">' . esc_html( $group[0] ) . '</h2>';
		if ( '' !== $group[1] ) {
			echo '<p class="description">' . esc_html( $group[1] ) . '</p>';
		}
		echo '<table class="form-table" role="presentation">';
		foreach ( $group[2] as $key => $field ) {
			$id    = 'artemis-site-' . $key;
			$value = (string) artemis_mod( $key );
			printf(
				'<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="%3$s" class="regular-text" id="%1$s" name="artemis_site[%4$s]" value="%5$s"%6$s><p class="description">%7$s</p>',
				esc_attr( $id ),
				esc_html( $field[0] ),
				esc_attr( in_array( $field[1], array( 'url', 'label', 'link' ), true ) ? 'text' : $field[1] ), // 'text' aceita "#" e links relativos
				esc_attr( $key ),
				esc_attr( $value ),
				'label' === $field[1] ? ' maxlength="20" required' : ( in_array( $field[1], array( 'url', 'email', 'link' ), true ) ? ' dir="ltr"' : '' ),
				esc_html( $field[2] )
			);
			if ( 'art_contact_whatsapp' === $key && '' !== $value && isset( $defaults[ $key ] ) && $value === $defaults[ $key ] ) {
				echo '<p class="description" style="color:#b32d2e">' . esc_html__( 'Este é o número de exemplo do layout. Troque pelo número real ou apague para esconder.', 'artemis' ) . '</p>';
			}
			echo '</td></tr>';
		}
		echo '</table>';
	}
	submit_button( __( 'Salvar alterações', 'artemis' ) );
	echo '</form></div>';
}
