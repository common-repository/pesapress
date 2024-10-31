<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Controller_Admin {

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
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		PP_Controller_Admin_DashBoard::instance();
		PP_Controller_Admin_Logs::instance();
		PP_Controller_Admin_Gateways::instance();
		PP_Controller_Admin_Settings::instance();

		//Load other controller
		do_action( 'pesapress_init_controller' );
	}

	/**
	 * Load admin scripts
	 *
	 * @since 1.0.0
	 */
	function admin_scripts() {
		$screen = get_current_screen();
		if ( strpos( $screen->id, 'pesapress' ) !== false ) {
			// UIkit js
			wp_register_script(
				'pp-uikit',
				PESAPRESS_ASSETS_URL . '/vendor/uikit/js/uikit.min.js',
				array( 'jquery' ),
				'3.0.0'
			);
			wp_register_script(
				'pp-uikit-icons',
				PESAPRESS_ASSETS_URL . '/vendor/uikit/js/uikit-icons.min.js',
				array( 'jquery' ),
				'3.0.0'
			);

			wp_register_script(
				'pp-admin',
				PESAPRESS_ASSETS_URL . '/js/pp-admin.min.js',
				array( 'jquery' ),
				PESAPRESS_VERSION
			);

			$vars = apply_filters(
				'pp-admin-vars',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
					'error'   => __( 'An error occured', 'pesapress' ),
					'assets'  => array(
						'spinner' => PESAPRESS_ASSETS_URL . '/img/spinner.gif',
					),
					'loading' => array(
						'title' => __( 'Loading content', 'pesapress' ),
						'body'  => sprintf( __( 'Loading content %s', 'pesapress' ), '<div class="uk-position-center"><div uk-spinner></div></div>' ),
						'error' => __( 'Error loading content', 'pesapress' ),
					),
				)
			);

			wp_localize_script( 'pp-admin', 'pesapress', $vars );

			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'pp-uikit' );
			wp_enqueue_script( 'pp-uikit-icons' );
			wp_enqueue_script( 'pp-admin' );
		}
	}

	/**
	 * Load admin styles
	 *
	 * @since 1.0.0
	 */
	function admin_styles() {
		$screen = get_current_screen();
		if ( strpos( $screen->id, 'pesapress' ) !== false ) {
			// UIkit css
			wp_register_style(
				'pp-uikit',
				PESAPRESS_ASSETS_URL . '/vendor/uikit/css/uikit.min.css',
				null,
				'3.0.0'
			);

			wp_register_style(
				'pp-admin',
				PESAPRESS_ASSETS_URL . '/css/pp-admin.min.css',
				null,
				PESAPRESS_VERSION
			);

			wp_enqueue_style( 'pp-uikit' );
			wp_enqueue_style( 'pp-admin' );
		}
	}

	/**
	 * Register admin menu
	 *
	 * @since 1.0.0
	 */
	function admin_menu() {
		$menu_title = apply_filters( 'pesapress_admin_menu_title', __( 'PesaPress', 'pesapress' ) );
		add_menu_page(
			$menu_title,
			$menu_title,
			'manage_options',
			'pesapress',
			null,
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9IjQwMS42MDFweCIgaGVpZ2h0PSI0MDEuNnB4IiB2aWV3Qm94PSIwIDAgNDAxLjYwMSA0MDEuNiIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNDAxLjYwMSA0MDEuNjsiDQoJIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPGc+DQoJCTxwYXRoIGQ9Ik0xMTYuNjgyLDIyOS4zMjljMTEuMjg2LDAsMjIuMTk1LTAuNzI5LDMyLjUxOC0yLjA4NlYxMTQuMDk0Yy0xMC4zMjItMS4zNTYtMjEuMjMyLTIuMDg1LTMyLjUxOC0yLjA4NQ0KCQkJYy02NC40NDEsMC0xMTYuNjgxLDIzLjY5My0xMTYuNjgxLDUyLjkyMXYxMS40NzdDMC4wMDEsMjA1LjYzNCw1Mi4yNDEsMjI5LjMyOSwxMTYuNjgyLDIyOS4zMjl6Ii8+DQoJCTxwYXRoIGQ9Ik0xMTYuNjgyLDI4OC40MTFjMTEuMjg2LDAsMjIuMTk1LTAuNzI5LDMyLjUxOC0yLjA4NHYtMzMuMTY2Yy0xMC4zMjUsMS4zNTYtMjEuMjI5LDIuMDk1LTMyLjUxOCwyLjA5NQ0KCQkJYy01Ni4yNSwwLTEwMy4xOTktMTguMDU0LTExNC4yMjctNDIuMDgyYy0xLjYwNiwzLjUtMi40NTQsNy4xMjQtMi40NTQsMTAuODM5djExLjQ3Nw0KCQkJQzAuMDAxLDI2NC43MTgsNTIuMjQxLDI4OC40MTEsMTE2LjY4MiwyODguNDExeiIvPg0KCQk8cGF0aCBkPSJNMTQ5LjE5OSwzMTQuODIzdi0yLjU3OGMtMTAuMzI1LDEuMzU2LTIxLjIyOSwyLjA5NS0zMi41MTgsMi4wOTVjLTU2LjI1LDAtMTAzLjE5OS0xOC4wNTQtMTE0LjIyNy00Mi4wODINCgkJCUMwLjg0OCwyNzUuNzU3LDAsMjc5LjM4MSwwLDI4My4wOTZ2MTEuNDc3YzAsMjkuMjI5LDUyLjI0LDUyLjkyMiwxMTYuNjgxLDUyLjkyMmMxMi44ODcsMCwyNS4yODItMC45NSwzNi44NzMtMi43DQoJCQljLTIuODczLTUuODc3LTQuMzU1LTEyLjA3NS00LjM1NS0xOC40OTZWMzE0LjgyM3oiLz4NCgkJPHBhdGggZD0iTTI4NC45MiwyMi4zNzljLTY0LjQ0MSwwLTExNi42ODEsMjMuNjkzLTExNi42ODEsNTIuOTIxdjExLjQ3N2MwLDI5LjIyOCw1Mi4yNCw1Mi45MjEsMTE2LjY4MSw1Mi45MjENCgkJCWM2NC40NCwwLDExNi42ODEtMjMuNjkzLDExNi42ODEtNTIuOTIxVjc1LjNDNDAxLjYwMSw0Ni4wNzIsMzQ5LjM2LDIyLjM3OSwyODQuOTIsMjIuMzc5eiIvPg0KCQk8cGF0aCBkPSJNMjg0LjkyLDE2NS42MjZjLTU2LjI1LDAtMTAzLjE5OS0xOC4wNTMtMTE0LjIyNy00Mi4wODJjLTEuNjA2LDMuNDk5LTIuNDU0LDcuMTIzLTIuNDU0LDEwLjgzOXYxMS40NzcNCgkJCWMwLDI5LjIyOCw1Mi4yNCw1Mi45MjEsMTE2LjY4MSw1Mi45MjFjNjQuNDQsMCwxMTYuNjgxLTIzLjY5MywxMTYuNjgxLTUyLjkyMXYtMTEuNDc3YzAtMy43MTYtMC44NDgtNy4zNC0yLjQ1NC0xMC44MzkNCgkJCUMzODguMTE5LDE0Ny41NzMsMzQxLjE3LDE2NS42MjYsMjg0LjkyLDE2NS42MjZ6Ii8+DQoJCTxwYXRoIGQ9Ik0yODQuOTIsMjI0LjcxYy01Ni4yNSwwLTEwMy4xOTktMTguMDU0LTExNC4yMjctNDIuMDgyYy0xLjYwNiwzLjQ5OS0yLjQ1NCw3LjEyMy0yLjQ1NCwxMC44Mzl2MTEuNDc3DQoJCQljMCwyOS4yMjksNTIuMjQsNTIuOTIyLDExNi42ODEsNTIuOTIyYzY0LjQ0LDAsMTE2LjY4MS0yMy42OTMsMTE2LjY4MS01Mi45MjJ2LTExLjQ3N2MwLTMuNzE2LTAuODQ4LTcuMzQtMi40NTQtMTAuODM5DQoJCQlDMzg4LjExOSwyMDYuNjU3LDM0MS4xNywyMjQuNzEsMjg0LjkyLDIyNC43MXoiLz4NCgkJPHBhdGggZD0iTTI4NC45MiwyODYuOTgzYy01Ni4yNSwwLTEwMy4xOTktMTguMDU0LTExNC4yMjctNDIuMDgyYy0xLjYwNiwzLjUtMi40NTQsNy4xMjMtMi40NTQsMTAuODM4djExLjQ3OA0KCQkJYzAsMjkuMjI4LDUyLjI0LDUyLjkyMSwxMTYuNjgxLDUyLjkyMWM2NC40NCwwLDExNi42ODEtMjMuNjkzLDExNi42ODEtNTIuOTIxdi0xMS40NzhjMC0zLjcxNS0wLjg0OC03LjM0LTIuNDU0LTEwLjgzOA0KCQkJQzM4OC4xMTksMjY4LjkyOCwzNDEuMTcsMjg2Ljk4MywyODQuOTIsMjg2Ljk4M3oiLz4NCgkJPHBhdGggZD0iTTI4NC45MiwzNDYuMDY2Yy01Ni4yNSwwLTEwMy4xOTktMTguMDUzLTExNC4yMjctNDIuMDgxYy0xLjYwNiwzLjUtMi40NTQsNy4xMjUtMi40NTQsMTAuODM4VjMyNi4zDQoJCQljMCwyOS4yMjgsNTIuMjQsNTIuOTIxLDExNi42ODEsNTIuOTIxYzY0LjQ0LDAsMTE2LjY4MS0yMy42OTMsMTE2LjY4MS01Mi45MjF2LTExLjQ3OGMwLTMuNzE1LTAuODQ4LTcuMzQtMi40NTQtMTAuODM4DQoJCQlDMzg4LjExOSwzMjguMDEyLDM0MS4xNywzNDYuMDY2LDI4NC45MiwzNDYuMDY2eiIvPg0KCTwvZz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjwvc3ZnPg0K',
			'55.5'
		);
	}
}

