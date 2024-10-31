<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Gateway Model
 */
class PP_Model_Gateway {

	/**
	 * Setting id
	 *
	 * @var int
	 */
	public $setting_id = 0;

	/**
	 * Setting name
	 *
	 * @var string
	 */
	public $setting_name;

	/**
	 * Active mode
	 *
	 * @var string
	 */
	public $active_mode;

	/**
	 * Enabled
	 *
	 * @var bool
	 */
	public $is_enabled = false;

	/**
	 * Setting details
	 *
	 * @var array
	 */
	public $setting_details = array();


	/**
	 * Setting details raw
	 *
	 * @var string
	 */
	public $setting_details_raw;


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
	 * The table name
	 *
	 * @var string
	 */
	protected $table_name;


	/**
	 * Initialize the Model
	 *
	 * @since 1.0
	 */
	public function __construct( $setting_id = null ) {
		$this->table_name = PP_Core_Helper::get_table_name( PP_Core_Helper::GATEWAY_SETTINGS );

		if ( is_numeric( $setting_id ) && $setting_id > 0 ) {
			$this->get( $setting_id );
		}
	}


	/**
	 * Load setting by id
	 * After load set setting to cache
	 *
	 * @since 1.0
	 *
	 * @param int $setting_id - the setting id
	 */
	public function get( $setting_id ) {
		global $wpdb;

		$sql     = "SELECT `setting_name`, `active_mode`, `setting_details`, `is_enabled`, `date_created` FROM {$this->table_name} WHERE `setting_id` = %d";
		$setting = $wpdb->get_row( $wpdb->prepare( $sql, $setting_id ) );
		if ( $setting ) {
			$this->setting_id       = $setting_id;
			$this->setting_name     = $setting->setting_name;
			$this->active_mode      = $setting->active_mode;
			$this->is_enabled       = $setting->is_enabled;
			$details                = $setting->setting_details;
			$this->setting_details  = is_array( $details ) ? array_map( 'maybe_unserialize', $details ) : maybe_unserialize( $details );
			$this->date_created_sql = $setting->date_created;
			$this->date_created     = date_i18n( 'j M Y', strtotime( $setting->date_created ) );
		}
	}


	/**
	 * Save setting
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function save() {
		global $wpdb;
		$cache_key = get_class( $this );
		if ( isset( $this->setting_details_raw ) && ! is_null( $this->setting_details_raw ) ) {
			$value = wp_unslash( $this->setting_details_raw );
		} else {
			$value = $this->setting_details;
		}

		$value = maybe_serialize( $value );

		if ( is_numeric( $this->setting_id ) && $this->setting_id > 0 ) {
			$wpdb->update(
				$this->table_name,
				array(
					'setting_name'    => $this->setting_name,
					'active_mode'     => $this->active_mode,
					'setting_details' => $value,
					'is_enabled'      => $this->is_enabled,
					'date_updated'    => date_i18n( 'Y-m-d H:i:s' ),
				),
				array( 'setting_id' => $this->setting_id )
			);
			wp_cache_delete( $this->setting_id, $cache_key );
		} else {
			$result = $wpdb->insert(
				$this->table_name,
				array(
					'setting_name'    => $this->setting_name,
					'active_mode'     => $this->active_mode,
					'setting_details' => $value,
					'is_enabled'      => $this->is_enabled,
					'date_created'    => date_i18n( 'Y-m-d H:i:s' ),
				)
			);

			if ( ! $result ) {
				return false;
			}

			$this->setting_id = (int) $wpdb->insert_id;
		}

		return true;
	}

	/**
	 * Delete setting
	 *
	 * @since 1.0.0
	 */
	public function delete() {
		global $wpdb;
		$cache_key = get_class( $this );
		$sql       = "DELETE FROM {$this->table_name} WHERE `setting_id` = %d";
		wp_cache_delete( $this->setting_id, $cache_key );
		$wpdb->query( $wpdb->prepare( $sql, $this->setting_id ) );
		PP_Model_Log::bulk_delete_by( 'gateways', $this->setting_id );
	}

	/**
	 * Clone
	 *
	 * @since 1.0.0
	 */
	public function clone() {
		$this->setting_id = 0;
		$this->save();
	}

	/**
	 * Get Setting nick name
	 *
	 * @since 1.0.0
	 */
	public function get_nickname( $include_name = false ) {
		if ( isset( $this->setting_details['nickname'] ) ) {
			if ( $include_name ) {
				return $this->setting_name . ' - ' . $this->setting_details['nickname'];
			}
			return $this->setting_details['nickname'];
		}
		return $this->setting_name;
	}

	/**
	 * Get Return url
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_ipn_url() {
		return PP_Core_Helper::home_url( '/pp-payment-ipn/' . $this->setting_name . '-' . $this->setting_id );
	}

	/**
	 * Count settings
	 *
	 * @since 1.0.0
	 *
	 * @param optional $db - the databasse
	 *
	 * @return int
	 */
	public static function count_all( $db = false ) {
		if ( ! $db ) {
			global $wpdb;
			$db = $wpdb;
		}
		$table_name = PP_Core_Helper::get_table_name( PP_Core_Helper::GATEWAY_SETTINGS );
		$sql        = "SELECT count(`setting_id`) FROM {$table_name}";
		$settings   = $db->get_var( $sql );
		return $settings;
	}

	/**
	 * List settings
	 *
	 * @since 1.0.0
	 *
	 * @param int $per_page - results per page
	 * @param int $page - the current page. Defaults to 0
	 *
	 * @return array(
	 *      PP_Model_Gateway
	 * )
	 */
	public static function list_by_page( $per_page, $page = 0 ) {
		global $wpdb;
		$settings   = array();
		$table_name = PP_Core_Helper::get_table_name( PP_Core_Helper::GATEWAY_SETTINGS );
		$sql        = "SELECT `setting_id` FROM {$table_name} ORDER BY `setting_id` DESC LIMIT %d, %d ";
		$results    = $wpdb->get_results( $wpdb->prepare( $sql, $page, $per_page ) );

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$settings[] = new PP_Model_Gateway( $result->setting_id );
			}
		}

		return $settings;
	}

	/**
	 * List simple list
	 *
	 * @since 1.0.0
	 *
	 * @return array(
	 *  id => name
	 * )
	 */
	public static function list_simple( $show_all = true ) {
		global $wpdb;
		$settings = array(
			'' => __( 'All', 'pesapress' ),
		);
		if ( ! $show_all ) {
			$settings = array();
		}
		$table_name = PP_Core_Helper::get_table_name( PP_Core_Helper::GATEWAY_SETTINGS );
		$sql        = "SELECT `setting_name`, `setting_id`, `setting_details` FROM {$table_name} ORDER BY `setting_id` DESC";
		$results    = $wpdb->get_results( $sql );

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$details                         = $result->setting_details;
				$details                         = is_array( $details ) ? array_map( 'maybe_unserialize', $details ) : maybe_unserialize( $details );
				$settings[ $result->setting_id ] = isset( $details['nickname'] ) ? $details['nickname'] : $result->setting_name;
			}
		}

		return $settings;
	}

	/**
	 * List dropdown list
	 *
	 * @since 2.0.0
	 *
	 * @return array(
	 *      label => name,
	 *      value => ''
	 * )
	 */
	public static function list_dropdown() {
		$output = array();
		$lists  = self::list_simple();
		foreach ( $lists as $key => $value ) {
			$output[] = array(
				'label' => $value,
				'value' => $key,
			);
		}
		return $output;
	}

	/**
	 * Bulk Delete
	 *
	 * @since 1.0
	 *
	 * @param string $ids - comma separated ids
	 */
	public static function bulk_delete( $ids ) {
		global $wpdb;
		$cache_key  = get_called_class();
		$table_name = PP_Core_Helper::get_table_name( PP_Core_Helper::GATEWAY_SETTINGS );
		$id_arr     = explode( ', ', $ids );
		foreach ( $id_arr as $id ) {
			wp_cache_delete( $id, $cache_key );
		}
		$sql = "DELETE FROM {$table_name} WHERE `setting_id` IN ($ids)";
		PP_Model_Log::bulk_delete_by( 'gateways', $ids );
		$wpdb->query( $sql );
	}

	/**
	 * Bulk activate gateways
	 *
	 * @since 1.0
	 *
	 * @param string $ids - comma separated ids
	 * @param int    $enabled - status (1 or 0 )
	 */
	public static function bulk_disable_enable( $ids, $enabled ) {
		global $wpdb;
		$cache_key  = get_called_class();
		$table_name = PP_Core_Helper::get_table_name( PP_Core_Helper::GATEWAY_SETTINGS );
		$id_arr     = explode( ', ', $ids );
		foreach ( $id_arr as $id ) {
			wp_cache_delete( $id, $cache_key );
		}
		$sql = "UPDATE {$table_name} SET `is_enabled` = %d WHERE `setting_id` IN ($ids)";
		$wpdb->query( $wpdb->prepare( $sql, $enabled ) );
	}

	/**
	 * Check if a gateway exists
	 *
	 * @param int $id - the gateway id
	 *
	 * @return bool
	 */
	public static function exists( $id ) {
		global $wpdb;
		$table_name = PP_Core_Helper::get_table_name( PP_Core_Helper::GATEWAY_SETTINGS );
		$sql        = "SELECT `setting_id` FROM {$table_name} WHERE `setting_id` = %d ";
		$setting_id = $wpdb->get_results( $wpdb->prepare( $sql, $id ) );
		return ( $setting_id ) ? true : false;
	}

}

