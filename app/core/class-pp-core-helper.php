<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Core_Helper {

	/**
	 * Table name keys
	 */
	const GATEWAY_SETTINGS = 'gateway_settings';
	const TRANSACTION_LOGS = 'transaction_logs';
	const TRANSACTION_META = 'transaction_log_meta';

	/**
	 * Current tables
	 */
	static $tables = array();


	/**
	 * Get all the used table names
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public static function table_names( $db = false ) {
		if ( ! $db ) {
			global $wpdb;
			$db = $wpdb;
		}

		return array(
			self::GATEWAY_SETTINGS => $db->prefix . 'pp_gateway_settings',
			self::TRANSACTION_LOGS => $db->prefix . 'pp_transaction_logs',
			self::TRANSACTION_META => $db->prefix . 'pp_transaction_log_meta',
		);
	}

	/**
	 * Get Table Name
	 *
	 * @since 1.0
	 * @param string $name - the name of the table
	 *
	 * @return string/bool
	 */
	public static function get_table_name( $name ) {
		if ( empty( self::$tables ) ) {
			self::$tables = self::table_names();
		}
		return isset( self::$tables[ $name ] ) ? self::$tables[ $name ] : false;
	}

	/**
	 * Get integrated gateways
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function get_integrated_gateways() {
		return apply_filters(
			'pesapress_integrated_gateways',
			array(
				'pesapal' => 'PesaPal',
				'mpesa'   => 'Mpesa',
			)
		);
	}

	/**
	 * Verify nonce.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $action - The action name to verify nonce.
	 * @param  string $request_method - POST or GET
	 * @param  string $nonce_field - The nonce field name
	 * @param  bool   $json - return json or boolean on error
	 *
	 * @return boolean True if verified, false otherwise.
	 */
	public static function verify_nonce( $action = null, $request_method = 'POST', $nonce_field = '_wpnonce', $json = true ) {
		switch ( $request_method ) {
			case 'GET':
				$request_fields = $_GET;
				break;

			case 'REQUEST':
			case 'any':
				$request_fields = $_REQUEST;
				break;

			case 'POST':
			default:
				$request_fields = $_POST;
				break;
		}

		if ( empty( $action ) ) {
			$action = ! empty( $request_fields['action'] ) ? $request_fields['action'] : '';
		}

		if ( ! empty( $request_fields[ $nonce_field ] )
			&& wp_verify_nonce( $request_fields[ $nonce_field ], $action )
		) {
			return apply_filters(
				'pesapress_helper_verify_nonce',
				true,
				$action,
				$request_method,
				$nonce_field
			);
		} else {
			if ( $json ) {
				wp_send_json_error( __( 'Invalid request, you are not allowed to make this request', 'pesapress' ) );
			} else {
				return false;
			}
		}
	}

	/**
	 * The gateway ipn url
	 */
	public static function get_ipn_url() {
		return self::home_url( '/pp-payment-ipn/' );
	}

	/**
	 * The gateway return url
	 *
	 * @param int $id - the order id
	 */
	public static function get_return_url( $id ) {
		return self::home_url( '/pp-payment-return/' . $id );
	}

	/**
	 * Returns the *correct* home-url for front-end pages.
	 *
	 * By default the home_url() function ignores the is_ssl() flag when it's
	 * called from the admin-dashboard. So when redirecting from dashboard to
	 * the front-page it will usually always redirect to http:// even when the
	 * front-end is on https:// - this function fixes this.
	 *
	 * @since  1.0.0
	 * @param  string $path Argument passed to the home_url() function.
	 * @return string The correct URL for a front-end page.
	 */
	public static function home_url( $path = '' ) {
		return self::get_home_url( null, $path );
	}

	/**
	 * Returns the *correct* home-url for front-end pages of a given site.
	 *
	 * {@see description of home_url above for details}
	 *
	 * @since  1.0.0
	 * @param  int    $blog_id Blog-ID; by default the current blog is used.
	 * @param  string $path Argument passed to the home_url() function.
	 * @return string The correct URL for a front-end page.
	 */
	public static function get_home_url( $blog_id = null, $path = '' ) {
		$schema = is_ssl() ? 'https' : 'http';
		$url    = get_home_url( $blog_id, $path, $schema );

		return apply_filters(
			'pesapress_helper_home_url',
			$url,
			$blog_id,
			$path,
			$schema
		);
	}

	/**
	 * Generate the external id
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	static function generate_external_id( $last_id ) {
		static $base = null;
		if ( null === $base ) {
			$base = get_option( 'site_url' );
		}

		$hash = strtolower( md5( $base . uniqid( rand(), true ) ) );

		$hash   = self::convert(
			$hash,
			'0123456789abcdef',
			'0123456789ABCDEFGHIJKLMNOPQRSTUVXXYZabcdefghijklmnopqrstuvxxyz'
		);
		$result = 'pp-' . $last_id . '-' . $hash;
		return $result;
	}

	/**
	 * Converts a number from any base to another base.
	 * The from/to base values can even be non-numeric values.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $number A number in the base_from base.
	 * @param  string $base_from List of characters
	 *         E.g. 0123456789 to convert from decimal.
	 * @param  string $base_to List of characters to use as destination base.
	 *         E.g. 0123456789ABCDEF to convert to hexadecimal.
	 * @return string The converted number
	 */
	static function convert( $number, $base_from = '0123456789', $base_to = '0123456789ABCDEF' ) {
		if ( $base_from == $base_to ) {
			// No conversion needed.
			return $number;
		}

		$retval     = '';
		$number_len = strlen( $number );

		if ( '0123456789' == $base_to ) {
			// Convert a value to normal decimal base.

			$arr_base_from = str_split( $base_from, 1 );
			$arr_number    = str_split( $number, 1 );
			$base_from_len = strlen( $base_from );
			$retval        = 0;
			for ( $i = 1; $i <= $number_len; $i += 1 ) {
				$retval = bcadd(
					$retval,
					bcmul(
						array_search( $arr_number[ $i - 1 ], $arr_base_from ),
						bcpow( $base_from_len, $number_len - $i )
					)
				);
			}
		} else {
			// Convert a value to a NON-decimal base.

			if ( '0123456789' != $base_from ) {
				// Base value is non-decimal, convert it to decimal first.
				$base10 = self::convert( $number, $base_from, '0123456789' );
			} else {
				// Base value is decimal.
				$base10 = $number;
			}

			$arr_base_to = str_split( $base_to, 1 );
			$base_to_len = strlen( $base_to );
			if ( $base10 < strlen( $base_to ) ) {
				$retval = $arr_base_to[ $base10 ];
			} else {
				while ( 0 != $base10 ) {
					$retval = $arr_base_to[ bcmod( $base10, $base_to_len ) ] . $retval;
					$base10 = bcdiv( $base10, $base_to_len, 0 );
				}
			}
		}

		return $retval;
	}
}

