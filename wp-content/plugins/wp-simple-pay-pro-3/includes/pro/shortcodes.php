<?php
/**
 * Shortcodes
 */

namespace SimplePay\Pro;

use SimplePay\Pro\Forms\Pro_Form;
use SimplePay\Core\Session;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcodes.
 *
 * Register and handle custom shortcodes.
 */
class Shortcodes {

	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'simpay_payment_receipt_html', array( $this, 'pro_payment_receipt' ) );

		add_filter( 'simpay_session_error', array( $this, 'check_session_errors' ) );
	}

	public function check_session_errors( $bool ) {

		$trial           = Session::get( 'trial' );
		$subscription_id = Session::get( 'subscription_id' );

		if ( $subscription_id || $trial ) {
			return false;
		}

		return $bool;
	}

	public function pro_payment_receipt( $payment ) {

		$trial           = Session::get( 'trial' );
		$subscription_id = Session::get( 'subscription_id' );

		if ( $subscription_id || $trial ) {

			$payment->set_subscription( $subscription_id );
		}
	}
}
