<?php

namespace SimplePay\Pro\Payments;

use SimplePay\Core\Payments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Plan
 *
 * @package SimplePay\Payments
 *
 * Wrapper of Stripe API Plan class. Used to get information about subscription plans.
 */
class Plan {

	/**
	 * Plan constructor.
	 */
	public function __construct() {

	}

	/**
	 * Create a new Stripe Plan
	 *
	 * @param $plan_args - Valid arguments for Stripe Plan::Create()
	 */
	public static function create_new( $plan_args ) {

		Payments\Stripe_API::request( 'Plan', 'create', $plan_args );
	}

	/**
	 * Remove a specific plan by ID
	 *
	 * @param $plan_id - ID of the plan to remove
	 */
	public static function remove( $plan_id ) {

		$plan = Payments\Stripe_API::request( 'Plan', 'retrieve', $plan_id );

		$plan->delete();
	}

	/**
	 * Get all of the plans.
	 *
	 * @return mixed Plans returned from Stripe API
	 */
	public static function get_plans() {

		$args = array(
			'limit' => 100,
		);

		$plans = Payments\Stripe_API::request( 'Plan', 'all', $args );

		return is_object( $plans ) ? $plans->data : false;
	}

	/**
	 * Get a plan by specific ID
	 *
	 * @param $id - The plan ID to retrieve
	 *
	 * @return mixed The specific Plan
	 */
	public static function get_plan_by_id( $id ) {

		return Payments\Stripe_API::request( 'Plan', 'retrieve', $id );
	}

	/**
	 * Get the amount for a specific Plan
	 *
	 * @param $plan - ID or plan object of the plan to get the amount for
	 *
	 * @return mixed The plan amount
	 */
	public static function get_plan_amount( $plan ) {

		if ( is_object( $plan ) ) {
			return $plan->amount;
		}

		$plan = self::get_plan_by_id( $plan );

		return is_object( $plan ) ? $plan->amount : false;
	}
}
