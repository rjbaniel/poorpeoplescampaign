<?php

namespace SimplePay\Pro;

use SimplePay\Pro\Admin;
use SimplePay\Pro\Admin\Metaboxes\Settings;
use SimplePay\Pro\Forms\Ajax;
use SimplePay\Pro\Payments\Webhooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


final class SimplePayPro {

	/**
	 * The single instance of this class.
	 *
	 * @access protected
	 * @var object
	 */
	protected static $_instance = null;

	/**
	 * Main Simple Pay instance.
	 *
	 * Ensures only one instance of Simple Pay is loaded or can be loaded.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'simple-pay' ), '2.1' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'simple-pay' ), '2.1' );
	}

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->load();

		add_filter( 'simpay_get_url', array( $this, 'get_url' ), 10, 2 );

	}

	/**
	 * Get URL.
	 *
	 * @since  3.0.0
	 *
	 * @param  string $case Requested url.
	 *
	 * @return string
	 */
	public function get_url( $url, $case ) {

		switch ( $case ) {
			case 'my-account':
				$url = 'https://wpsimplepay.com/my-account/';
				break;
		}

		return $url;
	}

	/**
	 * Load the plugin
	 */
	public function load() {

		include_once( 'functions/shared.php' );

		// Load Lite helper class to update various differences between Lite and Pro
		new Lite_Helper();

		new Webhooks();

		new Objects();

		new Assets();

		new Shortcodes();

		new Payments\Details();

		// Load frontend ajax
		new Ajax();

		if ( is_admin() ) {
			$this->load_admin();
		}

	}

	/**
	 * Load the plugin admin
	 */
	public function load_admin() {

		include_once( 'functions/admin.php' );

		new Plugin_Updater();

		new Admin\Menus();

		new Settings();

		new Admin\Pages();

		add_action( 'simpay_admin_notices', function ( $is_admin_screen ) {
			new Admin\Notices( $is_admin_screen );
		} );

		// Assets
		new Admin\Assets();

		//if ( defined( 'DOING_AJAX' ) ) {
		// Admin ajax callbacks.
		new Admin\Ajax();
		//}
	}
}

/**
 * Simple Pay PRO
 */
function pro_plugin() {
	return SimplePayPro::get_instance();
}

pro_plugin();
