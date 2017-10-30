<?php

namespace SimplePay\Pro\Payments;

use SimplePay\Core\Abstracts\Form;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Payment
 *
 * @package SimplePay\Payments
 *
 * A container class to hold all of the various data that might be tied to one single payment.
 */
class Payment extends \SimplePay\Core\Payments\Payment {

	// Public
	public $invoice = '';
	public $has_quantity = false;
	public $quantity = null;
	public $coupon = '';
	public $subscription = null;
	public $recurring_amount_toggle = false;

	/**
	 * Payment constructor.
	 *
	 * @param Form|null $form
	 * @param string    $action
	 */
	public function __construct( Form $form, $action = '' ) {

		// Charge the right thing
		add_filter( 'simpay_charge', array( $this, 'pro_charge' ) );

		parent::__construct( $form, $action );

		$this->pro_set_attributes();

		add_action( 'simpay_process_form', array( $this, 'pro_process_form' ) );

	}

	public function pro_charge( $charge ) {

		global $simpay_form;

		if ( $simpay_form->is_subscription() || $this->recurring_amount_toggle ) {
			return new Subscription( $this );
		}

		return $charge;
	}

	/**
	 * Process the form for payment
	 */
	public function pro_process_form() {

		// Check if a quantity was set
		if ( isset( $_POST['simpay_quantity'] ) ) {

			$quantity = intval( $_POST['simpay_quantity'] );

			if ( $quantity < 1 ) {
				$quantity = 1;
			}

			$this->has_quantity = true;
			$this->quantity     = $quantity;
		}

		$this->recurring_amount_toggle = isset( $_POST['recurring_amount_toggle'] ) && ! empty( $_POST['recurring_amount_toggle'] ) ? true : false;
	}

	/**
	 * Set all of the attributes we need to attach to this Payment.
	 */
	public function pro_set_attributes() {

		// Set coupon if one exists
		if ( isset( $_POST['simpay_field'] ) ) {

			$fields = $_POST['simpay_field'];

			if ( isset( $fields['coupon'] ) ) {
				// A coupon field was found so set it here
				$this->coupon = sanitize_text_field( $fields['coupon'] );
			}
		}

	}

	/**
	 * Set the subscription property of this instance to a specif subscription id
	 *
	 * @param $id
	 */
	public function set_subscription( $id ) {
		$this->subscription = Subscription::get_subscription_by_id( $id );
	}
}
