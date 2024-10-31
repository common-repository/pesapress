<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Statistics Model
 */
class PP_Model_Statistics {

	/**
	 * The table name
	 *
	 * @var string
	 */
	protected $table_name;


	/**
	 * The table meta name
	 *
	 * @var string
	 */
	protected $table_meta_name;

	/**
	 * The currency
	 *
	 * @var PP_Core_Currency
	 */
	protected $currency;

	/**
	 * Singletone instance of the plugin.
	 *
	 * @since  1.0.0
	 *
	 * @var PP_Model_Statistics
	 */
	private static $instance = null;

	/**
	 * Returns singleton instance of the plugin.
	 *
	 * @since  1.0.0
	 *
	 * @static
	 * @access public
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->table_name      = PP_Core_Helper::get_table_name( PP_Core_Helper::TRANSACTION_LOGS );
		$this->table_meta_name = PP_Core_Helper::get_table_name( PP_Core_Helper::TRANSACTION_META );
		$this->currency        = PP_Core_Currency::instance();
	}

	/**
	 * Get Total revenue
	 *
	 * @return string
	 */
	public function get_total_revenue() {
		global $wpdb;
		$sql   = "SELECT SUM(`amount`) FROM {$this->table_name} WHERE `status` = %s";
		$total = $wpdb->get_var( $wpdb->prepare( $sql, 'paid' ) );
		return $this->_get_amount_formatted( $total );
	}

	/**
	 * Get Months revenue
	 *
	 * @return string
	 */
	public function get_months_revenue() {
		global $wpdb;
		$sql   = "SELECT SUM(`amount`) FROM {$this->table_name} WHERE `status` = %s AND MONTH(`date_created`) = MONTH(CURRENT_DATE()) AND YEAR(`date_created`) = YEAR(CURRENT_DATE())";
		$total = $wpdb->get_var( $wpdb->prepare( $sql, 'paid' ) );
		return $this->_get_amount_formatted( $total );
	}

	/**
	 * Get Weeks revenue
	 *
	 * @return string
	 */
	public function get_weeks_revenue() {
		global $wpdb;
		$sql   = "SELECT SUM(`amount`) FROM {$this->table_name} WHERE `status` = %s AND YEARWEEK(`date_created`, 1) = YEARWEEK(CURDATE(), 1)";
		$total = $wpdb->get_var( $wpdb->prepare( $sql, 'paid' ) );
		return $this->_get_amount_formatted( $total );
	}


	/**
	 * Get Weeks revenue
	 *
	 * @return string
	 */
	public function get_last_transaction() {
		global $wpdb;
		$sql   = "SELECT `amount` FROM {$this->table_name} WHERE `status` = %s ORDER BY `date_created` DESC LIMIT 1";
		$total = $wpdb->get_var( $wpdb->prepare( $sql, 'paid' ) );
		return $this->_get_amount_formatted( $total );
	}

	/**
	 * Get Amount formatted
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function _get_amount_formatted( $amount ) {
		if ( ! $amount ) {
			$amount = 0;
		}
		return $this->currency->format_currency( false, $amount );
	}
}

