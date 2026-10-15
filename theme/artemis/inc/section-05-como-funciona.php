<?php
/**
 * Seção 05 — Como funciona (Figma 8307:3380).
 * Conteúdo: Customizer art_cf_* — textos da coluna esquerda + 4 passos fixos (art_cf_step_{1..4}_*).
 * Número do passo (01–04) é gerado pela posição; passo com título vazio não aparece.
 */

defined( 'ABSPATH' ) || exit;

/** Quantidade de passos editáveis (o Figma mostra quatro). */
const ARTEMIS_COMO_FUNCIONA_STEPS = 4;

add_filter( 'artemis_customizer_defaults', function ( $d ) {
	return $d + array(
		'art_cf_eyebrow'       => 'Como funciona',
		'art_cf_title'         => 'Você vê a inteligência artificial {errar antes de confiar nela}',
		'art_cf_text'          => "O que trava a automação de decisão técnica costuma ser confiança, não tecnologia. Ninguém deixa um software codificar a válvula que vai para a Escócia sem poder provar, caso a caso, que ele acerta.\n\nPor isso todo sistema que entregamos vem com um laboratório de teste embutido, e ele fica com você.",
		'art_cf_steps_eyebrow' => 'Quatro passos:',
		'art_cf_step_1_title'  => 'Amostra com gabarito',
		'art_cf_step_1_text'   => 'Antes de construir qualquer coisa, montamos uma base de casos reais com a resposta que o seu especialista daria.',
		'art_cf_step_2_title'  => 'Medição de assertividade',
		'art_cf_step_2_text'   => 'O sistema roda contra o gabarito. Cada divergência é analisada: se a IA errou, ajustamos o critério; se o humano errou, o caso sai da base.',
		'art_cf_step_3_title'  => 'Laboratório dentro do seu sistema',
		'art_cf_step_3_text'   => 'Você sobe novos casos, vê o raciocínio de cada decisão, o grau de confiança e onde houve divergência. Compara versões. Decide quando promover para produção, e reverte se quiser.',
		'art_cf_step_4_title'  => 'POC, MVP, escala',
		'art_cf_step_4_text'   => 'Cada projeto tem fase declarada. POC prova que dá conta. MVP testa usabilidade. Escala é produção com monitoramento. Você sempre sabe onde está.',
	);
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$fields = array(
		'art_cf_eyebrow'       => array( __( 'Frase de abertura (itálico)', 'artemis' ), 'text' ),
		'art_cf_title'         => array( __( 'Título — coloque {entre chaves} o trecho em cinza', 'artemis' ), 'textarea' ),
		'art_cf_text'          => array( __( 'Texto — deixe uma linha em branco entre os parágrafos', 'artemis' ), 'textarea' ),
		'art_cf_steps_eyebrow' => array( __( 'Frase acima dos passos (ex.: "Quatro passos:")', 'artemis' ), 'text' ),
	);
	for ( $i = 1; $i <= ARTEMIS_COMO_FUNCIONA_STEPS; $i++ ) {
		/* translators: %d: número do passo */
		$fields[ "art_cf_step_{$i}_title" ] = array( sprintf( __( 'Passo %d — título (vazio = passo oculto)', 'artemis' ), $i ), 'text' );
		/* translators: %d: número do passo */
		$fields[ "art_cf_step_{$i}_text" ] = array( sprintf( __( 'Passo %d — descrição', 'artemis' ), $i ), 'textarea' );
	}
	$s['artemis_como_funciona'] = array( __( 'Home — 05 Como funciona', 'artemis' ), $fields );
	return $s;
} );

/** Parágrafos do texto da coluna esquerda (separados por linha em branco). */
function artemis_como_funciona_paragraphs( $text ) {
	$parts = preg_split( '/\R\s*\R/u', trim( (string) $text ) );
	return array_values( array_filter( array_map( 'trim', (array) $parts ), 'strlen' ) );
}

/** Passos preenchidos, em ordem: array( array( 'title' => ..., 'text' => ... ) ). */
function artemis_como_funciona_steps() {
	$steps = array();
	for ( $i = 1; $i <= ARTEMIS_COMO_FUNCIONA_STEPS; $i++ ) {
		$title = trim( (string) artemis_mod( "art_cf_step_{$i}_title" ) );
		if ( '' === $title ) {
			continue;
		}
		$steps[] = array(
			'title' => $title,
			'text'  => trim( (string) artemis_mod( "art_cf_step_{$i}_text" ) ),
		);
	}
	return $steps;
}
