var form = document.getElementById('ssfForm');
var amount_field = document.getElementById('ssfAmountField');
var amount_input = document.getElementById('ssfAmountInput');
var email_input = document.getElementById('ssfEmailInput');
var form_error_container = document.getElementById('ssfErrorExplanation');
var get_zip = false;
var get_add = false;

var handler = StripeCheckout.configure({
	key: form_info.api_key,
	locale: 'auto',
	token: function(token) {
		var token_input = document.getElementById('stripeToken');
		token_input.value = token.id;
		form.submit();
	}
});

function maybe_submit_form(event) {
	event.preventDefault();
	validate_amount();
	if (this.checkValidity()) {
		var amount = get_unmasked_money_value(amount_input);
		open_modal(amount);
	}
}
function open_modal(amount) {
	if ( form_info.get_zip === 'yes' ) {
		get_zip = true;
	}
	if ( form_info.get_add === 'yes' ) {
		get_add = true;
	}
    handler.open({
		amount: amount,
		name: form_info.title,
		description: form_info.desc,
		zipCode: get_zip,
		billingAddress: get_add,
    });
}
function validate_amount() {
	var amount_enough = is_amount_enough();
	if (amount_enough) {
		amount_input.setCustomValidity('');
	} else {
		var form_min_string = form_info.minimum.toString();
		var dot_position = form_info.minimum.length - 2;
		form_min_string = "$" + form_min_string.substr(0, dot_position) + '.' + form_min_string.substr(dot_position);
		amount_input.setCustomValidity('Please enter an amount of at least ' + form_min_string);
	}
}
function is_amount_enough() {
	var amount = get_unmasked_money_value(amount_input);
	if ( parseInt(amount, 10) < parseInt(form_info.minimum, 10) ) {
		return false;
	} else {
		return true;
	}
}
function get_unmasked_money_value(input) {
	unmask_money_input(input);
	var value = input.value;
	mask_money_input(input);
	return value;
}

function close_modal() {
	handler.close();
}
function mask_money_input(input) {
	VMasker(input).maskMoney({
		separator: '.',
		unit: "$",
	});
}
function unmask_money_input(input) {
	VMasker(input).unMask();
}

mask_money_input(amount_input);
form.addEventListener('submit', maybe_submit_form);
amount_input.addEventListener('blur', validate_amount);
window.addEventListener('popstate', close_modal);
