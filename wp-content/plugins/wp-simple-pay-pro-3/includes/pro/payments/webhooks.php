<?php

namespace SimplePay\Pro\Payments;

use SimplePay\Core\Payments\Stripe_API;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Webhooks {

	private $id;

	public function __construct() {

		$this->id = 'stripe';

		add_action( 'init', array( $this, 'simple_pay_event_listener' ) );

	}

	/**
	 * Installment plans feature.
	 * See text file in this folder for notes.
	 * If SP is active the Stripe PHP library should not have to be loaded.
	 */

	public function simple_pay_event_listener() {

		if ( isset( $_GET['simple-pay-listener'] ) && $_GET['simple-pay-listener'] == 'stripe' ) {

			// Let Stripe know we successfully hit
			if ( ! function_exists( 'http_response_code' ) ) {
				$this->send_response_code(); // PHP < 5.4
			} else {
				http_response_code( 200 ); // PHP 5.4 or greater
			}

			// retrieve the request's body and parse it as JSON
			$body = @file_get_contents( 'php://input' );

			// grab the event information
			$event_json = json_decode( $body );

			if ( isset( $event_json->id ) ) {

				echo 'Simple Pay - Event Found.  ';

				// this will be used to retrieve the event from Stripe
				$event_id = $event_json->id;

				try {

					// to verify this is a real event, we re-retrieve the event from Stripe
					$event = Stripe_API::request( 'Event', 'retrieve', $event_id );

					do_action( 'simpay_webhook_event', $event );

					// Successful invoice paid against a subscription.
					if ( isset( $event ) && $event->type == 'invoice.payment_succeeded' ) {

						echo 'Simple Pay - Invoice Payment Succeeded.  ';

						// Get the invoice object and subscription
						$invoice      = $event->data->object;
						$subscription = Stripe_API::request( 'Subscription', 'retrieve', $invoice->subscription );

						do_action( 'simpay_webhook_invoice_payment_succeeded', $event, $invoice, $subscription );

						if ( isset( $subscription ) && isset( $subscription->metadata['simpay_charge_max'] ) ) {

							echo 'Simple Pay - Has an installment plan.  ';

							$max_charges  = $subscription->metadata['simpay_charge_max'];
							$charge_count = $subscription->metadata['simpay_charge_count'];

							$charge_count++;

							do_action( 'simpay_webhook_after_installment_increase' );

							echo 'Simple Pay - Max charges: ' . $max_charges . '  ';
							echo 'Simple Pay - New charge count: ' . $charge_count . '  ';

							// Update the total count metadata
							$subscription->metadata['simpay_charge_count'] = absint( $charge_count );
							$subscription->save();

							if ( $charge_count >= $max_charges ) {

								echo 'Simple Pay - Max charges hit, cancelling subscription.  ';

								// Cancel subscription after the period ends
								$subscription->cancel( array(
									'at_period_end' => true,
								) );

								do_action( 'simpay_webhook_after_subscription_cancel' );
							}
						}
					}

				} catch ( \Exception $e ) {

					// something failed, perhaps log a notice or email the site admin
					return new \WP_Error( 'invalid_request', 'Invalid webhook data. Webhook could not be processed.', array( 'status_header' => 500 ) );
				}
			}

			exit;
		}
	}

	// Send the http_response_code() for PHP < 5.4
	private function send_response_code( $new_code = null ) {

		static $code = 200;

		if ( $new_code !== null ) {

			header( 'X-PHP-Response-Code: ' . $new_code, true, $new_code );

			if ( ! headers_sent() ) {
				$code = $new_code;
			}
		}

		return $code;
	}

}
