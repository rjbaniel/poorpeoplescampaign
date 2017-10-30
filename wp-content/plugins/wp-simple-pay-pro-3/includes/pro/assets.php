<?php

namespace SimplePay\Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {

	public function __construct() {

		add_filter( 'simpay_before_register_public_scripts', array( $this, 'add_scripts' ), 10, 2 );

		add_filter( 'simpay_before_register_public_styles', array( $this, 'add_styles' ), 10, 2 );
	}

	public function add_scripts( $scripts, $min ) {

		$scripts['simpay-public-pro'] = array(
			'src'    => SIMPLE_PAY_ASSETS . 'js/public-pro' . $min . '.js',
			'deps'   => array(
				'jquery',
				'jquery-ui-datepicker',
				'simpay-jquery-validate',
				'simpay-accounting',
				'simpay-shared',
				'simpay-public',
			),
			'ver'    => SIMPLE_PAY_VERSION,
			'footer' => true,
		);

		return $scripts;
	}

	public function add_styles( $styles, $min ) {

		$styles['simpay-jquery-ui-cupertino'] = array(
			'src'   => SIMPLE_PAY_ASSETS . 'css/jquery-ui-cupertino' . $min . '.css',
			'deps'  => array(),
			'ver'   => SIMPLE_PAY_VERSION,
			'media' => 'all',
		);

		$styles['simpay-public-pro'] = array(
			'src'   => SIMPLE_PAY_ASSETS . 'css/public-pro' . $min . '.css',
			'deps'  => array(),
			'ver'   => SIMPLE_PAY_VERSION,
			'media' => 'all',
		);

		return $styles;
	}
}
