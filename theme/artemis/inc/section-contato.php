<?php
/**
 * Página Contato — Figma 8348:3835 · mobile 8693:2761.
 * Template: page-contato.php (página de slug "contato") → template-parts/contato/NN-slug.php. Espelha contato/index.html.
 *
 * Contatos     art_contact_email / art_contact_whatsapp / art_contact_address (os mesmos do rodapé)
 * Customizer   art_contato_title ({entre chaves} = cinza) · _lead · _button · _note · _ok (confirmação)
 *              art_contato_action (envio externo, opcional)
 * Dados do site art_contato_to (quem recebe; vazio = art_contact_email) — inc/options.php
 * Envio        sem art_contato_action: admin-post.php?action=artemis_contato → wp_mail() para art_contato_to,
 *              com nonce e campo-isca contra spam; volta para a página com ?contato=ok|erro|dados
 */

defined( 'ABSPATH' ) || exit;

/* --------------------------------------------------------------------------
   Customizer
   -------------------------------------------------------------------------- */
add_filter( 'artemis_customizer_defaults', function ( $d ) {
	return $d + array(
		'art_contato_title'  => 'Vamos começar {pelo diagnóstico}',
		'art_contato_lead'   => 'Se você chegou até aqui, provavelmente já tem um processo em mente. Nos conte qual é, e quanto ele custa hoje, se você souber.',
		'art_contato_button' => 'Enviar e agendar conversa',
		'art_contato_note'   => 'Respondemos em até 1 dia útil. Atendimento remoto em todo o Brasil; presencial quando o diagnóstico exigir.',
		'art_contato_ok'     => 'Recebemos sua mensagem. Respondemos em até 1 dia útil.',
		'art_contato_to'     => '',
		'art_contato_action' => '',
	);
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$s['artemis_contato'] = array(
		__( 'Contato — Formulário', 'artemis' ),
		array(
			'art_contato_title'  => array( __( 'Título — coloque {entre chaves} o trecho em cinza', 'artemis' ), 'text' ),
			'art_contato_lead'   => array( __( 'Texto abaixo do título', 'artemis' ), 'textarea' ),
			'art_contato_button' => array( __( 'Botão de envio', 'artemis' ), 'text' ),
			'art_contato_note'   => array( __( 'Nota ao lado do botão', 'artemis' ), 'textarea' ),
			'art_contato_ok'     => array( __( 'Mensagem depois do envio', 'artemis' ), 'textarea' ),
			'art_contato_action' => array( __( 'Envio externo (RD Station, HubSpot etc.) — vazio = o site envia por e-mail', 'artemis' ), 'url' ),
		),
	);
	return $s;
} );

/* --------------------------------------------------------------------------
   Envio por e-mail (admin-post)
   -------------------------------------------------------------------------- */

/** Campos do formulário: nome do campo => rótulo no e-mail. */
function artemis_contato_fields() {
	return array(
		'nome'     => __( 'Nome', 'artemis' ),
		'email'    => __( 'E-mail corporativo', 'artemis' ),
		'empresa'  => __( 'Empresa', 'artemis' ),
		'cargo'    => __( 'Cargo', 'artemis' ),
		'processo' => __( 'Processo que mais consome hora especializada', 'artemis' ),
	);
}

/** Para onde o formulário envia (externo ou admin-post do tema). */
function artemis_contato_action() {
	$external = trim( (string) artemis_mod( 'art_contato_action' ) );
	return '' !== $external ? $external : admin_url( 'admin-post.php' );
}

function artemis_contato_handle() {
	$back = wp_get_referer();
	$back = $back ? $back : home_url( '/contato/' );
	$back = remove_query_arg( 'contato', $back );
	$go   = function ( $status ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'contato', $status, $back ) . '#contato-form' );
		exit;
	};

	if ( ! isset( $_POST['artemis_contato_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['artemis_contato_nonce'] ), 'artemis_contato' ) ) {
		$go( 'erro' );
	}
	// Campo-isca: invisível para pessoas; robôs costumam preencher.
	if ( ! empty( $_POST['site'] ) ) {
		$go( 'ok' );
	}

	$data = array();
	foreach ( artemis_contato_fields() as $key => $label ) {
		$raw          = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$data[ $key ] = 'processo' === $key ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
	}
	if ( '' === $data['nome'] || ! is_email( $data['email'] ) ) {
		$go( 'dados' );
	}

	$to = trim( (string) artemis_mod( 'art_contato_to' ) );
	$to = is_email( $to ) ? $to : (string) artemis_mod( 'art_contact_email' );

	$body = '';
	foreach ( artemis_contato_fields() as $key => $label ) {
		$body .= $label . ":\n" . ( '' !== $data[ $key ] ? $data[ $key ] : '—' ) . "\n\n";
	}
	/* translators: 1: nome, 2: empresa */
	$subject = sprintf( __( 'Contato pelo site: %1$s%2$s', 'artemis' ), $data['nome'], '' !== $data['empresa'] ? ' (' . $data['empresa'] . ')' : '' );
	$headers = array( 'Reply-To: ' . $data['nome'] . ' <' . $data['email'] . '>' );

	$go( wp_mail( $to, $subject, $body, $headers ) ? 'ok' : 'erro' );
}
add_action( 'admin_post_nopriv_artemis_contato', 'artemis_contato_handle' );
add_action( 'admin_post_artemis_contato', 'artemis_contato_handle' );

/** Mensagem depois do envio (?contato=ok|erro|dados): [texto, classe extra]. */
function artemis_contato_status() {
	$status = isset( $_GET['contato'] ) ? sanitize_key( $_GET['contato'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( 'ok' === $status ) {
		return array( (string) artemis_mod( 'art_contato_ok' ), ' contato-form__msg--ok' );
	}
	if ( 'dados' === $status ) {
		return array( __( 'Confira o nome e o e-mail e envie de novo.', 'artemis' ), '' );
	}
	if ( 'erro' === $status ) {
		return array( __( 'Não foi possível enviar agora. Escreva para o e-mail ao lado.', 'artemis' ), '' );
	}
	return array( '', '' );
}

/* --------------------------------------------------------------------------
   Seed — página Contato
   -------------------------------------------------------------------------- */
add_filter( 'artemis_seed', function ( $log ) {
	if ( ! get_page_by_path( 'contato' ) ) {
		$id    = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Contato', 'post_name' => 'contato' ) );
		$log[] = $id && ! is_wp_error( $id ) ? 'Página criada: Contato (/contato)' : 'Falha ao criar a página Contato';
	}
	return $log;
} );
