<?php

/**
 * Plugin Name: Simple Stripe Forms
 * Description: A plugin for generating simple Stripe forms.
 * Author: Daniel Jones
 * Version: 0.1
 * Text Domain: simple-stripe-forms
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ssf__init() {
	ssf__define_constants();
	include_once( SSF_DIR_PATH . '/includes/functions.php' );
	if ( is_admin() ) {
		include_once( SSF_DIR_PATH . '/includes/options.php' );
	} else {
		include_once( SSF_DIR_PATH . '/includes/shortcodes.php' );
	}
}
add_action( 'init', 'ssf__init' );

function ssf__define_constants() {
	$ssf_constants = array(
		'SSF_MAIN_FILE' => __FILE__,
		'SSF_DIR_PATH' => plugin_dir_path( __FILE__ ),
		'SSF_DIR_URL' => plugin_dir_url( __FILE__ )
	);

	foreach( $ssf_constants as $key => $value ) {
		if ( ! defined( $key ) ) {
			define( $key, $value );
		}
	}	
}
