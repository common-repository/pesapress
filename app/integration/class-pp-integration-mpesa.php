<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Integration_Mpesa extends PP_Core_Integration {

	/**
	 * Gateway name
	 *
	 * @var string
	 */
	public $gateway = 'mpesa';


	/**
	 * Merchant Name
	 *
	 * @var string
	 */
	public $merchant_name = '';

	/**
	 * Credentials endpoint
	 * Defaults to sandbox
	 *
	 * @var string
	 */
	public $credentials_endpoint = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

	/**
	 * Payments Endpoint
	 * Defaults to sandbox
	 *
	 * @var string
	 */
	public $payments_endpoint = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

	/**
	 * PassKey
	 *
	 * @var string
	 */
	public $passkey = '';

	/**
	 * Consumer Key
	 *
	 * @var string
	 */
	public $consumer_key = '';

	/**
	 * Consumer Secret
	 *
	 * @var string
	 */
	public $consumer_secret = '';

	/**
	 * Shortcode
	 *
	 * @var string
	 */
	public $shortcode = '';

	/**
	 * Initialize the Model
	 *
	 * @since 1.0
	 */
	public function init() {
		$this->init_settings( $this->model->setting_details );
	}


	/**
	 * Initialise settings
	 *
	 * @param array $settings - saved settings
	 *
	 * @since 1.0
	 */
	public function init_settings( $settings ) {
		if ( $settings && is_array( $settings ) ) {
			$this->merchant_name        = $settings['merchant_name'];
			$this->credentials_endpoint = $settings['credentials_endpoint'];
			$this->payments_endpoint    = $settings['payments_endpoint'];
			$this->passkey              = $settings['passkey'];
			$this->consumer_key         = $settings['consumer_key'];
			$this->consumer_secret      = $settings['consumer_secret'];
			$this->shortcode            = $settings['shortcode'];
		}
	}

	/**
	 * Process Purchase
	 *
	 * @since 1.0.0
	 */
	public function init_gateway_purchase( $order_log ) {
		if ( $order_log->get_meta( 'mpesa_request_id' ) ) {
			echo sprintf( __( '%1$sTransaction already in process%2$s', 'pesapress' ), '<h2>', '</h2>' );
		} else {
			$credentials    = base64_encode( $this->consumer_key . ':' . $this->consumer_secret );
			$token_response = wp_remote_get(
				$this->credentials_endpoint,
				array( 'headers' => array( 'Authorization' => 'Basic ' . $credentials ) )
			);
			if ( ! is_wp_error( $token_response ) ) {
				$token_array = json_decode( '{"token_results":[' . $token_response['body'] . ']}' );

				$phone = $order_log->get_meta( 'phone' );
				if ( substr( $phone, 0, 1 ) === '0' ) {
					$phone = ltrim( $phone, '0' );
					$phone = '254' . $phone;
				}

				if ( is_array( $token_array->token_results ) &&
					array_key_exists( 'access_token', $token_array->token_results[0] ) ) {
					$access_token   = $token_array->token_results[0]->access_token;
					$timestamp      = date( 'YmdHis' );
					$b64            = $this->shortcode . '' . $this->passkey . '' . $timestamp;
					$pwd            = base64_encode( $b64 );
					$curl_post_data = array(
						'BusinessShortCode' => $this->shortcode,
						'Password'          => $pwd,
						'Timestamp'         => $timestamp,
						'TransactionType'   => 'CustomerPayBillOnline',
						'Amount'            => round( $order_log->amount ),
						'PartyA'            => $phone,
						'PartyB'            => $this->shortcode,
						'PhoneNumber'       => $phone,
						'CallBackURL'       => $this->callback_url,
						'AccountReference'  => time(),
						'TransactionDesc'   => __( 'Sending a lipa na mpesa request', 'pesapress' ),
					);
					$data_string    = json_encode( $curl_post_data );
					$response       = wp_remote_post(
						$this->payments_endpoint,
						array(
							'headers' => array(
								'Content-Type'  => 'application/json',
								'Authorization' => 'Bearer ' . $access_token,
							),
							'body'    => $data_string,
						)
					);
					if ( ! is_wp_error( $response ) ) {
						$response_array = json_decode( '{"callback_results":[' . $response['body'] . ']}' );
						if ( is_array( $response_array->callback_results ) &&
							array_key_exists( 'ResponseCode', $response_array->callback_results[0] ) &&
							$response_array->callback_results[0]->ResponseCode == 0 ) {
							$order_log->set_meta( 'mpesa_request_id', $response_array->callback_results[0]->MerchantRequestID );
							echo sprintf( __( '%1$sTransaction in process. Check your phone to enter M-PESA pin%2$s', 'pesapress' ), '<h2>', '</h2>' );
						} else {
							_e( 'Unable to send payment request. Please refresh the page to try again', 'pesapress' );
						}
					} else {
						echo sprintf( __( '%1$sUnable to send payment request. Please check your settings%2$s. Error %3$s', 'pesapress' ), '<h2>', '</h2>', $response->get_error_message() );
					}
				} else {
					echo sprintf( __( '%1$sUnable to send payment request. Please check your settings%2$s', 'pesapress' ), '<h2>', '</h2>' );
				}
			} else {
				echo sprintf( __( '%1$sUnable to send payment request. Please check your settings%2$s', 'pesapress' ), '<h2>', '</h2>' );
			}
		}
	}


	/**
	 * Process Return
	 *
	 * @since 1.0.0
	 */
	public function process_return( $order_log ) {

	}

	/**
	 * Process IPN
	 *
	 * @since 1.0.0
	 */
	public function process_ipn() {
		$data = file_get_contents( 'php://input' );
		$data = '{"callback_results":[' . $data . ']}';

		$json_data = json_decode( $data, true );

		$index = 0;
		if ( isset( $json_data['callback_results'][ $index ] ) &&
			is_array( $json_data['callback_results'][ $index ] ) &&
			isset( $json_data['callback_results'][ $index ]['Body'] ) &&
			is_array( $json_data['callback_results'][ $index ]['Body'] ) &&
			isset( $json_data['callback_results'][ $index ]['Body']['stkCallback'] ) &&
			is_array( $json_data['callback_results'][ $index ]['Body']['stkCallback'] ) ) {

			$merchant_id = $json_data['callback_results'][ $index ]['Body']['stkCallback']['MerchantRequestID'];
			$checkout_id = $json_data['callback_results'][ $index ]['Body']['stkCallback']['CheckoutRequestID'];
			$rescode     = intval( $json_data['callback_results'][ $index ]['Body']['stkCallback']['ResultCode'] );
			$resdesc     = $json_data['callback_results'][ $index ]['Body']['stkCallback']['ResultDesc'];

			$order_log = PP_Model_Log::get_by_meta( 'mpesa_request_id', $merchant_id );
			if ( $order_log ) {
				$order_log->set_meta( 'mpesa_result_code', $rescode . '' );
				$order_log->set_meta( 'mpesa_checkout_id', $checkout_id );
				$order_log->set_meta( 'mpesa_result_desc', $resdesc );
				switch ( $rescode ) {
					case 0:
						$order_log->status = 'paid';
						break;

					case 1032:
						$order_log->status = 'canceled';
						$order_log->set_meta( 'mpesa_response', __( 'Payment request was cancelled', 'pesapress' ) );
						break;

					case 1001:
						$order_log->status = 'pending';
						$order_log->set_meta( 'mpesa_response', __( 'A similar transaction is in progress, please wait as we process the transaction', 'pesapress' ) );
						break;

					case 2001:
						$order_log->status = 'pending';
						$order_log->set_meta( 'mpesa_response', __( 'Wrong M-PESA pin entered, please click on pay and enter pin again', 'pesapress' ) );
						break;

					case 1:
						$order_log->status = 'pending';
						$order_log->set_meta( 'mpesa_response', __( 'The balance is insufficient for the transaction', 'pesapress' ) );
						break;

					default:
						$order_log->status = 'pending';
						$order_log->set_meta( 'mpesa_response', __( 'Error encountered during payment processing', 'pesapress' ) );
						break;
				}

				$order_log->save();
				$this->order_log = $order_log;
			}
		}
	}
}

