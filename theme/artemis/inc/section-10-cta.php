<?php
/**
 * Seção 10 — Call to action (Figma 8160:1628).
 * Conteúdo: Customizer art_cta_* (sem CPT). Link do botão = art_contact_cta_url (global).
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'artemis_customizer_defaults', function ( $d ) {
	return $d + array(
		'art_cta_status'       => 'SYSTEM: Artemis DATABASE PLATFORM|[ Status: online ]|[ Connection: stable ]',
		'art_cta_note'         => 'Resposta em até 1 dia útil. Atendimento remoto em todo o Brasil, presencial quando o diagnóstico exigir.',
		'art_cta_title'        => 'Comece pelo diagnóstico',
		'art_cta_text'         => 'Um dia dentro da sua operação, com o time que decide. Saímos de lá com o mapa dos processos que mais consomem hora sênior, o custo estimado de cada um e um protótipo funcionando no problema mais caro.  Se não fizer sentido seguir, você fica com o mapa.',
	);
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$s['artemis_cta'] = array(
		__( 'Home — 10 Chamada final (diagnóstico)', 'artemis' ),
		array(
			'art_cta_status'       => array( __( 'Barra de status no topo — textos separados por | (o 1º leva o quadradinho)', 'artemis' ), 'text' ),
			'art_cta_note'         => array( __( 'Frase em itálico (à direita)', 'artemis' ), 'textarea' ),
			'art_cta_title'        => array( __( 'Título', 'artemis' ), 'text' ),
			'art_cta_text'         => array( __( 'Texto', 'artemis' ), 'textarea' ),
		),
	);
	return $s;
} );

/** Itens da barra de status (8160:1629), sem vazios. */
function artemis_cta_status_items() {
	$items = array_map( 'trim', explode( '|', (string) artemis_mod( 'art_cta_status' ) ) );
	return array_values( array_filter( $items, 'strlen' ) );
}

/** Grupo de sinal (8160:1633): ícone + 20 traços + ícone largo — mesmos SVGs do Figma. */
function artemis_cta_signal() {
	$line = esc_url( artemis_asset( 'images/10-cta/signal-line.svg' ) );
	$html = '<span class="cta__signal" aria-hidden="true">';
	$html .= '<span class="cta__signal-icon"><img src="' . esc_url( artemis_asset( 'images/10-cta/signal-icon.svg' ) ) . '" width="37" height="13.5" alt=""></span>';
	for ( $i = 0; $i < 20; $i++ ) {
		$html .= '<img class="cta__signal-line" src="' . $line . '" width="1" height="13.5" alt="">';
	}
	$html .= '<span class="cta__signal-icon cta__signal-icon--wide"><img src="' . esc_url( artemis_asset( 'images/10-cta/signal-icon-wide.svg' ) ) . '" width="55" height="13.5" alt=""></span>';
	$html .= '</span>';
	return $html;
}
