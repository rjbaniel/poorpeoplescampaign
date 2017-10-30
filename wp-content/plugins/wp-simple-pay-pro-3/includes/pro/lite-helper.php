<?php

namespace SimplePay\Pro;

use SimplePay\Pro\Forms\Pro_Form;
use SimplePay\Pro\Payments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Lite_Helper {

	public function __construct() {

		if ( is_admin() ) {
			// Remove the upgrade sidebar from settings pages
			add_filter( 'simpay_settings_sidebar_template', array( $this, 'remove_sidebar' ) );

			// Add a docs links
			add_action( 'simpay_admin_after_payment_options', array( $this, 'payment_options_docs_link' ), 999 );
			add_action( 'simpay_admin_after_overlay_display', array( $this, 'overlay_display_docs_link' ), 999 );

			// Add the trial button text option back to the form overlay display tab
			add_action( 'simpay_after_checkout_button_text', array( $this, 'add_trial_button_text_field' ) );

			// Add general settings fields
			add_filter( 'simpay_add_settings_general_fields', array( $this, 'general_settings' ) );

			// Update SSL notice message
			add_filter( 'simpay_ssl_admin_notice_message', array( $this, 'pro_ssl_message' ) );

			// Add {tax-amount} description field back to payment details screen.
			add_filter( 'simpay_payment_details_tag_descriptions', array( $this, 'simpay_tax_amount_description' ) );

			add_filter( 'simpay_upgrade_link', array( $this, 'pro_upgrade_link' ) );

		}

		// Process the metadata
		add_action( 'simpay_process_form', array( $this, 'add_metadata' ) );

		// Change the payment handler to the Pro version
		add_filter( 'simpay_payment_handler', array( $this, 'pro_payment_handler' ), 10, 3 );

		// Clear Pro session data
		add_action( 'simpay_clear_sessions', array( $this, 'pro_clear_session_data' ) );

		// Add default editor templates for payment details
		add_filter( 'simpay_editor_template', array( $this, 'add_default_templates' ), 10, 2 );

		add_filter( 'simpay_utm_campaign', array( $this, 'pro_ga_campaign' ) );

		// Load the pro shared script variables
		add_filter( 'simpay_shared_script_variables', array( $this, 'pro_shared_script_variables' ), 11 );

		// We need to make our object factory use the Pro_Form and not the Default_Form for form objects.
		add_filter( 'simpay_form_object_type', array( $this, 'pro_form_object' ) );
		add_filter( 'simpay_form_namespace', array( $this, 'pro_object_namespace' ) );

		// Use Pro form instead of Default_Form
		add_filter( 'simpay_form_view', array( $this, 'load_pro_form' ), 10, 2 );
	}

	public function load_pro_form( $view, $id ) {

		return new Pro_Form( $id );
	}

	public function pro_object_namespace() {
		return 'SimplePay\\Pro';
	}

	public function pro_form_object() {
		return 'pro-form';
	}

	public function pro_ga_campaign() {
		return 'pro-plugin';
	}

	public function pro_upgrade_link( $link ) {
		return simpay_ga_url( simpay_get_url( 'my-account' ), 'under-box-promo' );
	}

	public function add_default_templates( $template, $editor ) {

		switch ( $editor ) {
			case 'subscription':
				$html = __( 'Thanks for your purchase. Here are the details of your payment:', 'simple-pay' ) . "\n\n";
				$html .= '<strong>' . esc_html__( 'Item:', 'simple-pay' ) . '</strong>' . ' {item-description}' . "\n";
				$html .= '<strong>' . esc_html__( 'Purchased From:', 'simple-pay' ) . '</strong>' . ' {company-name}' . "\n";
				$html .= '<strong>' . esc_html__( 'Payment Date:', 'simple-pay' ) . '</strong>' . ' {charge-date}' . "\n";
				$html .= '<strong>' . esc_html__( 'Initial Payment Amount:', 'simple-pay' ) . '</strong>' . ' {total-amount}' . "\n";
				$html .= '<strong>' . esc_html__( 'Recurring Payment Amount: ', 'simple-pay' ) . '</strong>' . '{recurring-amount}' . "\n";

				return $html;
			case 'trial':
				$html = __( 'Thanks for subscribing. Your card will not be charged until your free trial ends.', 'simple-pay' ) . "\n\n";
				$html .= '<strong>' . esc_html__( 'Item:', 'simple-pay' ) . '</strong>' . ' {item-description}' . "\n";
				$html .= '<strong>' . esc_html__( 'Purchased From:', 'simple-pay' ) . '</strong>' . ' {company-name}' . "\n";
				$html .= '<strong>' . esc_html__( 'Trial End Date:', 'simple-pay' ) . '</strong>' . ' {trial-end-date}' . "\n";
				$html .= '<strong>' . esc_html__( 'Recurring Payment Amount: ', 'simple-pay' ) . '</strong>' . '{recurring-amount}' . "\n";

				return $html;
			default:
				return $template;
		}
	}

	public function pro_clear_session_data() {

		global $wp_session;

		$wp_session['simpay_multi_plan_setup_fee'] = '';
		$wp_session['subscription_id']             = '';
		$wp_session['trial']                       = '';
	}

	public function simpay_tax_amount_description( $html ) {
		$html .= '<p><code>{tax-amount}</code> - ' . esc_html__( 'The calculated tax amount based on the total and the tax percent setting.', 'simple-pay' ) . '</p>';

		return $html;
	}

	public function pro_payment_handler( $old, $form, $action ) {
		return new Payments\Payment( $form, $action );
	}

	public function add_metadata( $payment ) {

		if ( isset( $_POST['simpay_field'] ) ) {

			$arr  = $_POST['simpay_field'];
			$meta = array();

			if ( ! empty( $arr ) && is_array( $arr ) ) {
				foreach ( $arr as $k => $v ) {
					if ( '' !== $v ) {
						// Truncate metadata key to 40 characters and value to 500 characters.
						// https://stripe.com/docs/api#metadata
						$k          = simpay_truncate_metadata( 'title', $k );
						$v          = simpay_truncate_metadata( 'description', $v );
						$meta[ $k ] = $v;
					}
				}
				// Check for coupon field too since we can't get it from simpay_field because the value is cleared when applied
				if ( isset( $_POST['simpay_coupon'] ) && ! empty( $_POST['simpay_coupon'] ) ) {
					if ( isset( $_POST['simpay_coupon_details'] ) && ! empty( $_POST['simpay_coupon_details'] ) ) {
						$meta['coupon_code'] = simpay_truncate_metadata( 'description', sanitize_text_field( $_POST['simpay_coupon_details'] ) );
					} else {
						$meta['coupon_code'] = simpay_truncate_metadata( 'description', sanitize_text_field( $_POST['simpay_coupon'] ) );
					}
				}
			}
			// Set our metadata
			$payment->metadata = $meta;
		}
	}

	public function pro_ssl_message() {
		return sprintf( wp_kses( __( 'SSL (HTTPS) is not enabled. You will not be able to process live Stripe transactions until SSL is enabled. <a href="%1$s" target="_blank">Review the system requirements</a> for WP Simple Pay Pro.', 'simple-pay' ), array(
			'a' => array(
				'href'   => array(),
				'target' => array(),
			),
		) ), simpay_docs_link( '', 'system-requirements-wp-simple-pay-pro', 'wp-admin', true ) );
	}

	/**
	 * This function is used to insert a setting at a specific location based on the associative key.
	 *
	 * @param $new_key  string The new key to use for $fields[ $section ][ $new_key ]
	 * @param $value    array The array that holds the information for this settings array
	 * @param $needle   string The key to find in the current array of fields
	 * @param $haystack array The current array to search
	 *
	 * @return array
	 */
	private function insert_after_key( $new_key, $value, $needle, $haystack ) {

		$split = array(); // The split off portion of the array after the key we want to insert after
		$new   = array(); // The new array will consist of the opposite of the split + the new element we want to add

		if ( array_key_exists( $needle, $haystack ) ) {
			$offset = array_search( $needle, array_keys( $haystack ) );

			$split = array_slice( $haystack, $offset + 1 );
			$new   = array_slice( $haystack, 0, $offset + 1 );

			// Add the new element to the bottom
			$new[ $new_key ] = $value;
		}

		return $new + $split;
	}

	public function general_settings( $fields ) {

		$id           = 'general';
		$option_group = 'settings';
		$section      = 'general';
		$values       = get_option( 'simpay_' . $option_group . '_' . $id );

		// General settings
		$new = array(
			'title'       => esc_html__( 'Date Format', 'simple-pay' ),
			'type'        => 'standard',
			'subtype'     => 'text',
			'name'        => 'simpay_' . $option_group . '_' . $id . '[' . $section . '][date_format]',
			'id'          => 'simpay-' . $option_group . '-' . $id . '-' . $section . '-date-format',
			'value'       => $this->get_option_value( $values, $section, 'date_format' ),
			'description' => sprintf( wp_kses( __( '<a href="%s" target="_blank">Date format options</a> (uses jQuery UI Datepicker)', 'simple-pay' ), array(
				'a' => array(
					'href'   => array(),
					'target' => array(),
				),
			) ), 'http://api.jqueryui.com/datepicker/#utility-formatDate' ),
			'class'       => array(
				'simpay-medium-text',
			),
			'default'     => 'mm/dd/yy',
			'placeholder' => 'mm/dd/yy',
		);

		$fields[ $section ] = $this->insert_after_key( 'date_format', $new, 'failure_page', $fields[ $section ] );

		// Currency options
		$section = 'general_currency';

		$new = array(
			'title'       => esc_html__( 'Tax Rate Percentage', 'simple-pay' ),
			'type'        => 'standard',
			'subtype'     => 'number',
			'name'        => 'simpay_' . $option_group . '_' . $id . '[' . $section . '][tax_percent]',
			'id'          => 'simpay-' . $option_group . '-' . $id . '-' . $section . '-tax-percent',
			'value'       => $this->get_option_value( $values, $section, 'tax_percent' ),
			'attributes'  => array(
				'min'  => 0,
				'max'  => 100,
				'step' => 'any',
			),
			'class'       => array(
				'simpay-small-text',
				'simpay-tax-percent-field',
			),
			'description' => esc_html__( 'Enter a tax rate as a percentage to add to the charged amount (i.e. for 7.5% tax enter 7.5).', 'simple-pay' ),
		);

		$fields[ $section ] = $this->insert_after_key( 'tax_percent', $new, 'currency_position', $fields[ $section ] );

		// Styles
		$section = 'styles';

		$new = array(
			'title'   => esc_html__( 'Coupon Apply Button Style', 'simple-pay' ),
			'type'    => 'radio',
			'options' => array(
				'stripe' => esc_html__( 'Stripe blue', 'simple-pay' ),
				'none'   => esc_html__( 'None (inherit from theme)', 'simple-pay' ),
			),
			'inline'  => 'inline',
			'default' => 'none',
			'name'    => 'simpay_' . $option_group . '_' . $id . '[' . $section . '][apply_button_style]',
			'id'      => 'simpay-' . $option_group . '-' . $id . '-' . $section . '-apply-button-style',
			'value'   => $this->get_option_value( $values, $section, 'apply_button_style' ),
		);

		$fields[ $section ] = $this->insert_after_key( 'apply_button_style', $new, 'payment_button_style', $fields[ $section ] );

		return $fields;
	}

	private function get_option_value( $values, $section, $setting ) {

		$option = $values;

		if ( ! empty( $option ) && is_array( $option ) ) {
			return isset( $option[ $section ][ $setting ] ) ? $option[ $section ][ $setting ] : '';
		}

		return '';
	}

	public function pro_shared_script_variables( $arr ) {


		$i18n['i18n'] = array_merge( isset( $arr['i18n'] ) ? $arr['i18n'] : array(), array(
			'limitPaymentButtonField'         => esc_html__( 'You may only add one Payment Button field per form.', 'simple-pay' ),
			'limitCustomAmountField'          => esc_html__( 'You may only add one Custom Amount Field per form.', 'simple-pay' ),
			'limitPlanSelectField'            => esc_html__( 'You may only add one Subscription Plan Select field per form.', 'simple-pay' ),
			'limitCouponField'                => esc_html__( 'You may only add one Coupon field per form.', 'simple-pay' ),
			'limitRecurringAmountToggleField' => esc_html__( 'You may only add one Recurring Amount Toggle field per form', 'simple-pay' ),
			'limitMaxFields'                  => esc_html__( 'The maximum number of fields is 20.', 'simple-pay' ),
			'couponPercentOffText'            => esc_html_x( '% off', 'This is for the coupon percent off text on the frontend. i.e. 10% off', 'simple-pay' ),
			'couponAmountOffText'             => esc_html_x( 'off', 'This is for coupon amount off on the frontend. i.e. $3.00 off', 'simple-pay' ),
		) );

		$integers['integers'] = array_merge( isset( $arr['integers'] ) ? $arr['integers'] : array(), array(
			'minAmount' => simpay_is_zero_decimal() ? 50 : 1,
		) );

		return array_merge( $arr, $i18n, $integers );
	}

	/**
	 * Add the docs link to the payment options form settings tab
	 */
	public function payment_options_docs_link() {
		echo simpay_docs_link( __( 'Help docs for Payment Options', 'simple-pay' ), 'payment-options', 'form-settings' );
	}

	/**
	 * Add the docs link to the checkout overlay display form settings tab
	 */
	public function overlay_display_docs_link() {
		echo simpay_docs_link( __( 'Help docs for Stripe Checkout Overlay Display', 'simple-pay' ), 'stripe-checkout-overlay-display-options', 'form-settings' );
	}

	public function remove_sidebar() {
		return '';
	}

	public function add_trial_button_text_field() {

		global $post;

		if ( simpay_subscriptions_enabled() ) { ?>
			<tr class="simpay-panel-field">
				<th>
					<label for="_trial_button_text"><?php esc_html_e( 'Trial Button Text', 'simple-pay' ); ?></label>
				</th>
				<td>
					<?php

					simpay_print_field( array(
						'type'        => 'standard',
						'subtype'     => 'text',
						'name'        => '_trial_button_text',
						'id'          => '_trial_button_text',
						'value'       => simpay_get_saved_meta( $post->ID, '_trial_button_text' ),
						'class'       => array(
							'simpay-field-text',
							'simpay-label-input',
						),
						'placeholder' => esc_attr__( 'Start Your Free Trial', 'simple-pay' ),
						'description' => sprintf( esc_html__( "Text used for the final checkout button on the overlay (not the on-page payment button). Only applies to subscription plans with a free trial.", 'simple-pay' ), '{{amount}}', '{{amount}}' ),
					) );
					?>
				</td>
			</tr>
		<?php }
	}
}
