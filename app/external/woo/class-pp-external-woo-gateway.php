<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( class_exists( 'WC_Payment_Gateway' ) ) :

	/**
	 * WooCommerce Payment Class
	 *
	 * @since 1.0.1
	 */
	class PP_External_Woo_Gateway extends WC_Payment_Gateway {

		function __construct() {
			// Settings
			$this->id                 = 'pesapress';
			$this->method_title       = 'PesaPress';
			$this->has_fields         = false;
			$this->debug              = $this->get_option( 'debug' );
			$this->method_description = __( 'Enable PesaPress payment gateways', 'pesapress' );
			$this->init_form_fields();
			$this->init_settings();

			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->setting_id  = $this->get_option( 'setting_id' );
			$this->log_results = ( 'yes' == $this->debug );

			// Set up logging
			$this->log = wc_get_logger();

			if ( is_admin() ) {
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			}

			add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'payment_page' ) );

			add_action( 'pesapress_transaction_log_update', array( $this, 'transaction_update' ) );
		}

		/**
		 * Handle logging
		 *
		 * @since 1.0.0
		 *
		 * @param string $message
		 */
		function pesapress_do_log( $message ) {
			if ( $this->log_results ) {
				$this->log->debug( $message, array( 'source' => 'pesapress' ) );
			}
		}

		/**
		 * Form settings
		 */
		function init_form_fields() {
			$this->form_fields = array(

				'enabled'     => array(
					'title'   => __( 'Enable/Disable', 'pesapress' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable PesaPress', 'pesapress' ),
					'default' => 'no',
				),
				'title'       => array(
					'title'       => __( 'Title', 'pesapress' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'pesapress' ),
					'default'     => __( 'PesaPress Payment', 'pesapress' ),
				),
				'description' => array(
					'title'       => __( 'Description', 'pesapress' ),
					'type'        => 'textarea',
					'description' => __( 'This is the description which the user sees during checkout.', 'pesapress' ),
					'default'     => __( 'Payment via PesaPress.', 'pesapress' ),
				),
				'setting_id'  => array(
					'title'       => __( 'PesaPress Gateway Setting', 'pesapress' ),
					'type'        => 'select',
					'label'       => __( 'Select PesaPress Gateway', 'pesapress' ),
					'description' => __( 'The PesaPress gateway to use', 'pesapress' ),
					'options'     => PP_Model_Gateway::list_simple( false ),
				),

				'debug'       => array(
					'title'       => __( 'Debug Log', 'pesapress' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable logging', 'pesapress' ),
					'default'     => 'no',
					'description' => sprintf( __( 'Log PesaPress events, such as IPN requests, inside <code>woocommerce/logs/pesapress-%s.txt</code>', 'woocommerce' ), sanitize_file_name( wp_hash( 'pesapress' ) ) ),
				),
			);

		}

		/**
		 * Admin options
		 */
		public function admin_options() {
			?>
				<h3><?php _e( 'PesaPress', 'pesapress' ); ?></h3>
				<table class="form-table">
					<?php $this->generate_settings_html(); ?>
				</table>
			<?php
		}

		/**
		 * Process Payment
		 *
		 * @param int $order_id the WooCommerce Order ID
		 */
		function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );
			$this->pesapress_do_log( 'Processing Payment ' . $order_id );
			if ( $order->get_status() === 'completed' ) {
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
				);
			} else {
				return array(
					'result'   => 'success',
					'redirect' => $order->get_checkout_payment_url( true ),
				);
			}
		}

		/**
		 * Payment page
		 *
		 * @param int $order_id the WooCommerce Order ID
		 */
		function payment_page( $order_id ) {
			$order_log = PP_Model_Log::get_by_external_id( $order_id );
			$order     = wc_get_order( $order_id );
			if ( $order_log && $order ) {
				$this->pesapress_do_log( 'Processing return payment ' . $order_id );
				// Payment return
				if ( $order->get_status() === 'completed' ) {
					return array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $order ),
					);
				} else {
					$order->update_status( 'wc-processing', sprintf( __( 'Payment accepted, awaiting confirmation. Status : %s' ), $order_log->status ) );
					return array(
						'result'   => 'success',
						'redirect' => $order->get_checkout_payment_url( true ),
					);
				}
			} else {
				$this->pesapress_do_log( 'Processing return ' . $order_id );
				$return_url = $this->get_return_url( $order );
				$data 		= array(
					array(
						'name' 	=> 'firstname',
						'value' => $order->get_billing_first_name()
					),
					array(
						'name' 	=> 'lastname',
						'value' => $order->get_billing_last_name()
					),
					array(
						'name' 	=> 'email',
						'value' => $order->get_billing_email()
					),
					array(
						'name' 	=> 'phone',
						'value' => preg_replace( "/[^0-9]/", "", str_replace( ' ', '', $order->get_billing_phone() ) )
					)
				);
				PP_Model_Transaction::external_transaction( $this->setting_id, $order_id, $order->get_total(), $return_url, $data );
			}
		}

		/**
		 * Process transation update
		 *
		 * @param PP_Model_Log $order_log - the order log
		 *
		 * @since 1.0.0
		 */
		function transaction_update( $order_log ) {
			$order = wc_get_order( $order_log->external_id );
			if ( $order ) {
				if ( $order_log->status === 'paid' ) {
					$order->update_status( 'wc-completed', 'order_note' );
					$order->payment_complete();
				} else {
					$order->update_status( 'wc-processing', sprintf( __( 'Payment accepted, awaiting confirmation. Status : %s' ), $order_log->status ) );
				}
			}
		}
	}

endif;
?>
