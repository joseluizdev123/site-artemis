<?php
/**
 * Seção 08 — Stats / Números (Figma 8486:1798).
 * Conteúdo: Customizer art_stat_{1..4}_prefixo / _valor / _sufixo / _legenda.
 * Sem seed: os textos do Figma são os defaults centrais (artemis_mod).
 */

defined( 'ABSPATH' ) || exit;

/** Números do Figma (8486:1689, 8486:1556, 8486:1559, 8486:1562): [prefixo, valor, sufixo, legenda]. */
function artemis_stats_defaults() {
	return array(
		1 => array( 'R$', '40.000', '/MÊS', 'economizados em um único tipo de documento' ),
		2 => array( '', '97%', '', 'de assertividade medida contra gabarito humano' ),
		3 => array( '', '25 min', '', 'para quantificar uma planta que levava 3 horas' ),
		4 => array( '', '4', '', 'formatos de problema já resolvidos em produção' ),
	);
}

add_filter( 'artemis_customizer_defaults', function ( $d ) {
	foreach ( artemis_stats_defaults() as $i => $stat ) {
		$d += array(
			"art_stat_{$i}_prefixo" => $stat[0],
			"art_stat_{$i}_valor"   => $stat[1],
			"art_stat_{$i}_sufixo"  => $stat[2],
			"art_stat_{$i}_legenda" => $stat[3],
		);
	}
	return $d;
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$fields = array();
	foreach ( array_keys( artemis_stats_defaults() ) as $i ) {
		/* translators: %d: posição do número (1 a 4). */
		$fields[ "art_stat_{$i}_prefixo" ] = array( sprintf( __( 'Número %d — antes do valor, em letra menor (ex.: R$). Pode ficar vazio.', 'artemis' ), $i ), 'text' );
		/* translators: %d: posição do número (1 a 4). */
		$fields[ "art_stat_{$i}_valor" ] = array( sprintf( __( 'Número %d — valor em destaque (ex.: 40.000, 97%%, 25 min)', 'artemis' ), $i ), 'text' );
		/* translators: %d: posição do número (1 a 4). */
		$fields[ "art_stat_{$i}_sufixo" ] = array( sprintf( __( 'Número %d — depois do valor, em letra menor (ex.: /MÊS). Pode ficar vazio.', 'artemis' ), $i ), 'text' );
		/* translators: %d: posição do número (1 a 4). */
		$fields[ "art_stat_{$i}_legenda" ] = array( sprintf( __( 'Número %d — legenda abaixo do valor', 'artemis' ), $i ), 'textarea' );
	}
	$s['artemis_stats'] = array( __( 'Home — 08 Números', 'artemis' ), $fields );
	return $s;
} );

/**
 * Valor de um número (8486:1690): prefixo e sufixo em <span class="stats__unit"> (24px)
 * ao redor do valor (40px), separados por espaço como no Figma. Retorna HTML escapado.
 */
function artemis_stats_value( $i ) {
	$prefixo = trim( (string) artemis_mod( "art_stat_{$i}_prefixo" ) );
	$valor   = trim( (string) artemis_mod( "art_stat_{$i}_valor" ) );
	$sufixo  = trim( (string) artemis_mod( "art_stat_{$i}_sufixo" ) );

	$html = esc_html( $valor );
	if ( '' !== $prefixo ) {
		$html = '<span class="stats__unit">' . esc_html( $prefixo ) . '</span> ' . $html;
	}
	if ( '' !== $sufixo ) {
		$html .= ' <span class="stats__unit">' . esc_html( $sufixo ) . '</span>';
	}
	return $html;
}
