<?php
/**
 * Plugin Name: Easy Digital Downloads - FreshBooks Integration
 * Plugin URI: http://growdevelopment.com
 * Description: Integrates <a href="https://easydigitaldownloads.com/" target="_blank">Easy Digital Downloads</a> with the <a href="http://www.freshbooks.com" target="_blank">FreshBooks Cloud Accounting</a> accounting software.
 * Author: Grow Development
 * Author URI: http://growdevelopment.com
 * Version: 1.0.9
 *
 * Easy Digital Downloads is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Easy Digital Downloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Easy Digital Downloads. If not, see <http://www.gnu.org/licenses/>.
 */


/*
|--------------------------------------------------------------------------
| LICENSING
|--------------------------------------------------------------------------
*/

if ( class_exists('EDD_License')) {
    $license = new EDD_License( __FILE__, 'Freshbooks', '1.0.9', 'Daniel Espinoza' );
}

/*
|--------------------------------------------------------------------------
| CONSTANTS
|--------------------------------------------------------------------------
*/

// plugin folder url
if(!defined('GROWDEVFRESHBOOKS_URL')) {
	define('GROWDEVFRESHBOOKS_URL', plugin_dir_url( __FILE__ ));
}
// plugin folder path
if(!defined('GROWDEVFRESHBOOKS_DIR')) {
	define('GROWDEVFRESHBOOKS_DIR', plugin_dir_path( __FILE__ ));
}
// plugin root file
if(!defined('GROWDEVFRESHBOOKS_FILE')) {
	define('GROWDEVFRESHBOOKS_FILE', __FILE__);
}

/*
|--------------------------------------------------------------------------
| INCLUDES
|--------------------------------------------------------------------------
*/

include_once(GROWDEVFRESHBOOKS_DIR . 'includes/register-settings.php');
include_once(GROWDEVFRESHBOOKS_DIR . 'includes/freshbooks-functions.php'); 
include_once(GROWDEVFRESHBOOKS_DIR . 'includes/payment-actions.php');