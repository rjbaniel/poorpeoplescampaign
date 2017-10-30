<?php

namespace SimplePay\Pro\Forms\Fields;

use SimplePay\Core\Abstracts\Custom_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Text extends Custom_Field {


	/**
	 * Text constructor.
	 */
	public function __construct() {
		// No constructor needed, but to keep consistent will keep it here but just blank
	}

	/**
	 * Print HTML for text field on frontend
	 *
	 * @param $settings
	 *
	 * @return string
	 */
	public static function print_html( $settings ) {

		$html = '';

		$id          = isset( $settings['id'] ) ? $settings['id'] : '';
		$meta_name   = isset( $settings['metadata'] ) && ! empty( $settings['metadata'] ) ? $settings['metadata'] : $id;
		$label       = isset( $settings['label'] ) ? $settings['label'] : '';
		$placeholder = isset( $settings['placeholder'] ) ? $settings['placeholder'] : '';
		$required    = isset( $settings['required'] ) ? 'required' : '';
		$default     = isset( $settings['default'] ) ? $settings['default'] : '';
		$multiline   = isset( $settings['multiline'] ) ? true : false;
		$rows        = isset( $settings['rows'] ) && ! empty( $settings['rows'] ) ? intval( $settings['rows'] ) : 5;
		$name        = 'simpay_field[' . esc_attr( $meta_name ) . ']';

		$id    = simpay_dashify( $id );
		$label = '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';

		if ( ! $multiline ) {
			$field = '<input type="text" name="' . $name . '" id="' . esc_attr( $id ) . '" placeholder="' . esc_attr( $placeholder ) . '" value="' . esc_attr( $default ) . '" ' . $required . ' data-error="#' . esc_attr( $id ) . '-error" maxlength="500" /> ';
		} else {
			$field = '<textarea maxlength="500" placeholder="' . esc_attr( $placeholder ) . '" name="' . $name . '" id="' . esc_attr( $id ) . '" rows="' . esc_attr( $rows ) . '" ' . esc_attr( $required ) . ' data-error="#' . esc_attr( $id ) . '-error">' . esc_html( $default ) . '</textarea>';
		}

		$html .= '<div class="simpay-form-control">';
		$html .= '<p class="simpay-text-label simpay-label-wrap">';
		$html .= $label;
		$html .= '</p>';
		$html .= '<p class="simpay-text-wrap simpay-field-wrap">';
		$html .= $field;
		$html .= '</p>';

		$html .= '<p class="simpay-field-error" id="' . esc_attr( $id ) . '-error"></p>';

		$html .= '</div>';

		return $html;
	}

}
