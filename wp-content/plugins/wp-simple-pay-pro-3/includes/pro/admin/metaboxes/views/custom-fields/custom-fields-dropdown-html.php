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
			'name'       => '_simpay_custom_field[dropdown][' . $counter . '][id]',
			'id'         => 'simpay-dropdown-id-' . $counter,
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
		<label for="<?php echo 'simpay-dropdown-label-' . $counter; ?>"><?php esc_html_e( 'Form Field Label', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field( array(
			'type'        => 'standard',
			'subtype'     => 'text',
			'name'        => '_simpay_custom_field[dropdown][' . $counter . '][label]',
			'id'          => 'simpay-dropdown-label-' . $counter,
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
		<label for="<?php echo 'simpay-dropdown-required-' . $counter; ?>"><?php esc_html_e( 'Required Field', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field( array(
			'type'       => 'checkbox',
			'name'       => '_simpay_custom_field[dropdown][' . $counter . '][required]',
			'id'         => 'simpay-dropdown-required-' . $counter,
			'value'      => isset( $field['required'] ) ? $field['required'] : '',
			'attributes' => array(
				'data-field-key' => $counter,
			),
		) );

		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-dropdown-options-' . $counter; ?>"><?php esc_html_e( 'Options', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field( array(
			'type'       => 'standard',
			'subtype'    => 'text',
			'name'       => '_simpay_custom_field[dropdown][' . $counter . '][options]',
			'id'         => 'simpay-dropdown-options-' . $counter,
			'value'      => isset( $field['options'] ) ? $field['options'] : '',
			'class'      => array(
				'simpay-field-text',
			),
			'attributes' => array(
				'data-field-key' => $counter,
			),
		) );

		?>

		<p class="description">
			<?php esc_html_e( 'A comma separated list of options to fill the dropdown with.', 'simple-pay' ); ?>
		</p>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-dropdown-default-' . $counter; ?>"><?php esc_html_e( 'Default Value', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field( array(
			'type'       => 'standard',
			'subtype'    => 'text',
			'name'       => '_simpay_custom_field[dropdown][' . $counter . '][default]',
			'id'         => 'simpay-dropdown-default-' . $counter,
			'value'      => isset( $field['default'] ) ? $field['default'] : '',
			'class'      => array(
				'simpay-field-text',
			),
			'attributes' => array(
				'data-field-key' => $counter,
			),
		) );

		?>

		<p class="description">
			<?php esc_html_e( 'Option to be selected by default. Will be first in list if left blank or no match.', 'simple-pay' ); ?>
		</p>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-dropdown-type-' . $counter; ?>"><?php esc_html_e( 'Quantity or Amount Field', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		$type = isset( $field['amount_quantity'] ) && ! empty( $field['amount_quantity'] ) ? $field['amount_quantity'] : 'not_used';

		simpay_print_field( array(
			'type'       => 'radio',
			'name'       => '_simpay_custom_field[dropdown][' . $counter . '][amount_quantity]',
			'id'         => 'simpay-dropdown-amount-quantity-' . $counter,
			'value'      => $type,
			'attributes' => array(
				'data-disable-amount-single' => 'amount',
			),
			'class'      => array(
				'simpay-multi-toggle',
				'simpay-disable-amount',
			),
			'options'    => array(
				'not_used' => esc_html__( 'Not Used', 'simple-pay' ),
				'quantity' => esc_html__( 'Quantity (Multiplier)', 'simple-pay' ),
				'amount'   => esc_html__( 'Amount (User Selects)', 'simple-pay' ),
			),
			'inline'     => 'inline',
		) );
		?>
	</td>
</tr>

<tr class="simpay-panel-field simpay-dropdown-quantities-wrap <?php echo( 'quantity' !== $type ? 'simpay-panel-hidden' : '' ); ?> toggle-simpay-dropdown-amount-quantity-<?php echo $counter; ?>-quantity">
	<th>
		<label for="<?php echo 'simpay-dropdown-quantities-' . $counter; ?>"><?php esc_html_e( 'Quantities', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field( array(
			'type'       => 'standard',
			'subtype'    => 'text',
			'name'       => '_simpay_custom_field[dropdown][' . $counter . '][quantities]',
			'id'         => 'simpay-dropdown-quantities-' . $counter,
			'value'      => isset( $field['quantities'] ) ? $field['quantities'] : '',
			'class'      => array(
				'simpay-field-text',
			),
			'attributes' => array(
				'data-field-key' => $counter,
			),
		) );

		?>

		<p class="description">
			<?php esc_html_e( 'A comma separated list of quantity values to multiply the amount by (i.e. "1,5,10"). Needs to match the number of options and their order.', 'simple-pay' ); ?>
		</p>
	</td>
</tr>

<tr class="simpay-panel-field simpay-dropdown-amount-wrap <?php echo( 'amount' !== $type ? 'simpay-panel-hidden' : '' ); ?> toggle-simpay-dropdown-amount-quantity-<?php echo $counter; ?>-amount">
	<th>
		<label for="<?php echo 'simpay-dropdown-amounts-' . $counter; ?>"><?php esc_html_e( 'Amounts', 'simple-pay' ); ?></label>
	</th>
	<td>

		<?php

		simpay_print_field( array(
			'type'       => 'standard',
			'subtype'    => 'text',
			'name'       => '_simpay_custom_field[dropdown][' . $counter . '][amounts]',
			'id'         => 'simpay-dropdown-amounts-' . $counter,
			'value'      => isset( $field['amounts'] ) ? $field['amounts'] : '',
			'class'      => array(
				'simpay-field-text',
			),
			'attributes' => array(
				'data-field-key' => $counter,
			),
		) );

		?>

		<p class="description">
			<?php printf( esc_html__( 'A comma separated list of amounts without the "%s" symbol (i.e. "2.00,4.50,9.00"). Needs to match the number of options and their order.', 'simple-pay' ), simpay_get_saved_currency_symbol() ); ?>
		</p>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-dropdown-metadata-' . $counter; ?>"><?php esc_html_e( 'Stripe Metadata Label', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field( array(
			'type'        => 'standard',
			'subtype'     => 'text',
			'name'        => '_simpay_custom_field[dropdown][' . $counter . '][metadata]',
			'id'          => 'simpay-dropdown-metadata-' . $counter,
			'value'       => isset( $field['metadata'] ) ? $field['metadata'] : '',
			'class'       => array(
				'simpay-field-text',
				'simpay-label-input',
			),
			'attributes'  => array(
				'data-field-key' => $counter,
				'maxlength'      => simpay_metadata_title_length(),
			),
			'description' => simpay_metadata_label_description(),
		) );

		?>
	</td>
</tr>
