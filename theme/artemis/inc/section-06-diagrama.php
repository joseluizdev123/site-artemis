<?php
/**
 * Seção 06 — Diagram (Figma 8653:2286).
 * Conteúdo: Customizer art_diag_* — rótulos do diagrama POC → MVP → Escala + Description Block (8653:2355).
 * O desenho (posições, linhas verticais, ícones e cores) fica no template e no CSS.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'artemis_customizer_defaults', function ( $d ) {
	return $d + array(
		'art_diag_phase_1'     => 'POC',
		'art_diag_phase_2'     => 'MVP',
		'art_diag_phase_3'     => 'Escala',
		'art_diag_step_1'      => 'Amostra com gabarito',
		'art_diag_step_2'      => 'Medição de assertividade',
		'art_diag_adjust'      => 'Ajuste de critério',
		'art_diag_error_label' => 'Humano errou',
		'art_diag_error_title' => 'Caso sai da base',
		'art_diag_lab_label'   => 'Você testa',
		'art_diag_step_3'      => 'Laboratório no seu sistema',
		'art_diag_versions'    => 'V 1.0, V 1.1, V 1.2',
		'art_diag_legend_1'    => 'Gabarito pronto',
		'art_diag_legend_2'    => 'Promoção para produção',
		'art_diag_title'       => '{Não tocamos} no seu sistema',
		'art_diag_text'        => 'Não pedimos acesso ao seu banco de dados. Você manda os dados, processamos, devolvemos a resposta. O seu TI não precisa parar um projeto para nos atender.',
	);
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$s['artemis_diagrama'] = array(
		__( 'Home — 06 Diagrama (POC → MVP → Escala)', 'artemis' ),
		array(
			'art_diag_phase_1'     => array( __( 'Fase 1 — nome (coluna da esquerda)', 'artemis' ), 'text' ),
			'art_diag_phase_2'     => array( __( 'Fase 2 — nome (coluna do meio)', 'artemis' ), 'text' ),
			'art_diag_phase_3'     => array( __( 'Fase 3 — nome (coluna da direita)', 'artemis' ), 'text' ),
			'art_diag_step_1'      => array( __( 'Cartão branco 01', 'artemis' ), 'text' ),
			'art_diag_step_2'      => array( __( 'Cartão escuro 02', 'artemis' ), 'text' ),
			'art_diag_adjust'      => array( __( 'Cartão com ícone de repetir', 'artemis' ), 'text' ),
			'art_diag_error_label' => array( __( 'Cartão tracejado — frase pequena em vermelho', 'artemis' ), 'text' ),
			'art_diag_error_title' => array( __( 'Cartão tracejado — frase principal', 'artemis' ), 'text' ),
			'art_diag_lab_label'   => array( __( 'Cartão azul 03 — frase pequena acima (só no celular; vazio esconde)', 'artemis' ), 'text' ),
			'art_diag_step_3'      => array( __( 'Cartão azul 03', 'artemis' ), 'text' ),
			'art_diag_versions'    => array( __( 'Versões — separe por vírgula (ex.: V 1.0, V 1.1, V 1.2)', 'artemis' ), 'text' ),
			'art_diag_legend_1'    => array( __( 'Legenda — losango rosa', 'artemis' ), 'text' ),
			'art_diag_legend_2'    => array( __( 'Legenda — losango verde', 'artemis' ), 'text' ),
			'art_diag_title'       => array( __( 'Título abaixo do diagrama — coloque {entre chaves} o trecho em cinza', 'artemis' ), 'text' ),
			'art_diag_text'        => array( __( 'Texto ao lado do título', 'artemis' ), 'textarea' ),
		),
	);
	return $s;
} );

/** Versões do rodapé do diagrama (8653:2336), a partir da lista separada por vírgula. */
function artemis_diagrama_versions() {
	$parts = explode( ',', (string) artemis_mod( 'art_diag_versions' ) );
	return array_values( array_filter( array_map( 'trim', $parts ), 'strlen' ) );
}

/** Rótulo acessível do diagrama: "Diagrama POC → MVP → Escala: etapa, etapa, …". */
function artemis_diagrama_label() {
	$steps = array_filter( array_map( 'trim', array(
		(string) artemis_mod( 'art_diag_step_1' ),
		(string) artemis_mod( 'art_diag_step_2' ),
		(string) artemis_mod( 'art_diag_adjust' ),
		(string) artemis_mod( 'art_diag_step_3' ),
	) ), 'strlen' );
	return sprintf(
		/* translators: 1, 2, 3: nomes das fases; 4: etapas do diagrama separadas por vírgula. */
		__( 'Diagrama %1$s → %2$s → %3$s: %4$s', 'artemis' ),
		artemis_mod( 'art_diag_phase_1' ),
		artemis_mod( 'art_diag_phase_2' ),
		artemis_mod( 'art_diag_phase_3' ),
		implode( ', ', $steps )
	);
}
