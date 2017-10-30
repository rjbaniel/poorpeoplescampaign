<?php

namespace SimplePay\Pro\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SimplePay\Core\Payments\Stripe_API;

/**
 * Class Ajax
 *
 * Handles ajax functionality for forms.
 *
 * @package SimplePay\Forms
 */
class Ajax {

	/**
	 * Ajax constructor.
	 */
	public function __construct() {

		add_action( 'wp_ajax_simpay_get_coupon', array( $this, 'simpay_get_coupon' ) );
		add_action( 'wp_ajax_nopriv_simpay_get_coupon', array( $this, 'simpay_get_coupon' ) );
	}

	/**
	 * Check for a coupon and return the discounted amount.
	 */
	public function simpay_get_coupon() {

		// Check nonce first
		check_ajax_referer( 'simpay_coupon_nonce', 'couponNonce' );

		$json     = array();
		$code     = sanitize_text_field( $_POST['coupon'] );
		$amount   = floatval( $_POST['amount'] );
		$discount = 0;

		$json['coupon']['code'] = $code;
		$json['amount']         = $amount;

		$coupon = Stripe_API::request( 'Coupon', 'retrieve', $code );

		// If coupon is not found then exit now
		if ( false === $coupon ) {

			// Coupon not found
			echo esc_html__( 'Coupon is invalid.', 'simple-pay' );
			wp_die();
		}

		// Check coupon type
		if ( ! empty( $coupon->percent_off ) ) {

			// Coupon is percent off so handle that

			$json['coupon']['amountOff'] = $coupon->percent_off;
			$json['coupon']['type']      = 'percent';

			if ( $coupon->percent_off == 100 ) {
				$discount = $amount;
			} else {
				$discount = $amount - round( ( $amount * ( ( 100 - $coupon->percent_off ) / 100 ) ) );
			}
		} else if ( ! empty( $coupon->amount_off ) ) {

			// Coupon is a set amount off (e,g, $3.00 off)

			if ( simpay_is_zero_decimal() ) {
				$json['coupon']['amountOff'] = $coupon->amount_off;
			} else {
				$json['coupon']['amountOff'] = $coupon->amount_off / 100;
			}

			$json['coupon']['type'] = 'amount';

			$discount = $amount - ( $amount - $coupon->amount_off );

			if ( $discount < 0 ) {
				$discount = 0;
			}
		}

		// Check if the coupon puts the total below the minimum amount
		if ( $amount - $discount < simpay_get_stripe_minimum_amount( true ) ) {
			echo esc_html__( 'Coupon entered puts the total below the required minimum amount.', 'simple-pay' );
			wp_die();
		} else {

			$json['success'] = true;

			// We want to send the correct amount back to the JS
			$json['discount'] = $discount;
		}

		// Return as JSON
		echo json_encode( $json );

		wp_die();
	}
}
