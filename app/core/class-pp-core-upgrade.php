<?php
/**
 * Handle plugin upgrade for settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Core_Upgrade {

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
		$this->init_tables();
	}

	/**
	 * Set up database tables
	 *
	 * @since 1.0.0
	 */
	private function init_tables() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;

		$wpdb->hide_errors();

		$max_index_length = 191;
		$charset_collate  = $wpdb->get_charset_collate();

		$table_name = PP_Core_Helper::get_table_name( PP_Core_Helper::GATEWAY_SETTINGS );
		if ( $table_name ) {
			$sql = "CREATE TABLE {$table_name} (
				`setting_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`setting_name` VARCHAR(191) NOT NULL,
				`active_mode` VARCHAR(191) default NULL,
				`setting_details` LONGTEXT NULL,
				`is_enabled` TINYINT(1) NOT NULL DEFAULT 0,
				`date_created` datetime NOT NULL default '0000-00-00 00:00:00',
				`date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
				PRIMARY KEY (`setting_id`),
				KEY `setting_is_enabled` (`is_enabled` ASC ),
				KEY `setting_name` (`setting_name`($max_index_length)),
				KEY `setting_active_mode` (`active_mode`($max_index_length)))
				$charset_collate;";
			dbDelta( $sql );
		}

		$table_name = PP_Core_Helper::get_table_name( PP_Core_Helper::TRANSACTION_LOGS );
		if ( $table_name ) {
			$sql = "CREATE TABLE {$table_name} (
				`log_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`external_id` varchar(100) NOT NULL DEFAULT '',
				`amount` DOUBLE(5,2) NULL,
				`gateway_id` bigint(20) unsigned NOT NULL,
				`gateway_name` varchar(191) NOT NULL DEFAULT '',
				`status` ENUM('pending', 'paid', 'canceled', 'refunded') NOT NULL,
				`date_created` datetime NOT NULL default '0000-00-00 00:00:00',
				`date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
				PRIMARY KEY (`log_id`),
				KEY `transaction_amount` (`amount`),
				KEY `trnsaction_gateway_id` (`gateway_id`))
				$charset_collate;";
			dbDelta( $sql );
		}

		$table_name = PP_Core_Helper::get_table_name( PP_Core_Helper::TRANSACTION_META );
		if ( $table_name ) {
			$sql = "CREATE TABLE {$table_name} (
				`meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`log_id` bigint(20) unsigned NOT NULL,
				`meta_key` VARCHAR(191) default NULL,
				`meta_value` LONGTEXT NULL,
				`date_created` datetime NOT NULL default '0000-00-00 00:00:00',
				`date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
				PRIMARY KEY (`meta_id`),
				KEY `meta_key` (`meta_key`($max_index_length)),
				KEY `meta_log_id` (`log_id` ASC ),
				KEY `meta_key_object` (`log_id` ASC, `meta_key` ASC))
				$charset_collate;";
			dbDelta( $sql );
		}
		update_option( 'pesapress_db_version', PESAPRESS_DB_VERSION );
	}
}

