<?php

namespace SimplePay\Pro\Admin;

use SimplePay\Core\Admin\Notices as CoreNotice;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Notices {

	public function __construct( $is_admin_screen ) {

		$this->license_key_error( $is_admin_screen );
		$this->api_keys_changed_warning();
		$this->test_mode_change_warning();
	}

	/**
	 *
	 * Display the error message if no license key is found
	 *
	 */
	public function license_key_error( $is_admin_screen ) {

		$license_data = get_option( 'simpay_license_data' );

		if ( ( empty( $license_data ) || 'valid' !== $license_data->license ) && ( ( false !== $is_admin_screen && ( 'simpay_settings' === $is_admin_screen && isset( $_GET['tab'] ) && 'license' !== $_GET['tab'] ) || 'simpay' === $is_admin_screen ) ) ) {

			/* translators: This is part of the admin notice for missing license keys. The full string is "Your WP Simple Pay Pro 3 license key is invalid, inactive or missing. Please enter and activate your license key to enable automatic updates." */
			$notice_message = __( 'Your WP Simple Pay Pro 3 license key is invalid, inactive or missing. Please', 'simple-pay' );
			$notice_message .= ' <a href="' . admin_url( 'admin.php?page=simpay_settings&tab=license' ) . '">' . __( 'enter and activate', 'simple-pay' ) . '</a> ';
			$notice_message .= __( 'your license key to enable automatic updates.', 'simple-pay' );

			CoreNotice::print_notice( $notice_message, 'error' );
		}
	}

	/**
	 *
	 * Show warning if test or live Stripe API keys have changed.
	 *
	 */
	public function api_keys_changed_warning() {

		$test_keys_changed = get_option( 'simpay_test_keys_changed' );
		$live_keys_changed = get_option( 'simpay_live_keys_changed' );

		if ( $test_keys_changed || $live_keys_changed ) {

			$notice_message = esc_html__( 'It looks like you have changed your API keys. Make sure the correct subscription plans and coupons exist in the Stripe account tied to those keys.', 'simple-pay' );

			CoreNotice::print_notice( $notice_message, 'warning', 'keys_changed' );

			// Remove the options so the warning only shows until the user navigates from the page
			delete_option( 'simpay_test_keys_changed' );
			delete_option( 'simpay_live_keys_changed' );
		}
	}

	/**
	 *
	 * Show warning if test mode setting has been changed
	 *
	 */
	public function test_mode_change_warning() {

		$test_mode_change = get_option( 'simpay_test_mode_changed' );

		if ( $test_mode_change ) {

			if ( 'enabled' === $test_mode_change ) {
				$notice_message = sprintf( wp_kses( __( 'It looks like you have switched to test mode. Make sure the correct subscription plans and coupons exist in your test <a href="%s" target="_blank">Stripe account</a>.', 'simple-pay' ), array(
					'a' => array(
						'href'   => array(),
						'target' => array(),
					),
				) ), 'https://dashboard.stripe.com/test/dashboard' );
			} else {
				$notice_message = sprintf( wp_kses( __( 'It looks like you have switched to live mode. Make sure the correct subscription plans and coupons exist in your live <a href="%s" target="_blank">Stripe account</a>.', 'simple-pay' ), array(
					'a' => array(
						'href'   => array(),
						'target' => array(),
					),
				) ), 'https://dashboard.stripe.com/live/dashboard' );
			}

			CoreNotice::print_notice( $notice_message, 'warning', 'mode_changed' );

			// Remove the option so the warning only shows until the user navigates from the page.
			delete_option( 'simpay_test_mode_changed' );
		}
	}
}
