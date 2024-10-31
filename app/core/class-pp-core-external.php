<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class PP_Core_External {

	/**
	 * The single instance of the class
	 *
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Get the instance
	 *
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		PP_External_WpTravel_Filters::instance();
		add_action( 'plugins_loaded', array( &$this, 'init_woo' ) );
	}

	/**
	 * Check and initialise Woocoemmrce integration
	 *
	 * @since 1.0.0
	 */
	public function init_woo() {
		if ( function_exists( 'WC' ) ) {
			PP_External_Woo_Filters::instance();
			add_filter( 'woocommerce_payment_gateways', array( &$this, 'load_woo_gateway' ) );
		}
	}

	public function load_woo_gateway( $methods ) {
		$methods[] = 'PP_External_Woo_Gateway';
		return $methods;
	}
}

