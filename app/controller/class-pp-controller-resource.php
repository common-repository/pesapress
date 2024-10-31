<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Controller_Resource {

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
		add_action( 'init', array( $this, 'setup_block' ) );
		add_filter( 'block_categories', array( $this, 'block_categories' ), 10, 2 );
	}

	/**
	 * Set up block
	 *
	 * @since 1.0.0
	 */
	function setup_block() {
		// Scripts.
		wp_register_script(
			'pesapress-block-js',
			PESAPRESS_ASSETS_URL . '/block/pesapress-block.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
			false,
			true
		);

		/**
		 * Ajax variables
		 */
		$vars = array(
			'gutenberg' => array(
				'title'    => __( 'Display a custom HTML table with all users', 'pesapress' ),
				'field'    => array(
					'amount'  => __( 'Total Amount', 'pesapress' ),
					'gateway' => __( 'Gateway ID', 'pesapress' ),
				),
				'gateways' => PP_Model_Gateway::list_dropdown(),
			),
		);

		wp_localize_script( 'pesapress-block-js', 'pesapress', $vars );

		register_block_type(
			'pesapress/pesapress-block',
			array(
				'attributes'      => array(
					'amount'     => array(
						'type'    => 'string',
						'default' => '10',
					),
					'gateway_id' => array(
						'type'    => 'string',
						'default' => '10',
					),
				),
				'editor_script'   => 'pesapress-block-js',
				'render_callback' => array( 'PP_Controller_Front', 'payment_form' ),
			)
		);
	}

	/**
	 * Set up custom block categories
	 *
	 * @param array   $categories - default categories
	 * @param WP_Post $page - the current page
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	function block_categories( $categories, $post ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'pesapress',
					'title' => __( 'PesaPress', 'pesapress' ),
				),
			)
		);
	}
}


