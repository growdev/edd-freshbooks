<?php
/**
 * FreshBooks Functions
 *
 * @package     FreshBooks Integration for Easy Digital Downloads
 * @subpackage  Register Settings
 * @copyright   Copyright (c) 2012, Daniel Espinoza (daniel@growdevelopment.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/

/**
 * Create Invoice in Freshbooks for EDD Payment
 * 
 * Create an invoice record in FreshBooks for the given order.
 * 
 * @param  $payment_id
 * @access public
 * @return void
 *
 **/
function growdev_freshbooks_create_invoice( $payment_id ) {

	global $edd_options;

	$payment = get_post( $payment_id );
	$payment_meta = get_post_meta($payment->ID, '_edd_payment_meta', true);
	$user_info = maybe_unserialize($payment_meta['user_info']);
	$payment_gateway = edd_get_payment_gateway ( $payment_id );

	growdev_freshbooks_addlog( 'START - Creating invoice for order #' .$payment_id  );

	// Check if client exists with customer's email address

	growdev_freshbooks_addlog( 'Checking for client with email: ' . $user_info['email'] );

	$client_id = growdev_freshbooks_lookup_customer( $user_info['email'] );
	
	if ( $client_id == 0 ) {
		try{
			// client doesn't exist, so create a client record in FreshBooks
			$client_id = growdev_freshbooks_create_customer( $payment_id  );
		} catch ( Exception $e ) {
			growdev_freshbooks_addlog( $e->getMessage() ); 
			growdev_freshbooks_addlog('END - Creating invoice for order #' . $payment_id );
			return;
		}
	}

	$xml  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	$xml .= "<request method=\"invoice.create\">";  
	$xml .= "<invoice>";  
	$xml .= "<client_id>" . $client_id . "</client_id>";  			
	$invoice_string = ( $edd_options['growdev_fb_freshbooks_prefix'] == '' ) ?  edd_get_payment_number($payment->ID)  : $edd_options['growdev_fb_freshbooks_prefix'] . edd_get_payment_number($payment->ID);
	$xml .= "<number>" . $invoice_string . "</number>";
	$invoice_status = ( $edd_options['growdev_fb_freshbooks_invoice_status'] == '' ) ? 'sent' : $edd_options['growdev_fb_freshbooks_invoice_status'];
	$xml .= "<status>" . $invoice_status . "</status>";
	$xml .= "<date>" . $payment->post_date . "</date>";  
	$currency = isset($edd_options['currency']) ? $edd_options['currency'] : 'USD';
	$xml .= "<currency_code>" . $currency . "</currency_code>";

	// Order Notes
	$order_notes = '';

	// add order discount as note
	if ( $user_info['discount'] != 'none' ){
		$order_notes = __('Discount used: ','edd') . $user_info['discount'] . "\n" ;
	}
	if ( 'manual_purchases' == $payment_gateway ){
		$order_notes .= __('Order added via Manual Payments.');
	}

	if ( $order_notes !='' ){
		$xml .= "<notes>" . $order_notes . "</notes>";
	}

	// Add customer info
	if (isset($user_info['address'])){
	    $xml .= "<first_name>" . $user_info['first_name'] ."</first_name>";
	    $xml .= "<last_name>" . $user_info['last_name'] ."</last_name>";
	    if (isset($user_info['address']['line1'])) 
		    $xml .= "<p_street1>" . $user_info['address']['line1'] ."</p_street1>";
		if (isset($user_info['address']['line2']))
		    $xml .= "<p_street2>" . $user_info['address']['line2'] ."</p_street2>";
		if (isset($user_info['address']['city']))
		    $xml .= "<p_city>" . $user_info['address']['city'] ."</p_city>";
		if (isset($user_info['address']['state'] ))
		    $xml .= "<p_state>" . $user_info['address']['state'] ."</p_state>";
		if (isset($user_info['address']['country']))
	    	$xml .= "<p_country>" . $user_info['address']['country'] ."</p_country>";
	   	if (isset($user_info['address']['zip']))
	    	$xml .= "<p_code>" . $user_info['address']['zip'] ."</p_code>";
	}
	
	$xml .= "<lines>";
	
	$cart_items = isset($payment_meta['cart_details']) ? maybe_unserialize($payment_meta['cart_details']) : false;
	if( empty( $cart_items ) || !$cart_items ) {
		$cart_items = maybe_unserialize($payment_meta['downloads']);
	}

	if($cart_items) {

		foreach($cart_items as $key => $cart_item) {

			$xml .= "<line>";
			$xml .= "<name>" . $cart_item['name']  . "</name>";


			if ( 'manual_purchases' == $payment_gateway ){
				// Item price will be wrong if they set an amount, and set manually

				$item_price = $cart_item['price'] / count($payment_meta['downloads']);
				$xml .= "<unit_cost>" . $item_price . "</unit_cost>";

			} else {
				if ( 0 < $cart_item['price'] ) {
					$xml .= "<unit_cost>" . $cart_item['price'] . "</unit_cost>";
				} else {
					$xml .= "<unit_cost>0.00</unit_cost>";
				}

			}

			$xml .= "<quantity>1</quantity>";
			$xml .= "<type>Item</type>";
			$xml .= "</line>";
		}
	}
	
	// Add Fees as line items. Not added for manual payments.
	$fees = edd_get_payment_fees( $payment_id );
	if($fees && ( 'manual_purchases' != $payment_gateway )) {
		foreach ($fees as $fee) {			
			// get the fee label and amount
			$xml .="<line>";
			$xml .= "<name>" . $fee['label']  . "</name>";
			$xml .= "<unit_cost>" . $fee['amount'] . "</unit_cost>";
			$xml .= "<quantity>1</quantity>";
			$xml .= "<type>Item</type>";
			$xml .="</line>";
		}	
	}

    // Add

	$xml .= "</lines>";  			
	$xml .= "</invoice>";  
	$xml .= "</request>";
	
	try {
		growdev_freshbooks_addlog( 'sending xml: ' . $xml );
		$result = growdev_freshbooks_apicall( $xml );
		growdev_freshbooks_addlog( 'response: ' . $result );

	} catch ( Exception $e ) {

		growdev_freshbooks_addlog( $e->getMessage() ); 
		return;

	}

	$response = new SimpleXMLElement( $result );
	$is_error = isset( $response['error'] );
	
	if ( ($response->attributes()->status == 'ok') && !$is_error )  {
		// Invoice Created
		add_post_meta( $payment_id, '_freshbooks_invoice_id', (string) $response->invoice_id );
		growdev_freshbooks_addlog('END - Creating invoice for order #' . $payment_id );
	} else {
		// An error occured
		$error_string = __('There was an error adding the invocie to FreshBooks:','edd') . "\n" . 
						__('Error: ','edd') . $response->error . "\n" . 
						__('Error Code: ','edd') . $response->code . "\n" . 
						__('Error in field: ','edd') . $response->field;
						
		growdev_freshbooks_addlog( $error_string );
		growdev_freshbooks_addlog('END - Creating invoice for order #' . $payment_id );
	}
	
}
		
/**
 * Create payment in Freshbooks for EDD Payment
 * 
 * Create a payment record in FreshBooks for the given order.
 * 
 * @param  $payment_id 
 * @access public
 * @return void
 *
 */
function growdev_freshbooks_create_payment( $payment_id ) {

	global $edd_options;

	$payment = get_post($payment_id);

	$fb_invoice_id = (int) get_post_meta( $payment_id, '_freshbooks_invoice_id', true );

	if ( $fb_invoice_id > 0 ) {
		// there is an invoice ID, so create payment
			
		growdev_freshbooks_addlog( 'START - Creating payment in FreshBooks for invoice ID: ' . $fb_invoice_id );
		
		$xml  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
		$xml .= "<request method=\"payment.create\">";  		
		$xml .= "<payment>";
		$xml .= "<invoice_id>" . $fb_invoice_id . "</invoice_id>";

		$payment = edd_get_payment_amount( $payment_id );
		$amount = $payment;

		$xml .= "<amount>" . $amount . "</amount>";
		$xml .= "</payment>";
		$xml .= "</request>";  

		try {

			$result = growdev_freshbooks_apicall( $xml );

		} catch ( Exception $e ) {

			growdev_freshbooks_addlog( $e->getMessage() ); 
			return;

		}
		$response = new SimpleXMLElement( $result );
		$is_error = isset( $response['error'] ) ? true : false;

		if ( ($response->attributes()->status == 'ok') && !$is_error )  {
		
			// set fresh books invoice to status 'paid'
			growdev_freshbooks_addlog('END - Creating payment for order #' . $payment_id );							
		} else {
			// An error occured
			$error_string = __('There was an error creating a payment in FreshBooks:','edd') . "\n" . 
							__('Error: ','edd') . $response->error . "\n" . 
							__('Error Code: ','edd') . $response->code . "\n" . 
							__('Error in field: ','edd') . $response->field;									
			growdev_freshbooks_addlog( $error_string );
		}							
	} else { 
		// no invoice id so exit.				
		return;
	}
}

/**
 * Create customer in Freshbooks for EDD Customer
 * 
 * Create a client record in FreshBooks for the given order.
 * 
 * @param  $payment_id
 * @access public
 * @return void
 * @throws Exception on error
 *
 */
function growdev_freshbooks_create_customer( $payment_id ) {

	global $edd_options;

	$payment = get_post( $payment_id );
	$payment_meta = get_post_meta($payment->ID, '_edd_payment_meta', true);
	$user_info = maybe_unserialize($payment_meta['user_info']); 

	growdev_freshbooks_addlog( 'Creating customer record in FreshBooks for email: ' . $user_info['email'] );
	
	$xml  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	$xml .= "<request method=\"client.create\">";  		
	$xml .= "<client>";
	$xml .= "<first_name>" . $user_info['first_name'] . "</first_name>";
	$xml .= "<last_name>" . $user_info['last_name'] . "</last_name>";
	$xml .= "<email>" . $user_info['email'] . "</email>";

	// Add customer info
	if ( isset($user_info['address']) ){
	    if (isset($user_info['address']['line1'])) 
		    $xml .= "<p_street1>" . $user_info['address']['line1'] ."</p_street1>";
		if (isset($user_info['address']['line2']))
		    $xml .= "<p_street2>" . $user_info['address']['line2'] ."</p_street2>";
		if (isset($user_info['address']['city']))
		    $xml .= "<p_city>" . $user_info['address']['city'] ."</p_city>";
		if (isset($user_info['address']['state'] ))
		    $xml .= "<p_state>" . $user_info['address']['state'] ."</p_state>";
		if (isset($user_info['address']['country']))
	    	$xml .= "<p_country>" . $user_info['address']['country'] ."</p_country>";
	   	if (isset($user_info['address']['zip']))
	    	$xml .= "<p_code>" . $user_info['address']['zip'] ."</p_code>";
	}

	$xml .= "</client>";
	$xml .= "</request>";  

	try {

		$result = growdev_freshbooks_apicall( $xml );

	} catch ( Exception $e ) {

		growdev_freshbooks_addlog( $e->getMessage() ); 
		return 0;

	}
	$response = new SimpleXMLElement( $result );
	$is_error = isset( $response['error'] ) ? true : false;

	if ( ($response->attributes()->status == 'ok') && !$is_error )  {
		// get the first client
		if (isset( $response->client_id )) {
			return $response->client_id ; 
		} else {
			growdev_freshbooks_addlog( 'Unable to create client' . $response ); 
			throw new Exception('Unable to create client' . $response );
		}
					
	} else {
		// An error occured
		$error_string = __('There was an error looking up this customer in FreshBooks:','edd') . "\n" . 
						__('Error: ','edd') . $response->error . "\n" . 
						__('Error Code: ','edd') . $response->code . "\n" . 
						__('Error in field: ','edd') . $response->field;
						
		growdev_freshbooks_addlog( $error_string );
		throw new Exception('Unable to create client' . $response );
	}			

}

/**
 * Check Freshbooks for a client record corresponding to the supplied email address.
 * 
 * @param  $customer_email
 * @access public
 * @return void|$customer_id|0 if customer does not exist in FreshBooks
 *
 **/
function growdev_freshbooks_lookup_customer( $customer_email ) {

	$xml  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	$xml .= "<request method=\"client.list\">";  
	$xml .= "<email>" . $customer_email . "</email>";
	$xml .= "<folder>active</folder>";
	$xml .= "</request>";  

	try {

		$result = growdev_freshbooks_apicall( $xml );

	} catch ( Exception $e ) {

		growdev_freshbooks_addlog( $e->getMessage() ); 
		return;

	}
	$response = new SimpleXMLElement( $result );
	$is_error = isset( $response['error'] ) ? true : false;

	if ( ($response->attributes()->status == 'ok') )  {
		// get the first client
		if (isset( $response->clients->client->client_id )) {
			return $response->clients->client->client_id ; 
		} else {
			return 0; 
		}
					
	} else {
		// An error occured
		$error_string = __('There was an error looking up this customer in FreshBooks:','edd') . "\n" . 
						__('Error: ','edd') . $response->error . "\n" . 
						__('Error Code: ','edd') . $response->code . "\n" . 
						__('Error in field: ','edd') . $response->field;
						
		growdev_freshbooks_addlog( $error_string );
		return 0; 
	}			

}


/**
 * Send an XML request to the FreshBooks API
 * 
 * @param  $xml 
 * @access public
 * @throws Exception
 * @return string $response
 */
function growdev_freshbooks_apicall( $xml ) {

	global $edd_options; 
	
	growdev_freshbooks_addlog( "SENDING XML:\n" . $xml );

    $ch = curl_init();    // initialize curl handle
    curl_setopt($ch, CURLOPT_URL, trim( $edd_options['growdev_fb_freshbooks_api_url'] ) );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 40);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); // add the XML request
    curl_setopt($ch, CURLOPT_USERPWD, $edd_options['growdev_fb_freshbooks_api_token'] . ':X');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($ch);

    if(curl_errno($ch))
    {
        throw new Exception( 'A curl error occured: ' . curl_error($ch) );
    }
    curl_close($ch);

	growdev_freshbooks_addlog( "RESPONSE XML: " . $result );		
	
	return $result; 

} 

/**
 * Output data to a log
 * 
 * @param  $log_string - The string to be appended to the log file.
 * @access public
 * @return void
 *
 **/
function growdev_freshbooks_addlog( $log_string ) {

	global $edd_options; 
	
	if ( isset($edd_options['growdev_fb_freshbooks_debug_on']) && ($edd_options['growdev_fb_freshbooks_debug_on'] == 1) ) {
		$path = GROWDEVFRESHBOOKS_DIR . "freshbooks_debug.log";
		$log_string = "Log Date: " . date("r") . "\n" . $log_string ."\n";
		if ( file_exists( $path ) ) {
			if ($log = fopen( $path, "a") ) {
				fwrite( $log, $log_string, strlen( $log_string ));
				fclose( $log );
			}
		}else {
			if ( $log = fopen($path, "c") ) {
				fwrite( $log, $log_string, strlen( $log_string ));
				fclose( $log );
			}
		}
	}

}
