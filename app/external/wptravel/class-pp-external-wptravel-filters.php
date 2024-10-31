<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WP Travel Filters
 *
 * @since 2.0.0
 */
class PP_External_WpTravel_Filters {

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
		add_filter( 'wp_travel_payment_gateway_lists', array( $this, 'gateway_list' ) );
		add_action( 'wp_travel_payment_gateway_fields', array( $this, 'init_fields' ) );
		add_filter( 'wp_travel_before_save_settings', array( $this, 'save_settings' ) );
		add_action( 'wp_travel_after_frontend_booking_save', array( $this, 'process' ) );
		add_action( 'pesapress_transaction_log_update', array( $this, 'after_ipn' ) );
	}

	function gateway_list( $gateway ) {
		$gateway['pesapress'] = apply_filters( 'pesapress_wp_travel_name', __( 'PesaPress' ) );
		return $gateway;
	}

	function init_fields( $settings ) {
		$payment_option_pesapress = ( isset( $settings['settings']['payment_option_pesapress'] ) ) ? $settings['settings']['payment_option_pesapress'] : '';
		$payment_option_gateway   = ( isset( $settings['settings']['wp_travel_pesapress']['payment_option_gateway'] ) ) ? $settings['settings']['wp_travel_pesapress']['payment_option_gateway'] : '';
		?>
		<h3 class="wp-travel-tab-content-title"><?php esc_html_e( 'PesaPress' ); ?></h3>
		<table class="form-table">
			<tr>
				<th><label for="payment_option_pesapress"><?php esc_html_e( 'Enable', 'pesapress' ); ?></label></th>
				<td>
					<span class="show-in-frontend checkbox-default-design">
					<label data-on="ON" data-off="OFF">
						<input type="checkbox" value="yes" <?php checked( 'yes', $payment_option_pesapress ); ?> name="payment_option_pesapress" id="payment_option_pesapress"/>
						<span class="switch"></span>
					</label>
				</span>
					<p class="description"><?php esc_html_e( 'Check to enable PesaPress', 'pesapress' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="payment_option_pesapress"><?php esc_html_e( 'Select PesaPress Gateway', 'pesapress' ); ?></label></th>
				<td>
					<span class="show-in-frontend checkbox-default-design">
						<select name="payment_option_gateway" id="payment_option_gateway">
							<option value=""><?php esc_html_e( 'Select', 'pesapress' ); ?></option>
							<?php
								$gateways = PP_Model_Gateway::list_simple( false );
							foreach ( $gateways as $key => $value ) {
								?>
									<option value="<?php echo $key; ?>" <?php selected( $payment_option_gateway, $key ); ?>><?php echo $value; ?></option>
									<?php
							}
							?>
						</select>
					</span>
					<p class="description"><?php esc_html_e( 'Check to enable PesaPal gateway', 'pesapress' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	function save_settings( $settings ) {
		$payment_option_pesapress                                  = ( isset( $_POST['payment_option_pesapress'] ) && ! empty( $_POST['payment_option_pesapress'] ) ) ? $_POST['payment_option_pesapress'] : '';
		$payment_option_gateway                                    = ( isset( $_POST['payment_option_gateway'] ) && ! empty( $_POST['payment_option_gateway'] ) ) ? intval( $_POST['payment_option_gateway'] ) : 0;
		$settings['payment_option_pesapress']                      = $payment_option_pesapress;
		$settings['wp_travel_pesapress']['payment_option_gateway'] = $payment_option_gateway;
		return $settings;
	}

	function process( $booking_id ) {
		if ( ! $booking_id ) {
			return;
		}

		do_action( 'wt_before_payment_process', $booking_id );

		// Check if pesapal is selected.
		if ( ! isset( $_POST['wp_travel_payment_gateway'] ) || 'pesapress' !== $_POST['wp_travel_payment_gateway'] ) {
			return;
		}
		// Check if Booking with payment is selected.
		if ( ! isset( $_POST['wp_travel_booking_option'] ) || 'booking_with_payment' !== $_POST['wp_travel_booking_option'] ) {
			return;
		}

		global $wt_cart;
		$items = $wt_cart->getItems();

		if ( ! $items ) {
			return false;
		}

		$itinery_id   = isset( $_POST['wp_travel_post_id'] ) ? $_POST['wp_travel_post_id'] : 0;
		$current_url  = get_permalink( $itinery_id );
		$current_url  = apply_filters( 'wp_travel_thankyou_page_url', $current_url, $booking_id );
		$current_url  = add_query_arg(
			array(
				'booking_id' => $booking_id,
				'booked'     => true,
			),
			$current_url
		);
		$cart_amounts = $wt_cart->get_total();
		$total        = $cart_amounts['sub_total'];
		$payment_id   = get_post_meta( $booking_id, 'wp_travel_payment_id', true );
		update_post_meta( $payment_id, 'wp_travel_payment_amount', $total );
		if ( isset( $_POST['wp_travel_fname'] ) || isset( $_POST['wp_travel_email'] ) ) { // Booking using old booking form
			$first_name = $_POST['wp_travel_fname'];
			$last_name  = $_POST['wp_travel_lname'];
			$email      = $_POST['wp_travel_email'];
		} else {
			$first_name = $_POST['wp_travel_fname_traveller'];
			$last_name  = $_POST['wp_travel_lname_traveller'];
			$email      = $_POST['wp_travel_email_traveller'];

			reset( $first_name );
			$first_key = key( $first_name );

			$first_name = isset( $first_name[ $first_key ] ) && isset( $first_name[ $first_key ][0] ) ? $first_name[ $first_key ][0] : '';
			$last_name  = isset( $last_name[ $first_key ] ) && isset( $last_name[ $first_key ][0] ) ? $last_name[ $first_key ][0] : '';
			$email      = isset( $email[ $first_key ] ) && isset( $email[ $first_key ][0] ) ? $email[ $first_key ][0] : '';
		}

		$settings      = wp_travel_get_settings();
		$gateway_id    = ( isset( $settings['wp_travel_pesapress']['payment_option_gateway'] ) ) ? $settings['wp_travel_pesapress']['payment_option_gateway'] : 0;
		$currency_code = ( isset( $settings['currency'] ) ) ? $settings['currency'] : '';
		PP_Model_Transaction::external_transaction( $gateway_id, $booking_id, $total, $current_url, array() );
	}

	/**
	 * After IPN
	 */
	function after_ipn( $order_log ) {
		if ( $order_log->status === 'paid' ) {
			$booking_id = intval( $order_log->external_id );
			update_post_meta( $booking_id, 'wp_travel_booking_status', 'booked' );
			$payment_id = get_post_meta( $booking_id, 'wp_travel_payment_id', true );
			update_post_meta( $payment_id, 'wp_travel_payment_status', 'paid' );

			wp_travel_update_payment_status( $booking_id, $order_log->amount, 'paid', array() );
		}
	}
}

?>
