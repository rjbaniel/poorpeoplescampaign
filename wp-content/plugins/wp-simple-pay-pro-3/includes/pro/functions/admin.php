<?php

use SimplePay\Pro\Payments\Plan;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check the user's license to see if subscriptions are enabled or not
 *
 * @return bool
 */
function simpay_subscriptions_enabled() {

	$license_data = get_option( 'simpay_license_data' );

	if ( ! empty( $license_data ) && 'valid' === $license_data->license ) {
		$price_id = $license_data->price_id;

		if ( '1' !== $price_id ) {
			return true;
		}
	}

	return false;
}

/**
 * Get a list of all the Stripe plans
 */
function simpay_get_plan_list() {

	// Make sure the API keys exist before we try to load the plan list
	if ( simpay_check_keys_exist() ) {

		$plans = Plan::get_plans();

		if ( ! empty( $plans ) && is_array( $plans ) ) {

			$options = array();

			foreach ( $plans as $k => $v ) {

				$name           = $v['name'];
				$id             = $v['id'];
				$currency       = $v['currency'];
				$amount         = $v['amount'];
				$interval       = $v['interval'];
				$interval_count = $v['interval_count'];
				$decimals       = 0;

				if ( ! simpay_is_zero_decimal( $currency ) ) {
					$amount   = $amount / 100;
					$decimals = 2;
				}

				// Put currency symbol + amount in one string to make it easier
				$amount = simpay_get_currency_symbol( $currency ) . number_format( $amount, $decimals );

				$options[ $id ] = $name . ' ' . sprintf( _n( '(id: %1$s) - %2$s/%4$s', '(id: %1$s) - %2$s every %3$d %4$ss', $interval_count, 'simple-pay' ), $id, $amount, $interval_count, $interval );
			}

			asort( $options );

			return $options;
		}
	}

	return array();
}

/**
 * Get the description for webhooks
 */
function simpay_webhook_help_text() {

	$html = '<p class="description">' . esc_html__( 'In order for Max Charges to function correctly, you must set up a Stripe webhook endpoint.', 'simple-pay' ) . '<br>';
	$html .= '<strong>' . sprintf( esc_html__( 'Your webhook URL: %s', 'simple-pay' ), simpay_get_webhook_url() ) . '</strong><br>';
	$html .= '<a href="' . simpay_docs_link( '', 'installment-plans', '', true ) . '" target="_blank">' . esc_html__( 'See our documentation for a step-by-step guide.', 'simple-pay' ) . '</a></p>';

	return $html;
}

/**
 * Return the webhook URL specific for this user's site
 */
function simpay_get_webhook_url() {
	return trailingslashit( get_bloginfo( 'url' ) ) . '?simple-pay-listener=stripe';
}

/**
 * Get the description for Form Field Label
 */
function simpay_form_field_label_description() {
	return esc_html__( 'Label displayed above this field on the payment form.', 'simple-pay' );
}

/**
 * Get the description for Stripe Metadata Label
 */
function simpay_metadata_label_description() {
	return esc_html__( 'Used to identify this field in Stripe payment records. It is not displayed on the payment form and is limited to 40 characters.', 'simple-pay' );
}

/**
 * My Account/License upgrade URL with GA campaign values.
 *
 * @param string $ga_content
 *
 * @return string
 */
function simpay_my_account_url( $ga_content ) {

	return simpay_ga_url( simpay_get_url( 'my-account' ), $ga_content, true );
}
