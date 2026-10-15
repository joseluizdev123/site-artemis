<?php
/**
 * Seção 09 — Como trabalhamos (Figma 8490:2191).
 * Conteúdo: Customizer art_ct_eyebrow, art_ct_title e art_ct_rule_{1..5}_title / _text.
 *
 * Escolha: 5 campos fixos no Customizer (e não um CPT art_regra) — a grade do Figma é
 * 1 + 2×2 com a ilustração presa à regra 1; um CPT permitiria quantidades que quebram
 * a grade. Sem seed: os textos do Figma são os defaults centrais (artemis_mod).
 */

defined( 'ABSPATH' ) || exit;

/** Regras do Figma (8490:2221, 8490:2217, 8490:2218, 8490:2219, 8490:2220): [título, descrição]. */
function artemis_como_trabalhamos_defaults() {
	return array(
		1 => array( 'Medimos antes de construir', 'Amostra com gabarito humano vem antes da primeira linha de código.' ),
		2 => array( 'Não tocamos no seu sistema', 'Sem acesso ao seu banco, sem migração, sem projeto paralelo no seu TI.' ),
		3 => array( 'A prova é entregue junto com o produto', 'O laboratório de teste é módulo do seu sistema, operado por você.' ),
		4 => array( 'A ferramenta se adapta ao seu processo', 'Você não vai mudar como a operação funciona para caber no nosso' ),
		5 => array( 'A fase é declarada. POC, MVP ou escala', 'Você sempre sabe o que está comprando.' ),
	);
}

add_filter( 'artemis_customizer_defaults', function ( $d ) {
	$d += array(
		'art_ct_eyebrow' => 'Como trabalhamos',
		'art_ct_title'   => 'Cinco regras que valem {para todo projeto}',
	);
	foreach ( artemis_como_trabalhamos_defaults() as $i => $rule ) {
		$d += array(
			"art_ct_rule_{$i}_title" => $rule[0],
			"art_ct_rule_{$i}_text"  => $rule[1],
		);
	}
	return $d;
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$fields = array(
		'art_ct_eyebrow' => array( __( 'Frase de abertura (itálico)', 'artemis' ), 'text' ),
		'art_ct_title'   => array( __( 'Título — coloque {entre chaves} o trecho em cinza', 'artemis' ), 'textarea' ),
	);
	foreach ( array_keys( artemis_como_trabalhamos_defaults() ) as $i ) {
		$where = 1 === $i ? __( ' (bloco com a ilustração)', 'artemis' ) : '';
		/* translators: 1: posição da regra (1 a 5), 2: observação sobre o bloco. */
		$fields[ "art_ct_rule_{$i}_title" ] = array( sprintf( __( 'Regra %1$d — título%2$s', 'artemis' ), $i, $where ), 'text' );
		/* translators: %d: posição da regra (1 a 5). */
		$fields[ "art_ct_rule_{$i}_text" ] = array( sprintf( __( 'Regra %d — descrição', 'artemis' ), $i ), 'textarea' );
	}
	$s['artemis_como_trabalhamos'] = array( __( 'Home — 09 Como trabalhamos', 'artemis' ), $fields );
	return $s;
} );

/** As 5 regras com o conteúdo do Customizer: array( posição => array( título, descrição ) ). */
function artemis_como_trabalhamos_rules() {
	$rules = array();
	foreach ( array_keys( artemis_como_trabalhamos_defaults() ) as $i ) {
		$rules[ $i ] = array(
			(string) artemis_mod( "art_ct_rule_{$i}_title" ),
			(string) artemis_mod( "art_ct_rule_{$i}_text" ),
		);
	}
	return $rules;
}

/** URL de uma imagem da seção (assets/images/09-como-trabalhamos/). */
function artemis_como_trabalhamos_img( $file ) {
	return artemis_asset( 'images/09-como-trabalhamos/' . $file );
}

/**
 * Faixa de diagonais (8490:2977): 83 traços; nestas posições (base 0) o traço
 * tem 22 de largura em vez de 24 (8490:2983, 2991, 2992, 3038, 3040, 3045, 3047).
 */
function artemis_como_trabalhamos_narrow_strokes() {
	return array( 5, 13, 14, 60, 62, 67, 69 );
}
