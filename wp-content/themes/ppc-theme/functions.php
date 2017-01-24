<?php

function enqueue_jquery_tabs() {
	if ( is_page_template( 'page-toc.php' ) ) {
		wp_enqueue_script( 'toc-js', get_stylesheet_directory_uri() . '/js/ppc-toc.js', 'jquery');
	}
}

add_action( 'wp_enqueue_scripts', 'enqueue_jquery_tabs');

add_theme_support( "aesop-component-styles", array( "image", "quote", "gallery", "content", "video", "audio" ) );

function ppc_set_style_version( $styles ) {
	$styles ->default_version = "20150605-2";
}
add_action( 'wp_default_styles', 'ppc_set_style_version');

function ppc__includes() {
	require_once( __DIR__ . '/includes/home-sections.php' );
	require_once( __DIR__ . '/includes/navigation.php' );
	require_once( __DIR__ . '/includes/endorsements.php' );
}
add_action( 'init', 'ppc__includes' );
