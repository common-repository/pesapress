<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Controller_Admin_DashBoard {

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
	}

	/**
	 * Register admin menu
	 *
	 * @since 1.0.0
	 */
	function admin_menu() {
		add_submenu_page(
			'pesapress',
			__( 'DashBoard', 'pesapress' ),
			__( 'DashBoard', 'pesapress' ),
			'manage_options',
			'pesapress',
			array( $this, 'admin_page' )
		);
	}

	public function admin_page() {
		$view       = new PP_Views_Admin_Dash();
		$view->data = array(
			'list'  => PP_Model_Log::list_by_page( 8, 0, false ),
			'stats' => PP_Model_Statistics::instance(),
		);
		$view->render();
	}
}

