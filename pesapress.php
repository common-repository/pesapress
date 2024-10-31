<?php
/*
 Plugin Name:         PesaPress
 Plugin URI:          https://pesapress.com/
 Description:         Integrate PesaPal to WordPress and supported integrations
 Version:             2.3
 Author:              alloykenya
 Author URI:          https://hubloy.com
 Text Domain:         pesapress
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PesaPress' ) ) :

	/**
	 * Main Plugin class
	 *
	 * @since 1.0.0
	 */
	class PesaPress {
		/**
		 * Current plugin version.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $version = '2.3';


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


		/**
		 * Main plugin constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->define_constants();
			$this->auto_load();

			// Translate plugin
			add_action( 'plugins_loaded', array( $this, 'translate_plugin' ) );

			PP_Core_Init::instance();

			/**
			 * action called after plugin loaded
			 */
			do_action( 'pesapress_loaded' );
		}

		/**
		 * Define plugin constants
		 *
		 * @since 1.0.0
		 */
		protected function define_constants() {
			$upload_dir = wp_upload_dir();
			$this->define( 'PESAPRESS_VERSION', $this->version );
			$this->define( 'PESAPRESS_DB_VERSION', '1.0' );
			$this->define( 'PESAPRESS_PLUGIN_FILE', __FILE__ );
			$this->define( 'PESAPRESS_ORDER_PREFIX', 'pp-' );
			$this->define( 'PESAPRESS_PLUGIN', plugin_basename( __FILE__ ) );
			$this->define( 'PESAPRESS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			$this->define( 'PESAPRESS_PLUGIN_BASE_DIR', dirname( __FILE__ ) );
			$this->define( 'PESAPRESS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			$this->define( 'PESAPRESS_LIB_DIR', PESAPRESS_PLUGIN_DIR . '/app/lib/' );
			$this->define( 'PESAPRESS_LANG_DIR', PESAPRESS_PLUGIN_DIR . '/languages' );
			$this->define( 'PESAPRESS_ASSETS_URL', PESAPRESS_PLUGIN_URL . 'app/assets' );
			$this->define( 'PESAPRESS_LOG_DIR', $upload_dir['basedir'] . '/pp-logs/' );
		}

		/**
		 * Define constant if not already set
		 *
		 * @param  string      $name
		 * @param  string|bool $value
		 *
		 * @since 1.0.0
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Define class loader
		 *
		 * @since 1.0.0
		 */
		private function auto_load() {
			spl_autoload_register( array( &$this, '_autoload' ) );
		}

		/**
		 * Translate Plugin
		 *
		 * @since 1.0.0
		 */
		function translate_plugin() {
			if ( function_exists( 'determine_locale' ) ) {
				$locale = determine_locale();
			} else {
				// @todo Remove when start supporting WP 5.0 or later.
				$locale = is_admin() ? get_user_locale() : get_locale();
			}
	
			$locale = apply_filters( 'plugin_locale', $locale, 'pesapress' );
	
			unload_textdomain( 'woocopesapressmmerce' );
			load_textdomain( 'pesapress', WP_LANG_DIR . '/pesapress/pesapress-' . $locale . '.mo' );
			load_plugin_textdomain( 'pesapress', false, PESAPRESS_LANG_DIR );
		}

		/**
		 * Class autoloading callback function.
		 *
		 * Uses the **PP_** namespace to autoload classes when called.
		 * Avoids creating include functions for each file in the MVC structure.
		 * **PP_** namespace ONLY will be based on folder structure in /app/
		 *
		 * @since  1.0.0
		 *
		 * @param  string $class Uses PHP autoloader function.
		 *
		 * @return boolean
		 */
		public function _autoload( $class ) {
			// Classes start with PP
			if ( 'PP_' == substr( $class, 0, 3 ) ) {
				$path_array = explode( '_', $class );
				array_shift( $path_array ); // Remove the 'PP' prefix from path.
				$alt_dir  = array_pop( $path_array );
				$sub_path = implode( '/', $path_array );

				$filename      = str_replace( '_', '-', 'class-' . $class . '.php' );
				$file_path     = trim( strtolower( $sub_path . '/' . $filename ), '/' );
				$file_path_alt = trim( strtolower( $sub_path . '/' . $alt_dir . '/' . $filename ), '/' );
				$candidates    = array();
				$candidates[]  = PESAPRESS_PLUGIN_BASE_DIR . '/app/' . $file_path;
				$candidates[]  = PESAPRESS_PLUGIN_BASE_DIR . '/app/' . $file_path_alt;

				foreach ( $candidates as $path ) {
					$current_file = basename( $path );
					if ( is_file( $path ) ) {
						include_once $path;
						return true;
					}
				}
			}
			return false;
		}
	}

	/**
	 * Global function
	 *
	 * @since 1.0.0
	 */
	function pesapress() {
		return PesaPress::instance();
	}

	//Initiate plugin
	pesapress();

endif;

