<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Handle currency and currency conversions
 */
class PP_Core_Currency {

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
		include_once PESAPRESS_LIB_DIR . '/currencies.php';
	}

	/**
	 * Get settings model
	 *
	 * @since  1.0.0
	 *
	 * @return PP_Model_Settings
	 */
	private function get_settings() {
		return PP_Model_Settings::instance();
	}

	/**
	 * Get currency
	 *
	 * @return string
	 */
	public function get_currency() {
		$settings = $this->get_settings();
		return $settings->get_currency_setting( 'currency', 'USD' );
	}

	/**
	 * List all currencies
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_currencies() {
		return $this->currencies;
	}

	/**
	 * List all general currencies
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_general_currencies() {
		return $this->general_currencies;
	}

	/**
	 * List all paypal currencies
	 *
	 * @since 1.0.1
	 *
	 * @return array
	 */
	public function get_paypal_currencies() {
		return $this->paypal_currencies;
	}

	/**
	 * Format currency
	 *
	 * @return string
	 */
	function format_currency( $currency = false, $amount = false, $decode = false ) {
		$currencies = $this->get_currencies();
		$settings   = $this->get_settings();
		$decimal    = $settings->get_currency_setting( 'currency_decimal', 1 );
		$position   = $settings->get_currency_setting( 'currency_position', 1 );

		if ( ! $currency ) {
			$currency = $settings->get_currency_setting( 'currency', 'USD' );
		}
		if ( isset( $currencies[ $currency ] ) ) {
			// get the currency symbol
			$symbol = $currencies[ $currency ][1];
		} else {
			$symbol = '24';
		}
		// if many symbols are found, rebuild the full symbol
		$symbols = explode( ', ', $symbol );
		if ( is_array( $symbols ) ) {
			$symbol = '';
			foreach ( $symbols as $temp ) {
				$symbol .= '&#x' . $temp . ';';
			}
		} else {
			$symbol = '&#x' . $symbol . ';';
		}
		if ( $decode ) {
			$symbol = utf8_decode( $symbol );
		}

		// check decimal option
		if ( intval( $decimal ) == 0 ) {
			$decimal_place = 0;
			$zero          = '0';
		} else {
			$decimal_place = 2;
			$zero          = '0.00';
		}

		// format currency amount according to preference
		if ( $amount ) {
			if ( is_numeric( $amount ) ) {
				if ( $position == 1 ) {
					return $symbol . number_format_i18n( $amount, $decimal_place );
				} elseif ( $position == 2 ) {
					return $symbol . ' ' . number_format_i18n( $amount, $decimal_place );
				} elseif ( $position == 3 ) {
					return number_format_i18n( $amount, $decimal_place ) . $symbol;
				} elseif ( $position == 4 ) {
					return number_format_i18n( $amount, $decimal_place ) . ' ' . $symbol;
				}
			} else {
				return $amount;
			}
		} elseif ( $amount === false ) {
			return $symbol;
		} else {
			if ( $position == 1 ) {
				return $symbol . $zero;
			} elseif ( $position == 2 ) {
				return $symbol . ' ' . $zero;
			} elseif ( $position == 3 ) {
				return $zero . $symbol;
			} elseif ( $position == 4 ) {
				return $zero . ' ' . $symbol;
			}
		}
	}
}

