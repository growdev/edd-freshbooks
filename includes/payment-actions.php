<?php
/**
 * Payment Actions
 *
 * @package     FreshBooks Integration for Easy Digital Downloads
 * @subpackage  Payment Actions
 * @copyright   Copyright (c) 2012-2016, Daniel Espinoza
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
 */


/**
 * Complete a purchase
 *
 * Exports invoice and payment to FreshBooks when an order is complete
 * Triggered by the edd_update_payment_status() function.
 *
 * @param		 int $payment_id the ID number of the payment
 * @param		 string $new_status the status of the payment, probably "publish"
 * @param		 string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 * @access      private
 * @since       1.0.8
 * @return      void
 */
function growdev_freshbooks_complete_purchase( $payment_id, $new_status, $old_status ) {

	global $edd_options;

	if ( ( $old_status == 'pending' ) &&  ( $new_status == 'publish' )) {
		$create_invoice = isset($edd_options['growdev_fb_freshbooks_invoice']) ?$edd_options['growdev_fb_freshbooks_invoice'] : 0;
		if ( $create_invoice == 1 ) {
			growdev_freshbooks_create_invoice( $payment_id );
		}

		$create_payment = isset($edd_options['growdev_fb_freshbooks_payments']) ?$edd_options['growdev_fb_freshbooks_payments'] : 0;
		if ( $create_payment == 1 ) {
			growdev_freshbooks_create_payment( $payment_id );
		}
	}
}
add_action('edd_update_payment_status', 'growdev_freshbooks_complete_purchase', 10, 3);


/**
 * Record a subscription payment that comes in via IPN from PayPal or webhook from Stripe
 *
 * @param int $payment ID of the new payment that was created
 * @param int $parent_id ID of the original subscription order
 * @param int $amount amount of the subscription payment
 * @param int $txn_id transaction ID of the order
 * @param int $unique_key unique key of the order
 * @since 1.0.8
 */
function growdev_freshbooks_subscription_payment( $payment, $parent_id, $amount, $txn_id, $unique_key ) {

	global $edd_options;

	$create_invoice = isset($edd_options['growdev_fb_freshbooks_invoice']) ?$edd_options['growdev_fb_freshbooks_invoice'] : 0;
	if ( $create_invoice == 1 ) {
		growdev_freshbooks_create_invoice( $payment );
		growdev_freshbooks_addlog( 'Creating invoice for subscription order #' . $payment );
	}

	$create_payment = isset($edd_options['growdev_fb_freshbooks_payments']) ?$edd_options['growdev_fb_freshbooks_payments'] : 0;
	if ( $create_payment == 1 ) {
		growdev_freshbooks_create_payment( $payment );
		growdev_freshbooks_addlog( 'Creating payment for subscription order #' . $payment . ', for amount ' . $amount );
	}

}
add_action('edd_recurring_record_payment', 'growdev_freshbooks_subscription_payment', 10, 5);