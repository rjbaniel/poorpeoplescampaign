<?php

namespace SimplePay\Pro\Admin\Metaboxes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that contains everything for the custom fields meta boxes UI
 */
class Custom_Fields {

	/**
	 * Custom_Fields constructor.
	 */
	public function __construct() {

		self::html();
	}

	/**
	 * Get the custom fields post meta
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public static function get_fields( $post_id ) {

		$fields = get_post_meta( $post_id, '_custom_fields', true );

		return $fields;
	}

	/**
	 * Get the available custom field options
	 *
	 * @return array
	 */
	public static function get_options() {

		return apply_filters( 'simpay_custom_field_options', array(
			'text'                    => array(
				'label' => esc_html__( 'Text Field', 'simple-pay' ),
				'type'  => 'text',
			),
			'dropdown'                => array(
				'label' => esc_html__( 'Dropdown Select', 'simple-pay' ),
				'type'  => 'dropdown',
			),
			'radio'                   => array(
				'label' => esc_html__( 'Radio Button Select', 'simple-pay' ),
				'type'  => 'radio',
			),
			'date'                    => array(
				'label' => esc_html__( 'Date Field', 'simple-pay' ),
				'type'  => 'date',
			),
			'number'                  => array(
				'label' => esc_html__( 'Number Field', 'simple-pay' ),
				'type'  => 'number',
			),
			'checkbox'                => array(
				'label' => esc_html__( 'Checkbox', 'simple-pay' ),
				'type'  => 'checkbox',
			),
			'coupon'                  => array(
				'label' => esc_html__( 'Coupon Field', 'simple-pay' ),
				'type'  => 'coupon',
			),
			'custom_amount'           => array(
				'label' => esc_html__( 'Custom Amount Field', 'simple-pay' ),
				'type'  => 'custom_amount',
			),
			'plan_select'             => array(
				'label' => esc_html__( 'Subscription Plan Select', 'simple-pay' ),
				'type'  => 'plan_select',
			),
			'recurring_amount_toggle' => array(
				'label' => esc_html( 'Recurring Amount Toggle', 'simple-pay' ),
				'type'  => 'recurring_amount_toggle',
			),
			'total_amount'            => array(
				'label' => esc_html__( 'Total Amount Label', 'simple-pay' ),
				'type'  => 'total_amount',
			),
			'payment_button'          => array(
				'label' => esc_html__( 'Payment Button', 'simple-pay' ),
				'type'  => 'payment_button',
			),
		) );
	}

	/**
	 * Output the UI
	 */
	public static function html() {

		global $post;

		$options = self::get_options();

		if ( ! simpay_subscriptions_enabled() ) {

			// If no subscription capabilities remove plan select option.
			unset( $options['plan_select'] );

			// Remove recurring amount toggle
			unset( $options['recurring_amount_toggle'] );
		}

		?>

		<div id="simpay-custom-fields-wrap" class="panel simpay-metaboxes-wrapper">
			<div class="toolbar toolbar-top">

				<label for="custom-field-select"><?php esc_html_e( 'Add a field', 'simple-pay' ); ?>: </label>

				<select name="simpay_field_select" id="custom-field-select" class="simpay-field-select">
					<option value="">-- <?php esc_html_e( 'Select', 'simple-pay' ); ?> --</option>
					<?php
					if ( ! empty( $options ) && is_array( $options ) ) {
						foreach ( $options as $option ) {
							echo '<option value="' . esc_attr( $option['type'] ) . '" data-counter="' . esc_attr( self::get_counter() ) . '">' . esc_html( $option['label'] ) . '</option>';
						}
					}
					?>
				</select>
				<button type="button" class="button add-field"><?php esc_html_e( 'Add', 'simple-pay' ); ?></button>
			</div>
			<div class="simpay-custom-fields simpay-metaboxes ui-sortable">
				<?php

				// Print the meta boxes according to saved order

				$fields = self::get_fields( $post->ID );

				if ( ! empty( $fields ) && is_array( $fields ) ) {
					foreach ( $fields as $key => $v ) {
						foreach ( $v as $k2 => $field ) {

							$order   = isset( $field['order'] ) ? intval( $field['order'] ) : 1;
							$counter = intval( $k2 ) + 1;
							$uid     = isset( $field['uid'] ) ? intval( $field['uid'] ) : $counter;

							$key = sanitize_key( $key );


							// We use a different way of saving the custom amount label so we need to grab that post meta here so it will show the label in the custom field drag n drop header
							if ( 'custom_amount' === $key ) {

								$label = simpay_get_saved_meta( $post->ID, '_custom_amount_label' );

								$field['label'] = $label;
							} elseif ( 'plan_select' === $key ) {

								$label = simpay_get_saved_meta( $post->ID, '_plan_select_form_field_label' );

								$field['label'] = $label;
							}

							self::print_custom_field( $key, $order, $counter, $uid, $field );
						}
					}
				}

				?>
			</div>
		</div>

		<?php

		wp_nonce_field( 'simpay_custom_fields_nonce', 'simpay_custom_fields_nonce' );

		do_action( 'simpay_custom_field_panel' );
	}

	/**
	 * Function to get the current counter for a certain type of custom field.
	 */
	public static function get_counter() {

		global $post;

		$counter = 0;

		$fields = self::get_fields( $post->ID );

		if ( empty( $fields ) ) {
			return 0;
		}

		if ( is_array( $fields ) ) {
			foreach ( $fields as $k => $v ) {
				$counter = $counter + count( $v );
			}
		}

		return intval( $counter );
	}

	/**
	 * Print out a custom field inside the admin
	 *
	 * @param       $key
	 * @param       $order
	 * @param       $counter
	 * @param       $uid
	 * @param array $field
	 */
	public static function print_custom_field( $key, $order, $counter, $uid, $field = array() ) {

		$options = self::get_options();


		?>

		<div class="simpay-field-metabox simpay-metabox closed simpay-custom-field-<?php echo simpay_dashify( $key ); ?>" rel="<?php echo $order; ?>">
			<h3>
				<div class="handlediv" title="<?php esc_attr_e( 'Click to toggle', 'simple-pay' ); ?>"></div>
				<div class="simpay-field-type"><?php echo $options[ $key ]['label']; ?></div>
				<span class="custom-field-dashicon dashicons dashicons-menu"></span><strong class=""><?php echo isset( $field['label'] ) && ! empty( $field['label'] ) ? $field['label'] : $options[ $key ]['label']; ?></strong>
			</h3>
			<div class="simpay-field-data simpay-metabox-content">

				<!-- Hidden fields to keep track of the order and the unique ID -->
				<input type="hidden" name="<?php echo '_simpay_custom_field[' . $key . '][' . esc_attr( $counter ) . '][order]'; ?>" class="field-order"
				       value="<?php echo esc_attr( $order ); ?>" />
				<input type="hidden" name="<?php echo '_simpay_custom_field[' . $key . '][' . esc_attr( $counter ) . '][uid]'; ?>" class="field-uid"
				       value="<?php echo esc_attr( $uid ); ?>" />

				<table>

					<?php

					include( 'views/custom-fields/custom-fields-' . simpay_dashify( $key ) . '-html.php' );

					do_action( 'simpay_after_' . $key . '_meta' );

					?>

					<tr>
						<td colspan="2">
							<a href="#" class="simpay-remove-field-link"><?php esc_html_e( 'Remove Field', 'simple-pay' ); ?></a>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<?php
	}
}
