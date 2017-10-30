<?php
/**
 * Plugin Name: WP Simple Pay Pro 3
 * Plugin URI:  https://wpsimplepay.com
 * Description: Add high conversion Stripe payment forms to your WordPress site in minutes.
 * Author: WP Simple Pay
 * Author URI:  https://wpsimplepay.com
 * Version: 3.1.7
 * Text Domain: simple-pay
 * Domain Path: /i18n
 */

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright 2014-2017 Moonstone Media Group. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
$this_plugin_path = trailingslashit( dirname( __FILE__ ) );
$this_plugin_dir  = plugin_dir_url( __FILE__ );

if ( ! defined( 'SIMPLE_PAY_VERSION' ) ) {
	define( 'SIMPLE_PAY_VERSION', '3.1.7' );
}

if ( ! defined( 'SIMPLE_PAY_PLUGIN_NAME' ) ) {
	define( "SIMPLE_PAY_PLUGIN_NAME", 'WP Simple Pay Pro 3' );
}

if ( ! defined( 'SIMPLE_PAY_STRIPE_API_VERSION' ) ) {

	// Set the API Version from Stripe 'YYYY-MM-DD' format
	define( 'SIMPLE_PAY_STRIPE_API_VERSION', '2017-08-15' );
}

if ( ! defined( 'SIMPLE_PAY_MAIN_FILE' ) ) {
	define( 'SIMPLE_PAY_MAIN_FILE', __FILE__ );
}

if ( ! defined( 'SIMPLE_PAY_URL' ) ) {
	define( 'SIMPLE_PAY_URL', $this_plugin_dir );
}

if ( ! defined( 'SIMPLE_PAY_ASSETS' ) ) {
	define( 'SIMPLE_PAY_ASSETS', $this_plugin_dir . 'assets/' );
}

if ( ! defined( 'SIMPLE_PAY_PATH' ) ) {
	define( 'SIMPLE_PAY_PATH', $this_plugin_path );
}

if ( ! defined( 'SIMPLE_PAY_INC' ) ) {
	define( 'SIMPLE_PAY_INC', $this_plugin_path . 'includes/' );
}

if ( ! defined( 'SIMPLE_PAY_ITEM_NAME' ) ) {
	define( 'SIMPLE_PAY_ITEM_NAME', 'WP Simple Pay Pro 3' );
}

if ( ! defined( 'SIMPLE_PAY_ITEM_ID' ) ) {
	define( 'SIMPLE_PAY_ITEM_ID', 177993 );
}

if ( ! defined( 'SIMPLE_PAY_STORE_URL' ) ) {
	define( 'SIMPLE_PAY_STORE_URL', 'https://wpsimplepay.com/' );
}

if ( class_exists( 'SimplePay\Core\SimplePay' ) ) {
	add_action( 'admin_notices', 'simpay_deactivate_lite_notice' );

	return;
}

// PHP minimum requirement check.
if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
	add_action( 'admin_notices', 'simpay_admin_php_notice' );

	return;
}

function simpay_deactivate_lite_notice() {
	?>

	<div class="error">
		<p>
			<?php printf( __( 'You must <a href="%1$s">deactivate WP Simple Pay Lite</a> in order to use %2$s.', 'simple-pay' ), wp_nonce_url( 'plugins.php?action=deactivate&plugin=stripe%2Fstripe-checkout.php&plugin_status=all&paged=1&s=', 'deactivate-plugin_stripe/stripe-checkout.php' ), SIMPLE_PAY_ITEM_NAME ); ?>
		</p>
	</div>

	<?php
}

/**
 * Show an error message for PHP < 5.3 and don't load the plugin
 */
if ( ! function_exists( 'simpay_admin_php_notice' ) ) {
	function simpay_admin_php_notice() {
		?>

		<div class="error">
			<p>
				<?php printf( esc_html__( '%s requires PHP 5.3 or higher.', 'simple-pay' ), SIMPLE_PAY_ITEM_NAME ); ?>
			</p>
		</div>

		<?php
	}
}

include_once( 'vendor/autoload.php' );
include_once( 'includes/autoload.php' );

// Load plugin.
include_once( 'includes/core/main.php' );

include_once( 'includes/pro/main.php' );
