<?php
function do_simple_stripe_form( $atts ) {
	$args = shortcode_atts( array(
		'type' => 'single',
		'title' => 'Donate',
		'description' => '',
		'comment' => '',
		'get_zip' => false,
		'get_billing_address' => false,
		'button_text' => 'Submit',
		'button_bgcolor' => '',
		'button_textcolor' => '',
		'minimum' => 300,
	), $atts );

	ob_start();
	if ( isset( $_POST['ssf_nonce_field'] ) ) {
		if ( ssf__initialize_stripe() ) {
			ssf__process_charge( $args, $args['minimum'] );
		} else {
			echo '<p class="ssf-notice ssf-notice--error">There was a problem connecting to Stripe. Please contact the site administrator to let us know about this problem. Your card was not charged.</p>';
		}
	}
	ssf__display_form( $args );
	return ob_get_clean();

}

add_shortcode( 'simple-stripe-form', 'do_simple_stripe_form' );
?>
