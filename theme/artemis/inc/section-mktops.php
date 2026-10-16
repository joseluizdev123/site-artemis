<?php
/**
 * Página MktOps — Figma 8348:4181 · mobile 8695:3268.
 * Template: page-mktops.php (página de slug "mktops") → template-parts/mktops/NN-slug.php. Espelha mktops/index.html.
 *
 * Customizer   art_mk_title ({cinza}) · _lead · _btn1/_btn1_url · _btn2/_btn2_url
 *              art_mk_problema_title · _problema_text (linha em branco separa parágrafos)
 *              art_mk_inst_title ({cinza}) · art_mk_item{1..5} ({cinza}) · art_mk_comunidade
 *              art_mk_niveis_title · art_mk_plano{1..3}_name/_tag/_rows ("Rótulo | Valor", uma linha por item)
 *              art_mk_cta_note · _cta_title · _cta_bold (frase em negrito) · _cta_text · _cta_btn/_cta_btn_url
 * Imagens      do tema (assets/images/mktops): diagrama do topo, foto do problema, arquivos, ilustração da comunidade,
 *              desenhos dos planos e os traços verdes da chamada final
 */

defined( 'ABSPATH' ) || exit;

/** Itens de "O que é instalado": [rótulo do arquivo (só no desenho), texto padrão]. */
function artemis_mk_items_defaults() {
	return array(
		1 => array( 'tools', '15 ferramentas proprietárias remixáveis: {SDR no WhatsApp, follow-up automático, anti-churn, gestor de tráfego}' ),
		2 => array( 'brand', 'Sistema de produção com IA configurado no {contexto da sua marca}' ),
		3 => array( 'dna', 'DNA e critérios da marca documentados, {para que a IA produza no seu tom}' ),
		4 => array( 'template', '3 a 4 templates de peça {recorrente}' ),
		5 => array( 'guide', 'Operador treinado, com guia de operação {diária de uma a duas páginas}' ),
	);
}

/** Planos: [chave, nome, selo, linhas]. */
function artemis_mk_planos_defaults() {
	return array(
		1 => array( 'solo', 'Solo', '', "Para | Operador individual\nInvestimento | R$ 4.997/ano\nFerramentas implantadas | 3, via workshop\nAcesso ao time técnico | Comunidade\nCalls estratégicas | -" ),
		2 => array( 'squad', 'Squad', 'Popular', "Para | Times de 3 a 10\nInvestimento | R$ 11.997/ano\nFerramentas implantadas | 8, com implantação guiada\nAcesso ao time técnico | Canal do time\nCalls estratégicas | 1 por mês" ),
		3 => array( 'enterprise', 'Enterprise', '', "Para | Empresas de 30 a 150\nInvestimento | A partir de R$ 49.997/ano\nFerramentas implantadas | As 15, personalizadas\nAcesso ao time técnico | Canal exclusivo de engenharia\nCalls estratégicas | 2 por mês" ),
	);
}

add_filter( 'artemis_customizer_defaults', function ( $d ) {
	$add = array(
		'art_mk_title'          => '{Não terceirizamos seu marketing.} Instalamos a operação dentro da sua empresa.',
		'art_mk_lead'           => 'E a IA responde citando alguém. Trabalhamos para que seja você, no orgânico do Google e dentro do ChatGPT, do Perplexity e dos AI Overviews.',
		'art_mk_btn1'           => 'Agendar conversa',
		'art_mk_btn1_url'       => '/contato/',
		'art_mk_btn2'           => 'Rodar o SpyOps',
		'art_mk_btn2_url'       => '/contato/',
		'art_mk_problema_title' => 'O problema',
		'art_mk_problema_text'  => "Quando o contrato com a agência acaba, o que fica na sua empresa? Um Drive com arquivos e nenhuma capacidade nova instalada.\n\nAqui é o contrário: no fim, a operação continua rodando porque as pessoas da sua casa sabem operá-la.",
		'art_mk_inst_title'     => 'O que é {instalado}',
		'art_mk_comunidade'     => 'Comunidade, encontro semanal e mentoria técnica com o time de engenharia',
		'art_mk_niveis_title'   => 'Os três níveis',
		'art_mk_cta_note'       => 'Custo operacional adicional de cerca de R$ 100 por mês em Claude Code. Nenhum outro custo obrigatório.',
		'art_mk_cta_title'      => 'Garantias',
		'art_mk_cta_bold'       => 'Arrependimento em 7 dias. Devolução integral, sem perguntas.',
		'art_mk_cta_text'       => 'Conclusão. Se ao fim da terceira sessão o seu time ainda não estiver produzindo sozinho, o acompanhamento continua sem custo adicional até estar.',
		'art_mk_cta_btn'        => 'Agendar conversa',
		'art_mk_cta_btn_url'    => '/contato/',
	);
	foreach ( artemis_mk_items_defaults() as $n => $i ) {
		$add[ 'art_mk_item' . $n ] = $i[1];
	}
	foreach ( artemis_mk_planos_defaults() as $n => $p ) {
		$add[ 'art_mk_plano' . $n . '_name' ] = $p[1];
		$add[ 'art_mk_plano' . $n . '_tag' ]  = $p[2];
		$add[ 'art_mk_plano' . $n . '_rows' ] = $p[3];
	}
	return $d + $add;
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$hl = __( '— {entre chaves} = trecho em cinza', 'artemis' );
	$s['artemis_mk_topo'] = array(
		__( 'MktOps — Topo e problema', 'artemis' ),
		array(
			'art_mk_title'          => array( __( 'Título ', 'artemis' ) . $hl, 'textarea' ),
			'art_mk_lead'           => array( __( 'Texto do topo', 'artemis' ), 'textarea' ),
			'art_mk_btn1'           => array( __( 'Botão principal', 'artemis' ), 'text' ),
			'art_mk_btn1_url'       => array( __( 'Botão principal — link', 'artemis' ), 'text' ),
			'art_mk_btn2'           => array( __( 'Botão secundário (vazio esconde)', 'artemis' ), 'text' ),
			'art_mk_btn2_url'       => array( __( 'Botão secundário — link', 'artemis' ), 'text' ),
			'art_mk_problema_title' => array( __( '"O problema" — título', 'artemis' ), 'text' ),
			'art_mk_problema_text'  => array( __( '"O problema" — texto (linha em branco separa parágrafos)', 'artemis' ), 'textarea' ),
		),
	);
	$f = array( 'art_mk_inst_title' => array( __( 'Título ', 'artemis' ) . $hl, 'text' ) );
	foreach ( artemis_mk_items_defaults() as $n => $i ) {
		/* translators: 1: número do item, 2: rótulo do arquivo */
		$f[ 'art_mk_item' . $n ] = array( sprintf( __( 'Item %1$d (%2$s) ', 'artemis' ), $n, ucfirst( $i[0] ) ) . $hl, 'textarea' );
	}
	$f['art_mk_comunidade'] = array( __( 'Faixa da comunidade', 'artemis' ), 'textarea' );
	$s['artemis_mk_inst']   = array( __( 'MktOps — O que é instalado', 'artemis' ), $f );

	$f = array( 'art_mk_niveis_title' => array( __( 'Título', 'artemis' ), 'text' ) );
	foreach ( array( 1, 2, 3 ) as $n ) {
		/* translators: %d: número do plano */
		$f[ 'art_mk_plano' . $n . '_name' ] = array( sprintf( __( 'Plano %d — nome', 'artemis' ), $n ), 'text' );
		/* translators: %d: número do plano */
		$f[ 'art_mk_plano' . $n . '_tag' ] = array( sprintf( __( 'Plano %d — selo (vazio esconde)', 'artemis' ), $n ), 'text' );
		/* translators: %d: número do plano */
		$f[ 'art_mk_plano' . $n . '_rows' ] = array( sprintf( __( 'Plano %d — linhas ("Rótulo | Valor", uma por linha)', 'artemis' ), $n ), 'textarea' );
	}
	$s['artemis_mk_niveis'] = array( __( 'MktOps — Os três níveis', 'artemis' ), $f );

	$s['artemis_mk_cta'] = array(
		__( 'MktOps — Garantias', 'artemis' ),
		array(
			'art_mk_cta_note'    => array( __( 'Nota à direita', 'artemis' ), 'textarea' ),
			'art_mk_cta_title'   => array( __( 'Título', 'artemis' ), 'text' ),
			'art_mk_cta_bold'    => array( __( 'Frase em destaque (negrito)', 'artemis' ), 'textarea' ),
			'art_mk_cta_text'    => array( __( 'Texto', 'artemis' ), 'textarea' ),
			'art_mk_cta_btn'     => array( __( 'Botão', 'artemis' ), 'text' ),
			'art_mk_cta_btn_url' => array( __( 'Botão — link', 'artemis' ), 'text' ),
		),
	);
	return $s;
} );

/** Itens de "O que é instalado": [n, chave do arquivo, texto]. */
function artemis_mk_items() {
	$out = array();
	foreach ( artemis_mk_items_defaults() as $n => $i ) {
		$out[] = array( 'n' => $n, 'file' => $i[0], 'text' => (string) artemis_mod( 'art_mk_item' . $n ) );
	}
	return $out;
}

/** Planos: [chave, nome, selo, linhas [[rótulo, valor], …]]. */
function artemis_mk_planos() {
	$out = array();
	foreach ( artemis_mk_planos_defaults() as $n => $p ) {
		$rows = array();
		foreach ( preg_split( '/\r\n|\r|\n/', (string) artemis_mod( 'art_mk_plano' . $n . '_rows' ) ) as $line ) {
			if ( '' === trim( $line ) ) {
				continue;
			}
			$parts  = array_map( 'trim', explode( '|', $line, 2 ) );
			$rows[] = array( $parts[0], isset( $parts[1] ) ? $parts[1] : '' );
		}
		$out[] = array(
			'n'    => $n,
			'key'  => $p[0],
			'name' => (string) artemis_mod( 'art_mk_plano' . $n . '_name' ),
			'tag'  => (string) artemis_mod( 'art_mk_plano' . $n . '_tag' ),
			'rows' => $rows,
		);
	}
	return $out;
}

/* Seed — página MktOps */
add_filter( 'artemis_seed', function ( $log ) {
	if ( ! get_page_by_path( 'mktops' ) ) {
		$id    = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'MktOps', 'post_name' => 'mktops' ) );
		$log[] = $id && ! is_wp_error( $id ) ? 'Página criada: MktOps (/mktops)' : 'Falha ao criar a página MktOps';
	}
	return $log;
} );
