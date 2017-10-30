<?php

namespace SimplePay\Pro\Forms;

use SimplePay\Core\Abstracts\Form;
use SimplePay\Core\Forms\Default_Form;
use SimplePay\Pro\Payments\Subscription;
use SimplePay\Pro\Payments\Plan;
use SimplePay\Core\Session;
use SimplePay\Core\Admin\MetaBoxes\Custom_Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Pro_Form extends Default_Form {

	public $printed_subscriptions = false;
	public $printed_custom_amount = false;

	/*****
	 *
	 * GLOBAL SETTINGS
	 *
	 *****/

	/** GENERAL **/

	/* Currency Options */
	public $tax_percent = 0;

	/** DISPLAY **/

	/* Front-end Styles */
	public $apply_button_style = '';

	/*****
	 *
	 * FORM SETTINGS
	 *
	 *****/
	public $date_format = '';

	/** PAYMENT OPTIONS **/

	public $amount_type = '';

	/* one-time payment options */
	public $amount = '';

	/* custom amount options */
	public $one_time_custom_amount = false;
	public $minimum_amount = '';
	public $default_amount = '';
	public $custom_amount_label = '';

	// Statement descriptor setting
	public $statement_descriptor = '';

	/** CUSTOM FIELDS **/

	// Recurring amount toggle interval
	public $recurring_amount_toggle_frequency = 'month';
	public $recurring_amount_toggle_interval = 1;

	/** CHECKOUT OVERLAY DISPLAY **/

	public $company_name = '';
	public $item_description = '';
	public $image_url = '';
	public $enable_remember_me = '';
	public $checkout_button_text = '';
	public $trial_button_text = '';
	public $verify_zip = '';
	public $enable_billing_address = '';
	public $enable_shipping_address = '';
	public $enable_bitcoin = '';

	/** SUBSCRIPTION OPTIONS **/

	public $subscription_type = '';

	/* Single Plan */
	public $single_plan = '';
	public $plan = '';
	public $plan_interval = '';
	public $plan_interval_count = 1;

	/* Multi-plans */
	public $default_plan = '';
	public $plans = array();
	public $multi_plan_setup_fee = '';

	public $subscription_custom_amount = '';
	public $subscription_minimum_amount = '';
	public $subscription_default_amount = '';
	public $subscription_interval = '';
	public $subscription_frequency = '';
	public $subscription_custom_amount_label = '';
	public $subscription_setup_fee = '';
	public $subscription_display_type = '';
	public $has_max_charges = false;
	public $subscription_max_charges = 0;

	/*****
	 *
	 * OTHER OPTIONS
	 *
	 *****/

	// If the form has a trial or not
	public $is_trial = false;

	// Custom fields
	public $custom_fields = array();

	// Variable to store the tax amount for display
	public $tax_amount = 0;

	public $parent = null;

	// No settings for these, only available with filters
	public $fee_percent = 0;
	public $fee_amount = 0;

	/**
	 * Form constructor.
	 *
	 * @param $id int
	 */
	public function __construct( $id ) {

		add_filter( 'simpay_custom_fields', array( $this, 'pro_print_custom_fields' ), 10, 2 );

		if ( ! has_filter( 'simpay_form_' . absint( $id ) . '_script_variables' ) ) {
			add_filter( 'simpay_form_' . absint( $id ) . '_script_variables', array( $this, 'pro_get_form_script_variables' ), 10, 2 );
		}

		parent::__construct( $id );

		// Set our form specific filter to apply to each setting
		$this->filter = 'simpay_form_' . $this->id;

		// Setup the global settings tied to this form
		$this->pro_set_global_settings();

		// Setup the post meta settings tied to this form
		$this->pro_set_post_meta_settings();


		if ( $this->is_subscription() ) {
			$this->set_plan_settings();
		}

		$this->set_from_sessions();

		add_filter( 'simpay_before_form_close', array( $this, 'pro_html' ) );
	}

	public function pro_html() {

		$html = '';

		// In case they have subscriptions but have not set the custom field for placement we will print it after the other custom fields.
		if ( ! $this->printed_subscriptions && $this->is_subscription() && 'user' === $this->subscription_type ) {
			$html .= $this->print_subscription_options( $this->subscription_custom_amount );
		}

		// Print custom amount field if this is not a subscription (subscription custom amount is handled in the print_subscription() function
		if ( ! $this->printed_custom_amount ) {
			if ( $this->one_time_custom_amount || $this->subscription_custom_amount ) {
				$html .= $this->print_custom_amount();
			}
		}

		if ( $this->is_subscription() ) {
			$html .= '<input type="hidden" name="simpay_multi_plan_id" value="" class="simpay-multi-plan-id" />';
			$html .= '<input type="hidden" name="simpay_multi_plan_setup_fee" value="" class="simpay-multi-plan-setup-fee" />';

			$html .= '<input type="hidden" name="simpay_max_charges" value="" class="simpay-max-charges" />';
		}

		// Add a hidden field to hold the tax value
		if ( $this->tax_percent > 0 && ! $this->is_subscription() ) {
			$html .= '<input type="hidden" name="simpay_tax_amount" value="" class="simpay-tax-amount" />';
		}

		echo $html;
	}

	/**
	 * Print the subscription options
	 *
	 * @param bool $custom_amount If a custom amount is found and should be printed
	 *
	 * @return string
	 */
	public function print_subscription_options( $custom_amount = false ) {

		$html              = '';
		$plan_select_label = simpay_get_saved_meta( $this->id, '_plan_select_form_field_label' );

		if ( 'single' === $this->subscription_type ) {

			if ( $custom_amount ) {
				$html .= $this->print_custom_amount();
			}

		} elseif ( 'user' === $this->subscription_type ) {

			$plans = $this->plans;

			if ( empty( $plans ) ) {
				$html = simpay_admin_error( '<p>' . esc_html__( 'You have not set any plans to choose from.', 'simple-pay' ) . '</p>' );

				$this->printed_subscriptions = true;

				return $html;
			}

			$html .= '<div class="simpay-plan-wrapper simpay-form-control">';

			// Add label
			if ( ! empty( $plan_select_label ) ) {
				$html .= '<p class="simpay-plan-select-label simpay-label-wrap"><label>' . esc_html( $plan_select_label ) . '</label></p>';
			}

			if ( 'radio' === $this->subscription_display_type ) {

				$html .= '<ul class="simpay-multi-plan-radio-group">';

				if ( ! empty( $plans ) && is_array( $plans ) ) {
					foreach ( $plans as $k => $v ) {

						// If $v is not an array skip this one
						if ( ! is_array( $v ) ) {
							continue;
						}

						if ( empty( $this->default_plan ) ) {
							$this->default_plan = $v['select_plan'];
						}

						if ( 'empty' === $v['select_plan'] ) {
							continue;
						}


						if ( isset( $v['plan_object'] ) ) {
							// Use the cached plan object that is set on the form save
							$plan = $v['plan_object'];
						} else {
							// If no cached object is found then revert to calling the Stripe API
							$plan = Plan::get_plan_by_id( $v['select_plan'] );
						}

						if ( ! $plan ) {
							$html .= simpay_admin_error( '<li>' . sprintf( wp_kses( __( 'The plan <strong>%1$s</strong> does not exist.', 'simple-pay' ), array( 'strong' => array() ) ), $v['select_plan'] ) . '</li>' );
							continue;
						}

						// Our plan is good and we can process the rest
						$plan_name           = $plan->name;
						$plan_amount         = $plan->amount;
						$plan_interval       = $plan->interval;
						$plan_interval_count = $plan->interval_count;
						$is_trial            = $plan->trial_period_days > 0 ? true : false;
						$max_charges         = isset( $v['max_charges'] ) && ! empty( $v['max_charges'] ) ? $v['max_charges'] : 0;

						if ( ! empty( $v['custom_label'] ) ) {
							$label = $v['custom_label'];
						} else {
							$label = $plan_name . ' ' . sprintf( _n( '%1$s/%3$s', '%1$s every %2$d %3$ss', $plan_interval_count, 'simple-pay' ), simpay_formatted_amount( $plan_amount, $plan->currency ), $plan_interval_count, $plan_interval );
						}

						$checked = $this->default_plan === $v['select_plan'] ? 'checked' : '';

						if ( 'checked' === $checked ) {
							$this->is_trial = $is_trial;
						}

						$html .= '<li><label><input class="simpay-multi-sub" type="radio" name="simpay_multi_plan_' . esc_attr( $this->id ) . '" value="' . esc_attr( $v['select_plan'] ) . '" data-plan-id="' . esc_attr( $v['select_plan'] ) . '" data-plan-amount="' . intval( $plan_amount ) . '" data-plan-setup-fee="' . esc_attr( simpay_convert_amount_to_cents( $v['setup_fee'] ) ) . '" data-plan-interval="' . esc_attr( $plan_interval ) . '" ' . ( $is_trial ? ' data-plan-trial="true" ' : '' ) . ' data-plan-interval-count="' . esc_attr( $plan_interval_count ) . '" ' . $checked . ' data-plan-max-charges="' . absint( $max_charges ) . '" />' . esc_html( apply_filters( 'simpay_plan_name_label', $label, $plan ) ) . '</label></li>';
					}
				}

				if ( $custom_amount ) {

					$html .= '<li><label><input data-plan-setup-fee="0" type="radio" class="simpay-multi-sub simpay-custom-plan-option" name="simpay_multi_plan_' . esc_attr( $this->id ) . '" data-plan-interval="' . esc_attr( $this->subscription_frequency ) . '" data-plan-interval-count="' . esc_attr( $this->subscription_interval ) . '" value="simpay_custom_plan" />' . esc_html( $this->subscription_custom_amount_label ) . '</label>';
					$html .= $this->print_custom_amount( false );
					$html .= '</li>';
				}

				$html .= '</ul>';

			} elseif ( 'dropdown' === $this->subscription_display_type ) {


				$html .= '<div class="simpay-form-control">';

				$html .= '<select>';

				if ( ! empty( $plans ) && is_array( $plans ) ) {
					foreach ( $plans as $k => $v ) {

						// If $v is not an array we need to skip it
						if ( ! is_array( $v ) ) {
							continue;
						}

						if ( empty( $this->default_plan ) ) {
							$this->default_plan = $v['select_plan'];
						}

						if ( 'empty' === $v['select_plan'] ) {
							continue;
						}

						if ( isset( $v['plan_object'] ) ) {
							// Use the cached plan object that is set on the form save
							$plan = $v['plan_object'];
						} else {
							// If no cached object is found then revert to calling the Stripe API
							$plan = Plan::get_plan_by_id( $v['select_plan'] );
						}

						if ( false === $plan ) {
							$html .= simpay_admin_error( '<li>' . sprintf( wp_kses( __( 'The plan <strong>%1$s</strong> does not exist.', 'simple-pay' ), array( 'strong' => array() ) ), $v['select_plan'] ) . '</li>' );
							continue;
						}

						// Our plan is good and we can process the rest
						$plan_name           = $plan->name;
						$plan_amount         = $plan->amount;
						$plan_interval       = $plan->interval;
						$plan_interval_count = $plan->interval_count;
						$is_trial            = $plan->trial_period_days > 0 ? true : false;
						$max_charges         = isset( $v['max_charges'] ) && ! empty( $v['max_charges'] ) ? $v['max_charges'] : 0;

						if ( ! empty( $v['custom_label'] ) ) {
							$label = $v['custom_label'];
						} else {
							$label = $plan_name . ' ' . sprintf( _n( '%1$s/%3$s', '%1$s every %2$d %3$ss', $plan_interval_count, 'simple-pay' ), simpay_formatted_amount( $plan_amount, $plan->currency ), $plan_interval_count, $plan_interval );
						}

						// This needs to check selected status for dropdown. Bit different than radio
						$selected = $this->default_plan === $v['select_plan'] ? 'selected' : '';

						if ( 'selected' === $selected ) {
							$this->is_trial = $is_trial;
						}

						$html .= '<option class="simpay-multi-sub" name="simpay_multi_plan_' . esc_attr( $this->id ) . '" value="' . esc_attr( $v['select_plan'] ) . '" data-plan-id="' . esc_attr( $v['select_plan'] ) . '" data-plan-amount="' . intval( $plan_amount ) . '" data-plan-setup-fee="' . esc_attr( simpay_convert_amount_to_cents( $v['setup_fee'] ) ) . '" ' . ( $is_trial ? ' data-plan-trial="true" ' : '' ) . ' data-plan-interval="' . esc_attr( $plan_interval ) . '" ' . $selected . ' data-plan-max-charges="' . absint( $max_charges ) . '">' . esc_html( apply_filters( 'simpay_plan_name_label', $label, $plan ) ) . '</option>';
					}
				}

				if ( $custom_amount ) {
					$html .= '<option data-plan-setup-fee="0" name="simpay_multi_plan_' . esc_attr( $this->id ) . '" value="simpay_custom_plan" class="simpay-multi-sub simpay-custom-plan-option" data-plan-interval="' . esc_attr( $this->subscription_frequency ) . '" data-plan-interval-count="' . esc_attr( $this->subscription_interval ) . '">' . esc_html( $this->subscription_custom_amount_label ) . '</option>';
				}

				$html .= '</select>';

				$html .= '</div>';

				if ( $custom_amount ) {
					$html .= $this->print_custom_amount();
				}
			}

			$html .= '</div>';

			// Set flag to know we have printed these
			$this->printed_subscriptions = true;
		}

		return $html;

	}

	/**
	 * Print a custom amount field.
	 *
	 * @param bool $print_wrapper Check if we should print the outer wrapper for the field or not.
	 *
	 * @return string
	 */
	public function print_custom_amount( $print_wrapper = true ) {

		$html = '';

		$sub_min              = $this->subscription_minimum_amount;
		$custom_min           = $this->minimum_amount;
		$min                  = ( $this->is_subscription() ? ( '' !== $sub_min ? $sub_min : simpay_formatted_amount( '100', $this->currency, false, $this->decimal_separator ) ) : ( '' !== $custom_min ? $custom_min : simpay_formatted_amount( '100', $this->currency, false, $this->decimal_separator ) ) );
		$regular_default      = $this->default_amount;
		$subscription_default = $this->subscription_default_amount;
		$position             = $this->currency_position;

		// Setup default value, input name, and label based on if this form is a subscription or not.
		if ( $this->is_subscription() ) {
			$default_value = $subscription_default;
			$input_name    = 'simpay_subscription_custom_amount';
			$label         = 'user' !== $this->subscription_type ? simpay_get_saved_meta( $this->id, '_plan_select_form_field_label' ) : '';
		} else {
			$default_value = $regular_default;
			$input_name    = 'simpay_custom_amount';
			$label         = $this->custom_amount_label;
		}

		// outer wrap div
		if ( $print_wrapper ) {
			$html .= '<div class="simpay-form-control">';
		}

		$field_id = esc_attr( simpay_dashify( $input_name ) ) . '-' . $this->id;

		// Label
		$html .= '<p class="simpay-custom-amount-label simpay-label-wrap">';
		$html .= '<label for="' . esc_attr( $field_id ) . '">' . esc_html( $label ) . '</label>';
		$html .= '</p>';

		// Field output
		$html .= '<p class="simpay-custom-amount-wrap simpay-field-wrap">';

		if ( 'left' === $position || 'left_space' === $position ) {
			$html .= '<span class="simpay-currency-symbol simpay-currency-symbol-left">' . simpay_get_currency_symbol( $this->currency ) . '</span>';
		}

		if ( empty( $default_value ) || simpay_convert_amount_to_cents( $default_value, $this->decimal_separator ) < simpay_convert_amount_to_cents( $min, $this->decimal_separator ) ) {
			$default_value = '';
		}

		$custom_amount_input_type = apply_filters( 'simpay_custom_amount_field_type', 'tel' );
		$custom_amount_input_type = ( $custom_amount_input_type !== 'tel' && $custom_amount_input_type !== 'number' ) ? 'tel' : $custom_amount_input_type;

		$html .= '<input id="' . $field_id . '" name="' . esc_attr( $input_name ) . '" data-error="#simpay-custom-amount-input-' . esc_attr( $this->id ) . '-error" class="simpay-custom-amount-input simpay-amount-input" type="' . esc_attr( $custom_amount_input_type ) . '" value="' . esc_attr( $default_value ) . '" />';

		// If this is a subscription then add a field we can keep track of the custom amount selection
		if ( $this->is_subscription() ) {
			$html .= '<input type="hidden" name="simpay_has_custom_plan" class="simpay-has-custom-plan" value="' . ( 'single' === $this->subscription_type ? 'true' : '' ) . '" />';
		}

		if ( 'right' === $position || 'right_space' === $position ) {
			$html .= '<span class="simpay-currency-symbol simpay-currency-symbol-right">' . simpay_get_currency_symbol( $this->currency ) . '</span>';
		}

		$html .= '</p>';

		// Have error message field printed out if this field is required.
		$html .= '<p class="simpay-field-error" id="simpay-custom-amount-input-' . esc_attr( $this->id ) . '-error"></p>';

		// Close wrapper
		if ( $print_wrapper ) {
			$html .= '</div>';
		}

		// Set flag so we know this was already printed
		$this->printed_custom_amount = true;

		return $html;

	}

	/**
	 * Print out the custom fields.
	 *
	 * @return string
	 */
	public function pro_print_custom_fields( $html, $item ) {

		if ( empty( $item ) ) {
			return '';
		}

		switch ( $item['type'] ) {
			case 'checkbox':
				$html = Fields\Checkbox::html( $item );
				break;
			case 'coupon':
				$html = Fields\Coupon::html( $item );
				break;
			case 'date':
				$html = Fields\Date::html( $item );
				break;
			case 'dropdown':
				$html = Fields\Dropdown::html( $item );
				break;
			case 'number':
				$html = Fields\Number::html( $item );
				break;
			case 'radio':
				$html = Fields\Radio::html( $item );
				break;
			case 'custom_amount':
				if ( $this->one_time_custom_amount ) {
					$html = $this->print_custom_amount();
				}
				break;
			case 'plan_select':
				if ( $this->is_subscription() ) {
					$html = $this->print_subscription_options( $this->subscription_custom_amount );
					Fields\Total_Amount::set_recurring_total( $this->amount );
				}
				break;
			case 'total_amount':

				if ( ! $this->one_time_custom_amount ) {

					if ( $this->is_trial ) {
						Fields\Total_Amount::set_total( $this->subscription_setup_fee );
					} else {
						Fields\Total_Amount::set_total( $this->total_amount );
					}
				} else {
					Fields\Total_Amount::set_total( 0 );
				}

				$html = Fields\Total_Amount::html( $item );
				break;
			case 'text':
				$html = Fields\Text::html( $item );
				break;
			case 'recurring_amount_toggle':
				$html = Fields\Recurring_Amount_Toggle::html( $item );
				break;
			default:

		}

		return $html;
	}

	/**
	 * Check if there are session variables we need and set them now
	 */
	public function set_from_sessions() {

		if ( Session::get( 'trial' ) ) {
			$this->is_trial = Session::get( 'trial' );
		}
	}

	/**
	 * Set the global settings options to the form attributes.
	 */
	public function pro_set_global_settings() {

		// Set all the global settings that have been saved here.
		// Doing this here allows us to make every setting filterable on a per-form basis

		// We have to use simpay_get_filtered() for these since this is the first time setting these values. That's why we can't use something like simpay_get_setting()
		// Basically, think of this as the construction of global $simpay_form, so anything that uses $simpay_form will not work because the global will still be null at this point.

		/** GENERAL **/

		/* Currency Options */
		$this->tax_percent = simpay_get_filtered( 'tax_percent', simpay_get_global_setting( 'tax_percent' ), $this->id );

		/* Date Options */
		$this->date_format = simpay_get_filtered( 'date_format', simpay_get_date_format(), $this->id );

		/** DISPLAY **/

		/* Front-end Styles */
		$this->apply_button_style = simpay_get_filtered( 'apply_button_style', simpay_get_global_setting( 'apply_button_style' ), $this->id );
	}

	/**
	 * Set the form settings options to the form attributes.
	 */
	public function pro_set_post_meta_settings() {

		// Set all the form settings that have been saved here.
		// Doing this here allows us to make every setting filterable on a per-form

		// We have to use simpay_get_filtered() for these since this is the first time setting these values. That's why we can't use something like simpay_get_setting()
		// Basically, think of this as the construction of global $simpay_form, so anything that uses $simpay_form will not work because the global will still be null at this point.

		// Custom Fields sorted by order
		$this->custom_fields = $this->sort_fields( Custom_Fields::get_fields( $this->id ) );

		/** PAYMENT OPTIONS **/

		$this->amount_type = simpay_get_filtered( 'amount_type', simpay_get_saved_meta( $this->id, '_amount_type' ), $this->id );

		$this->one_time_custom_amount = simpay_get_filtered( 'one_time_custom_amount', ( ( 'one_time_custom' === $this->amount_type ) ? true : false ), $this->id );

		/* one-time payment options */

		$this->amount = simpay_convert_amount_to_cents( simpay_get_filtered( 'amount', simpay_get_saved_meta( $this->id, '_amount', '100' ), $this->id ) );

		/* custom amount options */
		$this->minimum_amount      = simpay_get_filtered( 'minimum_amount', simpay_get_saved_meta( $this->id, '_minimum_amount' ), $this->id );
		$this->default_amount      = simpay_formatted_amount( simpay_convert_amount_to_cents( simpay_get_filtered( '_default_amount', simpay_get_saved_meta( $this->id, '_custom_amount_default' ), $this->id ), $this->decimal_separator ), $this->currency, false, $this->decimal_separator );
		$this->custom_amount_label = simpay_get_filtered( 'custom_amount_label', simpay_get_saved_meta( $this->id, '_custom_amount_label' ), $this->id );

		/** CUSTOM FIELD SETTINGS */

		// Recurring amount toggle interval and frequency
		$this->recurring_amount_toggle_interval  = absint( $this->extract_custom_field_setting( 'recurring_amount_toggle', 'plan_interval', 1 ) );
		$this->recurring_amount_toggle_frequency = $this->extract_custom_field_setting( 'recurring_amount_toggle', 'plan_frequency', 'month' );

		/** CHECKOUT OVERLAY DISPLAY **/
		$this->trial_button_text = simpay_get_filtered( 'trial_button_text', simpay_get_saved_meta( $this->id, '_trial_button_text', esc_html__( 'Start Your Free Trial', 'simple-pay' ) ), $this->id );

		/** SUBSCRIPTION OPTIONS **/

		$this->subscription_type = simpay_get_filtered( 'subscription_type', simpay_get_saved_meta( $this->id, '_subscription_type' ), $this->id );

		// Single and multi-plans. Not sure how to handle this just yet so these are just placeholders.
		$this->single_plan = simpay_get_filtered( 'single_plan', simpay_get_saved_meta( $this->id, '_single_plan' ), $this->id );

		// Multi-plan subscription display style (radio, dropdown)
		$this->subscription_display_type = simpay_get_filtered( 'subscription_display_type', simpay_get_saved_meta( $this->id, '_multi_plan_display' ), $this->id );

		// Check if multi plans and set it
		if ( 'user' === $this->subscription_type ) {
			$this->default_plan = simpay_get_filtered( 'default_plan', simpay_get_saved_meta( $this->id, '_multi_plan_default_value' ), $this->id );
			$this->plans        = simpay_get_filtered( 'plans', simpay_get_saved_meta( $this->id, '_multi_plan' ), $this->id );
		}

		$subscription_custom_amount       = simpay_get_filtered( 'subscription_custom_amount', simpay_get_saved_meta( $this->id, '_subscription_custom_amount' ), $this->id );
		$this->subscription_custom_amount = ( 'disabled' !== $subscription_custom_amount && 'disabled' != $this->subscription_type ) ? true : false;

		$this->subscription_minimum_amount      = simpay_get_filtered( 'subscription_minimum_amount', simpay_get_saved_meta( $this->id, '_multi_plan_minimum_amount' ), $this->id );
		$this->subscription_default_amount      = simpay_get_filtered( 'subscription_default_amount', simpay_get_saved_meta( $this->id, '_multi_plan_default_amount' ) );
		$this->subscription_interval            = intval( simpay_get_filtered( 'subscription_interval', simpay_get_saved_meta( $this->id, '_plan_interval' ), $this->id ) );
		$this->subscription_frequency           = simpay_get_filtered( 'subscription_frequency', simpay_get_saved_meta( $this->id, '_plan_frequency' ), $this->id );
		$this->subscription_custom_amount_label = simpay_get_filtered( 'subscription_custom_amount_label', simpay_get_saved_meta( $this->id, '_custom_plan_label', esc_html__( 'Other amount', 'simple-pay' ) ), $this->id );
		$this->subscription_setup_fee           = simpay_convert_amount_to_cents( simpay_get_filtered( 'subscription_setup_fee', simpay_get_saved_meta( $this->id, '_setup_fee' ), $this->id ) );
		$this->subscription_max_charges         = simpay_get_filtered( 'subscription_max_charges', simpay_get_saved_meta( $this->id, '_max_charges', 0 ), $this->id );

		if ( $this->subscription_max_charges > 0 ) {
			$this->has_max_charges = true;
		}

		/** OTHER **/
		$this->fee_percent = floatval( simpay_get_filtered( 'fee_percent', 0, $this->id ) );
		$this->fee_amount  = floatval( simpay_get_filtered( 'fee_amount', 0, $this->id ) );
	}

	/**
	 * Extract the value from a custom field setting if it exists
	 *
	 * @param        $field_type
	 * @param        $setting
	 * @param string $default
	 *
	 * @return string
	 */
	public function extract_custom_field_setting( $field_type, $setting, $default = '' ) {

		if ( is_array( $this->custom_fields ) && ! empty( $this->custom_fields ) ) {
			foreach ( $this->custom_fields as $k => $field ) {
				if ( $field_type === $field['type'] ) {
					return isset( $field[ $setting ] ) ? $field[ $setting ] : $default;
				}
			}
		}

		return $default;
	}

	/**
	 * Set the plan settings
	 */
	public function set_plan_settings() {

		$plans = $this->plans;

		if ( is_array( $plans ) ) {

			foreach ( $plans as $k => $v ) {

				if ( ! is_array( $v ) ) {
					continue;
				}

				if ( empty( $this->default_plan ) || $this->default_plan === 'empty' ) {
					$this->default_plan = $v['select_plan'];
				}

				$is_default = $this->default_plan === $v['select_plan'] ? 'checked' : '';

				if ( $is_default ) {
					$this->multi_plan_setup_fee = $v['setup_fee'];

					// Checked for cached plan and fallback to plan ID
					$plan = isset( $v['plan_object'] ) ? $v['plan_object'] : $v['select_plan'];

					$this->amount = Plan::get_plan_amount( $plan );
					break;
				}
			}
		}

		if ( 'single' === $this->subscription_type ) {
			if ( ! $this->subscription_custom_amount ) {

				$id          = get_post_meta( $this->id, '_single_plan', true );
				$cached_plan = get_post_meta( $this->id, '_single_plan_object', true );

				if ( ! empty( $id ) && 'empty' !== $id ) {

					if ( $cached_plan ) {
						// Use cached plan object if found
						$plan = $cached_plan;
					} else {
						// Default to calling Stripe API for plan if cached not found
						$plan = Plan::get_plan_by_id( $id );
					}

					if ( false !== $plan ) {
						// No need to convert here since Stripe returns it to us as we need it
						$this->plan                   = $plan->id;
						$this->amount                 = $plan->amount;
						$this->subscription_frequency = $plan->interval;
						$this->subscription_interval  = $plan->interval_count;
						$this->is_trial               = Subscription::has_trial( $plan );
					} else {
						echo 'An error occurred ' . $plan;
					}
				} else {
					esc_html_e( 'You have not selected a plan.', 'simple-pay' );
				}
			}
		}

		if ( 'user' === $this->subscription_type ) {
			$this->multi_plan_setup_fee = Session::get( 'simpay_multi_plan_setup_fee' ) ? Session::get( 'simpay_multi_plan_setup_fee' ) : $this->multi_plan_setup_fee;
		}
	}

	/**
	 * Check if this form has subscriptions enabled or not
	 *
	 * @return bool
	 */
	public function is_subscription() {
		return ( 'disabled' !== $this->subscription_type && ! empty( $this->subscription_type ) ? true : false );
	}

	/**
	 * Sort the custom fields by their order
	 *
	 * @param $arr
	 *
	 * @return array|string
	 */
	private function sort_fields( $arr ) {

		// If our array is empty then exit now
		if ( empty( $arr ) ) {
			return '';
		}

		$fields     = $arr;
		$new_fields = array();
		$order      = array();

		if ( is_array( $fields ) ) {
			foreach ( $fields as $key => $row ) {

				if ( is_array( $row ) ) {
					foreach ( $row as $k => $v ) {

						$order[] = isset( $v['order'] ) ? $v['order'] : 9999;

						$v['type']    = $key;
						$new_fields[] = $v;

					}
				}
			}
		}

		array_multisort( $order, SORT_ASC, $new_fields );

		return $new_fields;
	}

	/**
	 * Place to set our script variables for this form.
	 *
	 * @return array
	 */
	public function pro_get_form_script_variables( $arr, $id ) {

		$custom_fields = simpay_get_saved_meta( $this->id, '_custom_fields' );
		$loading_text  = '';

		$form_arr = $arr[ $id ]['form'];

		if ( isset( $custom_fields['payment_button'] ) && is_array( $custom_fields['payment_button'] ) ) {

			foreach ( $custom_fields['payment_button'] as $k => $v ) {
				if ( is_array( $v ) && array_key_exists( 'processing_text', $v ) ) {
					if ( isset( $v['processing_text'] ) && ! empty( $v['processing_text'] ) ) {
						$loading_text = $v['processing_text'];
						break;
					}
				}
			}
		}

		if ( empty( $loading_text ) ) {
			$loading_text = esc_html__( 'Please wait...', 'simple-pay' );
		}

		$bools['bools'] = array_merge( isset( $form_arr['bools'] ) ? $form_arr['bools'] : array(), array(
			'isSubscription' => $this->is_subscription(),
			'isTrial'        => $this->is_trial,
		) );

		$min     = simpay_get_saved_meta( $this->id, '_minimum_amount' );
		$min     = simpay_convert_amount_to_cents( ! empty( $min ) ? $min : ( simpay_is_zero_decimal() ? '100' : '1' ) );
		$sub_min = simpay_get_saved_meta( $this->id, '_multi_plan_minimum_amount' );
		$sub_min = simpay_convert_amount_to_cents( ! empty( $sub_min ) ? $sub_min : ( simpay_is_zero_decimal() ? '100' : '1' ) );

		$integers['integers'] = array_merge( isset( $form_arr['integers'] ) ? $form_arr['integers'] : array(), array(
			'setupFee'          => $this->subscription_setup_fee,
			'minAmount'         => $min,
			'subMinAmount'      => $sub_min,
			'planIntervalCount' => $this->subscription_interval,
			'taxPercent'        => floatval( $this->tax_percent ),
			'feePercent'        => $this->fee_percent,
			'feeAmount'         => $this->fee_amount,
		) );

		$strings['strings'] = array_merge( isset( $form_arr['strings'] ) ? $form_arr['strings'] : array(), array(
			'subscriptionType'    => $this->subscription_type,
			'planInterval'        => $this->subscription_frequency,
			'freeTrialButtonText' => $this->trial_button_text,
			'loadingText'         => $loading_text,
			'dateFormat'          => $this->date_format,
		) );

		$i18n['i18n'] = array_merge( isset( $form_arr['i18n'] ) ? $form_arr['i18n'] : array(), array(
			/* translators: message displayed on front-end for amount below minimum amount for one-time payment custom amount field */
			'minCustomAmountError'    => sprintf( esc_html__( 'The minimum amount allowed is %s', 'simple-pay' ), simpay_formatted_amount( $min ) ),
			/* translators: message displayed on front-end for amount below minimum amount for subscription custom amount field */
			'subMinCustomAmountError' => sprintf( esc_html__( 'The minimum amount allowed is %s', 'simple-pay' ), simpay_formatted_amount( $sub_min ) ),
		) );

		$form_arr = array_merge( $form_arr, $integers, $strings, $bools, $i18n );

		$arr[ $id ]['form'] = $form_arr;

		return $arr;
	}

	/**
	 * Set all the script variables for the Stripe specific settings (the ones Stripe needs for the checkout form)
	 *
	 * @return array
	 */
	public function pro_get_stripe_script_variables() {

		// Key is required so we always include it
		$strings['strings']['key'] = simpay_get_publishable_key();

		// Boolean/dropdown options
		$bools = array(
			'bools' => array(
				'allowRememberMe' => ( $this->enable_remember_me ? true : false ),
			),
		);

		if ( $this->enable_billing_address ) {
			$bools['bools']['billingAddress'] = true;

			// Stripe doesn't like shipping being enabled unless Billing is
			if ( $this->enable_shipping_address ) {
				$bools['bools']['shippingAddress'] = true;
			}
		}

		if ( $this->verify_zip ) {
			$bools['bools']['zipCode'] = true;
		}

		if ( $this->enable_bitcoin ) {
			$bools['bools']['bitcoin'] = true;
		}

		// Optional params if set in the settings only

		// Company name
		if ( ! empty( $this->company_name ) ) {
			$strings['strings']['name'] = $this->company_name;
		}

		// Image URL
		if ( ! empty( $this->image_url ) ) {
			$strings['strings']['image'] = $this->image_url;
		}

		// Locale
		if ( ! empty( $this->locale ) ) {
			$strings['strings']['locale'] = $this->locale;
		}

		// Currency
		if ( ! empty( $this->currency ) ) {
			$strings['strings']['currency'] = $this->currency;
		}

		// Checkout button label (overlay)
		if ( ! empty( $this->checkout_button_text ) ) {
			$strings['strings']['panelLabel'] = $this->checkout_button_text;
		}

		// Item description
		if ( ! empty( $this->item_description ) ) {
			$strings['strings']['description'] = $this->item_description;
		}

		// Return as hookable data
		return apply_filters( 'simpay_stripe_script_variables', array_merge( $strings, $bools ) );
	}
}
