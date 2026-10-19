<?php
/**
 * Customizer — textos das seções (MODEL.md). Dados globais (contato, redes, link do CTA) ficam na página
 * "Dados do site" (inc/options.php); os defaults deles continuam aqui, num lugar só.
 *
 * Cada inc/section-NN-slug.php estende via filtros:
 *   artemis_customizer_defaults  → array( 'art_chave' => 'valor padrão' )
 *   artemis_customizer_sections  → array( 'artemis_<id>' => array( 'Título', array( 'art_chave' => array( 'Label', 'text|textarea|email|url' ) ) ) )
 */

defined( 'ABSPATH' ) || exit;

function artemis_customizer_defaults() {
	static $defaults = null;
	if ( null === $defaults ) {
		$defaults = apply_filters( 'artemis_customizer_defaults', array(
			// Contato global (editado em "Dados do site")
			'art_contact_cta_url'   => '/contato',
			'art_contact_cta_label' => 'Agendar diagnóstico',
			'art_contact_email'     => 'contato@studioartemis.co',
		) );
	}
	return $defaults;
}

add_action( 'customize_register', function ( WP_Customize_Manager $wp ) {
	$d = artemis_customizer_defaults();

	$wp->add_panel( 'artemis', array(
		'title'       => __( 'Studio Artemis', 'artemis' ),
		'description' => __( 'Textos das seções. E-mail, WhatsApp, endereço, redes sociais e o botão "Agendar diagnóstico" (texto e link) ficam no menu "Dados do site" do painel.', 'artemis' ),
		'priority'    => 30,
	) );

	$sections = apply_filters( 'artemis_customizer_sections', array() );

	$priority = 10;
	foreach ( $sections as $section_id => $section ) {
		$wp->add_section( $section_id, array(
			'title'    => $section[0],
			'panel'    => 'artemis',
			'priority' => $priority++,
		) );
		foreach ( $section[1] as $key => $field ) {
			$type = $field[1];
			$wp->add_setting( $key, array(
				'default'           => isset( $d[ $key ] ) ? $d[ $key ] : '',
				'sanitize_callback' => 'textarea' === $type ? 'sanitize_textarea_field' : ( 'url' === $type ? 'esc_url_raw' : 'sanitize_text_field' ),
			) );
			$wp->add_control( $key, array(
				'label'   => $field[0],
				'section' => $section_id,
				'type'    => $type,
			) );
		}
	}
} );

/** Texto com trecho {entre chaves} renderizado em <em> (cinza no CSS). */
function artemis_highlight( $text ) {
	return preg_replace( '/\{([^}]+)\}/', '<em>$1</em>', esc_html( $text ) );
}
