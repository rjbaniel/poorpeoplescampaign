
var simpayAppPro = {};

(function( $ ) {
	'use strict';

	var body = $( document.body );

	simpayAppPro = {

		init: function() {

			console.log( 'simpayAppPro initialized' );

			body.on( 'simpayFormVarsInitialized', function( event, spFormElem, formData ) {
				simpayAppPro.setupValidation( spFormElem, formData );
			} );

			body.on( 'simpayBindEventsAndTriggers', simpayAppPro.processForm );

			body.on( 'simpayFinalizeAmount', simpayAppPro.setFinalAmount );
		},

		processForm: function( event, spFormElem, formData ) {

			formData = simpayApp.spFormData[ formData.formId ];

			// Set the tax amount
			formData.taxAmount = simpayAppPro.calculateTaxAmount( formData.amount, formData.taxPercent );

			// Check for a single subscription and set the planAmount
			if ( formData.isSubscription && ( 'single' === formData.subscriptionType ) ) {
				formData.planAmount = formData.amount;
			}

			// Set an empty couponCode attribute now
			formData.couponCode = '';

			/** Event handlers for form elements **/

			// On custom amount field change update the amount and then update total amount
			// Check keyup and change events
			spFormElem.find( '.simpay-custom-amount-input' ).on( 'keyup.simpayCustomAmountInput change.simpayCustomAmountInput', function( e ) {

				// Update the custom amount variable to what was entered and then update the total amount label
				simpayAppPro.processCustomAmountInput( spFormElem, formData );
				simpayAppPro.updateTotalAmount( spFormElem, formData );
			} );

			// Apply coupons
			spFormElem.find( '.simpay-apply-coupon' ).on( 'click.simpayApplyCoupon', function( e ) {

				e.preventDefault();

				simpayAppPro.applyCoupon( spFormElem, formData );
				simpayAppPro.updateTotalAmount( spFormElem, formData );
			} );

			// Remove Coupon
			spFormElem.find( '.simpay-remove-coupon' ).on( 'click.simpayRemoveCoupon', function( e ) {

				e.preventDefault();

				simpayAppPro.removeCoupon( spFormElem, formData );
			} );

			// Radio and dropdown amount change
			spFormElem.find( '.simpay-amount-dropdown, .simpay-amount-radio' ).on( 'change.simpayAmountSelect', function( e ) {

				simpayAppPro.updateAmountSelect( spFormElem, formData );
			} );

			// Radio and dropdown quantity change
			spFormElem.find( '.simpay-quantity-dropdown, .simpay-quantity-radio' ).on( 'change.simpayQuantitySelect', function() {

				simpayAppPro.updateQuantitySelect( spFormElem, formData );
			} );

			// Number field quantiy update
			spFormElem.find( '.simpay-quantity-input' ).on( 'keyup.simpayNumberQuantity, change.simpayNumberQuantity, input.simpayNumberQuantity', function( e ) {

				simpayAppPro.updateQuantitySelect( spFormElem, formData );

				simpayAppPro.updateTotalAmount( spFormElem, formData );
			} );

			// Subscription multi-plan options
			spFormElem.find( '.simpay-multi-sub, .simpay-plan-wrapper select' ).on( 'change.simpayMultiPlan', function( e ) {

				simpayAppPro.changeMultiSubAmount( spFormElem, formData );
			} );

			// Custom amount input clicked
			spFormElem.find( '.simpay-custom-amount-input' ).on( 'focus.simpayCustomAmountFocus', function( e ) {
				simpayAppPro.handleCustomAmountFocus( spFormElem, formData );
			} );

			// Custom event fired after each successful coupon applied and new values returned after ajax post.
			spFormElem.on( 'simpayCouponApplied', function( e ) {
				simpayAppPro.updateTotalAmount( spFormElem, formData );
			} );

			// Custom event fired when coupon removed.
			spFormElem.on( 'simpayCouponRemoved', function( e ) {
				simpayAppPro.updateTotalAmount( spFormElem, formData );
			} );

			// Custom event fired when Dropdown Amount select is changed
			spFormElem.on( 'simpayDropdownAmountChange', function( e ) {

				if ( '' === formData.couponCode.trim() ) {
					simpayAppPro.updateTotalAmount( spFormElem, formData );
				}
			} );

			// Custom event fired when Radio Amount select is changed
			spFormElem.on( 'simpayRadioAmountChange', function( e ) {

				if ( '' === formData.couponCode.trim() ) {
					simpayAppPro.updateTotalAmount( spFormElem, formData );
				}
			} );

			// Custom event fired when Dropdown Quantity select is changed
			spFormElem.on( 'simpayDropdownQuantityChange', function( e ) {

				if ( '' === formData.couponCode.trim() ) {
					simpayAppPro.updateTotalAmount( spFormElem, formData );
				}
			} );

			// Custom event fired when Radio Quantity select is changed
			spFormElem.on( 'simpayRadioQuantityChange', function( e ) {

				if ( '' === formData.couponCode.trim() ) {
					simpayAppPro.updateTotalAmount( spFormElem, formData );
				}
			} );

			// Custom event fired when Number Quantity is changed
			spFormElem.on( 'simpayNumberQuantityChange', function( e ) {

				if ( '' === formData.couponCode.trim() ) {
					simpayAppPro.updateTotalAmount( spFormElem, formData );
				}
			} );

			// After a multi-plan selection is updated
			spFormElem.on( 'simpayMultiPlanChanged', function( e ) {

				if ( '' === formData.couponCode.trim() ) {
					simpayAppPro.updateTotalAmount( spFormElem, formData );
				}
			} );

			spFormElem.on( 'simpayBeforeStripePayment', function( e, spFormElem, formData ) {
				simpayAppPro.submitPayment( spFormElem, formData );
			} );

			// Initialize date field
			simpayAppPro.initDateField( spFormElem, formData );

			/** Trigger some events on intial page load. **/

			// Trigger the custom amount input on page load to set initial value.
			// If it is after the multi-plan trigger then it will cause the multi-plan to default to
			// the custom amount of the field and not the default option.
			spFormElem.find( '.simpay-custom-amount-input' ).trigger( 'change.simpayCustomAmountInput' );

			// Trigger change event on dropdown/radio amount field
			spFormElem.find( '.simpay-amount-dropdown, .simpay-amount-radio' ).trigger( 'change.simpayAmountSelect' );

			// Trigger change event on dropdown/radio quantity field
			spFormElem.find( '.simpay-quantity-dropdown, .simpay-quantity-radio' ).trigger( 'change.simpayQuantitySelect' );

			// Trigger multi-plan selection on page load to set initial amount
			spFormElem.find( '.simpay-multi-sub:checked, .simpay-plan-wrapper select:selected' ).trigger( 'change.simpayMultiPlan' );

			// Trigger quantity to update
			spFormElem.find( '.simpay-quantity-input' ).trigger( 'input.simpayNumberQuantity' );

			// Check for single subscription and update the recurring amount label since no DOM element will trigger it.
			if ( formData.isSubscription && ( 'single' === formData.subscriptionType ) ) {
				simpayAppPro.updateRecurringAmount( spFormElem, formData );
			}

			simpayAppPro.updateTotalAmount( spFormElem, formData );
		},

		/**
		 * Handle events when a custom amount input is clicked
		 */
		handleCustomAmountFocus: function( spFormElem, formData ) {

			var selectOption = spFormElem.find( '.simpay-custom-plan-option' );
			var customAmountInput = spFormElem.find( '.simpay-custom-amount-input' );

			spShared.debugLog( 'Custom Amount Focus hit', spFormElem );

			// Check what type of display we are dealing with and select the option accordingly
			if ( selectOption.is( 'input' ) ) {

				// Radio option
				selectOption.prop( 'checked', true );

			} else if ( selectOption.is( 'option' ) ) {

				// Dropdown (select) option
				selectOption.prop( 'selected', true );
			}

			// Update multi-sub amount
			formData.useCustomPlan = true;

			spFormElem.find( '.simpay-has-custom-plan' ).val( 'true' );

			formData.customPlanAmount = simpayApp.convertToCents( customAmountInput.val() );

			spShared.debugLog( 'formData.subMinAmount', formData.subMinAmount );
		},

		submitPayment: function( spFormElem, formData ) {

			// Change the panel label for a trial
			if ( formData.isTrial && ( 0 === formData.finalAmount ) ) {
				formData.stripeParams.panelLabel = formData.freeTrialButtonText;
			} else {
				formData.stripeParams.panelLabel = formData.oldPanelLabel;
			}
		},

		setupValidation: function( spFormElem ) {

			var formId = spFormElem.data( 'simpay-form-id' );

			jQuery.validator.addMethod( 'minCheck', function( value, element, minimum ) {

				// Make a new min and a new value here so we can retain the original values passed in.
				var newValue = spShared.unformatCurrency( value );
				var newMin = spShared.unformatCurrency( minimum );

				return this.optional( element ) || ( parseFloat( newValue ) >= parseFloat( newMin ) ) || '' === newValue;

			} );

			// Validate the form before we send it
			spFormElem.validate( {
				errorPlacement: function( error, element ) {
					var placement = spFormElem.find( element ).data( 'error' );
					if ( placement ) {
						spFormElem.find( placement ).append( error );
					} else {
						error.insertAfter( spFormElem.find( element ) );
					}
				},
				rules: {
					simpay_custom_amount: {
						required: true,
						minCheck: simpayApp.formatMoney( spShared.unformatCurrency( simplePayForms[ formId ].form.integers.minAmount ) )
					},
					simpay_subscription_custom_amount: {
						required: true,
						minCheck: simpayApp.formatMoney( spShared.unformatCurrency( simplePayForms[ formId ].form.integers.subMinAmount ) )
					}
				},
				messages: {
					simpay_custom_amount: simplePayForms[ formId ].form.i18n.minCustomAmountError,
					simpay_subscription_custom_amount: simplePayForms[ formId ].form.i18n.subMinCustomAmountError
				}
			} );
		},

		initDateField: function( spFormElem, formData ) {
			spFormElem.find( '.simpay-date-input' ).datepicker();
			spFormElem.find( '.simpay-date-input' ).datepicker( 'option', 'dateFormat', formData.dateFormat );
		},

		processCustomAmountInput: function( spFormElem, formData ) {

			var unformattedAmount = spFormElem.find( '.simpay-custom-amount-input' ).val();
			formData.customAmount = simpayApp.convertToCents( spShared.unformatCurrency( unformattedAmount ) );

			if ( formData.isSubscription ) {
				formData.customPlanAmount = formData.customAmount;
				formData.planAmount = formData.customAmount;
			}

			// Apply any coupons that may exist
			simpayAppPro.applyCoupon( spFormElem, formData );
		},

		// Run this to process and get the final amount when the payment button is clicked
		setFinalAmount: function( e, spFormElem, formData ) {

			var finalAmount = formData.amount;

			if ( ( 'undefined' !== formData.customAmount ) && ( formData.customAmount > 0 ) ) {

				if ( spGeneral.booleans.isZeroDecimal ) {
					finalAmount = parseInt( formData.customAmount );
				} else {
					finalAmount = parseFloat( formData.customAmount );
				}
			}

			if ( ( 'undefined' !== typeof formData.isSubscription ) && formData.isSubscription ) {

				// Check for single subscription
				if ( 'single' === formData.subscriptionType ) {

					// Check if we are using a custom plan or a regular plan and change amount accordingly
					if ( 'undefined' !== typeof formData.customPlanAmount ) {
						finalAmount = formData.customPlanAmount;
					} else {
						finalAmount = formData.amount;
					}
				} else {

					// Check if we are using a custom plan or a regular plan and change amount accordingly
					if ( ( 'undefined' !== typeof formData.useCustomPlan ) && formData.useCustomPlan ) {
						finalAmount = formData.customPlanAmount;
					} else {
						finalAmount = formData.planAmount;
					}
				}

				if ( formData.isTrial ) {
					finalAmount = 0;
				}

				// Normal setupFee
				if ( 'undefined' !== typeof formData.setupFee ) {

					// Add the total of all setup fees to the finalAmount
					finalAmount = finalAmount + formData.setupFee;
				}

				// Individual plan setupFee
				if ( 'undefined' !== typeof formData.planSetupFee ) {

					// Add the total of all setup fees to the finalAmount
					finalAmount = finalAmount + formData.planSetupFee;
				}
			}

			if ( 'undefined' !== typeof formData.quantity ) {
				finalAmount = finalAmount * formData.quantity;
			}

			// Check for coupon discount
			if ( 'undefined' !== typeof formData.discount ) {
				finalAmount = finalAmount - formData.discount;
			}

			// Only add fee or fee percent if we are not using a subscription
			if ( ( 'undefined' !== typeof formData.isSubscription ) && ! formData.isSubscription ) {
				spShared.debugLog( 'percentage fee', formData.feePercent );

				if ( formData.feePercent > 0 ) {
					finalAmount = finalAmount + ( finalAmount * ( formData.feePercent / 100 ) );
				}

				spShared.debugLog( 'amount fee', formData.feeAmount );

				// Add additional fee amount (from user filters currently)
				if ( formData.feeAmount > 0 ) {
					finalAmount = finalAmount + formData.feeAmount;
				}
			}

			if ( formData.taxPercent > 0 ) {

				if ( formData.isTrial ) {
					formData.taxAmount = simpayAppPro.calculateTaxAmount( formData.planAmount, formData.taxPercent );
				} else {
					formData.taxAmount = simpayAppPro.calculateTaxAmount( finalAmount, formData.taxPercent );
					finalAmount += formData.taxAmount;
				}

				formData.taxAmount = formData.taxAmount.toFixed( 0 );

				spFormElem.find( '.simpay-tax-amount' ).val( formData.taxAmount );
			}

			formData.finalAmount = finalAmount;
		},

		applyCoupon: function( spFormElem, formData ) {

			// Set our variables before we do anything else
			var couponField = spFormElem.find( '.simpay-coupon-field' ),
				data = null,
				responseContainer = spFormElem.find( '.simpay-coupon-message' ),
				loadingImage = spFormElem.find( '.simpay-coupon-loading' ),
				removeCoupon = spFormElem.find( '.simpay-remove-coupon' ),
				hiddenCouponElem = spFormElem.find( '.simpay-coupon' ),
				couponCode = '',
				couponMessage = '',
				amount = formData.amount;

			// Make sure a coupon exists either by entry or has not been removed and set the proper couponCode
			if ( !couponField.val() ) {
				if ( !formData.couponCode ) {
					return;
				} else {
					couponCode = formData.couponCode;
				}
			} else {
				couponCode = couponField.val();
			}

			// Check for subscription amount
			if ( formData.isSubscription ) {

				if ( formData.useCustomPlan ) {
					amount = formData.customPlanAmount;
				} else {
					amount = formData.planAmount;
				}
			}

			if ( ( 'undefined' !== formData.customAmount ) && ( formData.customAmount > 0 ) ) {
				amount = formData.customAmount;
			}

			// Also check for quantity multiplier before calculating discount
			if ( 'undefined' !== typeof formData.quantity ) {
				amount = amount * formData.quantity;
			}

			// AJAX params
			data = {
				action: 'simpay_get_coupon',
				coupon: couponCode,
				amount: amount,
				couponNonce: spFormElem.find( '#simpay_coupon_nonce' ).val()
			};

			// Clear the response container and hide the remove coupon link
			responseContainer.text( '' );
			removeCoupon.hide();

			// Clear textbox
			couponField.val( '' );

			// Show the loading image
			loadingImage.show();

			$.ajax( {
				url: spGeneral.strings.ajaxurl,
				method: 'POST',
				data: data,
				dataType: 'json',
				success: function( response ) {

					spShared.debugLog( 'coupon discount', response.discount );

					// Set the coupon code attached to this form to the couponCode being used here
					formData.couponCode = couponCode;

					// Set an attribute to store the discount so we can subtract it later
					formData.discount = response.discount;

					// Coupon message for frontend
					couponMessage = response.coupon.code + ': ';

					// Output different text based on the type of coupon it is - amount off or a percentage
					if ( 'percent' === response.coupon.type ) {
						couponMessage += response.coupon.amountOff + spGeneral.i18n.couponPercentOffText;
					} else if ( 'amount' === response.coupon.type ) {
						couponMessage += simpayApp.formatMoney( simpayApp.convertToCents( response.coupon.amountOff ) ) + ' ' + spGeneral.i18n.couponAmountOffText;
					}

					$( '.coupon-details' ).remove();

					// Update the coupon message text
					responseContainer.append( couponMessage );

					// Create a hidden input to send our coupon details for Stripe metadata purposes
					$( '<input />', {
						name: 'simpay_coupon_details',
						type: 'hidden',
						value: couponMessage,
						class: 'simpay-coupon-details'
					} ).appendTo( responseContainer );

					// Show remove coupon link
					removeCoupon.show();

					// Add the coupon to our hidden element for processing
					hiddenCouponElem.val( couponCode );

					// Hide the loading image
					loadingImage.hide();

					// Trigger custom event when coupon apply done.
					spFormElem.trigger( 'simpayCouponApplied' );
				},
				error: function( response ) {

					var errorMessage = '';

					spShared.debugLog( 'coupon error', response.responseText );

					if ( response.responseText ) {
						errorMessage = response.responseText;
					}

					// Show invalid coupon message
					responseContainer.append( $( '<p />' ).addClass( 'simpay-field-error' ).text( errorMessage ) );

					// Hide loading image
					loadingImage.hide();
				}
			} );
		},

		removeCoupon: function( spFormElem, formData ) {

			spFormElem.find( '.simpay-coupon-loading' ).hide();
			spFormElem.find( '.simpay-remove-coupon' ).hide();
			spFormElem.find( '.simpay-coupon-message' ).text( '' );
			spFormElem.find( '.simpay-coupon' ).val( '' );

			formData.couponCode = '';
			formData.discount = 0;

			// Trigger custom event when coupon apply done.
			spFormElem.trigger( 'simpayCouponRemoved' );
		},

		updateAmountSelect: function( spFormElem, formData ) {

			if ( spFormElem.find( '.simpay-amount-dropdown' ).length ) {

				// Update the amount to the selected dropdown amount
				formData.amount = spFormElem.find( '.simpay-amount-dropdown' ).find( 'option:selected' ).data( 'amount' );

				spFormElem.trigger( 'simpayDropdownAmountChange' );
			} else if ( spFormElem.find( '.simpay-amount-radio' ) ) {

				// Update the amount to the selected radio button
				formData.amount = spFormElem.find( '.simpay-amount-radio' ).find( 'input[type="radio"]:checked' ).data( 'amount' );

				spFormElem.trigger( 'simpayRadioAmountChange' );
			}

			// Update any coupons
			simpayAppPro.applyCoupon( spFormElem, formData );
		},

		updateQuantitySelect: function( spFormElem, formData ) {

			formData.quantity = 1;

			if ( spFormElem.find( '.simpay-quantity-dropdown' ).length ) {

				// Update the amount to the selected dropdown amount
				formData.quantity = spFormElem.find( '.simpay-quantity-dropdown' ).find( 'option:selected' ).data( 'quantity' );

				spFormElem.trigger( 'simpayDropdownQuantityChange' );
			} else if ( spFormElem.find( '.simpay-quantity-radio' ).length ) {

				// Update the amount to the selected radio button
				formData.quantity = spFormElem.find( '.simpay-quantity-radio' ).find( 'input[type="radio"]:checked' ).data( 'quantity' );

				spFormElem.trigger( 'simpayRadioQuantityChange' );
			} else if ( spFormElem.find( '.simpay-quantity-input' ).length ) {

				formData.quantity = parseInt( spFormElem.find( '.simpay-quantity-input' ).val() );

				spFormElem.trigger( 'simpayNumberQuantityChange' );
			}

			if ( formData.quantity < 1 ) {
				formData.quantity = 1;
			}

			// Update hidden quantity field
			spFormElem.find( '.simpay-quantity' ).val( formData.quantity );

			// Apply any coupons
			simpayAppPro.applyCoupon( spFormElem, formData );
		},

		updateTotalAmount: function( spFormElem, formData ) {
			simpayAppPro.setFinalAmount( null, spFormElem, formData );

			spFormElem.find( '.simpay-total-amount-value' ).text( simpayApp.formatMoney( formData.finalAmount ) );

			simpayAppPro.updateRecurringAmount( spFormElem, formData );

			simpayAppPro.updateTaxAmount( spFormElem, formData );
		},

		updateRecurringAmount: function( spFormElem, formData ) {

			var recurringAmount = ( formData.planAmount * formData.quantity ) + formData.taxAmount;

			spShared.debugLog( 'quantity:', formData.quantity );

			if ( formData.planIntervalCount > 1 ) {
				spFormElem.find( '.simpay-total-amount-recurring-value' ).text( simpayApp.formatMoney( recurringAmount ) + ' every ' + formData.planIntervalCount + ' ' + formData.planInterval + 's' );
			} else {
				spFormElem.find( '.simpay-total-amount-recurring-value' ).text( simpayApp.formatMoney( recurringAmount ) + '/' + formData.planInterval );
			}
		},

		updateTaxAmount: function( spFormElem, formData ) {

			spFormElem.find( '.simpay-tax-amount-value' ).text( simpayApp.formatMoney( formData.taxAmount ) );
		},

		calculateTaxAmount: function( amount, percent ) {
			return amount * ( percent / 100 );
		},

		changeMultiSubAmount: function( spFormElem, formData ) {

			var selectedOption = '',
				customAmountInput = spFormElem.find( '.simpay-custom-amount-input' ),
				wrapperElem = spFormElem.find( '.simpay-plan-wrapper' ),
				options = wrapperElem.find( '.simpay-multi-sub' ),
				planSetupFee = 0,
				planId = '',
				planAmount = 0,
				planInterval = '',
				planIntervalCount = 1,
				planTrial = false,
				planMaxCharges = 0;

			// Check if it is a dropdown or a radio button setup and act accordingly
			if ( options.first().is( 'option' ) ) {

				// Dropdown
				selectedOption = options.filter( ':selected' );

				planId = selectedOption.data( 'plan-id' );
				planSetupFee = selectedOption.data( 'plan-setup-fee' );
				planAmount = selectedOption.data( 'plan-amount' );
				planInterval = selectedOption.data( 'plan-interval' );
				planIntervalCount = selectedOption.data( 'plan-interval-count' );
				planTrial = undefined !== selectedOption.data( 'plan-trial' );
				planMaxCharges = selectedOption.data( 'plan-max-charges' );

				if ( 'simpay_custom_plan' === selectedOption.val() ) {
					formData.useCustomPlan = true;
					planAmount = simpayApp.convertToCents( customAmountInput.val() );
				} else {
					formData.useCustomPlan = false;
				}

				if ( planTrial ) {
					formData.amount = 0;
				}

			} else {

				// Radio buttons
				selectedOption = options.filter( ':checked' );

				planId = selectedOption.data( 'plan-id' );
				planSetupFee = selectedOption.data( 'plan-setup-fee' );
				planAmount = selectedOption.data( 'plan-amount' );
				planInterval = selectedOption.data( 'plan-interval' );
				planIntervalCount = selectedOption.data( 'plan-interval-count' );
				planTrial = undefined !== selectedOption.data( 'plan-trial' );
				planMaxCharges = selectedOption.data( 'plan-max-charges' );

				if ( 'simpay_custom_plan' === selectedOption.val() ) {
					formData.useCustomPlan = true;
					planAmount = simpayApp.convertToCents( customAmountInput.val() );
				} else {
					formData.useCustomPlan = false;
				}

				if ( planTrial ) {
					formData.amount = 0;
				}
			}

			// Update formData plan attributes
			formData.planId = planId;
			formData.planSetupFee = planSetupFee; // Add the overall setup fee + the individual setup fee together for one total setupFee amount
			formData.planAmount = planAmount;
			formData.planInterval = planInterval;
			formData.planIntervalCount = planIntervalCount;
			formData.isTrial = planTrial;

			// Update custom amount checker
			if ( selectedOption.hasClass( 'simpay-custom-plan-option' ) ) {
				spFormElem.find( '.simpay-has-custom-plan' ).val( 'true' );
			} else {
				spFormElem.find( '.simpay-has-custom-plan' ).val( '' );
			}

			customAmountInput.val( simpayApp.convertFromCents( planAmount ) );

			if ( formData.useCustomPlan ) {
				customAmountInput.focus();
			}

			// Update hidden fields
			spFormElem.find( '.simpay-multi-plan-id' ).val( planId );
			spFormElem.find( '.simpay-multi-plan-setup-fee' ).val( planSetupFee );
			spFormElem.find( '.simpay-max-charges' ).val( planMaxCharges );

			// Custom trigger after completed
			spFormElem.trigger( 'simpayMultiPlanChanged' );

			// Apply any coupons entered
			simpayAppPro.applyCoupon( spFormElem, formData );
		}

	};

	/*$( document ).ready( function( $ ) {

		simpayAppPro.init();

	} );*/

	body.on( 'simpayInit', simpayAppPro.init() );

}( jQuery ) );
