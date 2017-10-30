<?php

namespace SimplePay\Pro\Payments;

use SimplePay\Core\Session;
use SimplePay\Pro\Payments\Payment;
use SimplePay\Core\Payments\Stripe_API;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Subscription
 *
 * @package SimplePay\Payments
 *
 * Wrapper for Stripe API Subscription. Used to handle anything we need to do with actual subscriptions (not Plans)
 */
class Subscription {

	// Class variables
	public $subscription = null;
	public $interval = '';
	public $interval_count = '';
	public $custom_plan_id = '';
	public $custom_plan_name = '';
	public $setup_fee = 0;
	public $trial_period = 0; // Trial period in number of days
	public $has_trial = false;

	/**
	 * Subscription constructor.
	 *
	 * @param Payment $payment Payment object this subscription object is tied to
	 */
	public function __construct( Payment $payment ) {

		global $simpay_form;

		// Set the payment to out Payment object and our form to the Form being processed
		$this->payment = $payment;
		//$this->form    = $this->payment->form;

		if ( $this->payment->recurring_amount_toggle ) {

			// Change the subscription type here to 'toggle' to force the Form is_subscription() check to now return true.
			$simpay_form->subscription_type = 'toggle';

			// Get toggle setting for max charges
			$toggle_max_charges = $simpay_form->extract_custom_field_setting( 'recurring_amount_toggle', 'max_charges', 0 );

			// Check for max charges
			if ( $toggle_max_charges > 0 ) {
				$simpay_form->subscription_max_charges = absint( $toggle_max_charges );
				$simpay_form->has_max_charges          = true;
			}

			// Process recurring amount toggle charge
			$this->toggle_subscription_charge();

			// We don't want to continue with other subscription actions so we return now.
			return;
		}

		// Set the setup fee
		$this->set_setup_fee();

		// Check for max charges (from multi-plan)
		if ( isset( $_POST['simpay_max_charges'] ) && ! empty( $_POST['simpay_max_charges'] ) && empty( $_POST['simpay_has_custom_plan'] ) ) {
			if ( absint( $_POST['simpay_max_charges'] ) > 0 ) {
				$simpay_form->subscription_max_charges = absint( $_POST['simpay_max_charges'] );
				$simpay_form->has_max_charges          = true;
			}
		}

		// Check if we are working with a custom amount or not and charge accordingly
		if ( true === $simpay_form->subscription_custom_amount && isset( $_POST['simpay_has_custom_plan'] ) && ! empty( $_POST['simpay_has_custom_plan'] ) ) {
			$this->custom_amount_charge();
		} else {

			// Single subscription
			if ( 'single' === $simpay_form->subscription_type ) {
				$plan = $simpay_form->plan;
			} else {

				// Multi-plan subscriptions
				$plan = isset( $_POST['simpay_multi_plan_id'] ) ? sanitize_text_field( $_POST['simpay_multi_plan_id'] ) : '';

				// For multi-plans we need to check the trial here and set it since this is when we know what plan they selected.
				$simpay_form->is_trial = Subscription::has_trial( $plan );

			}

			$this->charge( $this->payment->customer->get_id(), $plan );
		}
	}

	/**
	 * Handle a recurring amount toggle subscription
	 */
	public function toggle_subscription_charge() {

		global $simpay_form;

		// Check if custom amount was entered into the field and convert it to cents
		if ( isset( $_POST['simpay_custom_amount'] ) && ! empty( $_POST['simpay_custom_amount'] ) ) {
			$amount = simpay_convert_amount_to_cents( $_POST['simpay_custom_amount'] );
		} else {
			$amount = $simpay_form->amount;
		}

		$customer_id = $this->payment->customer->get_id();

		$this->custom_plan_id = $this->payment->get_email() . '_' . $amount . '_' . time();

		$this->interval       = $simpay_form->recurring_amount_toggle_frequency;
		$this->interval_count = $simpay_form->recurring_amount_toggle_interval;

		$item_description = $simpay_form->item_description;

		// If there is no item description we generate one here
		if ( empty( $item_description ) ) {
			// TODO: i18n this
			$item_description = html_entity_decode( simpay_formatted_amount( $amount, $this->payment->get_currency() ) ) . ' every ' . $this->interval_count . ' ' . $this->interval . '(s) plan';
		}

		// Set the custom plan name to the Item Description name
		$this->custom_plan_name = $item_description;

		// Create a plan
		$plan = array(
			'amount'         => $amount,
			'interval'       => $this->interval,
			'interval_count' => $this->interval_count,
			'currency'       => $this->payment->get_currency(),
			'id'             => $this->custom_plan_id,
			'name'           => $this->custom_plan_name,
		);

		$this->custom_plan_charge( $plan, $customer_id );
	}

	/**
	 * Function to use for charging a custom amount (user entered amount) plan
	 *
	 * @param string $customer_id Specific customer ID if provided
	 *
	 * @return bool Returns false if there is an error
	 */
	public function custom_amount_charge( $customer_id = '' ) {

		global $simpay_form;

		// Set all the attributes to the saved form attributes
		$this->interval       = simpay_get_saved_meta( $simpay_form->id, '_plan_frequency', 'month' );
		$this->interval_count = simpay_get_saved_meta( $simpay_form->id, '_plan_interval', 1 );

		// Create a unique plan ID for temporary use
		$this->custom_plan_id = $this->payment->get_email() . '_' . $this->payment->amount . '_' . time();

		$item_description = simpay_get_saved_meta( $simpay_form->id, '_item_description' );

		// If there is no item description we generate one here
		if ( empty( $item_description ) ) {
			// TODO: i18n this
			$item_description = html_entity_decode( simpay_formatted_amount( $this->payment->amount - $this->get_setup_fee(), $this->payment->get_currency() ) ) . ' every ' . $this->interval_count . ' ' . $this->interval . '(s) plan';
		}

		// Set the custom plan name to the Item Description name
		$this->custom_plan_name = $item_description;

		// If the customer ID is not passed in then we get the one tied to the current Payment
		if ( empty( $customer_id ) ) {
			$customer_id = $this->payment->customer->get_id();
		}

		// Check if custom amount was entered into the field and convert it to cents
		if ( isset( $_POST['simpay_subscription_custom_amount'] ) && ! empty( $_POST['simpay_subscription_custom_amount'] ) ) {
			$amount = simpay_convert_amount_to_cents( $_POST['simpay_subscription_custom_amount'] );
		} else {
			return false;
		}

		// Create a plan
		$plan = array(
			'amount'         => $amount,
			'interval'       => $this->interval,
			'interval_count' => $this->interval_count,
			'currency'       => $this->payment->get_currency(),
			'id'             => $this->custom_plan_id,
			'name'           => $this->custom_plan_name,
		);

		// Coupon
		if ( isset( $_POST['simpay_coupon'] ) && ! empty( $_POST['simpay_coupon'] ) ) {
			$coupon = sanitize_text_field( $_POST['simpay_coupon'] );
		} else {
			$coupon = '';
		}

		$this->custom_plan_charge( $plan, $customer_id, $this->get_setup_fee(), $coupon );
	}

	/**
	 * Make a custom plan charge using the parameters
	 *
	 * @param        $plan_args
	 * @param string $customer_id
	 * @param int    $setup_fee
	 * @param string $coupon
	 *
	 * @return bool
	 */
	public function custom_plan_charge( $plan_args, $customer_id = '', $setup_fee = 0, $coupon = '' ) {

		global $simpay_form;

		if ( empty( $plan_args ) || ! is_array( $plan_args ) ) {
			return false;
		}

		if ( empty( $customer_id ) ) {
			$customer_id = $this->payment->customer->get_id();
		}

		// Create a plan
		$plan = $plan_args;

		// Add any metadata that was processed
		if ( ! empty( $this->payment->metadata ) ) {
			$plan['metadata'] = $this->payment->metadata;
		}

		// Create a temporary Plan to create all the charges
		Plan::create_new( $plan );

		// Setup subscription arguments
		$subscription = array(
			'customer' => $customer_id,
			'plan'     => $plan_args['id'],
		);

		// Apply a coupon if it exists
		if ( ! empty( $coupon ) ) {
			$subscription['coupon'] = sanitize_text_field( $coupon );
		}

		// Check for setup fee
		if ( $setup_fee > 0 ) {

			// Create an invoice item that will charge the setup fee right away
			Invoice::create_invoice_item( array(
				'amount'   => $setup_fee,
				'currency' => simpay_get_setting( 'currency' ),
				'customer' => $customer_id,
			) );
		}

		// Add the tax percent if one is set
		if ( $simpay_form->tax_percent > 0 ) {
			$subscription['tax_percent'] = $simpay_form->tax_percent;

			$simpay_form->tax_amount = simpay_formatted_amount( simpay_calculate_tax( $plan_args['amount'] + $this->get_setup_fee(), $simpay_form->tax_percent ) );
		}

		// Insert installment plan meta
		if ( $simpay_form->has_max_charges ) {
			$subscription = $this->add_installment_plan( $subscription );
		}

		// Now create subscription using new plan
		$this->subscription = Stripe_API::request( 'Subscription', 'create', $subscription );

		// Fire action hook as soon as the subscription is created
		do_action( 'simpay_subscription_created', $this->subscription, $this->payment->customer->customer, $this->payment->metadata );

		// Set the charge ID of Payment to our new Charge ID here
		$charge_id = $this->get_charge_id( $customer_id );

		if ( $this->payment->amount >= simpay_get_stripe_minimum_amount( true ) ) {
			$this->payment->set_charge( $charge_id );
		}

		// Update any meta
		$this->update_meta();

		// Save our charge ID to WP Session
		Session::add( 'charge_id', $charge_id );

		Session::add( 'subscription_id', $this->subscription->id );
		Session::add( 'customer_id', $customer_id );

		// Now that the charge has been made we can remove the temporary Plan
		Plan::remove( $plan_args['id'] );
	}

	/**
	 * Actually process the charge for the subscription
	 *
	 * @param string $customer_id A specific customer ID if provided
	 * @param string $plan        A specific plan ID if provided
	 *
	 * @return bool Returns false if no plan is found
	 */
	public function charge( $customer_id = '', $plan = '' ) {

		global $simpay_form;

		// If no plan then exit now
		if ( empty( $plan ) ) {
			return false;
		}

		// Check for trial
		$this->has_trial = self::has_trial( $plan );

		// If customer ID was not sent over then grab the saved customer ID
		if ( empty( $customer_id ) ) {
			$customer_id = $this->payment->customer_id;
		}

		// Start setting up subscription arguments
		$args = array(
			'customer' => $customer_id,
			'plan'     => $plan,
		);

		// Apply a coupon if it exists
		if ( isset( $_POST['simpay_coupon'] ) && ! empty( $_POST['simpay_coupon'] ) ) {
			$args['coupon'] = sanitize_text_field( $_POST['simpay_coupon'] );
		}

		// Check for quantity here
		if ( $this->payment->has_quantity ) {
			$args['quantity'] = $this->payment->quantity;
		}

		// Check for setup fee
		if ( $this->get_setup_fee() > 0 ) {

			// Create an invoice item that will charge the setup fee right away
			Invoice::create_invoice_item( array(
				'amount'   => $this->get_setup_fee(),
				'currency' => simpay_get_setting( 'currency' ),
				'customer' => $customer_id,
			) );
		}

		// Add the tax percent if one is set
		if ( $simpay_form->tax_percent > 0 ) {

			if ( ! $this->has_trial ) {
				$args['tax_percent'] = $simpay_form->tax_percent;
			}

			$plan_amount = Stripe_API::request( 'Plan', 'retrieve', $plan )->amount;

			$simpay_form->tax_amount = simpay_formatted_amount( simpay_calculate_tax( $plan_amount + $this->get_setup_fee(), $simpay_form->tax_percent ) );
		}

		// Insert a database entry if this subscription has max charges enabled
		if ( $simpay_form->has_max_charges ) {
			$args = $this->add_installment_plan( $args );
		}

		// Create the subscription
		$this->subscription = Stripe_API::request( 'Subscription', 'create', $args );

		// Fire action hook as soon as the subscription is created
		do_action( 'simpay_subscription_created', $this->subscription, $this->payment->customer->customer, $this->payment->metadata );

		// If there is no trial proceed with charge ID
		if ( ! $this->has_trial ) {
			// Get our new charge ID and save it to the Payment object
			$charge_id = $this->get_charge_id( $customer_id );

			if ( $this->payment->amount >= simpay_get_stripe_minimum_amount( true ) ) {
				$this->payment->set_charge( $charge_id );
			}

			// Update any meta
			$this->update_meta();

			// Pass along our charge ID to WP Session
			Session::add( 'charge_id', $charge_id );
		} else {
			Session::add( 'trial', true );

			$this->update_meta();
		}

		Session::add( 'subscription_id', $this->subscription->id );
		Session::add( 'customer_id', $customer_id );

		// Set the item description to this plan's description if it is a multi-plan
		if ( 'user' == $simpay_form->subscription_type ) {
			$this->set_item_description( $plan );
		}

		if ( isset( $_POST['simpay_multi_plan_setup_fee'] ) ) {
			Session::add( 'simpay_multi_plan_setup_fee', intval( $_POST['simpay_multi_plan_setup_fee'] ) );
		}
	}

	// Add an installment plan entry to the metadata
	public function add_installment_plan( $args ) {

		global $simpay_form;

		// If this is a trial then we need to start at -1 to account for the initial $0.00 charge that will bump it up
		$args['metadata']['simpay_charge_count'] = ( $this->has_trial ) ? -1 : 0;
		$args['metadata']['simpay_charge_max']   = $simpay_form->subscription_max_charges;

		return $args;

	}

	// Search the plans and find the current one and get set the form description
	public function set_item_description( $plan ) {

		global $simpay_form;

		$current_plan = array();

		if ( ! empty ( $simpay_form->plans ) && is_array( $simpay_form->plans ) ) {
			foreach ( $simpay_form->plans as $p ) {

				if ( ! is_array( $p ) ) {
					continue;
				}

				if ( $plan === $p['select_plan'] ) {
					$current_plan = $p;
					break;
				}
			}
		}

		if ( empty( $current_plan['custom_label'] ) ) {
			$simpay_form->item_description = $current_plan['plan_object']->name;
		} else {
			$simpay_form->item_description = $current_plan['custom_label'];
		}
	}

	/**
	 * Updates the subscription metadata
	 */
	public function update_meta() {

		global $simpay_form;

		if ( ! $this->has_trial ) {
			// Get last recorded charge based on customer ID.
			$charge_list = Stripe_API::request( 'Charge', 'all', array(
				'customer' => $this->payment->customer_id,
				'limit'    => 1,
			) );

			$charge  = isset( $charge_list->data[0] ) ? $charge_list->data[0] : false;
			$invoice = new Invoice( $this->payment );
			$invoice = $invoice->get_invoice();
			$do_save = false;

			// We want to add the metadata to the charge so that users can still view metadata sent with
			// a subscription + custom fields the same way that they would normally view it without subscriptions installed.
			// Besides the charge, add the metadata to the invoice & subscription.
			if ( ! empty( $this->payment->metadata ) ) {

				if ( $charge ) {
					$charge->metadata = $this->payment->metadata;
				}

				if ( $invoice ) {
					$invoice->metadata = $this->payment->metadata;
				}

				$this->subscription->metadata = $this->payment->metadata;

				$do_save = true;
			}

			// Add the description if there is one
			if ( ! empty( $this->payment->description ) ) {

				if ( $charge ) {
					$charge->description = $this->payment->description;
				}

				if ( $invoice ) {
					$invoice->description = $this->payment->description;
				}

				// Subscriptions don't have a description property, so merge a new "description" metadata field containing the description.
				$this->subscription->metadata = array_merge( $this->payment->metadata, array( 'description' => $this->payment->description ) );

				$do_save = true;
			}

			// If we need to save then save the invoice, charge, and subscription so that the metadata will stick
			if ( $do_save ) {

				if ( $charge ) {
					$charge->save();
				}

				if ( $invoice ) {
					$invoice->save();
				}

				$this->subscription->save();
			}

		} else {

			$save_trial = false;

			// This is a trial

			// Update the tax percent for the next charges
			if ( $simpay_form->tax_percent > 0 ) {
				$this->subscription->tax_percent = $simpay_form->tax_percent;

				$save_trial = true;
			}

			if ( ! empty( $this->payment->metadata ) ) {
				$this->subscription->metadata = $this->payment->metadata;

				$save_trial = true;
			}

			if ( ! empty( $this->payment->description ) ) {
				$this->subscription->metadata = array_merge( $this->payment->metadata, array( 'description' => $this->payment->description ) );

				$save_trial = true;
			}

			if ( $save_trial ) {
				$this->subscription->save();
			}
		}
	}

	/**
	 * Get the ID of a subscription charge
	 *
	 * @param $subscription_id Subscription ID to get the charge of
	 *
	 * @return mixed The subscription ID
	 */
	private function get_charge_id( $customer_id ) {

		$charge_list = Stripe_API::request( 'Charge', 'all', array(
			'customer' => $customer_id,
			'limit'    => 1,
		) );

		if ( isset( $charge_list->data[0] ) ) {
			return $charge_list->data[0]->id;
		}

		return false;
	}

	/**
	 * Set the setup fee if there is one
	 *
	 * @param null $setup_fee
	 */
	public function set_setup_fee( $setup_fee = null ) {

		global $simpay_form;

		// Check wp session first if it exists
		$plan_fee = Session::get( 'simpay_multi_plan_setup_fee', '0' );

		// WP session wasn't there so let's check POST next
		if ( empty( $plan_fee ) ) {
			$plan_fee = isset( $_POST['simpay_multi_plan_setup_fee'] ) ? intval( $_POST['simpay_multi_plan_setup_fee'] ) : 0;
		}

		// Update our WP session to whatever we have at this point
		Session::add( 'simpay_multi_plan_setup_fee', $plan_fee );

		if ( ! null === $setup_fee ) {
			$this->setup_fee = $setup_fee + $plan_fee;
		} else {
			$this->setup_fee = floatval( $simpay_form->subscription_setup_fee ) + floatval( $plan_fee );
		}
	}

	/**
	 * Return the total setup fee
	 *
	 * @return int The setup fee
	 */
	public function get_setup_fee() {
		return $this->setup_fee;
	}

	/**
	 * Check if a plan has a trial period
	 *
	 * @param $plan - The plan ID to check
	 *
	 * @return bool
	 */
	public static function has_trial( $plan ) {
		// Get the plan object by ID and check it's trial period days attribute. If it is greater than 0 return true because it has a trial, otherwise false

		if ( is_object( $plan ) ) {
			return ( $plan->trial_period_days > 0 );
		}

		return ( Plan::get_plan_by_id( $plan )->trial_period_days > 0 );
	}

	public static function get_subscription_by_id( $id ) {
		return Stripe_API::request( 'Subscription', 'retrieve', $id );
	}
}
