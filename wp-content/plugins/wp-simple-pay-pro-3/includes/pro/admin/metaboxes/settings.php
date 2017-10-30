<?php

namespace SimplePay\Pro\Admin\Metaboxes;

use SimplePay\Core\Payments\Stripe_API;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {


	public function __construct() {

		$this->set_admin_tabs();

		add_action( 'simpay_save_form_settings', array( $this, 'save' ), 0, 2 );

		add_action( 'simpay_amount_options', array( $this, 'add_amount_options_radio' ) );

		add_action( 'simpay_admin_after_amount_options', array( $this, 'add_custom_amount_options' ), 0 );

		add_filter( 'simpay_amount_options_classes', array( $this, 'add_amount_hidden_class' ) );
	}

	public function add_amount_hidden_class( $classes ) {

		global $post;

		$amount_type = simpay_get_saved_meta( $post->ID, '_amount_type', 'one_time_set' );

		$check = ( ( 'one_time_set' !== $amount_type ) || false !== $this->amount_type_disabled() ) ? 'simpay-panel-hidden' : '';

		return $classes . ' ' . $check;
	}

	private function amount_type_disabled() {

		global $post;

		// Check if subscriptions are enabled
		$subscription_type = simpay_get_saved_meta( $post->ID, '_subscription_type', 'disabled' );

		// Use these for checking radio/dropdown amount fields
		$custom_fields        = simpay_get_saved_meta( $post->ID, '_custom_fields' );
		$dropdown_amount      = false;
		$radio_amount         = false;
		$amount_type_disabled = false;

		if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
			// Check for dropdown amount field
			if ( array_key_exists( 'dropdown', $custom_fields ) ) {
				if ( ! empty( $custom_fields['dropdown'] ) && is_array( $custom_fields['dropdown'] ) ) {
					foreach ( $custom_fields['dropdown'] as $k => $v ) {
						if ( is_array( $v ) && array_key_exists( 'amount_quantity', $v ) ) {
							if ( isset( $v['amount_quantity'] ) && 'amount' === $v['amount_quantity'] ) {
								$dropdown_amount = true;
							}
							break;
						}
					}
				}
			}
			// Check for radio amount field
			if ( array_key_exists( 'radio', $custom_fields ) ) {
				if ( ! empty( $custom_fields['radio'] ) && is_array( $custom_fields['radio'] ) ) {
					foreach ( $custom_fields['radio'] as $k => $v ) {
						if ( is_array( $v ) && array_key_exists( 'amount_quantity', $v ) ) {
							if ( isset( $v['amount_quantity'] ) && 'amount' === $v['amount_quantity'] ) {
								$radio_amount = true;
							}
							break;
						}
					}
				}
			}
		}
		if ( 'disabled' !== $subscription_type ) {
			return 'subscription';
		} else if ( $dropdown_amount ) {
			return 'dropdown_amount';
		} else if ( $radio_amount ) {
			return 'radio_amount';
		}

		return false;
	}

	public function add_custom_amount_options() {

		global $post;

		$position = simpay_get_currency_position();

		$amount_type = simpay_get_saved_meta( $post->ID, '_amount_type', 'one_time_set' );

		$amount_type_disabled = false !== $this->amount_type_disabled() ? true : false;

		?>
		<table class="<?php echo ( ( 'one_time_custom' !== $amount_type ) || $amount_type_disabled ) ? 'simpay-panel-hidden' : ''; ?> toggle-_amount_type-one_time_custom">
			<tbody>
			<tr class="simpay-panel-field">
				<th>
					<label for="_minimum_amount"><?php esc_html_e( 'Minimum Custom Amount', 'simple-pay' ); ?></label>
				</th>
				<td>
					<?php if ( 'left' === $position || 'left_space' === $position ) { ?>
						<span class="simpay-currency-symbol simpay-currency-symbol-left"><?php echo simpay_get_saved_currency_symbol(); ?></span>
					<?php } ?>


					<?php
					// Attributes
					$attr = array(
						'min' => simpay_get_stripe_minimum_amount(),
					);
					// Classes
					$classes = array(
						'simpay-field-tiny',
						'simpay-amount-input',
					);
					// Check saved currency and set default to 100 or 1 accordingly and set steps and class
					if ( simpay_is_zero_decimal() ) {
						$minimum_amount = simpay_get_saved_meta( $post->ID, '_minimum_amount', '100' );
					} else {
						$minimum_amount = simpay_get_saved_meta( $post->ID, '_minimum_amount', '1' );
					}
					simpay_print_field( array(
						'type'        => 'standard',
						'subtype'     => 'tel',
						'name'        => '_minimum_amount',
						'id'          => '_minimum_amount',
						'value'       => $minimum_amount,
						'attributes'  => $attr,
						'class'       => $classes,
						'placeholder' => simpay_formatted_amount( '100', simpay_get_setting( 'currency' ), false ),
					) );
					?>

					<?php if ( 'right' === $position || 'right_space' === $position ) { ?>
						<span class="simpay-currency-symbol simpay-currency-symbol-right"><?php echo simpay_get_saved_currency_symbol(); ?></span>
					<?php } ?>
				</td>
			</tr>

			<tr class="simpay-panel-field">
				<th>
					<label for="_custom_amount_default"><?php esc_html_e( 'Default Custom Amount', 'simple-pay' ); ?></label>
				</th>
				<td>
					<?php if ( 'left' === $position || 'left_space' === $position ) { ?>
						<span class="simpay-currency-symbol simpay-currency-symbol-left"><?php echo simpay_get_saved_currency_symbol(); ?></span>
					<?php } ?>


					<?php
					// Attributes
					$attr = array(
						'min' => simpay_get_stripe_minimum_amount(),
					);
					// Classes
					$classes = array(
						'simpay-field-tiny',
						'simpay-amount-input',
					);
					// Set the default amount
					$custom_amount_default = simpay_get_saved_meta( $post->ID, '_custom_amount_default', '' );
					simpay_print_field( array(
						'type'       => 'standard',
						'subtype'    => 'tel',
						'name'       => '_custom_amount_default',
						'id'         => '_custom_amount_default',
						'value'      => $custom_amount_default,
						'attributes' => $attr,
						'class'      => $classes,
						// Description set below
					) );
					?>

					<?php if ( 'right' === $position || 'right_space' === $position ) { ?>
						<span class="simpay-currency-symbol simpay-currency-symbol-right"><?php echo simpay_get_saved_currency_symbol(); ?></span>
					<?php } ?>

					<p class="description">
						<?php esc_html_e( 'The custom amount field will load with this amount set by default.', 'simple-pay' ); ?>
					</p>
				</td>
			</tr>
			</tbody>
		</table>
		<?php
	}

	public function add_amount_options_radio() {

		global $post;
		?>

		<tr class="simpay-panel-field">
			<th>
				<label for="_amount"><?php esc_html_e( 'Amount Type', 'simple-pay' ); ?></label>
			</th>
			<td>
				<?php
				$amount_type = simpay_get_saved_meta( $post->ID, '_amount_type', 'one_time_set' );
				$attr        = array();

				$amount_type_disabled = $this->amount_type_disabled();

				if ( false !== $amount_type_disabled ) {
					$attr['disabled'] = 'disabled';
				}

				simpay_print_field( array(
					'type'       => 'radio',
					'name'       => '_amount_type',
					'id'         => '_amount_type',
					'value'      => $amount_type,
					'class'      => array(
						'simpay-field-text',
						'simpay-multi-toggle',
					),
					'options'    => array(
						'one_time_set'    => esc_html__( 'One-Time Set Amount', 'simple-pay' ),
						'one_time_custom' => esc_html__( 'One-Time Custom Amount', 'simple-pay' ),
					),
					'inline'     => 'inline',
					'attributes' => $attr,
					// Description for this field set below so we can use wp_kses() without clashing with the wp_kses() already being applied to simpay_print_field()
				) );
				?>

				<p class="description">
					<?php
					// Messaging to display if subscriptions capabilities detected (i.e. biz license or higher).
					if ( simpay_subscriptions_enabled() ) {
						printf( wp_kses( __( '<a href="%1$s" class="%2$s" data-show-tab="%3$s">See Subscriptions Options</a> to set a recurring amount.', 'simple-pay' ), array(
							'a' => array(
								'href'          => array(),
								'class'         => array(),
								'data-show-tab' => array(),
							),
						) ), '#', 'simpay-tab-link', 'simpay-subscription_options' );
						echo '<br />';
						// If subscriptions are being used, one-time amounts don't apply, so show warning.
						if ( false !== $amount_type_disabled ) {
							echo '<span class="simpay-important">';
							if ( 'subscription' === $amount_type_disabled ) {
								esc_html_e( 'Subscriptions are currently enabled.', 'simple-pay' );
							} elseif ( 'dropdown_amount' === $amount_type_disabled ) {
								esc_html_e( 'A Dropdown Select custom field using amount is enabled.', 'simple-pay' );
							} elseif ( 'radio_amount' === $amount_type_disabled ) {
								esc_html_e( 'A Radio Button Select custom field using amount is enabled.', 'simple-pay' );
							}
							echo '</span><br />';
						}
					} else {
						// Messaging to display if subscriptions capabilities are not allowed (i.e. personal license).
						printf( wp_kses( __( '<a href="%s" target="_blank">Upgrade your license</a> to connect Stripe subscriptions to your payment forms.', 'simple-pay' ), array(
							'a' => array(
								'href'   => array(),
								'target' => array(),
							),
						) ), simpay_my_account_url( 'form-settings' ) );
					}
					?>
				</p>
			</td>
		</tr>
		<?php
	}

	public function set_admin_tabs() {

		add_filter( 'simpay_form_display_template', function () {
			return SIMPLE_PAY_INC . 'pro/admin/metaboxes/views/tabs/tab-form-display.php';
		} );

		add_filter( 'simpay_subscription_options_template', function ( $file ) {
			if ( simpay_subscriptions_enabled() ) {
				return SIMPLE_PAY_INC . 'pro/admin/metaboxes/views/tabs/tab-subscription-options.php';
			} else {
				return $file;
			}
		} );
	}

	/**
	 * Clears out the empty plans and returns the reformed array
	 *
	 * @param $arr
	 *
	 * @return array
	 */
	public static function clear_empty_plans( $arr ) {

		$temp = array();
		$i    = 1;

		if ( ! empty ( $arr ) && is_array( $arr ) ) {
			foreach ( $arr as $k => $v ) {

				// If the plan is NOT empty then add this to our temp array (skipping the empty ones)
				if ( 'empty' !== $v['select_plan'] ) {
					$temp[ $i ] = $v;
					$i++;
				}
			}
		}

		return $temp;
	}

	/**
	 * Validate and save the meta box fields.
	 *
	 * @since  3.0.0
	 *
	 * @param  int      $post_id
	 * @param  \WP_Post $post
	 *
	 * @return void
	 */
	public static function save( $post_id, $post ) {

		// See what type of currency we are dealing with so we know how to save the values
		$is_zero_decimal = simpay_is_zero_decimal();

		/** Payment Options */

		// Minimum Amount
		if ( $is_zero_decimal ) {
			$minimum_amount = isset( $_POST['_minimum_amount'] ) ? sanitize_text_field( $_POST['_minimum_amount'] ) : ( false !== get_post_meta( $post_id, '_minimum_amount', true ) ? get_post_meta( $post_id, '_minimum_amount', true ) : '100' );
		} else {
			$minimum_amount = isset( $_POST['_minimum_amount'] ) ? sanitize_text_field( $_POST['_minimum_amount'] ) : ( false !== get_post_meta( $post_id, '_minimum_amount', true ) ? get_post_meta( $post_id, '_minimum_amount', true ) : '1' );
		}
		update_post_meta( $post_id, '_minimum_amount', $minimum_amount );

		// Custom Amount Default
		$custom_amount_default = isset( $_POST['_custom_amount_default'] ) ? sanitize_text_field( $_POST['_custom_amount_default'] ) : '';
		update_post_meta( $post_id, '_custom_amount_default', $custom_amount_default );

		// Custom Amount Label
		$custom_amount_label = isset( $_POST['_custom_amount_label'] ) ? sanitize_text_field( $_POST['_custom_amount_label'] ) : '';
		update_post_meta( $post_id, '_custom_amount_label', $custom_amount_label );

		// Plan select Form Field Label
		$form_field_label = isset( $_POST['_plan_select_form_field_label'] ) ? sanitize_text_field( $_POST['_plan_select_form_field_label'] ) : '';
		update_post_meta( $post_id, '_plan_select_form_field_label', $form_field_label );

		// Trial Button Text
		$trial_button_text = isset( $_POST['_trial_button_text'] ) ? sanitize_text_field( $_POST['_trial_button_text'] ) : '';
		update_post_meta( $post_id, '_trial_button_text', $trial_button_text );

		// Enable Billing Address
		$enable_billing_address = isset( $_POST['_enable_billing_address'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_enable_billing_address', $enable_billing_address );

		// Enable Shipping Address
		$enable_shipping_address = isset( $_POST['_enable_shipping_address'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_enable_shipping_address', $enable_shipping_address );

		/** Subscription Options */

		// Subscription Plans
		$subscription_type = isset( $_POST['_subscription_type'] ) ? esc_attr( $_POST['_subscription_type'] ) : 'disabled';
		update_post_meta( $post_id, '_subscription_type', $subscription_type );

		// Select Plan (Single)
		$single_plan = isset( $_POST['_single_plan'] ) ? sanitize_text_field( $_POST['_single_plan'] ) : 'empty';

		if ( 'empty' !== $single_plan ) {

			// Save the entire plan object to it's own post_meta slot
			$single_plan_object = Stripe_API::request( 'Plan', 'retrieve', array( 'id' => $single_plan ) );

			if ( $single_plan_object ) {
				update_post_meta( $post_id, '_single_plan_object', $single_plan_object );
			}
		}
		update_post_meta( $post_id, '_single_plan', $single_plan );

		// Display Style
		$display_style = isset( $_POST['_multi_plan_display'] ) ? esc_attr( $_POST['_multi_plan_display'] ) : 'radio';
		update_post_meta( $post_id, '_multi_plan_display', $display_style );

		// Plan Setup Fee
		if ( $is_zero_decimal ) {
			$setup_fee = isset( $_POST['_setup_fee'] ) ? sanitize_text_field( $_POST['_setup_fee'] ) : '';
		} else {
			$setup_fee = isset( $_POST['_setup_fee'] ) ? sanitize_text_field( $_POST['_setup_fee'] ) : '';
		}
		update_post_meta( $post_id, '_setup_fee', $setup_fee );

		// Max charges
		$max_charges = isset( $_POST['_max_charges'] ) ? absint( $_POST['_max_charges'] ) : 0;
		update_post_meta( $post_id, '_max_charges', $max_charges );

		// Custom Plan Label
		$custom_plan_label = isset( $_POST['_custom_plan_label'] ) ? sanitize_text_field( $_POST['_custom_plan_label'] ) : '';
		update_post_meta( $post_id, '_custom_plan_label', $custom_plan_label );

		// Show Recurring Total Label
		$enable_recurring_total = isset( $_POST['_enable_recurring_total'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_enable_recurring_total', $enable_recurring_total );

		// Stripe Recurring Total Label
		$recurring_total_label = isset( $_POST['_recurring_total_label'] ) ? sanitize_text_field( $_POST['_recurring_total_label'] ) : '';
		update_post_meta( $post_id, '_recurring_total_label', $recurring_total_label );


		// Multi-plans
		$multi_plan = isset( $_POST['_multi_plan'] ) ? self::clear_empty_plans( $_POST['_multi_plan'] ) : array();

		// Add plan_object array slot to each of our multi-plan items that grabs the whole plan object now.
		if ( ! empty( $multi_plan ) && is_array( $multi_plan ) ) {
			foreach ( $multi_plan as $k => &$v ) {

				if ( is_array( $v ) ) {
					foreach ( $v as $k2 => $v2 ) {
						if ( 'select_plan' === $k2 ) {
							$plan = Stripe_API::request( 'Plan', 'retrieve', array( 'id' => $v2 ) );

							if ( $plan ) {
								$v['plan_object'] = $plan;
							}

							break;
						}
					}
				}
			}
		}

		update_post_meta( $post_id, '_multi_plan', $multi_plan );

		// Default radio button value for default plan
		$multi_plan_default_value = isset( $_POST['_multi_plan_default_value'] ) ? $_POST['_multi_plan_default_value'] : array();
		update_post_meta( $post_id, '_multi_plan_default_value', $multi_plan_default_value );


		// Custom Amount (multi-plan)
		$subscription_custom_amount = isset( $_POST['_subscription_custom_amount'] ) ? esc_attr( $_POST['_subscription_custom_amount'] ) : 'disabled';
		update_post_meta( $post_id, '_subscription_custom_amount', $subscription_custom_amount );

		// Minimum Amount
		if ( $is_zero_decimal ) {
			$multi_plan_minimum_amount = isset( $_POST['_multi_plan_minimum_amount'] ) ? sanitize_text_field( $_POST['_multi_plan_minimum_amount'] ) : '';
		} else {
			$multi_plan_minimum_amount = isset( $_POST['_multi_plan_minimum_amount'] ) ? sanitize_text_field( $_POST['_multi_plan_minimum_amount'] ) : '';
		}
		update_post_meta( $post_id, '_multi_plan_minimum_amount', $multi_plan_minimum_amount );

		// Default amount
		$multi_plan_default_amount = isset( $_POST['_multi_plan_default_amount'] ) ? sanitize_text_field( $_POST['_multi_plan_default_amount'] ) : '';
		update_post_meta( $post_id, '_multi_plan_default_amount', $multi_plan_default_amount );

		// Interval
		$plan_interval = isset( $_POST['_plan_interval'] ) ? intval( $_POST['_plan_interval'] ) : '';
		update_post_meta( $post_id, '_plan_interval', $plan_interval );

		// Frequency
		$plan_frequency = isset( $_POST['_plan_frequency'] ) ? esc_attr( $_POST['_plan_frequency'] ) : '';
		update_post_meta( $post_id, '_plan_frequency', $plan_frequency );

		// Save custom fields
		$fields = isset( $_POST['_simpay_custom_field'] ) ? $_POST['_simpay_custom_field'] : array();

		$fields = self::check_missing_fields( $fields, $post_id );

		if ( ! empty( $fields ) && is_array( $fields ) ) {

			$fields = self::update_ids( $fields, $post_id );

			// Re-index the array so if fields were removed we don't overwrite the index with a new field
			foreach ( $fields as $k => $v ) {
				$fields[ $k ] = array_values( $v );
			}

			update_post_meta( $post_id, '_custom_fields', $fields );
		}
	}

	/**
	 * Converts the IDs for the fields before saving
	 */
	private static function update_ids( $arr, $form_id ) {

		if ( ! empty( $arr ) && is_array( $arr ) ) {
			foreach ( $arr as $k => &$v ) {

				if ( ! empty( $v ) && is_array( $v ) ) {
					foreach ( $v as $k2 => &$v2 ) {

						if ( ! empty( $v2 ) && is_array( $v2 ) ) {
							foreach ( $v2 as $k3 => &$v3 ) {
								if ( empty ( $v3 ) ) {
									if ( 'id' === $k3 ) {

										if ( 'payment_button' !== $k ) {
											$v3 = 'simpay_' . $form_id . '_' . $k . '_' . $v2['uid'];
										} else {
											$v3 = 'simpay_' . $form_id . '_' . $k;
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return $arr;
	}

	/**
	 * Fires on form save and checks for missing fields to keep things easy for users. Example: adds a payment button
	 * if one was not added.
	 *
	 * @param $fields
	 * @param $form_id
	 *
	 * @return array
	 */
	private static function check_missing_fields( $fields, $form_id ) {

		$has_button = false;
		$use_custom = false;
		$has_custom = false;
		$use_sub    = false;
		$has_sub    = false;

		$total_count             = 1;
		$payment_button_position = 1;

		if ( isset( $_POST['_amount_type'] ) && 'one_time_custom' === sanitize_text_field( $_POST['_amount_type'] ) ) {
			$use_custom = true;
		}
		if ( isset( $_POST['_subscription_type'] ) && 'user' === sanitize_text_field( $_POST['_subscription_type'] ) || isset( $_POST['_subscription_custom_amount'] ) && 'enabled' === sanitize_text_field( $_POST['_subscription_custom_amount'] ) ) {
			$use_sub = true;
		}

		if ( ! empty( $fields ) && is_array( $fields ) ) {
			foreach ( $fields as $type => $values ) {

				$total_count += count( $values );

				if ( 'payment_button' === $type ) {
					$has_button              = true;
					$payment_button_position = $total_count;
				}

				if ( 'custom_amount' === $type ) {
					$has_custom = true;
				}

				if ( 'plan_select' === $type ) {
					$has_sub = true;
				}
			}
		}

		if ( ! $has_custom && $use_custom ) {

			if ( $has_button ) {
				$position = $payment_button_position - 2;
			} else {
				$position = $total_count;
			}

			$fields['custom_amount'][] = array(
				'order'           => $position,
				'uid'             => $total_count,
				'id'              => 'simpay_' . $form_id . '_custom_amount_' . $total_count,
				'text'            => '',
				'processing_text' => '',
			);

			$total_count++;
		}

		if ( ! $has_sub && $use_sub ) {

			if ( $has_button ) {
				$position = $payment_button_position - 2;
			} else {
				$position = $total_count;
			}

			$fields['plan_select'][] = array(
				'order'           => $position,
				'uid'             => $total_count,
				'id'              => 'simpay_' . $form_id . '_plan_select_' . $total_count,
				'text'            => '',
				'processing_text' => '',
			);

			$total_count++;
		}

		if ( ! $has_button ) {

			// Add a Payment Button field
			$fields['payment_button'][] = array(
				'order'           => $total_count,
				'uid'             => $total_count,
				'id'              => 'simpay_' . $form_id . '_payment_button',
				'text'            => '',
				'processing_text' => '',
			);
		}

		return $fields;
	}
}
