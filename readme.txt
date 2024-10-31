=== PesaPress ===
Contributors: alloykenya
Tags: Pesapal, e-commerce, ecommerce, WooCommerce, PayPal, Wp Travel, Mpesa, Forms
Requires at least: 4.0
Tested up to: 5.6
Requires PHP: 5.6
Stable tag: 2.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

PesaPress allows you to easily integrate Payment gateways to any ecommerce website and solution

== Description ==

A quick way to integrate Payment Gateways to your website to handle the payment process. 
Setup as many credentials and handle payments in all common integrations.
Setup recurring reminders and track how your sales are going from a single dashboard.


Main Features:

* Multiple Payment Gateway integration (Integrate with popular plugins incuding WooCommerce and WP Travel)
* Single shortcode with multiple variables
* Transaction logs
* Dashboard with summary
* Custom Form elements
* Accept all types of payments
* Included Gateways (PesaPal, Mpesa PayBill)

More coming your way (E-commerce plugin integrations) which include :

* PayPal
* Stripe
* Authorize.net
* Paystack
* Africa's Talking
* Alipay
* SecurePay
* 2Checkout
* PayFast


== Installation ==

1. Upload the pesapress folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Once the plugin is activated, Navigate to the admin menu "PesaPress"


== Frequently Asked Questions ==

= Which Payment Gateways are available? =
Currently we only support [PesaPal](https://www.pesapal.com/ "PesaPal Official Page") and Mpesa
We are currently working on integrating more payment gateways on PesaPress

= Do you store any user information? =
We only save payment transaction logs within the plugin


== Screenshots ==

1. Dashboard
2. Transaction View
3. Handle multiple gateways
4. Simple Settings


== Changelog ==

= 2.3 =
* New : Version bump
* Fix : Filters for next version
* Fix : Other fixes

= 2.2.9.2 =
* Fix : Form fields

= 2.2.9.1 =
* New : Action in controller setup to load extra controllers
* New : Redirect url filter
* Fixed : JS message handler for creating gateways

= 2.2.9 =
* Added: New Code Filters

= 2.2.8 =
* Improvements: Code cleanup and styling fixes on order page where the filter section did not look good on smaller screens

= 2.2.7 =
* Added: New shortcode attribute to how amount on payment form called `show_amount` . This is documented in the integrations page

= 2.2.6 =
* Fix: Order saving for WooCommerce. There was an issue with how the transaction data was saved, which threw an error for WooCommerce

= 2.2.5.1 =
* Update: Preparing to port new changes

= 2.2.5 =
* Fix: WooCommerce integration

= 2.2.4 =
* Added : Prefill fields with logged in user details

= 2.2.3 =
* Improved : Code formatting
* Fixed: PHP 7 compatability

= 2.2.2 =
* A few minor fixes

= 2.2.1 =
* Fixed: WP Travel Filter

= 2.2 =
* Fixed: Form field saving
* Fixed: Mpesa integration
* Added: Phone field when setting is Mpesa

= 2.1 =
* Fixed: PHP 5.6 compatability

= 2.0 =
* Added: Gutenberg blocks (Easily add a block in your page and configure your preferred gateway)
* Added: Mpesa integration
* Added: WP Travel integration
* Fixed: WooCommerce integration
* Fixed: Form style

= 1.0.2 =
* Added: Currencies for Tanzania and Uganda

= 1.0.1 =
* Added: Action hooks for transaction log
* Added: WooCommerce integration

= 1.0 =
Initial plugin release