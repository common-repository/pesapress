<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Plugin settings
 */
class PP_Model_Forms {

	/**
	 * Settings key
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $option_key = 'pesapress_form_settings';

	/**
	 * The single instance of the class
	 *
	 * @since 1.0.0
	 */
	protected static $_instance = null;


	/**
	 * Form Settings
	 *
	 * @since  1.0.0
	 *
	 * @var array
	 */
	protected $settings = array();

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
		$this->_load();
	}

	/**
	 * Load model
	 */
	private function _load() {
		$settings = get_option( $this->option_key );
		$this->_import( $settings );
	}

	/**
	 * Import data to option
	 *
	 * @param array $data
	 */
	private function _import( $data ) {
		if ( $data ) {
			foreach ( $data as $key => $value ) {
				if ( $value ) {
					$value = maybe_unserialize( $value );
				}

				if ( null !== $value ) {
					$this->set_field( $key, $value );
				}
			}
		}
	}

	/**
	 * Set field value, bypassing the __set validation.
	 *
	 * Used for loading from db.
	 *
	 * @since  1.0.0
	 *
	 * @param string $field
	 * @param mixed  $value
	 */
	public function set_field( $field, $value ) {
		// Don't deserialize values of "private" fields.
		if ( '_' !== $field[0] ) {

			// Only set values of existing fields, don't create a new field.
			if ( property_exists( $this, $field ) ) {
				$this->$field = $value;
			}
		}
	}


	/**
	 * Save content in wp_option table.
	 *
	 * @since  1.0.0
	 */
	public function save() {
		$settings = array(
			'settings' => $this->settings,
		);
		update_option( $this->option_key, $settings );

		$this->instance = $this;
	}

	/**
	 * Reads the options from options table
	 *
	 * @since  1.0.0
	 */
	public function refresh() {
		$this->_load();
	}

	/**
	 * Delete from wp option table
	 *
	 * @since  1.0.0
	 */
	public function delete() {
		delete_option( $this->option_key );
	}


	/**
	 * Get currency settings
	 *
	 * @param string $key - the setting key
	 *
	 * @return bool|string|array
	 */
	public function get_setting( $key = null, $default = false ) {
		if ( ! empty( $this->settings ) ) {
			$settings = $this->settings;
			if ( $key ) {
				if ( isset( $settings[ $key ] ) ) {
					return $settings[ $key ];
				}
			} else {
				return $settings;
			}
		}
		return $default;
	}

	/**
	 * Set form setting
	 *
	 * @param string $key - the setting key
	 * @param string $value - the value
	 */
	public function set_setting( $key, $value ) {
		$this->settings[ $key ] = $value;
	}

	/**
	 * Set form settings
	 *
	 * @param array $settings - the settings
	 */
	public function set_settings( $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Get Settings
	 *
	 * @return array
	 */
	public function get_settings() {
		if ( empty( $this->settings ) ) {
			return $this->default_settings();
		}
		return $this->settings;
	}

	/**
	 * Default Settings
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function default_settings() {
		return array(
			array(
				'name'      => __( 'First Name', 'pesapress' ),
				'type'      => 'text',
				'uname'     => 'pesapress_firstname',
				'initial'   => '',
				'mandatory' => 'checked',
				'visible'   => 'checked',
				'delete'    => false,
			),
			array(
				'name'      => __( 'Last Name', 'pesapress' ),
				'type'      => 'text',
				'uname'     => 'pesapress_lastname',
				'initial'   => '',
				'mandatory' => 'checked',
				'visible'   => 'checked',
				'delete'    => false,
			),
			array(
				'name'      => __( 'Email', 'pesapress' ),
				'type'      => 'text',
				'uname'     => 'pesapress_email',
				'initial'   => '',
				'mandatory' => 'checked',
				'visible'   => 'checked',
				'delete'    => false,
			),
			array(
				'name'      => __( 'Amount', 'pesapress' ),
				'type'      => 'text',
				'uname'     => 'pesapress_amount',
				'initial'   => __( 'Payment Amount', 'pesapress' ),
				'mandatory' => 'checked',
				'visible'   => 'checked',
				'delete'    => false,
			),
		);
	}

	/**
	 * List of supported form elements
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function form_elements() {
		return apply_filters(
			'pesapress_form_elements',
			array(
				'text'      => __( 'Text', 'pesapress' ),
				'textarea'  => __( 'Text Area', 'pesapress' ),
				'checkbox'  => __( 'Check Box', 'pesapress' ),
				'paragraph' => __( 'Paragraph', 'pesapress' ),
			)
		);
	}

	/**
	 * Render the checkout fields
	 *
	 * @param string $total_field_label - the total field label
	 * @param int    $default_amount - the default amount
	 */
	public function render( $total_field_label, $default_amount = 0 ) {
		$content   		= '';
		$fields    		= $this->get_settings();
		$currency  		= PP_Core_Currency::instance();
		$tab_index 		= 1;
		$is_hidden 		= false;
		$value			= '';
		$current_user 	= is_user_logged_in() ? wp_get_current_user() : false;
		foreach ( $fields as $checkout_row ) {
			$visible   	= isset( $checkout_row['visible'] ) ? $checkout_row['visible'] : false;
			$mandatory 	= isset( $checkout_row['mandatory'] ) ? $checkout_row['mandatory'] : '';
			$uname     	= isset( $checkout_row['uname'] ) ? $checkout_row['uname'] : '';
			$initial   	= isset( $checkout_row['initial'] ) ? $checkout_row['initial'] : '';
			$name      	= isset( $checkout_row['name'] ) ? $checkout_row['name'] : '';
			$value		= '';
			if ( $visible && $visible === 'checked' ) {
				if ( $current_user ) {
					if ( $uname == 'pesapress_firstname' ) {
						$value		= ( !empty( $current_user->first_name ) ) ? $current_user->first_name : trim( $current_user->display_name );
					} else if ( $uname == 'pesapress_lastname' ) {
						$value		= $current_user->first_name;
					} else if ( $uname == 'pesapress_email' ) {
						$value		= $current_user->user_email;
					}
				}
				$field_class   = '';
				$required_attr = '';
				$required_text = '';
				if ( $mandatory == 'checked' ) {
					$field_class   = 'required';
					$required_attr = 'required="required"';
					$required_text = sprintf( __( '%1$srequired%2$s' ), '<small>(', ')</small>' );
				}
				$input_elem = '';
				$input_type = $checkout_row['type'];
				if ( $uname == 'pesapress_amount' ) {
					$is_hidden  = true;
					$input_elem = $currency->format_currency( false, $default_amount );
				} else {
					switch ( $input_type ) {
						case 'text':
							$input_elem = '<input type="text" value="' . $value . '" tabindex="' . $tab_index . '" class="pesapress-text-input ' . $field_class . ' ' . $uname . '_input" name="' . $uname . '" ' . $required_attr . ' placeholder="' . $initial . '" />';
							break;
						case 'textarea':
							$input_elem = '<textarea value="' . $value . '" tabindex="' . $tab_index . '" class="pesapress-textarea-input ' . $field_class . ' ' . $uname . '_input" name="' . $uname . '"  ' . $required_attr . ' placeholder="' . $initial . '"></textarea>';
							break;
						case 'checkbox':
							$input_elem = '<input tabindex="' . $tab_index . '" type="checkbox" class="' . $field_class . ' ' . $uname . '_input" name="' . $uname . '" ' . $required_attr . '/>';
							break;
						case 'paragraph':
							$input_elem = '<p class="' . $uname . '_input">' . $initial . '</p>';
							break;
					}
					if ( $input_type != 'paragraph' ) {
						$tab_index++;
					}
				}

				$content .= apply_filters( 'pesapress_form_element_open_wrapper', "<p class='pesapress_element'>" );
				if ( ! $is_hidden ) {
					$content .= apply_filters( 'pesapress_form_element_label', '<label for="' . $uname . '" class="pesapress-label ' . $uname . '">' . $name . ' ' . $required_text . '</label>', $checkout_row );
					$content .= apply_filters( 'pesapress_form_element_break', '<br/>', $checkout_row );
					$content .= apply_filters( 'pesapress_form_element_input', $input_elem, $checkout_row );
				} else {
					$content .= apply_filters( 'pesapress_form_element_label', '<label for="' . $uname . '" class="pesapress-label ' . $uname . '">' . $total_field_label . ' ' . $input_elem . '</label>', $checkout_row );
				}
				$content .= apply_filters( 'pesapress_form_element_close_wrapper', '</p>' );

			}
		}
		return apply_filters( 'pesapress_form_elements_render', $content, $this, $default_amount );
	}
}

