<?php

require_once(__DIR__.'/postype-pokemon.php');

// Define path and URL to the ACF plugin.
define( 'MY_ACF_PATH', get_stylesheet_directory() . '/inc/plugins/acf/' );
define( 'MY_ACF_URL', get_stylesheet_directory_uri() . '/inc/plugins/acf/' );

// Include the ACF plugin.
//include_once( MY_ACF_PATH . 'acf.php' );

// Customize the url setting to fix incorrect asset URLs.
add_filter('acf/settings/url', 'my_acf_settings_url');
function my_acf_settings_url( $url ) {
    return MY_ACF_URL;
}

// (Optional) Hide the ACF admin menu item.
add_filter('acf/settings/show_admin', '__return_false');

function wpdocs_theme_name_scripts() {
	wp_enqueue_script( 'pokemon-js', get_template_directory_uri() . '/inc/pokemon/scripts.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'wpdocs_theme_name_scripts' );


/**
 * Instantiate class, creating post type Pokemon
 */

global $pokemon;
$pokemon = new Pokemon\Pokemon();