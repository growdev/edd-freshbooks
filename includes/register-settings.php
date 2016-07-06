<?php
/**
 * Register Settings
 *
 * @package     FreshBooks Integration for Easy Digital Downloads
 * @subpackage  Register Settings
 * @copyright   Copyright (c) 2012-2016, Daniel Espinoza (daniel@growdevelopment.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/

/**
 * Register Settings
 *
 * Registers the required settings for the plugin and adds them to the 'Misc' tab.
 *
 * @access      private
 * @since       1.0
 * @param       array $settings
 * @return      array $settings
*/
function growdev_fb_register_settings( $settings) {
	
	$settings[] = array(
					'id' => 'growdev_freshbooks',
					'name' => '<strong>' . __('FreshBooks Settings', 'edd') . '</strong>',
					'desc' => '',
					'type' => 'header',
				);

	$settings[] = array(
					'id' 		=> 'growdev_fb_freshbooks_api_url',
					'name'		=> __('FreshBooks API URL','edd'),
					'desc' 		=> __('Get this from your FreshBooks dashboard. My Account > FreshBooks API', 'edd'),
					'type' 		=> 'text',
				);
	$settings[] = array(
					'id' 		=> 'growdev_fb_freshbooks_api_token',
					'name'		=> __('FreshBooks Authentication Token','edd'),
					'desc' 		=> __('Get this from your FreshBooks dashboard.  My Account > FreshBooks API', 'edd'),
					'type' 		=> 'text',
				);
	$settings[] = array(
					'id' 		=> 'growdev_fb_freshbooks_invoice',
					'name'		=> __('Auto create invoice','edd'),
					'desc' 		=> __('Check this box to create a invoice when the order is placed','edd'),
					'type' 		=> 'checkbox',
				);
	$settings[] = array(
					'id' 		=> 'growdev_fb_freshbooks_payments',
					'name'		=> __('Auto create payment','edd'),
					'desc' 		=> __('Check the box to create a payment when order is placed.', 'edd'),
					'type' 		=> 'checkbox',
				);
	$settings[] = array(
					'id' 		=> 'growdev_fb_freshbooks_invoice_status',
					'name'		=> __('Invoice Status','edd'),
					'desc' 		=> __('Status for the invoices being created in FreshBooks', 'edd'),
					'std' 		=> 'draft',
					'type' 		=> 'select',
					'options'	=> array(
						'draft'			=> __('Draft', 'edd'),
						'sent'			=> __('Sent', 'edd'),
						'viewed'		=> __('Viewed', 'edd')
					)
				);
	$settings[] = array(
					'id' 		=> 'growdev_fb_freshbooks_prefix',
					'name'		=> __('Invoice Number Prefix','edd'),
					'desc' 		=> __('Will be added in front of the EDD order number. Prefix and order number can not exceed 10 characters.','edd'),
					'type' 		=> 'text',
				);
	$settings[] = array(
				'id' 		=> 'growdev_fb_freshbooks_debug_on',
				'name'		=> __('Enable Debug','edd'),
				'desc' 		=> __('Check the box to create a log file for debug purposes.', 'edd'),
				'type' 		=> 'checkbox',
			);	
	return $settings; 

}

add_filter('edd_settings_misc', 'growdev_fb_register_settings', 10, 1);

/**
 * Plugin Links.
 *
 * @param $links
 * @return array
 */
function edd_freshbooks_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="' . admin_url( 'edit.php?post_type=download&page=edd-settings&tab=misc' ) . '">' . __( 'Settings', 'edd' ) . '</a>',
	);

	return array_merge( $plugin_links, $links );
}

add_filter( 'plugin_action_links_' . GROWDEVFRESHBOOKS_FILE, 'edd_freshbooks_plugin_links' );
