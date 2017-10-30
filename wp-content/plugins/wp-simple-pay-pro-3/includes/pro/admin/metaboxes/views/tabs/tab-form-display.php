<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

	<table>
		<thead>
		<tr>
			<th colspan="2"><?php esc_html_e( 'On-Page Form Display', 'simple-pay' ); ?></th>
		</tr>
		</thead>
		<tbody class="simpay-panel-section">
		<tr class="simpay-panel-field">
			<td style="padding-top: 0;">
				<?php new \SimplePay\Pro\Admin\Metaboxes\Custom_Fields(); ?>
			</td>
		</tr>
		</tbody>
	</table>

<?php echo simpay_docs_link( __( 'Help docs for On-Page Form Display', 'simple-pay' ), 'on-page-form-display-options', 'form-settings' );
