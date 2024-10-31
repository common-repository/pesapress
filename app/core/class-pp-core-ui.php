<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Core_UI {

	/**
	 * Base ui directory
	 *
	 * @since  1.0.0
	 *
	 * @var string
	 */
	protected $base = 'default';


	/**
	 * Singletone instance of the plugin.
	 *
	 * @since  1.0.0
	 *
	 * @var UI
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

	}

	/**
	 * Handles ui render
	 *
	 * @param string $file - file relative to the base in the ui path
	 * @param array  $params - params to pass to the ui element
	 * @param bool   $return - set to false to echo and true to return
	 *
	 * @return void|string
	 */
	public function render( $file, $params = array(), $return = false ) {

		if ( array_key_exists( 'this', $params ) ) {
			unset( $params['this'] );
		}

		extract( $params, EXTR_OVERWRITE );

		if ( $return ) {
			ob_start();
		}
		$ui_file       = $file;
		$template_file = join( DIRECTORY_SEPARATOR, array( untrailingslashit( PESAPRESS_PLUGIN_DIR ), 'app', 'ui', $this->base, $ui_file . '.php' ) );
		if ( file_exists( $template_file ) ) {
			include $template_file;
		}

		if ( $return ) {
			return ob_get_clean();
		}

		if ( ! empty( $params ) ) {
			foreach ( $params as $param ) {
				unset( $param );
			}
		}
	}
}

