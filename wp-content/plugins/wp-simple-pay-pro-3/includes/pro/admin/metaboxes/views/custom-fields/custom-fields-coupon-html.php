<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Do intval on counter here so we don't have to run it each time we use it below. Saves some function calls.
$counter = absint( $counter );

?>

<!-- Hidden ID Field -->
<tr class="simpay-panel-field">
	<th>
		<?php esc_html_e( 'Field ID:', 'simple-pay' ); ?>
	</th>
	<td>
		<?php
		echo absint( $uid );

		simpay_print_field( array(
			'type'       => 'standard',
			'subtype'    => 'hidden',
			'name'       => '_simpay_custom_field[coupon][' . $counter . '][id]',
			'id'         => 'simpay-coupon-id-' . $counter,
			'value'      => isset( $field['id'] ) ? $field['id'] : '',
			'attributes' => array(
				'data-field-key' => $counter,
			),
		) );
		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-coupon-label-' . $counter; ?>"><?php esc_html_e( 'Form Field Label', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field( array(
			'type'        => 'standard',
			'subtype'     => 'text',
			'name'        => '_simpay_custom_field[coupon][' . $counter . '][label]',
			'id'          => 'simpay-coupon-label-' . $counter,
			'value'       => isset( $field['label'] ) ? $field['label'] : '',
			'class'       => array(
				'simpay-field-text',
				'simpay-label-input',
			),
			'attributes'  => array(
				'data-field-key' => $counter,
			),
			'description' => simpay_form_field_label_description(),
		) );

		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-coupon-placeholder-' . $counter; ?>"><?php esc_html_e( 'Placeholder Value', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field( array(
			'type'       => 'standard',
			'subtype'    => 'text',
			'name'       => '_simpay_custom_field[coupon][' . $counter . '][placeholder]',
			'id'         => 'simpay-coupon-placeholder-' . $counter,
			'value'      => isset( $field['placeholder'] ) ? $field['placeholder'] : '',
			'class'      => array(
				'simpay-field-text',
			),
			'attributes' => array(
				'data-field-key' => $counter,
			),
		) );

		?>
	</td>
</tr>
