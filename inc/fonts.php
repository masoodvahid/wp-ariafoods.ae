<?php
/**
 * Font picker: Customizer → Aria Foods settings → Fonts.
 * "Theme default" uses the self-hosted Montserrat + Archivo (fastest).
 * Any other choice loads that family from Google Fonts, for trying fonts out;
 * once a font is chosen it can be bundled locally again.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

/** Google Fonts families: name => [ available weights, fallback stack ]. */
function aria_font_library() {
	$sans  = 'ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif';
	$serif = 'Georgia,"Times New Roman",serif';
	$all   = array( 300, 400, 500, 600, 700, 800, 900 );
	return array(
		// Sans serif.
		'Inter'             => array( $all, $sans ),
		'Poppins'           => array( $all, $sans ),
		'Montserrat'        => array( $all, $sans ),
		'Archivo Black'     => array( array( 400 ), $sans ),
		'Manrope'           => array( array( 300, 400, 500, 600, 700, 800 ), $sans ),
		'Plus Jakarta Sans' => array( array( 300, 400, 500, 600, 700, 800 ), $sans ),
		'DM Sans'           => array( $all, $sans ),
		'Outfit'            => array( $all, $sans ),
		'Urbanist'          => array( $all, $sans ),
		'Figtree'           => array( $all, $sans ),
		'Sora'              => array( array( 300, 400, 500, 600, 700, 800 ), $sans ),
		'Lexend'            => array( $all, $sans ),
		'Work Sans'         => array( $all, $sans ),
		'Rubik'             => array( $all, $sans ),
		'Nunito Sans'       => array( $all, $sans ),
		'Open Sans'         => array( array( 300, 400, 500, 600, 700, 800 ), $sans ),
		'Roboto'            => array( $all, $sans ),
		'Lato'              => array( array( 300, 400, 700, 900 ), $sans ),
		'Raleway'           => array( $all, $sans ),
		'Red Hat Display'   => array( $all, $sans ),
		'Archivo'           => array( $all, $sans ),
		'Barlow'            => array( $all, $sans ),
		'Space Grotesk'     => array( array( 300, 400, 500, 600, 700 ), $sans ),
		'Josefin Sans'      => array( array( 300, 400, 500, 600, 700 ), $sans ),
		'Oswald'            => array( array( 300, 400, 500, 600, 700 ), $sans ),
		// Serif.
		'Playfair Display'   => array( array( 400, 500, 600, 700, 800, 900 ), $serif ),
		'Lora'               => array( array( 400, 500, 600, 700 ), $serif ),
		'Merriweather'       => array( array( 300, 400, 700, 900 ), $serif ),
		'EB Garamond'        => array( array( 400, 500, 600, 700, 800 ), $serif ),
		'Cormorant Garamond' => array( array( 300, 400, 500, 600, 700 ), $serif ),
		'Libre Baskerville'  => array( array( 400, 700 ), $serif ),
		'Fraunces'           => array( $all, $serif ),
		'Cinzel'             => array( array( 400, 500, 600, 700, 800, 900 ), $serif ),
		'DM Serif Display'   => array( array( 400 ), $serif ),
		'Marcellus'          => array( array( 400 ), $serif ),
	);
}

function aria_font_choice( $role ) {
	$v   = get_theme_mod( 'aria_font_' . $role, '' );
	$lib = aria_font_library();
	return isset( $lib[ $v ] ) ? $v : '';
}

/** Weights to request: what the theme uses, limited to what the family has. */
function aria_font_weights( $family, $want ) {
	$lib  = aria_font_library();
	$have = $lib[ $family ][0];
	$w    = array_values( array_intersect( $want, $have ) );
	return $w ? $w : array( max( $have ) );
}

add_action( 'customize_register', function ( WP_Customize_Manager $wp ) {
	$wp->add_section( 'aria_fonts', array(
		'title'       => __( 'Fonts', 'aria' ),
		'panel'       => 'aria',
		'description' => __( 'Pick fonts and watch the preview. "Theme default" is self-hosted and fastest; Google fonts load from Google until you ask for the chosen font to be bundled with the theme.', 'aria' ),
	) );
	$choices = array( '' => __( 'Theme default', 'aria' ) );
	foreach ( array_keys( aria_font_library() ) as $name ) {
		$choices[ $name ] = $name;
	}
	$fields = array(
		'head' => __( 'Headings font (theme default: Manrope)', 'aria' ),
		'body' => __( 'Body text font (theme default: Inter)', 'aria' ),
	);
	foreach ( $fields as $role => $label ) {
		$wp->add_setting( 'aria_font_' . $role, array(
			'default'           => '',
			'sanitize_callback' => function ( $v ) {
				return array_key_exists( $v, aria_font_library() ) ? $v : '';
			},
		) );
		$wp->add_control( 'aria_font_' . $role, array(
			'label'   => $label,
			'section' => 'aria_fonts',
			'type'    => 'select',
			'choices' => $choices,
		) );
	}
} );

/* Load the chosen Google fonts and point the theme's font variables at them. */
add_action( 'wp_head', function () {
	$head = aria_font_choice( 'head' );
	$body = aria_font_choice( 'body' );
	if ( ! $head && ! $body ) {
		return;
	}
	$lib  = aria_font_library();
	$fams = array();
	if ( $head ) {
		$fams[ $head ] = aria_font_weights( $head, array( 600, 700, 800 ) );
	}
	if ( $body ) {
		$w             = aria_font_weights( $body, array( 400, 500, 600, 700 ) );
		$fams[ $body ] = isset( $fams[ $body ] ) ? array_values( array_unique( array_merge( $fams[ $body ], $w ) ) ) : $w;
	}
	$q = array();
	foreach ( $fams as $name => $w ) {
		sort( $w );
		$q[] = 'family=' . str_replace( ' ', '+', $name ) . ':wght@' . implode( ';', $w );
	}
	$url = 'https://fonts.googleapis.com/css2?' . implode( '&', $q ) . '&display=swap';
	echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	echo '<link rel="stylesheet" href="' . esc_url( $url ) . '">' . "\n";

	$css = ':root{';
	if ( $head ) {
		$css .= '--fh:"' . $head . '",' . $lib[ $head ][1] . ';';
	}
	if ( $body ) {
		$css .= '--f:"' . $body . '",' . $lib[ $body ][1] . ';';
	}
	$css .= '}';
	if ( $head && 1 === count( $lib[ $head ][0] ) ) {
		$css .= 'h1,h2,h3,h4{font-weight:' . (int) $lib[ $head ][0][0] . ';letter-spacing:0}';
	}
	echo '<style id="aria-fonts">' . $css . '</style>' . "\n"; // phpcs:ignore -- values come from the fixed list above.
}, 20 ); // After the theme stylesheet, so the chosen fonts win.
