<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Controller_Admin_Gateways {

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
		add_action( 'wp_ajax_pesapress_add_gateway', array( $this, 'add_gateway' ) );
		add_action( 'wp_ajax_pesapress_load_setup_form', array( $this, 'load_setup_form' ) );
		add_action( 'wp_ajax_pesapress_save_gateway', array( $this, 'save_gateway' ) );
		add_action( 'wp_ajax_pesapress_clone_gateway', array( $this, 'clone_gateway' ) );
		add_action( 'wp_ajax_pesapress_delete_gateway', array( $this, 'delete_gateway' ) );
		add_action( 'wp_ajax_pesapress_edit_gateway', array( $this, 'load_edit_form' ) );
		add_action( 'wp_ajax_pesapress_update_gateway', array( $this, 'update_gateway' ) );
		add_action( 'wp_ajax_pesapress_bulk_gateway', array( $this, 'bulk_actions' ) );
	}

	/**
	 * Register admin menu
	 *
	 * @since 1.0.0
	 */
	function admin_menu() {
		add_submenu_page(
			'pesapress',
			__( 'Gateways', 'pesapress' ),
			__( 'Gateways', 'pesapress' ),
			'manage_options',
			'pesapress-gateways',
			array( $this, 'admin_page' )
		);
	}

	public function admin_page() {
		$pagenum    = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
		$per_page   = apply_filters( 'pesapress_gateways_per_page', 20 );
		$view       = new PP_Views_Admin_Gateways();
		$view->data = array(
			'per_page' => $per_page,
			'total'    => PP_Model_Gateway::count_all(),
			'list'     => PP_Model_Gateway::list_by_page( $per_page, $pagenum ),
		);
		$view->render();
	}

	/**
	 * Add Gateway
	 * Load the gateway modal
	 *
	 * @since 1.0.0
	 */
	public function add_gateway() {
		PP_Core_Helper::verify_nonce( 'pesapress_add_gateway' );
		$view       = new PP_Views_Admin_Gateways_Create();
		$view->data = array(
			'gateways' => PP_Core_Helper::get_integrated_gateways(),
		);

		wp_send_json_success(
			array(
				'html'  => $view->render( true ),
				'title' => __( 'New Gateway', 'pesapress' ),
			)
		);
	}

	/**
	 * Load setup form
	 *
	 * @return string
	 */
	public function load_setup_form() {
		PP_Core_Helper::verify_nonce( 'pesapress_load_setup_form' );
		$integration = sanitize_text_field( $_POST['gateway'] );
		$class       = 'PP_Views_Admin_Gateways_Integrations_' . ucfirst( $integration );
		if ( class_exists( $class ) ) {
			$view = new $class();
			wp_send_json_success( $view->render( true ) );
		}
		wp_send_json_error( __( 'Gateway not fully integrated', 'pesapress' ) );
	}

	/**
	 * Save Gateway
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function save_gateway() {
		PP_Core_Helper::verify_nonce( 'pesapress_gateway_save' );
		$integration                  = sanitize_text_field( $_POST['gateway'] );
		$mode                         = sanitize_text_field( $_POST['mode'] );
		$data                         = $_POST['data'];
		$data                         = array_map( 'sanitize_text_field', $data );
		$gateway                      = new PP_Model_Gateway();
		$gateway->setting_name        = $integration;
		$gateway->active_mode         = $mode;
		$gateway->setting_details_raw = $data;
		$gateway->save();
		if ( $gateway->setting_id > 0 ) {

			if ( $integration == 'mpesa' ) {
				$pesapress_forms = PP_Model_Forms::instance();
				$settings        = $pesapress_forms->get_settings();
				$has_phone       = false;
				foreach ( $settings as $setting ) {
					if ( $setting['uname'] === 'phone' ) {
						$has_phone = true;
					}
				}
				if ( ! $has_phone ) {
					$settings[] = array(
						'name'      => __( 'Phone', 'pesapress' ),
						'type'      => 'text',
						'uname'     => 'pesapress_phone',
						'initial'   => '',
						'mandatory' => 'checked',
						'visible'   => 'checked',
						'delete'    => false,
					);
					$pesapress_forms->set_settings( $settings );
					$pesapress_forms->save();
				}
			}
			wp_send_json_success(
				array(
					'message' => __( 'Gateway setting saved', 'pesapress' ),
					'url'     => admin_url( 'admin.php?page=pesapress-gateways' ),
				)
			);
		}
		wp_send_json_error( __( 'Error saving gateway', 'pesapress' ) );
	}

	/**
	 * Clone gateway
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function clone_gateway() {
		PP_Core_Helper::verify_nonce( 'pesapress_gateway_clone' );
		$id      = sanitize_text_field( $_POST['id'] );
		$gateway = new PP_Model_Gateway( $id );
		$gateway->clone();
		if ( $gateway->setting_id > 0 ) {
			wp_send_json_success(
				array(
					'message' => __( 'Gateway cloned', 'pesapress' ),
					'url'     => admin_url( 'admin.php?page=pesapress-gateways' ),
				)
			);
		}
		wp_send_json_error( __( 'Error cloning gateway', 'pesapress' ) );
	}

	/**
	 * Delete gatewy
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function delete_gateway() {
		PP_Core_Helper::verify_nonce( 'pesapress_gateway_delete' );
		$id      = sanitize_text_field( $_POST['id'] );
		$gateway = new PP_Model_Gateway( $id );
		$gateway->delete();
		wp_send_json_success(
			array(
				'message' => __( 'Gateway deleted', 'pesapress' ),
				'url'     => admin_url( 'admin.php?page=pesapress-gateways' ),
			)
		);
	}

	/**
	 * Load edit form
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function load_edit_form() {
		PP_Core_Helper::verify_nonce( 'pesapress_edit_gateway' );
		$id      = sanitize_text_field( $_POST['id'] );
		$gateway = new PP_Model_Gateway( $id );
		if ( $gateway->setting_id > 0 ) {
			$class = 'PP_Views_Admin_Gateways_Integrations_' . ucfirst( $gateway->setting_name );
			if ( class_exists( $class ) ) {
				$view       = new PP_Views_Admin_Gateways_Edit();
				$view->data = array(
					'form'    => $class,
					'gateway' => $gateway,
				);
				wp_send_json_success(
					array(
						'html'  => $view->render( true ),
						'title' => __( 'Edit Gateway', 'pesapress' ),
					)
				);
			}
		}

		wp_send_json_error( __( 'Gateway not fully integrated', 'pesapress' ) );
	}

	/**
	 * Update gateway
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function update_gateway() {
		PP_Core_Helper::verify_nonce( 'pesapress_gateway_update' );
		$id      = sanitize_text_field( $_POST['id'] );
		$gateway = new PP_Model_Gateway( $id );
		if ( $gateway->setting_id > 0 ) {
			$mode                         = sanitize_text_field( $_POST['mode'] );
			$data                         = $_POST['data'];
			$data                         = array_map( 'sanitize_text_field', $data );
			$gateway->active_mode         = $mode;
			$gateway->setting_details_raw = $data;
			$gateway->save();
			if ( $gateway->setting_id > 0 ) {
				if ( $gateway->setting_name == 'mpesa' ) {
					$pesapress_forms = PP_Model_Forms::instance();
					$settings        = $pesapress_forms->get_settings();
					$has_phone       = false;
					foreach ( $settings as $setting ) {
						if ( $setting['uname'] === 'phone' ) {
							$has_phone = true;
						}
					}
					if ( ! $has_phone ) {
						$settings[] = array(
							'name'      => __( 'Phone', 'pesapress' ),
							'type'      => 'text',
							'uname'     => 'pesapress_phone',
							'initial'   => '',
							'mandatory' => 'checked',
							'visible'   => 'checked',
							'delete'    => false,
						);
						$pesapress_forms->set_settings( $settings );
						$pesapress_forms->save();
					}
				}
				wp_send_json_success(
					array(
						'message' => __( 'Gateway setting updated', 'pesapress' ),
						'url'     => admin_url( 'admin.php?page=pesapress-gateways' ),
					)
				);
			}
			wp_send_json_error( __( 'Error updating gateway', 'pesapress' ) );
		}
		wp_send_json_error( __( 'Gateway not fully integrated', 'pesapress' ) );
	}

	/**
	 * Process bulk actions
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function bulk_actions() {
		PP_Core_Helper::verify_nonce( 'pesapress_bulk_action' );
		$ids    = sanitize_text_field( $_POST['ids'] );
		$action = sanitize_text_field( $_POST['bulk_action'] );
		if ( ! empty( $ids ) ) {
			if ( ! empty( $action ) ) {
				switch ( $action ) {
					case 'disable':
						PP_Model_Gateway::bulk_disable_enable( $ids, 0 );
						break;
					case 'enable':
						PP_Model_Gateway::bulk_disable_enable( $ids, 1 );
						break;
					case 'delete':
						PP_Model_Gateway::bulk_delete( $ids );
						break;
				}
				wp_send_json_success(
					array(
						'message' => __( 'Bulk action implemented', 'pesapress' ),
						'url'     => admin_url( 'admin.php?page=pesapress-gateways' ),
					)
				);
			} else {
				wp_send_json_error( __( 'No action select', 'pesapress' ) );
			}
		}
		wp_send_json_error( __( 'No id selected', 'pesapress' ) );
	}
}

