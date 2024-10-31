<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Plugin settings
 */
class PP_Model_Settings {

	/**
	 * Settings key
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $option_key = 'pesapress_settings';

	/**
	 * The single instance of the class
	 *
	 * @since 1.0.0
	 */
	protected static $_instance = null;


	/**
	 * Currency Settings
	 *
	 * @since  1.0.0
	 *
	 * @var array
	 */
	protected $currency_settings = array();


	/**
	 * Checkout Settings
	 *
	 * @since  1.0.0
	 *
	 * @var array
	 */
	protected $checkout_settings = array();

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
		$this->_load();
	}

	/**
	 * Load model
	 */
	private function _load() {
		$settings = get_option( $this->option_key );
		$this->_import( $settings );
	}

	/**
	 * Import data to option
	 *
	 * @param array $data
	 */
	private function _import( $data ) {
		if ( $data ) {
			foreach ( $data as $key => $value ) {
				if ( $value ) {
					$value = maybe_unserialize( $value );
				}

				if ( null !== $value ) {
					$this->set_field( $key, $value );
				}
			}
		}
	}

	/**
	 * Set field value, bypassing the __set validation.
	 *
	 * Used for loading from db.
	 *
	 * @since  1.0.0
	 *
	 * @param string $field
	 * @param mixed  $value
	 */
	public function set_field( $field, $value ) {
		// Don't deserialize values of "private" fields.
		if ( '_' !== $field[0] ) {

			// Only set values of existing fields, don't create a new field.
			if ( property_exists( $this, $field ) ) {
				$this->$field = $value;
			}
		}
	}


	/**
	 * Save content in wp_option table.
	 *
	 * @since  1.0.0
	 */
	public function save() {
		$settings = array(
			'currency_settings' => $this->currency_settings,
			'checkout_settings' => $this->checkout_settings,
		);
		update_option( $this->option_key, $settings );

		$this->instance = $this;
	}

	/**
	 * Reads the options from options table
	 *
	 * @since  1.0.0
	 */
	public function refresh() {
		$this->_load();
	}

	/**
	 * Delete from wp option table
	 *
	 * @since  1.0.0
	 */
	public function delete() {
		delete_option( $this->option_key );
	}


	/**
	 * Get currency settings
	 *
	 * @param string $key - the setting key
	 *
	 * @return bool|string|array
	 */
	public function get_currency_setting( $key = null, $default = false ) {
		if ( ! empty( $this->currency_settings ) ) {
			$settings = $this->currency_settings;
			if ( $key ) {
				if ( isset( $settings[ $key ] ) ) {
					return $settings[ $key ];
				}
			} else {
				return $settings;
			}
		}
		return $default;
	}

	/**
	 * Get Checkout settings
	 *
	 * @param string $key - the setting key
	 *
	 * @return bool|string|array
	 */
	public function get_checkout_setting( $key = null, $default = false ) {
		if ( ! empty( $this->checkout_settings ) ) {
			$settings = $this->checkout_settings;
			if ( $key ) {
				if ( isset( $settings[ $key ] ) ) {
					return $settings[ $key ];
				}
			} else {
				return $settings;
			}
		}
		return $default;
	}

	/**
	 * Set checkout setting
	 *
	 * @param string $key - the setting key
	 * @param string $value - the value
	 */
	public function set_checkout_setting( $key, $value ) {
		$this->checkout_settings[ $key ] = $value;
	}

	/**
	 * Set currency setting
	 *
	 * @param string $key - the setting key
	 * @param string $value - the value
	 */
	public function set_currency_setting( $key, $value ) {
		$this->currency_settings[ $key ] = $value;
	}
}

