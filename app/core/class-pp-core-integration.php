<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class PP_Core_Integration {

	/**
	 * Gateway name
	 *
	 * @var string
	 */
	public $gateway = 'default';

	/**
	 * Gateway Model
	 *
	 * @var PP_Model_Gateway
	 */
	public $model = false;

	/**
	 * Gateway Mode
	 *
	 * @var string
	 */
	public $mode = '';

	/**
	 * IPN URL
	 *
	 * @var string
	 */
	public $ipn_url = '';

	/**
	 * The CallBack url
	 *
	 * @var string
	 */
	public $callback_url = '';

	/**
	 * Currency
	 *
	 * @var string
	 */
	public $currency = '';

	/**
	 * Order log
	 *
	 * @var PP_Model_Log
	 */
	public $order_log = null;

	/**
	 * Initialize the Model
	 *
	 * @since 1.0
	 */
	public function __construct( $model ) {
		$this->model    = $model;
		$this->mode     = $model->active_mode;
		$this->ipn_url  = $model->get_ipn_url();
		$curr           = PP_Core_Currency::instance();
		$this->currency = $curr->get_currency();
		$this->init();
	}

	/**
	 * Init
	 *
	 * @since 1.0
	 */
	public function init() {
		// Call init instead of __construct in modules
	}

	/**
	 * Set callback url
	 *
	 * @param int $order_id - the order id
	 */
	public function set_callback_url( $order_id, $callback_url = false ) {
		$this->callback_url = ( ! $callback_url ) ? PP_Core_Helper::get_return_url( $order_id ) : $callback_url;
	}

	/**
	 * Process Purchase
	 *
	 * @since 1.0.0
	 */
	public function process_purchase( $order_log, $callback_url = false ) {
		$this->set_callback_url( $order_log->log_id, $callback_url );
		$this->init_gateway_purchase( $order_log );
	}


	/**
	 * Process Purchase
	 *
	 * @since 1.0.0
	 */
	public function init_gateway_purchase( $order_log ) {
	}

	/**
	 * Process Return
	 *
	 * @since 1.0.0
	 */
	public function process_return( $order_log ) {

	}

	/**
	 * Process IPN
	 *
	 * @since 1.0.0
	 */
	public function process_ipn() {

	}
}

