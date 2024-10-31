<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Controller_Admin_Logs {

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

		add_action( 'wp_ajax_pesapress_view_log', array( $this, 'view_log' ) );
		add_action( 'wp_ajax_pesapress_update_log', array( $this, 'update_log' ) );
		add_action( 'wp_ajax_pesapress_delete_log', array( $this, 'delete_log' ) );
		add_action( 'wp_ajax_pesapress_bulk_logs', array( $this, 'bulk_actions' ) );
		add_action( 'wp_ajax_pesapress_filter_logs', array( $this, 'filter_logs' ) );
		add_action( 'admin_action_pesapress_export_logs', array( $this, 'export_logs' ) );
	}

	/**
	 * Register admin menu
	 *
	 * @since 1.0.0
	 */
	function admin_menu() {
		add_submenu_page(
			'pesapress',
			__( 'Transactions', 'pesapress' ),
			__( 'Transactions', 'pesapress' ),
			'manage_options',
			'pesapress-logs',
			array( $this, 'admin_page' )
		);
	}

	public function admin_page() {
		$pagenum    = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
		$per_page   = apply_filters( 'pesapress_logs_per_page', 20 );
		$view       = new PP_Views_Admin_Logs();
		$gateway    = isset( $_GET['gateway_id'] ) ? $_GET['gateway_id'] : false;
		$view->data = array(
			'per_page' => $per_page,
			'total'    => PP_Model_Log::count_all( $gateway ),
			'list'     => PP_Model_Log::list_by_page( $per_page, $pagenum, $gateway ),
			'gateways' => PP_Model_Gateway::list_simple(),
		);
		$view->render();
	}

	/**
	 * View log
	 *
	 * @since 1.0.0
	 */
	function view_log() {
		PP_Core_Helper::verify_nonce( 'pesapress_view_log' );
		$id         = sanitize_text_field( $_POST['id'] );
		$view       = new PP_Views_Admin_Logs_View();
		$view->data = array(
			'log' => new PP_Model_Log( $id ),
		);
		wp_send_json_success(
			array(
				'html'  => $view->render( true ),
				'title' => __( 'View Log', 'pesapress' ),
			)
		);
	}

	/**
	 * Update log
	 *
	 * @since 1.0.0
	 */
	function update_log() {
		$id     = sanitize_text_field( $_POST['id'] );
		$status = sanitize_text_field( $_POST['status'] );
		PP_Core_Helper::verify_nonce( 'pesapress_manage_log_' . $id );
		$log = new PP_Model_Log( $id );
		if ( $log->log_id && $log->log_id > 0 ) {
			$log->status = $status;
			$log->save();
			wp_send_json_success();
		}
		wp_send_json_error( __( 'Invalid log', 'pesapress' ) );
	}

	/**
	 * Delete Single log
	 *
	 * @since 1.0.0
	 */
	function delete_log() {
		PP_Core_Helper::verify_nonce( 'pesapress_log_delete' );
		$id  = sanitize_text_field( $_POST['id'] );
		$log = new PP_Model_Log( $id );
		$log->delete();
		wp_send_json_success(
			array(
				'message' => __( 'Gateway deleted', 'pesapress' ),
				'url'     => admin_url( 'admin.php?page=pesapress-logs' ),
			)
		);
	}

	/**
	 * Handle Bulk Actions
	 *
	 * @since 1.0.0
	 */
	function bulk_actions() {
		PP_Core_Helper::verify_nonce( 'pesapress_bulk_logs' );
		$ids    = sanitize_text_field( $_POST['ids'] );
		$action = sanitize_text_field( $_POST['bulk_action'] );
		if ( ! empty( $ids ) ) {
			if ( ! empty( $action ) ) {
				switch ( $action ) {
					case 'delete':
						PP_Model_Log::bulk_delete_by( 'logs', $ids );
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

	/**
	 * Filter logs
	 */
	function filter_logs() {
		PP_Core_Helper::verify_nonce( 'pesapress_filter_logs' );
		$action = sanitize_text_field( $_POST['bulk_action'] );
		if ( ! empty( $action ) ) {
			wp_send_json_success(
				array(
					'message' => __( 'Loading Gateway Logs', 'pesapress' ),
					'url'     => admin_url( 'admin.php?page=pesapress-logs&gateway_id=' . $action ),
				)
			);
		} else {
			wp_send_json_success(
				array(
					'message' => __( 'Loading all logs', 'pesapress' ),
					'url'     => admin_url( 'admin.php?page=pesapress-logs' ),
				)
			);
		}
	}

	/**
	 * Export logs
	 *
	 * @since 1.0.0
	 */
	function export_logs() {
		PP_Core_Helper::verify_nonce( 'pesapress_export_logs' );
		$ids = sanitize_text_field( $_POST['ids'] );
		PP_Model_Log::export( $ids );
	}
}

