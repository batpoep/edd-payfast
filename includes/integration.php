<?php

// registers the gateway
function pf_edd_register_gateway($gateways) {
	$gateways['payfast'] = array('admin_label' => 'Payfast', 
	'checkout_label' => __('Payfast', 'pf_edd'));
	return $gateways;
}
add_filter('edd_payment_gateways', 'pf_edd_register_gateway');

function pf_edd_payfast_cc_form() {
	// register the action to remove default CC form
	return;
}
add_action('edd_payfast_cc_form', 'pf_edd_payfast_cc_form');

/**
 * Process the payment through PayFast
 *
 * @param array   $purchase_data
 */
// ---- MAGI's CODE START ---- //

function post_to_payfast($purchase_data)
{
	edd_debug_log( "posting to payfast soon" );
	$price = $purchase_data['price']; 
	$user_email = $purchase_data['user_email'];
	$gateway = $purchase_data['gateway'];
	edd_debug_log($price);
	edd_debug_log($user_email);
	edd_debug_log($gateway);
	// edd_debug_log($payfast);
	// $fields = prepare_fields($payment_info);
	//do curl using $fields
}

// ---- MAGI's CODE END ---- //

// processes the payment
function pf_edd_process_payment($purchase_data) {
	edd_debug_log( "process payfast payments" );
	global $edd_options;

	/**********************************
	* set transaction mode
	**********************************/

	if(edd_is_test_mode()) {
		$domain = 'https://www.payfast.co.za';
	} else {
		$domain = 'https://sandbox.payfast.co.za';
	}

	/**********************************
	* check for errors here
	**********************************/
	
	/*
	// errors can be set like this
	if(!isset($_POST['card_number'])) {
		// error code followed by error message
		edd_set_error('empty_card', __('You must enter a card number', 'edd'));
	}
	*/
	
	// check for any stored errors
	$errors = edd_get_errors();
	if(!$errors) {

		$purchase_summary = edd_get_purchase_summary($purchase_data);

		/**********************************
		* setup the payment details
		**********************************/
		
		$payment = array( 
			'price' 		=> $purchase_data['price'], 
			'date' 			=> $purchase_data['date'], 
			'user_email' 	=> $purchase_data['user_email'],
			'purchase_key'	=> $purchase_data['purchase_key'],
			'currency' 		=> $edd_options['currency'],
			'downloads' 	=> $purchase_data['downloads'],
			'cart_details' 	=> $purchase_data['cart_details'],
			'user_info' 	=> $purchase_data['user_info'],
			'status' 		=> 'pending'
		);
	
		// record the pending payment
		$payment_id = edd_insert_payment($payment);
		
		$merchant_payment_confirmed = false;
		
		/**********************************
		* Process the credit card here.
		* If not using a credit card
		* then redirect to merchant
		* and verify payment with an IPN
		**********************************/
		post_to_payfast($purchase_data);
		header( 'HTTP/1.0 200 OK' );
		flush();
		
		// if the merchant payment is complete, set a flag
		$merchant_payment_confirmed = true;		
		
		if($merchant_payment_confirmed) { // this is used when processing credit cards on site
			
			// once a transaction is successful, set the purchase to complete
			edd_update_payment_status($payment_id, 'complete');
			
			// go to the success page			
			edd_send_to_success_page();
			
		} else {
			$fail = true; // payment wasn't recorded
		}
		
	} else {
		$fail = true; // errors were detected
	}
	
	if( $fail !== false ) {
		// if errors are present, send the user back to the purchase page so they can be corrected
		edd_send_back_to_checkout('?payment-mode=' . $purchase_data['post_data']['edd-gateway']);
	}
}
add_action('edd_gateway_payfast', 'pf_edd_process_payment');

/**
 * Register the PayFast gateway subsection
 *
 * @since  2.6
 * @param  array $gateway_sections  Current Gateway Tab subsections
 * @return array                    Gateway subsections with PayFast
 */
 
function edd_register_payfast_section( $gateway_sections ) {
	$gateway_sections['payfast'] = __( 'PayFast', 'easy-digital-downloads' );

	return $gateway_sections;
}
add_filter( 'edd_settings_sections_gateways', 'edd_register_payfast_section', 1, 1 );


/**
 * Registers the PayFast settings for the PayFast subsection
 *
 * @since  2.6
 * @param  array $gateway_settings  Gateway tab settings
 * @return array                    Gateway tab settings with the PayFast settings
 */
function pf_edd_add_settings($settings) {
	
	$payfast_settings = array(
		array(
			'id' => 'payfast_settings',
			'name' => '<strong>' . __('Payfast Settings', 'pf_edd') . '</strong>',
			'desc' => __('Configure the gateway settings', 'pf_edd'),
			'type' => 'header'
		),
		
		array(
			'id' => 'merchant_id',
			'name' => __('Merchant ID', 'pf_edd'),
			'desc' => __('Enter your Merchant ID', 'pf_edd'),
			'type' => 'text',
			'size' => 'regular'
		),
		array(
			'id' => 'merchant_key',
			'name' => __('Merchant Key', 'pf_edd'),
			'desc' => __('Enter your Merchant Key', 'pf_edd'),
			'type' => 'text',
			'size' => 'regular'
		),
		array(
			'id' => 'passphrase',
			'name' => __('Passphrase', 'pf_edd'),
			'desc' => __('Enter your Passphrase (Optional)', 'pf_edd'),
			'type' => 'text',
			'size' => 'regular'
		),
		array(
			'id' => 'sandbox_merchant_idmerchant_id',
			'name' => __('Sandbox Merchant ID', 'pf_edd'),
			'desc' => __('Enter your Sandbox Merchant ID', 'pf_edd'),
			'type' => 'text',
			'size' => 'regular'
		),
		array(
			'id' => 'sandbox_merchant_key',
			'name' => __('Sandbox Merchant Key', 'pf_edd'),
			'desc' => __('Enter your Sandbox Merchant Key', 'pf_edd'),
			'type' => 'text',
			'size' => 'regular'
		)

	);
	$payfast_settings = apply_filters( 'edd_payfast_settings', $payfast_settings );
	
	$settings['payfast'] = $payfast_settings;

	return $settings;
}
add_filter('edd_settings_gateways', 'pf_edd_add_settings');