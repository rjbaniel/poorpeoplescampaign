=== WP Simple Pay Pro 3 ===
Requires at least: 4.7
Tested up to: 4.9
Requires PHP: 5.3
License: GPLv2 or later

== Description ==

WP Simple Pay Pro 3 - Add high conversion Stripe Checkout forms to your WordPress site.

== Changelog ==

= 3.1.7 - October 25, 2017 =

* Feature: Added option to set the payment success page (or redirect URL) per payment form.
* Fix: Super rare case where a certain amount value was off by one cent when using dropdowns as an amount field.
* Fix: Add fallback check for wp_doing_ajax() introduced in WP 4.7.
* Dev: Better handling of alternate Stripe API keys.
* Dev: Add better extensibility to webhook handling with some new and refined action hooks.
* Dev: Updated to Stripe PHP library v5.4.0.

= 3.1.6 - September 28, 2017 =

* Feature: Add support for zero amount and less than 50 currency unit subscription plans.
* Fix: Refresh license key check on license page to make it easier for upgraded accounts to see changes right away.
* Fix: Make sure automatic updates work on multi-site.
* Tweak: Add an error message if trying to activate Pro with Lite already installed.
* Dev: Overhaul to plugin code structure.
* Dev: Add filter simpay_payment_button_class to add or remove classes from the on-page form payment button.
* Dev: Add metadata to the charge_created and subscription_created action hooks.
* Dev: Updated to Stripe PHP library v5.2.3.

= 3.1.5 - September 8, 2017 =

* Fix: Metadata values for radio field + custom amount will show the label instead of "on".
* Fix: Prevent activation when WP Simple Pay Lite is active to avoid a fatal error.

= 3.1.4 - August 29, 2017 =

* Fix: Live mode keys will now load properly.
* Updated to Stripe PHP library v5.2.0.

= 3.1.3 - August 28, 2017 =

* Fix: Numeric only plan IDs will now work correctly.
* Tweak: JavaScript updates to improve performance.
* Dev: Update success page redirect filter to allow for external URLs.
* Dev: Make simpay_fee_amount filter also work as a form-specific filter.
* Dev: Make simpay_fee_percent filter also work as a form-specific filter.
* Dev: Add simpay_plan_name_label filter.
* Updated to Stripe PHP library v5.1.3.

= 3.1.2 - July 24, 2017 =

* Fix: Correct a JavaScript bug that was breaking forms.

= 3.1.1 - July 24, 2017 =

* Feature: Added setting to control Tax Rate Percent.
* Fix: Fix bug with invoices showing an initial $0.00 charge in some cases.
* Fix: Send Stripe API Version information with requests.
* Fix: Remove payment confirmation pages on full uninstall.
* Tweak: Automatic cache exclusion for payment confirmation pages.
* Dev: Add simpay_cache_exclusion_uris filter.
* Dev: Add per-form filter for new tax percent setting.

= 3.1.0 - July 13, 2017 =

* Feature: Installment plans added for subscriptions.
* Feature: Add a setting to control free trial button text.
* Fix: Remove support for Alipay since it is no longer supported through Stripe Checkout.
* Fix: Added plugin information to Stripe API calls.
* Tweak: Make recurring total label show the correct amount when multiplied by a quantity.
* UI: Minor tweaks to the multi-plan admin area.
* Dev: Updated to Stripe PHP library v5.1.1.

= 3.0.3 - June 29, 2017 =

* UI: Update field label description for checkbox custom field.
* UI: Add a placeholder setting for coupon fields.
* Fix: Make sure metadata gets added to the subscription if it has a trial period.
* Fix: Get the processing text setting to work correctly.
* Dev: Add 3 new action hooks.
* Dev: Updated to Stripe PHP library v5.0.0.

= 3.0.2 - June 21, 2017 =

* Fix: Make trial details template load correctly for multi-plans.

= 3.0.1 - June 21, 2017 =

* Fix: Bug with fee amount filter causing issues with zero-decimal currencies.
* Fix: Subscription custom amount field will properly take the default value.
* Fix: Custom amount default fields can now be left blank.
* Fix: Allow HTML in the custom field checkbox label.
* Fix: Checkout overlay will load properly now in IE.
* Dev: Updated to Stripe PHP library v4.13.0.

= 3.0.0 - June 13, 2017 =

* A brand spankin' new rewrite from the ground up. Too many updates to list here.
