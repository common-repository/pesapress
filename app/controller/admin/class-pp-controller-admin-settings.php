<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Controller_Admin_Settings {

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
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'wp_ajax_pesapress_save_settings', array( $this, 'save_settings' ) );
		add_action( 'wp_ajax_pesapress_save_fields', array( $this, 'save_fields' ) );
	}

	/**
	 * Register admin menu
	 *
	 * @since 1.0.0
	 */
	function admin_menu() {
		add_submenu_page(
			'pesapress',
			__( 'Settings', 'pesapress' ),
			__( 'Settings', 'pesapress' ),
			'manage_options',
			'pesapress-settings',
			array( $this, 'admin_page' )
		);
	}

	/**
	 * Get Setting
	 *
	 * @since 1.0.0
	 *
	 * @return PP_Model_Settings
	 */
	public function get_setting() {
		return PP_Model_Settings::instance();
	}

	/**
	 * Admin Page
	 *
	 * @since 1.0.0
	 */
	public function admin_page() {
		$view       = new PP_Views_Admin_Settings();
		$view->data = array(
			'settings' => $this->get_setting(),
		);
		$view->render();
	}

	/**
	 * Save Settings
	 *
	 * @since 1.0.0
	 */
	function save_settings() {
		PP_Core_Helper::verify_nonce( 'pesapress_settings_save' );
		$success_page      = sanitize_text_field( $_POST['success_page'] );
		$currency          = sanitize_text_field( $_POST['currency'] );
		$currency_position = $_POST['currency_position'];
		$currency_decimal  = $_POST['currency_decimal'];
		$settings          = $this->get_setting();
		$settings->set_checkout_setting( 'success_page', $success_page );
		$settings->set_currency_setting( 'currency', $currency );
		$settings->set_currency_setting( 'currency_decimal', $currency_decimal );
		$settings->set_currency_setting( 'currency_position', $currency_position );
		$settings->save();
		wp_send_json_success( __( 'Settings updated', 'pesapress' ) );
	}

	/**
	 * Save form fields
	 *
	 * @since 1.0.0
	 */
	function save_fields() {
		PP_Core_Helper::verify_nonce( 'pesapress_fields_save' );
		$settings       = $_POST['pp'];
		$clean_settings = array();
		foreach ( $settings as $setting ) {
			$setting          = array_map( 'sanitize_text_field', $setting );
			$clean_settings[] = $setting;
		}
		$pesapress_forms = PP_Model_Forms::instance();
		$pesapress_forms->set_settings( $clean_settings );
		$pesapress_forms->save();
		wp_send_json_success( __( 'Form Fields updated', 'pesapress' ) );
	}
}

