<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Core_View {

	/**
	 * The storage of all data associated with this render.
	 *
	 * @since  1.0.0
	 *
	 * @var array
	 */
	public $data;

	/**
	 * UI object
	 * Used to render based on the UI class handling the elements
	 *
	 * @since  1.0.0
	 *
	 * @var Soko_Base_UI
	 */
	protected $ui;

	/**
	 * Constructor.
	 *
	 * @since  1.0.0
	 *
	 * @param array $data The data what has to be associated with this render.
	 */
	public function __construct( $data = array() ) {

		$this->data = $data;
		$this->ui   = PP_Core_UI::instance();

		do_action( 'pesapress_view_construct', $this );
	}

	/**
	 * Page header
	 *
	 * @since 1.0.0
	 *
	 * @return String
	 */
	protected function header() {
		return '';
	}


	/**
	 * Builds template and return it as string.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function to_html() {
		$content = $this->header();
		return apply_filters( 'pesapress_view_to_html', $content );
	}

	/**
	 * Output the rendered template to the browser.
	 *
	 * @since  1.0.0
	 */
	public function render( $return = false ) {
		$html = $this->to_html();

		if ( $return ) {
			return apply_filters(
				'pesapress_view_render',
				$html,
				$this
			);
		} else {
			echo apply_filters(
				'pesapress_view_render',
				$html,
				$this
			);
		}
	}
}

