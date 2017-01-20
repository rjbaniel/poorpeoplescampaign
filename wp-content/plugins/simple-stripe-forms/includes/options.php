<?php
	
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'admin_init', 'ssf__register_settings' );
add_action( 'admin_menu', 'ssf__settings_menu' );

function ssf__register_settings() {
	// Add Settings Sections
	add_settings_section( 'ssf-api-settings', 'API Settings', 'ssf_api_settings', 'ssf-settings' );
	
	// Add API Settings Fields
	add_settings_field( 'ssf_live_pub_key', "Live Publishable Key", 'ssf_text_setting', 'ssf-settings', 'ssf-api-settings', array( 'id' => 'ssf_live_pub_key' , 'title' => "Live Publishable Key" ) );
	add_settings_field( 'ssf_live_sec_key', "Live Secret Key", 'ssf_text_setting', 'ssf-settings', 'ssf-api-settings', array( 'id' => 'ssf_live_sec_key' , 'title' => "Live Secret Key" ) );
	add_settings_field( 'ssf_test_pub_key', "Test Publishable Key", 'ssf_text_setting', 'ssf-settings', 'ssf-api-settings', array( 'id' => 'ssf_test_pub_key' , 'title' => "Test Publishable Key" ) );
	add_settings_field( 'ssf_test_sec_key', "Test Secret Key", 'ssf_text_setting', 'ssf-settings', 'ssf-api-settings', array( 'id' => 'ssf_test_sec_key' , 'title' => "Test Secret Key" ) );
	add_settings_field( 'ssf_is_live', "Use Live Mode?", 'ssf_is_live', 'ssf-settings', 'ssf-api-settings', array( 'id' => 'ssf_is_live' , 'title' => "Use Live Mode?" ) );

	// Register API Settings Fields as part of 'ssf-settings' option group
	register_setting( 'ssf-settings', 'ssf_live_pub_key' );
	register_setting( 'ssf-settings', 'ssf_live_sec_key' );
	register_setting( 'ssf-settings', 'ssf_test_pub_key' );
	register_setting( 'ssf-settings', 'ssf_test_sec_key' );
	register_setting( 'ssf-settings', 'ssf_is_live' );
}

function ssf_api_settings() {
	echo "These are settings related to the Stripe API";
}

function ssf_text_setting( $args ) {
	echo '<input id="' . $args['id'] . '" name="' . $args['id'] . '" type="text" value="'. ( get_option( $args['id'] ) ? esc_attr( get_option( $args['id'] ) ) : '' ) . '"></input>';
}

function ssf_is_live( $args ) {
	echo '<input id="' . $args['id'] . '" name="' . $args['id'] . '" type="checkbox"' . ( get_option( $args['id'] ) ? " checked" : '' ) . '></input>'; 
}

function ssf__settings_menu() {
	add_options_page( 'Simple Stripe Forms Settings', 'Simple Stripe Forms', 'manage_options', 'ssf-settings', 'ssf__settings_page' );
}

function ssf__settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have sufficiant permissions to access this page' );
	} ?>

	<div class="ssf-settings-page_wrap wrap">
	<h2>Simple Stripe Forms Options</h2>
	<form method="POST" action="options.php">
	<?php
		settings_fields( 'ssf-settings' );
		do_settings_sections( 'ssf-settings' );
		submit_button();
	?>
	</form>
	</div>
	<?php
}

?>