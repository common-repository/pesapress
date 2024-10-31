<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Controller_Front {

	/**
	 * The single instance of the class
	 *
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Response message
	 * Used in full form submit
	 *
	 * @var array
	 */
	private static $response = array();

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
		add_action( 'pre_get_posts', array( $this, 'handle_payment_return' ), 1 );
		add_shortcode( 'pesapress_form', array( __CLASS__, 'payment_form' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'front_resources' ) );

		// Handle form submits
		add_action( 'wp', array( $this, 'maybe_handle_submit' ), 9 );
		add_action( 'wp_ajax_pesapress_process_payment_form', array( $this, 'process_form' ) );
		add_action( 'wp_ajax_nopriv_pesapress_process_payment_form', array( $this, 'process_form' ) );

		// Error mesages
		add_action( 'pesapress_payment_form_submit_response', array( $this, 'show_error_messages' ) );
	}

	/**
	 * Front scripts and styles
	 *
	 * @since 1.0.0
	 */
	function front_resources() {
		wp_register_script(
			'pp-front',
			PESAPRESS_ASSETS_URL . '/js/pp-public.min.js',
			array( 'jquery' ),
			PESAPRESS_VERSION
		);

		wp_register_style(
			'pp-front',
			PESAPRESS_ASSETS_URL . '/css/pp-public.min.css',
			null,
			PESAPRESS_VERSION
		);

		$vars = apply_filters(
			'pp-admin-vars',
			array(
				'ajaxurl'    => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
				'error'      => __( 'An error occured', 'pesapress' ),
				'processing' => __( 'Processing, please wait...', 'pesapress' ),
				'assets'     => array(
					'spinner' => PESAPRESS_ASSETS_URL . '/img/spinner.gif',
				),
			)
		);

		wp_localize_script( 'pp-front', 'ppfront', $vars );

		wp_enqueue_script( 'pp-front' );
		wp_enqueue_style( 'pp-front' );
	}

	/**
	 * Handle payment gateway return IPNs.
	 *
	 * Used by All gateways.
	 *
	 * Related action hooks:
	 * - pre_get_posts
	 *
	 * @since  1.0.0
	 *
	 * @param WP_Query $wp_query The WordPress query object
	 */
	function handle_payment_return( $wp_query ) {
		// Do not check custom loops.
		if ( ! $wp_query->is_main_query() ) {
			return; }

		// pp-payment-ipn/XYZ becomes index.php?ppgateway=XYZ
		if ( ! empty( $wp_query->query_vars['ppgateway'] ) ) {
			$gateway   = $wp_query->query_vars['ppgateway'];
			$gateways  = PP_Core_Helper::get_integrated_gateways();
			$gateways  = array_keys( $gateways );
			$gw_pieces = explode( '-', $gateway );
			if ( count( $gw_pieces ) == 2 ) {
				if ( in_array( $gw_pieces[0], $gateways ) ) {
					$gw_class = new PP_Model_Gateway( $gw_pieces[1] );
					if ( is_numeric( $gw_class->setting_id ) && $gw_class->setting_id > 0 ) {
						$class = 'PP_Integration_' . ucfirst( $gateway->setting_name );
						if ( class_exists( $class ) ) {
							$integration = new $class( $gw_class );
							$integration->process_ipn();
						} else {
							do_action( 'pesapress_payment_ipn_default', $gw_class );
						}
					}
				}
			}
		} elseif ( ! empty( $wp_query->query_vars['ppreturn'] ) ) {
			// pp-payment-return/XYZ becomes index.php?ppgateway=XYZ
			$order_id     = $wp_query->query_vars['ppreturn'];
			$settings     = PP_Model_Settings::instance();
			$order_log    = new PP_Model_Log( $order_id );

			/**
			 * Process payment return
			 * 
			 * @since 2.2.9.2
			 */
			do_action( 'pesapress_process_payment_return', $order_id, $settings, $order_log );
			
			$success_page = $settings->get_checkout_setting( 'success_page' );
			$success_page = get_permalink( $success_page );
			$success_page = apply_filters( 'pesapress_payment_return_success', get_permalink( $success_page ), $order_log );
			$success_page = add_query_arg( 'order_id', $order_id, $success_page );
			
			if ( $order_log->log_id ) {
				$gw_class = new PP_Model_Gateway( $order_log->gateway_id );
				if ( is_numeric( $gw_class->setting_id ) && $gw_class->setting_id > 0 ) {
					$class = 'PP_Integration_' . ucfirst( $order_log->gateway_name );
					if ( class_exists( $class ) ) {
						$integration = new $class( $gw_class );
						$integration->process_return( $order_log );
						if ( $order_log->get_meta( 'return_url' ) ) {
							$success_page = $order_log->get_meta( 'return_url' );
							$success_page = add_query_arg( 'order_id', $order_id, $success_page );
						}
						do_action( 'pesapress_payment_return_' . $order_log->gateway_name, $order_log );
					} else {
						do_action( 'pesapress_payment_return_default', $order_log );
					}
				}
			}

			do_action( 'pesapress_after_process_payment_return', $order_id, $settings, $order_log );
			
			if ( wp_redirect( $success_page ) ) {
				exit;
			}
		}
	}

	/**
	 * Payment form shortcode
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	static function payment_form( $atts ) {
		extract(
			shortcode_atts(
				array(
					'button_name' 	=> __( 'Complete Payment', 'pesapress' ),
					'total_label' 	=> __( 'Total', 'pesapress' ),
					'amount'      	=> 1.00,
					'gateway_id'  	=> 0,
					'form_class'  	=> 'payment-form',
					'ajax_form'   	=> 'true',
					'show_amount'	=> 'false'
				),
				$atts
			)
		);

		$amount_form = $show_amount === 'true'? true: false;

		if ( $gateway_id && intval( $gateway_id ) > 0 && PP_Model_Gateway::exists( intval( $gateway_id ) ) ) {
			$show_payment_form = false;
			$order_log         = null;
			if ( isset( $_REQUEST['pp-pay'] ) ) {
				$order_id  = (int) $_REQUEST['pp-pay'];
				$order_log = new PP_Model_Log( $order_id );
				if ( $order_log->log_id && $order_log->status === 'pending' ) {
					$show_payment_form = true;
				}
			}
			if ( ! $show_payment_form ) {
				if ( $amount_form ) {
					$view 	= new PP_Views_Front_Forms_Input();
				} else {
					$view	= new PP_Views_Front_Forms_Payment();
				}
				
				$view->data = array(
					'button_name' => $button_name,
					'total_label' => $total_label,
					'amount'      => $amount,
					'gateway_id'  => $gateway_id,
					'form_class'  => $form_class,
					'ajax_form'   => $ajax_form,
				);
				return $view->render( true );
			} else {
				if ( $order_log->log_id ) {
					$gw_class = new PP_Model_Gateway( $order_log->gateway_id );
					if ( is_numeric( $gw_class->setting_id ) && $gw_class->setting_id > 0 ) {
						$class = 'PP_Integration_' . ucfirst( $order_log->gateway_name );
						ob_start();
						if ( class_exists( $class ) ) {
							$integration = new $class( $gw_class );
							$integration->process_purchase( $order_log );
						} else {
							do_action( 'pesapress_payment_payment_form', $order_log );
						}
						$content = ob_get_clean();
						return apply_filters( 'pesapress_payment_form', $content, $order_log );
					}
				}
			}
		}
		return apply_filters( 'pesapress_payment_form_default_messages', __( 'Form not set up correctly', 'pesapress' ) );

	}

	/**
	 * Handle full form submit
	 *
	 * @since 1.0.0
	 */
	function maybe_handle_submit() {
		if (
			isset( $_POST['pesapress_nonce'] )
			&& wp_verify_nonce( $_POST['pesapress_nonce'], 'pesapress_payment_form' )
		) {
			// Handle full form submit
			$response = PP_Model_Transaction::save_tranaction();

			$response = apply_filters( 'pesapress_process_form_submit', $response );

			self::$response = $response;

			if ( $response['success'] ) {
				if ( isset( $response['url'] ) ) {
					if ( wp_redirect( $response['url'] ) ) {
						exit;
					}
				}
			}
		}
	}

	/**
	 * Process form
	 *
	 * @since 1.0.0
	 */
	function process_form() {
		if ( wp_verify_nonce( $_POST['pesapress_nonce'], 'pesapress_payment_form' ) ) {
			$response = PP_Model_Transaction::save_tranaction();

			$response = apply_filters( 'pesapress_process_ajax_form', $response );

			if ( ! $response['success'] && isset( $response['errors'] ) ) {
				wp_send_json_error( $response );
			} else {
				wp_send_json_success( $response );
			}
		}
		wp_send_json_error(
			array(
				'message' => __( 'Invalid action', 'pesapress' ),
				'success' => false,
			)
		);
	}

	/**
	 * Show error or success message
	 *
	 * @param object $form
	 *
	 * @return string
	 */
	function show_error_messages( $form ) {
		$response = self::$response;
		if ( ! empty( $response ) && is_array( $response ) ) {
			$label_class = $response['success'] ? 'success' : 'error';
			?>
			<label class="pesapress-label--<?php echo esc_attr( $label_class ); ?>"><span><?php echo $response['message']; ?></span></label>
			<?php
		}
	}
}
?>
