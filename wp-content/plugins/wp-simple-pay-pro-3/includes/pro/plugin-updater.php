<?php

namespace SimplePay\Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin_Updater {

	/**
	 * Plugin_Updater constructor.
	 */
	public function __construct() {
		$this->load_updater();

		// Check for activation
		if ( isset( $_POST['simpay_activate_license'] ) ) {
			self::activate_license( sanitize_text_field( trim( $_POST['simpay_settings_license']['key']['license_key'] ) ) );
		}

		// Check for deactivation
		if ( isset( $_POST['simpay_deactivate_license'] ) ) {
			self::deactivate_license( sanitize_text_field( trim( $_POST['simpay_settings_license']['key']['license_key'] ) ) );
		}

		// Check license is still valid
		add_action( 'admin_init', array( $this, 'check_license_still_valid' ) );
	}

	/**
	 * Load the EDD SL Updater class if a key is saved
	 */
	public function load_updater() {

		$key = self::get_license_key();

		if ( ! empty( $key ) ) {
			new EDD_SL_Plugin_Updater( SIMPLE_PAY_STORE_URL, SIMPLE_PAY_MAIN_FILE, array(
				'version' => SIMPLE_PAY_VERSION, // current version number
				'license' => $key, // license key (used get_option above to retrieve from DB)
				'item_id' => SIMPLE_PAY_ITEM_ID,
				'author'  => 'Moonstone Media' // author of this plugin
			) );
		}
	}

	/**
	 * Activate a license key.
	 *
	 * @param $key - The license key to activate
	 *
	 * @return mixed An error message or true if successful
	 */
	public static function activate_license( $key ) {
		// Activate a license

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $key,
			'item_id'    => SIMPLE_PAY_ITEM_ID,
			'url'        => home_url(),
		);

		// Call the custom API.
		$response = wp_remote_post( SIMPLE_PAY_STORE_URL, array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			$error_message = $response->get_error_message();

			return ( is_wp_error( $response ) && ! empty( $error_message ) ) ? $error_message : esc_html__( 'An error occurred, please try again.', 'simple-pay' );

		} else {

			// Successful activation

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			update_option( 'simpay_license_key', sanitize_text_field( $key ) );
			update_option( 'simpay_license_data', $license_data );

			return true;
		}
	}

	/**
	 * Deactivate a license key.
	 *
	 * @param $key - The license key to deactivate
	 *
	 * @return mixed An error message or true if successful
	 */
	public static function deactivate_license( $key ) {
		// Deactivate a license key

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $key,
			'item_id'    => SIMPLE_PAY_ITEM_ID,
			'url'        => home_url(),
		);

		// Call the custom API.
		$response = wp_remote_post( SIMPLE_PAY_STORE_URL, array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			$error_message = $response->get_error_message();

			return ( is_wp_error( $response ) && ! empty( $error_message ) ) ? $error_message : esc_html__( 'An error occurred, please try again.', 'simple-pay' );

		} else {

			// Successful deactivation

			// Remove saved license data and key
			delete_option( 'simpay_license_data' );
			delete_option( 'simpay_license_key' );

			return true;
		}
	}

	/**
	 * Get the saved license key
	 *
	 * @return mixed Returns the saved license key if it exists or false
	 */
	public static function get_license_key() {

		$key = get_option( 'simpay_license_key' );

		return $key;
	}

	/**
	 * Get the saved license data.
	 *
	 * @return mixed Return false if no option data, otherwise returns the license data saved in our options
	 */
	public static function get_saved_license_data() {

		$license_data = get_option( 'simpay_license_data' );

		if ( ! empty( $license_data ) ) {
			return $license_data;
		}

		return false;

	}

	/**
	 * A function to check if a current license is still valid. Run check once every 24 hours, or 2 hours if an error
	 * is encountered.
	 */
	public function check_license_still_valid() {

		if ( get_option( 'simpay_check_license_timer' ) && get_option( 'simpay_check_license_timer' ) > current_time( 'timestamp' ) ) {
			return;
		}

		$license = trim( get_option( 'simpay_license_key' ) );

		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $license,
			'item_id'    => SIMPLE_PAY_ITEM_ID,
			'url'        => home_url(),
		);

		// Call the custom API.
		$response = wp_remote_post( SIMPLE_PAY_STORE_URL, array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		) );

		if ( is_wp_error( $response ) ) {
			update_option( 'simpay_check_license_timer', current_time( 'timestamp' ) + ( 60 * 60 * 2 ) );

			return;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( 'simpay_license_data', $license_data );

		update_option( 'simpay_check_license_timer', current_time( 'timestamp' ) + ( 60 * 60 * 24 ) );
	}
}
