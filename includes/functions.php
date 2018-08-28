<?php

/******************************
* Functions: EDD Payfast
******************************/

// // registers the gateway
// function pw_edd_register_gateway($gateways) {
	// $gateways['payfast'] = array('admin_label' => 'Payfast Gateway', 
	// 'checkout_label' => __('Payfast Gateway', 'pw_edd'));
	// return $gateways;
// }
// add_filter('edd_payment_gateways', 'pw_edd_register_gateway');

function eddpf_extra_edd_currencies( $currencies ) {
	$currencies['ZAR'] = __('South African Rand', 'your_domain');
	return $currencies;
}
add_filter('edd_currencies', 'eddpf_extra_edd_currencies');
