<?php

function ssf__initialize_stripe() {
	ssf__load_stripe_library();
	if ( ! class_exists( 'Stripe\Stripe' ) ) {
		return false;
	}

	$private_api_key = '';
	if ( get_option( 'ssf_is_live' ) === "on" ) {
		$private_api_key = get_option( 'ssf_live_sec_key' );
	} else {
		$private_api_key = get_option( 'ssf_test_sec_key' );
	}
	
	$public_api_key = '';
	if ( get_option( 'ssf_is_live' ) === "on" ) {
		$public_api_key = get_option( 'ssf_live_pub_key' );
	} else {
		$public_api_key = get_option( 'ssf_test_pub_key' );
	}

	if ( empty( $private_api_key ) || empty( $public_api_key ) ) {
		return false;
	}

	ssf__set_stripe_key( $private_api_key );
	return true;
}

function ssf__process_charge( $args, $minimum = 300 ) {
	if ( ! ssf__verify_nonce() ) {
		?><p class="ssf-notice ssf-notice--error">Sorry, there was a problem verifying your request. Your card was not charged. Please try again!</p><?php
	} else {
		$amount = ssf__validate_and_format_amount( $minimum );
		if ( $amount['success'] ) {
			$metadata = ssf__get_metadata( $args );

			$charge_results = ssf__attempt_charge( $amount['formatted_amount'], $args, $metadata );
			if ( $charge_results['success'] ) {
				?><p class="ssf-notice ssf-notice--success">Your payment was successfully processed. Thank you very much!</p><?php
			} elseif( $charge_results['error_type'] === 'card' ) {
				?><p class="ssf-notice ssf-notice--error">Sorry, there was a problem processing your card: <?php print_r( $charge_results['error_message'] ) ?></p><?php
			} elseif( $charge_results['error_type'] === 'already-subscribed' ) {
				?><p class="ssf=notice ssf-notice--error">A membership already exists with that email address. Your card was not charged.</p><?php
			} else {
				?><p class="ssf-notice ssf-notice--error">Sorry, there was a problem processing your payment! Your card was not charged.
				</p><?php
			}
		} elseif ( $amount['message'] == 'no-amount' ) {
			?><p class="ssf-notice ssf-notice--error">Sorry, there was a problem processing your payment: it looks like no amount was entered. Your card was not charged. Please try again!</p><?php
		} elseif ( $amount['message'] == 'too-low' ) {
			?><p class="ssf-notice ssf-notice--error">Sorry, we can only accept payments of at least $3.00 online. Your card was not charged.</p><?php
		}
	}
}
function ssf__attempt_charge( $amount, $args, $metadata ) {
	if ( $args['type'] == 'single' ) {
		$results = ssf__attempt_donation( $amount, $metadata );
	} elseif( $args['type'] == 'subscription' ) {
		$results = ssf__attempt_subscription( $amount, $metadata );
	} else {
		$results = array( 'success' => false, 'error_type' => 'general' );
	}
	return $results;
}
function ssf__attempt_donation( $amount, $metadata ) {
	try {
		\Stripe\Charge::create( array(
			'amount' => $amount,
			'currency' => 'usd',
			'source' => $_POST['stripeToken'],
			'description' => $metadata['description'],
			'metadata' => array(
				'first_name' => $metadata['first_name'],
				'last_name' => $metadata['last_name'],
				'comment' => $metadata['comment']
			),
			'receipt_email' => $metadata['email'],
		) );
	} catch ( \Stripe\Error\Card $e ) {
		$body = $e->getJsonBody();
		$err = $body['error'];
		return array( 'success' => false, 'error_type' => 'card', 'error_message' => $err );
	} catch ( \Stripe\Error\Base $e ) {
		return array( 'success' => false, 'error_type' => 'general', 'error_object' => $e );
	}

	return array( 'success' => true );
}
function ssf__attempt_subscription( $amount, $metadata ) {
	try {
		$plan = \Stripe\Plan::create( array(
			'amount' => $amount,
			'interval' => 'month',
			'name' => 'Subscription for ' . $metadata['email'],
			'currency' => 'usd',
			'id' => 'membership-' . $metadata['email'],
		) );
	} catch ( Exception $e ) {
		if ( $e->getMessage() == "Plan already exists." ) {
			return array( 'success' => false, 'error_type' => 'already-subscribed' );
		} else {
			 return array( 'success' => false, 'error_type' => 'general' );
		}
	}
	// Create the customer
	try {
		$customer = \Stripe\Customer::create( array( 
			'source' => $_POST['stripeToken'],
			'plan' => 'membership-' . $metadata['email'],
			'email' => $metadata['email'],
			'metadata' => array(
				'first_name' => $metadata['first_name'],
				'last_name' => $metadata['last_name']
			)
		) );
	} catch ( Exception $e ) {
		// if we couldn't create the customer, then delete their plan
		$plan->delete();
		return array( 'success' => false, 'error_type' => 'general' );
	};
	return array( 'success' => true );
}

function ssf__display_form( $args ) {
	wp_enqueue_style( 'ssf-form-css', SSF_DIR_URL . 'css/ssf-form.css', array(), 0.1, 'all' );
	?>
	<form class="ssf-form" id="ssfForm" action="<?php echo get_permalink() ?>" method="POST">
		<div id="ssfErrorExplanation" class="ssf-form__errors"></div>
		
		<div class="ssf-form__field">
			<label for="ssfFirstNameInput" class="ssf-form__label">First Name:</label>
			<input name="first-name" id="ssfFirstNameInput" class="ssf-form__input" type="text">
		</div>
		<div class="ssf-form__field">
			<label for="ssfLastNameInput" class="ssf-form__label">Last Name:</label>
			<input name="last-name" id="ssfLastNameInput" class="ssf-form__input" type="text">
		</div>
		<div id="ssfAmountField" class="ssf-form__field">
			<label for="ssfAmountInput" class="ssf-form__label">Amount:</label>
			<input name="amount" id="ssfAmountInput" class="ssf-form__input" type="text" required>
		</div>
		<input type="submit" value="<?php echo esc_attr( $args['button_text'] ) ?>" id="ssfSubmitButton" class="ssf-form__submit" style="background-color: <?php echo esc_attr( $args['button_bgcolor'] ); ?>">
		<input type="hidden" name="stripeToken" id="stripeToken">
		<?php wp_nonce_field( 'ssf_nonce', 'ssf_nonce_field' ); ?>
	</form>

	<script src="https://checkout.stripe.com/checkout.js"></script>
	<?php 
		wp_enqueue_script( 'vanilla-masker-js', SSF_DIR_URL . '/js/vanilla-masker.js', '1.1.0' );
		wp_enqueue_script( 'ssf-form-js', SSF_DIR_URL . '/js/ssf-form.js', array( 'vanilla-masker-js' ) );

		$public_api_key = '';
		if ( get_option( 'ssf_is_live' ) === "on" ) {
			$public_api_key = get_option( 'ssf_live_pub_key' );
		} else {
			$public_api_key = get_option( 'ssf_test_pub_key' );
		}

		$stripe_form_info = array(
			'api_key' => esc_html( $public_api_key ), 
			'title' => esc_html( $args['title'] ),
			'desc' => esc_html( $args['description'] ),
			'minimum' => esc_html( $args['minimum'] ),
			'get_zip' => esc_html( $args['get_zip'] ),
			'get_add' => esc_html( $args['get_billing_address'] )
		);
		wp_localize_script( 'ssf-form-js', 'form_info', $stripe_form_info );
}

function ssf__load_stripe_library() {
	if ( ! class_exists( 'Stripe\Stripe' ) ) {
			require_once( SSF_DIR_PATH . '/lib/stripe-php/init.php' );
	}
}
function ssf__set_stripe_key( $key ) {
	\Stripe\Stripe::setApiKey( $key );
}
function ssf__verify_nonce() {
	if ( ! wp_verify_nonce( $_POST['ssf_nonce_field' ], 'ssf_nonce' ) ) {
		return false;
	} else {
		return true;
	}
}
function ssf__validate_and_format_amount( $minimum ) {
	$return = false;
	if ( ! isset( $_POST['amount'] ) ) {
		$return = array( 'success' => false, 'message' => 'no-amount' );
	} else {
		$raw_amount = esc_html( $_POST['amount'] );
		$formatted_amount = ssf__format_amount( $raw_amount );
		if ( $formatted_amount < $minimum ) {
			$return = array( 'success' => false, 'message' => 'too-low' );
		} else {
			$return = array( 'success' => true, 'formatted_amount' => $formatted_amount );
		}
	}
	return $return;
}
function ssf__get_metadata( $args ) {
	$description = '';
	$metadata = array();

	$first_name = '';
	$last_name = '';
	
	if ( ! empty( $_POST['first-name'] ) ) {
		$first_name = esc_html( $_POST['first-name'] );
	}
	$metadata['first_name'] = $first_name;

	if ( ! empty( $_POST['last-name'] ) ) {
		$last_name = esc_html( $_POST['last-name'] );
	}
	$metadata['last_name'] = $last_name;

	$token_obj = \Stripe\Token::retrieve($_POST['stripeToken']);
	$card_obj = $token_obj->offsetGet('card');
	$card_email = $card_obj->offsetGet('name');
	$metadata['email'] = $card_email;

	$metadata['comment'] = $args['comment'];

	$description = '';
	if ( $first_name || $last_name ) {
		if ( $args['type'] == 'subscription' ) {
			$description .= 'Subscription for ';
		} else {
			$description .= 'Single payment from ';
		}
		if ( $first_name ) {
			$description .= $first_name;
			if ( $last_name ) {
				$description .= ' ';
			}
		}
		if ( $last_name ) {
			$description .= $last_name . '.';
		}
		$metadata['description'] = $description;
	}

	return $metadata;
}

function ssf__format_amount( $amount ) {
	$chars_to_remove = array( '$', ',' );
	$amount = str_replace( $chars_to_remove, '', $amount );
	try {
		$amount = floatval($amount);
	} catch( Exception $e ) {
		?><p>Sorry, please enter a valid amount (e.g., '$15.00'). Your card was not charged. Please try again!</p><?php
	}
	$amount = round( $amount, 2 ) * 100;
	return $amount;
}
?>
