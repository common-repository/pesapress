<?php
/**
 * Core plugin loader
 * Sets up the plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Core_Init {

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
		// Plugin activation Hook.
		register_activation_hook(
			PESAPRESS_PLUGIN_FILE,
			array( $this, 'plugin_activation' )
		);

		// Plugin deactivation hook
		register_deactivation_hook(
			PESAPRESS_PLUGIN_FILE,
			array( $this, 'plugin_deactivation' )
		);

		add_filter(
			'plugin_action_links_' . PESAPRESS_PLUGIN_FILE,
			array( $this, 'plugin_settings_link' )
		);

		if ( is_admin() ) {
			$this->init_admin();
		}
		PP_Controller_Resource::instance();
		PP_Controller_Front::instance();

		/**
		 * Load external integrations
		 *
		 * @since 1.0.1
		 */
		PP_Core_External::instance();

		add_action( 'init', array( $this, 'add_rewrite_rules' ), 1 );
		add_action( 'init', array( $this, 'add_rewrite_tags' ), 1 );
	}

	/**
	 * Action to run on plugin activation
	 *
	 * @since 1.0.0
	 */
	function plugin_activation() {
		PP_Core_Upgrade::instance();
		flush_rewrite_rules();
		do_action( 'pesapress_plugin_activation', $this );
	}

	/**
	 * Deactivation hook
	 *
	 * @since 1.0.0
	 */
	function plugin_deactivation() {
		flush_rewrite_rules();
		do_action( 'pesapress_plugin_deactivation', $this );
	}

	/**
	 * Create setting links
	 */
	function plugin_settings_link( $links ) {
		if ( ! is_network_admin() ) {
			$settings_link = apply_filters(
				'pesapress_plugin_settings_link',
				sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=pesapress' ), __( 'Settings', 'pesapress' ) ),
				$this
			);
			array_unshift( $links, $settings_link );
		}
		return $links;
	}

	/**
	 * Set up admin stuff
	 *
	 * @since 1.0.0
	 */
	private function init_admin() {
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 999 );
		PP_Controller_Admin::instance();
	}

	/**
	 * Add admin bar
	 *
	 * @since 1.0.0
	 *
	 * @param object $wp_admin_bar
	 */
	function admin_bar_menu( $wp_admin_bar ) {
		$menu_title = apply_filters( 'pesapress_admin_menu_title', __( 'PesaPress' ) );
		$args 		= array(
			'id'    => 'pesapress',
			'title' => $menu_title,
			'href'  => admin_url( 'edit.php?page=pesapress' ),
			'meta'  => array(
				'class' => 'pesapress',
				'title' => sprintf( __( '%s Settings', 'pesapress' ), $menu_title ),
			),
		);

		$wp_admin_bar->add_node( $args );
	}


	/**
	 * Add rewrite rules.
	 *
	 * @since  1.0.0
	 */
	public function add_rewrite_rules() {
		// Gateway return - IPN.
		add_rewrite_rule(
			'pp-payment-ipn/(.+)/?',
			'index.php?ppgateway=$matches[1]',
			'top'
		);

		// REdirect
		add_rewrite_rule(
			'pp-payment-return/(.+)/?',
			'index.php?ppreturn=$matches[1]',
			'top'
		);

		do_action( 'pesapress_add_rewrite_rules', $this );
	}

	/**
	 * Add rewrite tags.
	 *
	 * @since  1.0.0
	 */
	public function add_rewrite_tags() {
		// Gateway return - IPN.
		add_rewrite_tag( '%ppgateway%', '(.+)' );

		add_rewrite_tag( '%ppreturn%', '(.+)' );

		do_action( 'pesapress_add_rewrite_tags', $this );
	}
}

