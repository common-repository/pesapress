<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WooCommerce Filters
 *
 * @since 1.0.1
 */
class PP_External_Woo_Filters {

	/**
	 * The single instance of the class
	 *
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Currency object
	 *
	 * @var PP_Core_Currency
	 */
	private $currency = null;

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
		$this->currency = PP_Core_Currency::instance();
		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway_class' ) );
		add_filter( 'woocommerce_currencies', array( $this, 'add_currencies' ) );
		add_filter( 'woocommerce_currency_symbol', array( $this, 'add_currency_symbol' ), 10, 2 );

		add_action( 'pesapress_transaction_log_update', array( $this, 'handle_gateway_update' ) );
	}

	/**
	 * Add the gateway class
	 *
	 * @since 1.0.1
	 *
	 * @param array $methods - list of classes
	 *
	 * @return array $methods
	 */
	function add_gateway_class( $methods ) {
		$methods[] = 'PP_External_Woo_Gateway';
		return $methods;
	}

	/**
	 * Add currencies
	 *
	 * @since 1.0.1
	 *
	 * @param array $currencies
	 *
	 * @return array $currencies
	 */
	function add_currencies( $currencies ) {
		$general_currencies = $this->currency->get_general_currencies();
		foreach ( $general_currencies as $symbol => $name ) {
			if ( ! isset( $currencies[ $symbol ] ) ) {
				$currencies[ $symbol ] = $name;
			}
		}
		return $currencies;
	}

	/**
	 * Add currency symbol
	 *
	 * @since 1.0.1
	 *
	 * @param string $currency_symbol - defauly symbol
	 * @param string $currency - -the currency
	 *
	 * @return string $currency_symbol
	 */
	function add_currency_symbol( $currency_symbol, $currency ) {
		$currencies = $this->currency->get_currencies();
		if ( isset( $currencies[ $currency ] ) ) {
			// get the currency symbol
			$symbol = $currencies[ $currency ][1];
		} else {
			$symbol = '24';
		}
		$symbols = explode( ', ', $symbol );
		if ( is_array( $symbols ) ) {
			$symbol = '';
			foreach ( $symbols as $temp ) {
				$symbol .= utf8_decode( '&#x' . $temp . ';' );
			}
			$currency_symbol = $symbol;
		} else {
			$currency_symbol = utf8_decode( '&#x' . $symbol . ';' );
		}
		return $currency_symbol;
	}

	/**
	 * Handle order log change
	 *
	 * @since 1.0.1
	 *
	 * @param PP_Model_Log $order_log - the order log
	 */
	function handle_gateway_update( $order_log ) {
		$order = wc_get_order( $order_log->external_id );
		if ( $order ) {
			if ( $order_log->status === 'paid' ) {
				$order->update_status( 'wc-completed', 'order_note' );
				$order->payment_complete();
			} else {
				$order->update_status( 'wc-processing', sprintf( __( 'Payment accepted, awaiting confirmation. Status : %s' ), $order_log->status ) );
			}
		}
	}
}

