<?php
/**
 * Customizer: contact details, documents and media used across the theme.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

add_action( 'customize_register', function ( WP_Customize_Manager $wp ) {
	$wp->add_panel( 'aria', array(
		'title'    => __( 'Aria Foods settings', 'aria' ),
		'priority' => 30,
	) );

	$groups = array(
		'aria_contact' => array(
			'title'  => __( 'Contact details', 'aria' ),
			'fields' => array(
				'phone'       => array( __( 'Office phone', 'aria' ), 'text' ),
				'whatsapp'    => array( __( 'WhatsApp number (trade desk)', 'aria' ), 'text' ),
				'email'       => array( __( 'Public email', 'aria' ), 'email' ),
				'leads_email' => array( __( 'Send inquiries to (leave empty to use the admin email)', 'aria' ), 'email' ),
				'address'     => array( __( 'Office address', 'aria' ), 'textarea' ),
			),
		),
		'aria_brand'   => array(
			'title'  => __( 'Company & documents', 'aria' ),
			'fields' => array(
				'company'       => array( __( 'Brand name', 'aria' ), 'text' ),
				'legal_name'    => array( __( 'Legal company name', 'aria' ), 'text' ),
				'founded'       => array( __( 'Founded (year)', 'aria' ), 'text' ),
				'catalogue'     => array( __( 'Catalogue PDF URL', 'aria' ), 'url' ),
				'cert_iso9001'  => array( __( 'ISO 9001 certificate URL', 'aria' ), 'url' ),
				'cert_iso22000' => array( __( 'ISO 22000 certificate URL', 'aria' ), 'url' ),
				'cert_haccp'    => array( __( 'HACCP certificate URL', 'aria' ), 'url' ),
				'hero_image'    => array( __( 'Home hero image URL', 'aria' ), 'url' ),
				'shorts'        => array( __( 'YouTube Shorts IDs (comma separated)', 'aria' ), 'textarea' ),
			),
		),
		'aria_branch'  => array(
			'title'  => __( 'Dubai branch', 'aria' ),
			'fields' => array(
				'branch_legal' => array( __( 'Registered name in the UAE (as on the trade licence)', 'aria' ), 'text' ),
				'license_no'   => array( __( 'Trade licence number', 'aria' ), 'text' ),
				'license_auth' => array( __( 'Issued by (e.g. Dubai DET, JAFZA, DMCC)', 'aria' ), 'text' ),
				'street'       => array( __( 'Office / warehouse street address', 'aria' ), 'text' ),
				'city'         => array( __( 'City', 'aria' ), 'text' ),
				'phone_ae'     => array( __( 'UAE phone (+971 …)', 'aria' ), 'text' ),
				'hours'        => array( __( 'Opening hours (e.g. Mo-Fr 09:00-18:00)', 'aria' ), 'text' ),
				'map_url'      => array( __( 'Google Maps link of the office', 'aria' ), 'url' ),
				'parent_name'  => array( __( 'Head office name', 'aria' ), 'text' ),
				'parent_url'   => array( __( 'Head office website', 'aria' ), 'url' ),
			),
		),
		'aria_social'  => array(
			'title'  => __( 'Social profiles', 'aria' ),
			'fields' => array(
				'linkedin'  => array( 'LinkedIn', 'url' ),
				'instagram' => array( 'Instagram', 'url' ),
				'facebook'  => array( 'Facebook', 'url' ),
				'youtube'   => array( 'YouTube', 'url' ),
				'telegram'  => array( 'Telegram', 'url' ),
			),
		),
	);

	$defaults = aria_defaults();
	foreach ( $groups as $section => $g ) {
		$wp->add_section( $section, array( 'title' => $g['title'], 'panel' => 'aria' ) );
		foreach ( $g['fields'] as $key => $f ) {
			$sanitize = 'url' === $f[1] ? 'esc_url_raw' : ( 'email' === $f[1] ? 'sanitize_email' : ( 'textarea' === $f[1] ? 'sanitize_textarea_field' : 'sanitize_text_field' ) );
			$wp->add_setting( 'aria_' . $key, array(
				'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
				'sanitize_callback' => $sanitize,
			) );
			$wp->add_control( 'aria_' . $key, array(
				'label'   => $f[0],
				'section' => $section,
				'type'    => $f[1],
			) );
		}
	}
} );
