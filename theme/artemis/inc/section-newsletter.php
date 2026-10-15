<?php
/**
 * Newsletter (componente) — símbolo 8436:2153 (Blog) · mobile 8673:4986.
 * Markup em template-parts/components/newsletter.php (incluído por home.php, a página de posts).
 * Conteúdo: Customizer art_newsletter_* (sem CPT, sem imagens de conteúdo — nada a semear).
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'artemis_customizer_defaults', function ( $d ) {
	return $d + array(
		'art_newsletter_status'      => 'SYSTEM: Artemis DATABASE PLATFORM|[ Status: online ]|[ Connection: stable ]',
		'art_newsletter_eyebrow'     => 'Newsletter',
		'art_newsletter_title'       => 'Transforme erros caros em atalhos: receba os bastidores da IA na prática.',
		'art_newsletter_text'        => 'Receba semanalmente estudos de caso reais, lições sobre custos e arquitetura, e os erros mais comuns de quem já passou pelo processo — direto no seu e-mail.',
		'art_newsletter_placeholder' => 'Seu melhor e-mail',
		'art_newsletter_button'      => 'Enviar',
		'art_newsletter_action'      => '',
		'art_newsletter_field'       => 'email',
	);
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$s['artemis_newsletter'] = array(
		__( 'Blog — Newsletter', 'artemis' ),
		array(
			'art_newsletter_status'      => array( __( 'Barra de status no topo — textos separados por | (o 1º leva o quadradinho)', 'artemis' ), 'text' ),
			'art_newsletter_eyebrow'     => array( __( 'Chamada em itálico (acima do título)', 'artemis' ), 'text' ),
			'art_newsletter_title'       => array( __( 'Título — coloque {entre chaves} um trecho para deixá-lo em cinza', 'artemis' ), 'textarea' ),
			'art_newsletter_text'        => array( __( 'Texto', 'artemis' ), 'textarea' ),
			'art_newsletter_placeholder' => array( __( 'Campo de e-mail — texto de exemplo (também lido por leitores de tela)', 'artemis' ), 'text' ),
			'art_newsletter_button'      => array( __( 'Botão — texto', 'artemis' ), 'text' ),
			'art_newsletter_action'      => array( __( 'Endereço de envio do formulário (copie do Mailchimp, RD Station etc.). Vazio = formulário desativado: avisa a quem tentar enviar', 'artemis' ), 'url' ),
			'art_newsletter_field'       => array( __( 'Nome do campo de e-mail pedido pelo serviço (ex.: email, EMAIL)', 'artemis' ), 'text' ),
		),
	);
	return $s;
} );

/** Itens da barra de status (8314:2133), sem vazios. */
function artemis_blog_newsletter_status_items() {
	$items = array_map( 'trim', explode( '|', (string) artemis_mod( 'art_newsletter_status' ) ) );
	return array_values( array_filter( $items, 'strlen' ) );
}

/** Grupo de sinal (8314:2137): ícone + 20 traços + ícone largo — SVGs do Figma. */
function artemis_blog_newsletter_signal() {
	$line = esc_url( artemis_asset( 'images/newsletter/signal-line.svg' ) );
	$html = '<span class="newsletter__signal" aria-hidden="true">';
	$html .= '<span class="newsletter__signal-icon"><img src="' . esc_url( artemis_asset( 'images/newsletter/signal-icon.svg' ) ) . '" width="37" height="13.5" alt=""></span>';
	for ( $i = 0; $i < 20; $i++ ) {
		$html .= '<img class="newsletter__signal-line" src="' . $line . '" width="1" height="13.5" alt="">';
	}
	$html .= '<span class="newsletter__signal-icon newsletter__signal-icon--wide"><img src="' . esc_url( artemis_asset( 'images/newsletter/signal-icon-wide.svg' ) ) . '" width="55" height="13.5" alt=""></span>';
	$html .= '</span>';
	return $html;
}

/** Nome do campo de e-mail: só caracteres seguros, mantém maiúsculas (ex.: EMAIL do Mailchimp). */
function artemis_blog_newsletter_field() {
	$field = preg_replace( '/[^A-Za-z0-9_\-\[\]]/', '', (string) artemis_mod( 'art_newsletter_field' ) );
	return $field ? $field : 'email';
}
