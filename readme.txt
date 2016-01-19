== Installation ==

 * Unzip the files and upload the folder into your plugins folder (wp-content/plugins/)
 * Activate the plugin in your WordPress admin area.

== Confiuration ==

 * Navigate to Downloads > Settings 
 * Click on the tab labeled "Misc"
 * Find the settings area with heading of "FreshBooks Settings"
 * Configure these settings and add your FreshBooks credentials (see below)


== Where to find your FreshBooks Credentials ==
 
 1. Login to your FreshBooks Account
 2. Click on "My Account" in the top menu
 3. Click on "FreshBooks API"
 4. You will see two fields: API URL and Authentication Token
 5. Copy these two values to your WordPress Easy Digital Downloads dashboard


== Changelog ==

2016-01-12 Version 1.0.9
* Add <type> to payment creation
* Update logging.
* Verify with latest version of EDD

2015-07-27 Version 1.0.9
* Update logging.
* Verify with latest version of EDD

2014-12-09 Version 1.0.8
* Added support for EDD Recurring payments

2014-09-15 Version 1.0.7
* Fix for Undefined index: address
* Fix for cancelled/refunded/updated purchases and subscriptions being sent as payment credits
* Fix for free purchases being sent as credits
* Workaround for Manual Purchases setting a purchase amount. The manual amount will be split out to all downloads.
* Added order note when the order was created by the Manual Payment plugin

2014-07-25 Version 1.0.6
* Fix for unit_cost on recurring payments

2014-06-08 Version 1.0.5
  * Fix for Sequential Order Numbers

2014-04-04 Version 1.0.4
  * Branding update

2014-02-06 Version 1.0.3
  * Added billing address to the invoice
  * Added billing address to the client record
  * Added support for EDD Fees.  Fees are added as line items.
  * Adding a payment sets invoice to "paid" status

2013-06-20 Version 1.0.2

  * Fix for error "Call to a member function add_order_note() on a non-object". The API URL had whitespace. Added trim().

2013-01-10 Version 1.0.1

  * Fix for a PHP warning

2012-10-09 Version 1.0.0

  * Initial release

