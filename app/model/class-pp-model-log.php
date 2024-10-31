<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Log Model
 */
class PP_Model_Log {

	/**
	 * The log id
	 *
	 * @var int
	 */
	public $log_id = 0;

	/**
	 * External id
	 *
	 * @var string
	 */
	public $external_id = '';

	/**
	 * Amount
	 *
	 * @var double
	 */
	public $amount = 0;

	/**
	 * Gateway id
	 *
	 * @var int
	 */
	public $gateway_id = 0;

	/**
	 * Gateway name
	 *
	 * @var string
	 */
	public $gateway_name = '';

	/**
	 * Status
	 *
	 * @var string
	 */
	public $status;


	/**
	 * Transaction meta
	 *
	 * @var array
	 */
	public $meta_data = array();

	/**
	 * Date created in sql format 0000-00-00 00:00:00
	 *
	 * @var string
	 */
	public $date_created_sql;

	/**
	 * Date created in sql format D M Y
	 *
	 * @var string
	 */
	public $date_created;


	/**
	 * Date updated in sql format D M Y
	 *
	 * @var string
	 */
	public $date_updated;


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
	 * Initialize the Model
	 *
	 * @since 1.0
	 */
	public function __construct( $log_id = null ) {
		$this->table_name      = PP_Core_Helper::get_table_name( PP_Core_Helper::TRANSACTION_LOGS );
		$this->table_meta_name = PP_Core_Helper::get_table_name( PP_Core_Helper::TRANSACTION_META );

		if ( $log_id && $log_id > 0 ) {
			$this->get( $log_id );
		}
	}

	/**
	 * Load log by id
	 * After load set log to cache
	 *
	 * @since 1.0
	 *
	 * @param int $log_id - the setting id
	 */
	public function get( $log_id ) {
		global $wpdb;

		$cache_key            = get_class( $this );
		$setting_object_cache = wp_cache_get( $log_id, $cache_key );

		if ( $setting_object_cache ) {
			return $setting_object_cache;
		} else {
			$sql = "SELECT `external_id`, `amount`, `gateway_id`, `gateway_name`, `status`, `date_created`, `date_updated` FROM {$this->table_name} WHERE `log_id` = %d";
			$log = $wpdb->get_row( $wpdb->prepare( $sql, $log_id ) );
			if ( $log ) {
				$this->log_id           = $log_id;
				$this->external_id      = $log->external_id;
				$this->amount           = $log->amount;
				$this->gateway_id       = $log->gateway_id;
				$this->gateway_name     = $log->gateway_name;
				$this->status           = $log->status;
				$this->date_created_sql = $log->date_created;
				$this->date_created     = date_i18n( 'j M Y', strtotime( $log->date_created ) );
				$this->date_updated     = date_i18n( 'j M Y', strtotime( $log->date_updated ) );
				$this->load_meta( $wpdb );
				wp_cache_set( $log_id, $this, $cache_key );
			}
		}
	}

	/**
	 * Load all meta data for entry
	 *
	 * @since 1.0
	 *
	 * @param object|bool $db - the WP_Db object
	 */
	public function load_meta( $db = false ) {
		if ( ! $db ) {
			global $wpdb;
			$db = $wpdb;
		}
		$this->meta_data = array();
		$sql             = "SELECT `meta_id`, `meta_key`, `meta_value` FROM {$this->table_meta_name} WHERE `log_id` = %d";
		$results         = $db->get_results( $db->prepare( $sql, $this->log_id ) );
		foreach ( $results as $result ) {
			$this->meta_data[ $result->meta_key ] = array(
				'id'    => $result->meta_id,
				'value' => is_array( $result->meta_value ) ? array_map( 'maybe_unserialize', $result->meta_value ) : maybe_unserialize( $result->meta_value ),
			);
		}
	}


	/**
	 * Set fields
	 *
	 * @since 1.0
	 * @param array $meta_array {
	 *      Array of data to be saved
	 *      @type key - string the meta key
	 *      @type value - string the meta value
	 * }
	 *
	 * @return bool - true or false
	 */
	public function set_fields( $meta_array ) {
		global $wpdb;

		if ( $meta_array && ! is_array( $meta_array ) && ! empty( $meta_array ) ) {
			return false;
		}

		if ( ! $this->log_id ) {
			return false;
		}

		/**
		 * Action called before fields are set
		 *
		 * @since 1.0.1
		 *
		 * @param PP_Model_Log order log object
		 * @param array $meta_array - meta array to save
		 */
		do_action( 'pesapress_transaction_log_before_set_fields', $this, $meta_array );

		// clear cache first
		$cache_key = get_class( $this );
		wp_cache_delete( $this->log_id, $cache_key );
		foreach ( $meta_array as $meta ) {
			if ( isset( $meta['name'] ) && isset( $meta['value'] ) ) {
				$key   = $meta['name'];
				$value = $meta['value'];
				$key   = wp_unslash( $key );
				$value = wp_unslash( $value );
				$value = maybe_serialize( $value );

				$meta_id = $wpdb->insert(
					$this->table_meta_name,
					array(
						'log_id'       => $this->log_id,
						'meta_key'     => $key,
						'meta_value'   => $value,
						'date_created' => date_i18n( 'Y-m-d H:i:s' ),
					)
				);

				if ( $meta_id ) {
					$this->meta_data[ $key ] = array(
						'id'    => $meta_id,
						'value' => is_array( $value ) ? array_map( 'maybe_unserialize', $value ) : maybe_unserialize( $value ),
					);
				}
			}
		}

		/**
		 * Action called after fields are set
		 *
		 * @since 1.0.1
		 *
		 * @param PP_Model_Log order log object
		 * @param array $meta_array - meta array to save
		 */
		do_action( 'pesapress_transaction_log_after_set_fields', $this, $meta_array );
		return true;
	}


	/**
	 * Get Meta
	 *
	 * @since 1.0
	 *
	 * @param string      $meta_key - the meta key
	 * @param bool|object $default_value - the default value
	 *
	 * @return bool|string
	 */
	public function get_meta( $meta_key, $default_value = false ) {
		if ( ! empty( $this->meta_data ) && isset( $this->meta_data[ $meta_key ] ) ) {
			return $this->meta_data[ $meta_key ]['value'];
		}
		return $default_value;
	}

	/**
	 * Set Meta
	 *
	 * @since 2.0.0
	 */
	public function set_meta( $meta_key, $meta_value ) {
		global $wpdb;

		$meta_id = $wpdb->insert(
			$this->table_meta_name,
			array(
				'log_id'       => $this->log_id,
				'meta_key'     => $meta_key,
				'meta_value'   => $meta_value,
				'date_created' => date_i18n( 'Y-m-d H:i:s' ),
			)
		);

		if ( $meta_id ) {
			$this->meta_data[ $meta_key ] = array(
				'id'    => $meta_id,
				'value' => is_array( $meta_value ) ? array_map( 'maybe_unserialize', $value ) : maybe_unserialize( $meta_value ),
			);
		}
	}

	/**
	 * Save entry
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function save() {
		global $wpdb;

		if ( is_numeric( $this->log_id ) && $this->log_id > 0 ) {

			$wpdb->update(
				$this->table_name,
				array(
					'status'       => $this->status,
					'date_updated' => date_i18n( 'Y-m-d H:i:s' ),
				),
				array( 'log_id' => $this->log_id )
			);

			/**
			 * Action called after update
			 *
			 * @since 1.0.1
			 *
			 * @param PP_Model_Log order log object
			 */
			do_action( 'pesapress_transaction_log_update', $this );

		} else {
			$result = $wpdb->insert(
				$this->table_name,
				array(
					'external_id'  => empty( $this->external_id ) ? $this->generate_external_id() : $this->external_id,
					'amount'       => $this->amount,
					'gateway_id'   => $this->gateway_id,
					'gateway_name' => $this->gateway_name,
					'status'       => $this->status,
					'date_created' => date_i18n( 'Y-m-d H:i:s' ),
				)
			);

			if ( ! $result ) {
				return false;
			}
			$this->log_id = (int) $wpdb->insert_id;

			/**
			 * Action called after save
			 * Only called if save was successful
			 *
			 * @since 1.0.1
			 *
			 * @param PP_Model_Log order log object
			 */
			do_action( 'pesapress_transaction_log_save', $this );
		}

		return true;
	}

	/**
	 * Delete log with meta
	 *
	 * @since 1.0
	 */
	public function delete() {
		global $wpdb;
		$sql = "DELETE FROM {$this->table_name} WHERE `log_id` = %d";
		$wpdb->query( $wpdb->prepare( $sql, $this->log_id ) );
		$sql = "DELETE FROM {$this->table_meta_name} WHERE `log_id` = %d";
		$wpdb->query( $wpdb->prepare( $sql, $this->log_id ) );
	}

	/**
	 * Get Gateway name
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function gateway_name() {
		$gateway      = new PP_Model_Gateway( $this->gateway_id );
		$gateway_name = $this->gateway_name;
		if ( $gateway->setting_id > 0 ) {
			$gateway_name = $gateway->get_nickname( true );
		}
		return $gateway_name;
	}

	/**
	 * Get Amount formatted
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_amount_formatted() {
		$currency = PP_Core_Currency::instance();
		return $currency->format_currency( false, $this->amount );
	}

	/**
	 * Generate the external id
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function generate_external_id() {
		global $wpdb;
		$table_name = PP_Core_Helper::get_table_name( PP_Core_Helper::TRANSACTION_LOGS );
		$sql        = "SELECT MAX(`log_id`) FROM {$table_name}";
		$max_id     = $wpdb->get_var( $sql );
		if ( $max_id ) {
			$max_id = $max_id + 1;
		} else {
			$max_id = 1;
		}
		return PP_Core_Helper::generate_external_id( $max_id );
	}

	/**
	 * Get log by external id
	 *
	 * @param string $external_id - the external id
	 *
	 * @return PP_Model_Log|bool
	 */
	public static function get_by_external_id( $external_id ) {
		global $wpdb;
		$table_name = PP_Core_Helper::get_table_name( PP_Core_Helper::TRANSACTION_LOGS );
		$sql        = "SELECT `log_id` FROM {$table_name} WHERE `external_id` = %s";
		$log_id     = $wpdb->get_var( $wpdb->prepare( $sql, $external_id ) );
		if ( $log_id ) {
			return new PP_Model_Log( $log_id );
		}
		return false;
	}

	/**
	 * Get log by Meta
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @since 2.0.0
	 *
	 * @return PP_Model_Log|bool
	 */
	public static function get_by_meta( $key, $value ) {
		global $wpdb;
		$table_name = PP_Core_Helper::get_table_name( PP_Core_Helper::TRANSACTION_META );
		$sql        = "SELECT `log_id` FROM $table_name WHERE `meta_key` = %s AND `meta_value` = %s";
		$log_id     = $wpdb->get_var( $wpdb->prepare( $sql, $key, $value ) );
		if ( $log_id ) {
			return new PP_Model_Log( $log_id );
		}
		return false;
	}

	/**
	 * Get gateway name
	 *
	 * @param int $order_id - the log id
	 *
	 * @return string
	 */
	public static function get_gateway_name( $order_id ) {
		global $wpdb;
		$table_name   = PP_Core_Helper::get_table_name( PP_Core_Helper::TRANSACTION_LOGS );
		$sql          = "SELECT `gateway_name` FROM {$table_name} WHERE `log_id` = %d";
		$gateway_name = $wpdb->get_var( $wpdb->prepare( $sql, $order_id ) );
		return $gateway_name;
	}

	/**
	 * Count logs
	 *
	 * @since 1.0.0
	 *
	 * @param int|optional $gateway_id - the gateway id
	 *
	 * @return int
	 */
	public static function count_all( $gateway_id = false, $db = false ) {
		if ( ! $db ) {
			global $wpdb;
			$db = $wpdb;
		}
		$table_name = PP_Core_Helper::get_table_name( PP_Core_Helper::TRANSACTION_LOGS );
		if ( $gateway_id && is_numeric( $gateway_id ) && $gateway_id > 0 ) {
			$sql  = "SELECT count(`log_id`) FROM {$table_name} WHERE `gateway_id` = %d";
			$logs = $db->get_var( $db->prepare( $sql, $gateway_id ) );
			if ( $logs ) {
				return $logs;
			}
		} else {
			$sql  = "SELECT count(`log_id`) FROM {$table_name} ";
			$logs = $db->get_var( $sql );
			if ( $logs ) {
				return $logs;
			}
		}
		return 0;
	}

	/**
	 * List logs
	 *
	 * @since 1.0
	 *
	 * @param int          $per_page - results per page
	 * @param int          $page - the current page. Defaults to 0
	 * @param int|optional $gateway_id - the gateway id
	 *
	 * @return array(
	 *      PP_Model_Log
	 * )
	 */
	public static function list_by_page( $per_page, $page = 0, $gateway_id = false ) {
		global $wpdb;
		$logs       = array();
		$table_name = PP_Core_Helper::get_table_name( PP_Core_Helper::TRANSACTION_LOGS );
		if ( $gateway_id && is_numeric( $gateway_id ) && $gateway_id > 0 ) {
			$sql     = "SELECT `log_id` FROM {$table_name} WHERE `gateway_id` = %d ORDER BY `log_id` DESC LIMIT %d, %d ";
			$results = $wpdb->get_results( $wpdb->prepare( $sql, $gateway_id, $page, $per_page ) );
		} else {
			$sql     = "SELECT `log_id` FROM {$table_name}  ORDER BY `log_id` DESC LIMIT %d, %d ";
			$results = $wpdb->get_results( $wpdb->prepare( $sql, $page, $per_page ) );
		}
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$logs[] = new PP_Model_Log( $result->log_id );
			}
		}
		return $logs;
	}


	/**
	 * List logs
	 *
	 * @since 1.0
	 *
	 * @param int|optional $gateway_id - the gateway id
	 *
	 * @return array(
	 *      PP_Model_Log
	 * )
	 */
	public static function list_all( $gateway_id = false ) {
		global $wpdb;
		$logs       = array();
		$table_name = PP_Core_Helper::get_table_name( PP_Core_Helper::TRANSACTION_LOGS );
		if ( $gateway_id && is_numeric( $gateway_id ) && $gateway_id > 0 ) {
			$sql     = "SELECT `log_id` FROM {$table_name} WHERE `gateway_id` = %d ORDER BY `log_id` DESC";
			$results = $wpdb->get_results( $wpdb->prepare( $sql, $gateway_id ) );
		} else {
			$sql     = "SELECT `log_id` FROM {$table_name}  ORDER BY `log_id` DESC";
			$results = $wpdb->get_results( $sql );
		}
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$logs[] = new PP_Model_Log( $result->log_id );
			}
		}
		return $logs;
	}


	/**
	 * Delete by string of comma separated ids
	 *
	 * @since 1.0
	 *
	 * @param string                                     $type - the type. Could be gateway or logs
	 * @param string                                     $ids - the ids
	 * @param bool|object - the WP_Object optional param
	 */
	public static function bulk_delete_by( $type = 'logs', $ids, $db = false ) {
		if ( ! $db ) {
			global $wpdb;
			$db = $wpdb;
		}
		if ( ! $type || empty( $type ) ) {
			return false;
		}

		$table_name      = PP_Core_Helper::get_table_name( PP_Core_Helper::TRANSACTION_LOGS );
		$table_meta_name = PP_Core_Helper::get_table_name( PP_Core_Helper::TRANSACTION_META );

		$id_array  = explode( ',', $ids );
		$cache_key = get_called_class();
		if ( $type === 'logs' ) {

			$sql = "DELETE FROM {$table_meta_name} WHERE `log_id` IN ($ids)";
			$db->query( $sql );

			$sql = "DELETE FROM {$table_name} WHERE `log_id` IN ($ids)";
			$db->query( $sql );
			foreach ( $id_array as $id ) {
				wp_cache_delete( $id, $cache_key );
			}
		} elseif ( $type === 'gateways' ) {
			$sql  = "SELECT GROUP_CONCAT(`log_id`) FROM {$table_name} WHERE `gateway_id` IN ($ids)";
			$logs = $db->get_var( $sql );

			if ( $logs ) {
				$sql = "DELETE FROM {$table_meta_name} WHERE `log_id` IN ($logs)";
				$db->query( $sql );
				foreach ( $logs as $id ) {
					wp_cache_delete( $id, $cache_key );
				}
			}

			$sql = "DELETE FROM {$table_name} WHERE `gateway_id` IN ($ids)";
			$db->query( $sql );
		}

		wp_cache_delete( $form_id, 'pesapress_total_counts' );
	}


	/**
	 * Log status
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function log_status() {
		return array(
			'pending'  => __( 'Pending', 'pesapress' ),
			'paid'     => __( 'Paid', 'pesapress' ),
			'canceled' => __( 'Canceled', 'pesapress' ),
			'refunded' => __( 'Refunded', 'pesapress' ),
		);
	}

	/**
	 * Export Logs
	 *
	 * @since 1.0.0
	 *
	 * @param string       $ids - the ids
	 * @param int|optional $gateway_id - the gateway id
	 */
	public static function export( $ids, $gateway_id = false ) {
		$logs = array();
		if ( ! empty( $ids ) ) {
			$id_array = explode( ',', $ids );
			foreach ( $id_array as $id ) {
				$logs[] = new PP_Model_Log( $id );
			}
		} else {
			$logs = self::list_all( $gateway_id );
		}

		$filename = 'pesapress-logs-' . date( 'ymdHis' ) . '.csv';
		$contents = __( 'No logs to export', 'pesapress' );
		$header   = array(
			__( 'ID', 'pesapress' ),
			__( 'Status', 'pesapress' ),
			__( 'Amount', 'pesapress' ),
			__( 'Gateway', 'pesapress' ),
			__( 'External ID', 'pesapress' ),
			__( 'Date Created', 'pesapress' ),
			__( 'Date Updated', 'pesapress' ),
		);
		if ( ! empty( $logs ) ) {
			$contents   = implode( ',', $header );
			$last_index = end( $logs );
			foreach ( $logs as $log ) {
				$contents .= "{$log->log_id},{$log->status},{$log->get_amount_formatted()},{$log->gateway_name()},{$log->external_id},{$log->date_created},{$log->date_updated}";
				if ( $log->log_id != $last_index->log_id ) {
					$contents .= "\r\n";
				}
			}
		}

		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Cache-Control: private', false ); // required for certain browsers
		header( 'Content-type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Content-Length: ' . strlen( $contents ) );

		// Finally send the export-file content.
		echo $contents;

		exit;
	}
}

