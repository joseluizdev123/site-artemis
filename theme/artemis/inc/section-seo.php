<?php
/**
 * Página Artemis SEO — Figma 8348:4008 · mobile 8704:6450.
 * Template: page-artemis-seo.php (página de slug "artemis-seo") → template-parts/seo/NN-slug.php. Espelha artemis-seo/index.html.
 *
 * Customizer   art_seo_title ({cinza}) · _lead · _btn1/_btn1_url · _btn2/_btn2_url (os dois botões do topo e do fim)
 *              art_seo_problema_title · _problema_text (linha em branco separa parágrafos)
 *              art_seo_formas_title ({cinza}) · art_seo_opt{1,2}_name/_tag/_desc/_para → as duas formas de contratar
 *              art_seo_cta_title · art_seo_cta_text
 * Imagens      do tema (assets/images/seo): navegador com a resposta de IA, pintura do topo e as imagens das opções
 *              (o retângulo branco é o espaço da tela do produto, como no Figma)
 */

defined( 'ABSPATH' ) || exit;

function artemis_seo_opts_defaults() {
	return array(
		1 => array( 'Plataforma', 'A partir de R$ 997/mês', 'Seu time usa o sistema: pesquisa de palavra-chave, briefing e geração de conteúdo otimizado, publicação após validação humana ou automática, GEO Score de menções em IA e acompanhamento de ranqueamento.', 'Já tem alguém cuidando de conteúdo e falta ferramenta.' ),
		2 => array( 'SEO’Ops', 'a partir de R$ 3.500/mês', 'Nós dirigimos a operação: planejamento, arquitetura de conteúdo e calendário, produção em escala com revisão humana antes de publicar, otimização para citação em IA, relatório de ranqueamento e de menções, e iscas digitais nos posts para transformar tráfego em lead.', 'Quer o resultado sem montar time.' ),
	);
}

add_filter( 'artemis_customizer_defaults', function ( $d ) {
	$add = array(
		'art_seo_title'          => 'Seu cliente parou de pesquisar no Google. {Ele pergunta para uma IA.}',
		'art_seo_lead'           => 'E a IA responde citando alguém. Trabalhamos para que seja você, no orgânico do Google e dentro do ChatGPT, do Perplexity e dos AI Overviews.',
		'art_seo_btn1'           => 'Agendar conversa',
		'art_seo_btn1_url'       => '/contato/',
		'art_seo_btn2'           => 'Medir minha visibilidade em IA',
		'art_seo_btn2_url'       => '/contato/',
		'art_seo_problema_title' => 'O problema',
		'art_seo_problema_text'  => "Quando o comprador B2B pergunta a uma IA quem é a melhor empresa de um segmento, ele não recebe dez links azuis para comparar. Recebe três nomes, com a recomendação já formada.\n\nQuem está na primeira página do Google e fora dessas três respostas perdeu a disputa antes de ela começar. E a lógica que coloca uma empresa ali não é a mesma que a coloca no topo da busca clássica.",
		'art_seo_formas_title'   => 'Duas formas {de contratar}',
		'art_seo_cta_title'      => 'Por que conosco',
		'art_seo_cta_text'       => 'A Artemis usa a mesma plataforma para gerar a própria demanda orgânica. O que não funciona para nós não chega até você. O produto está validado há mais de um ano, com carteira própria.',
	);
	foreach ( artemis_seo_opts_defaults() as $n => $o ) {
		$add[ 'art_seo_opt' . $n . '_name' ] = $o[0];
		$add[ 'art_seo_opt' . $n . '_tag' ]  = $o[1];
		$add[ 'art_seo_opt' . $n . '_desc' ] = $o[2];
		$add[ 'art_seo_opt' . $n . '_para' ] = $o[3];
	}
	return $d + $add;
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$hl = __( '— {entre chaves} = trecho em cinza', 'artemis' );
	$s['artemis_seo_topo'] = array(
		__( 'Artemis SEO — Topo, problema e fim', 'artemis' ),
		array(
			'art_seo_title'          => array( __( 'Título ', 'artemis' ) . $hl, 'textarea' ),
			'art_seo_lead'           => array( __( 'Texto do topo', 'artemis' ), 'textarea' ),
			'art_seo_btn1'           => array( __( 'Botão principal (topo e fim)', 'artemis' ), 'text' ),
			'art_seo_btn1_url'       => array( __( 'Botão principal — link', 'artemis' ), 'text' ),
			'art_seo_btn2'           => array( __( 'Botão secundário (vazio esconde)', 'artemis' ), 'text' ),
			'art_seo_btn2_url'       => array( __( 'Botão secundário — link', 'artemis' ), 'text' ),
			'art_seo_problema_title' => array( __( '"O problema" — título', 'artemis' ), 'text' ),
			'art_seo_problema_text'  => array( __( '"O problema" — texto (linha em branco separa parágrafos)', 'artemis' ), 'textarea' ),
			'art_seo_cta_title'      => array( __( '"Por que conosco" — título', 'artemis' ), 'text' ),
			'art_seo_cta_text'       => array( __( '"Por que conosco" — texto', 'artemis' ), 'textarea' ),
		),
	);
	$f = array( 'art_seo_formas_title' => array( __( 'Título ', 'artemis' ) . $hl, 'text' ) );
	foreach ( array( 1, 2 ) as $n ) {
		/* translators: %d: número da opção */
		$f[ 'art_seo_opt' . $n . '_name' ] = array( sprintf( __( 'Opção %d — nome', 'artemis' ), $n ), 'text' );
		/* translators: %d: número da opção */
		$f[ 'art_seo_opt' . $n . '_tag' ] = array( sprintf( __( 'Opção %d — selo de preço', 'artemis' ), $n ), 'text' );
		/* translators: %d: número da opção */
		$f[ 'art_seo_opt' . $n . '_desc' ] = array( sprintf( __( 'Opção %d — texto', 'artemis' ), $n ), 'textarea' );
		/* translators: %d: número da opção */
		$f[ 'art_seo_opt' . $n . '_para' ] = array( sprintf( __( 'Opção %d — "Para quem"', 'artemis' ), $n ), 'text' );
	}
	$s['artemis_seo_formas'] = array( __( 'Artemis SEO — Duas formas de contratar', 'artemis' ), $f );
	return $s;
} );

/** As duas opções: [n, chave, nome, selo, texto, para quem, cor, ícone, imagem, imagem do celular]. */
function artemis_seo_opts() {
	$meta = array(
		1 => array( 'plataforma', 'azul', 'icon-para-quem.svg', 'formas-plataforma.jpg', 'm-formas-plataforma.jpg' ),
		2 => array( 'seoops', 'verde', 'icon-para-quem-verde.svg', 'formas-seoops.jpg', 'm-formas-seoops.jpg' ),
	);
	$out = array();
	foreach ( $meta as $n => $m ) {
		$out[] = array(
			'n'    => $n,
			'key'  => $m[0],
			'name' => (string) artemis_mod( 'art_seo_opt' . $n . '_name' ),
			'tag'  => (string) artemis_mod( 'art_seo_opt' . $n . '_tag' ),
			'desc' => (string) artemis_mod( 'art_seo_opt' . $n . '_desc' ),
			'para' => (string) artemis_mod( 'art_seo_opt' . $n . '_para' ),
			'cor'  => $m[1],
			'icon' => $m[2],
			'img'  => $m[3],
			'mimg' => $m[4],
		);
	}
	return $out;
}

/* Seed — página Artemis SEO */
add_filter( 'artemis_seed', function ( $log ) {
	if ( ! get_page_by_path( 'artemis-seo' ) ) {
		$id    = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Artemis SEO', 'post_name' => 'artemis-seo' ) );
		$log[] = $id && ! is_wp_error( $id ) ? 'Página criada: Artemis SEO (/artemis-seo)' : 'Falha ao criar a página Artemis SEO';
	}
	return $log;
} );
