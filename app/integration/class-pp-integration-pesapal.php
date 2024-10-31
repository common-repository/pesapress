<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Integration_Pesapal extends PP_Core_Integration {

	/**
	 * Gateway name
	 *
	 * @var string
	 */
	public $gateway = 'pesapal';

	/**
	 * Base URL
	 *
	 * @var string
	 */
	public $base_url = '';

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
	 * Consumer
	 *
	 * @var PesaPalOAuthConsumer
	 */
	public $consumer;

	/**
	 * Signature Method
	 *
	 * @var PesaPalOAuthSignatureMethod_HMAC_SHA1
	 */
	public $signature_method;

	/**
	 * Token
	 *
	 * @var string
	 */
	public $token;

	/**
	 * Params
	 *
	 * @var string
	 */
	public $params;

	/**
	 *
	 * @var string
	 */
	public $gatewayURL;

	/**
	 *
	 * @var string
	 */
	public $QueryPaymentStatus;

	/**
	 *
	 * @var string
	 */
	public $QueryPaymentStatusByMerchantRef;

	/**
	 *
	 * @var string
	 */
	public $querypaymentdetails;

	/**
	 * Initialize the Model
	 *
	 * @since 1.0
	 */
	public function init() {
		require_once PESAPRESS_LIB_DIR . 'pesapal/OAuth.php';

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
			$this->consumer_key    = $settings['consumer_key'];
			$this->consumer_secret = $settings['consumer_secret'];
			if ( $this->mode === 'sandbox' ) {
				$api = 'http://demo.pesapal.com/';
			} else {
				$api = 'https://www.pesapal.com/';
			}

			// OAuth Signatures
			$this->consumer         = new PesaPalOAuthConsumer( $this->consumer_key, $this->consumer_secret );
			$this->signature_method = new PesaPalOAuthSignatureMethod_HMAC_SHA1();
			$this->token            = $this->params = null;

			// PesaPal End Points
			$this->gatewayURL                      = $api . 'api/PostPesapalDirectOrderV4';
			$this->QueryPaymentStatus              = $api . 'API/QueryPaymentStatus';
			$this->QueryPaymentStatusByMerchantRef = $api . 'API/QueryPaymentStatusByMerchantRef';
			$this->querypaymentdetails             = $api . 'API/querypaymentdetails';
		}
	}


	/**
	 * Process Purchase
	 *
	 * @since 1.0.0
	 */
	public function init_gateway_purchase( $order_log ) {
		$order_xml = $this->generate_xml(
			$order_log->amount,
			$order_log->external_id,
			$order_log->get_meta( 'firstname' ) . ' ' . $order_log->get_meta( 'lastname' ),
			$order_log->get_meta( 'email' ),
			$order_log->get_meta( 'phone' )
		);
		$url       = PesaPalOAuthRequest::from_consumer_and_token( $this->consumer, $this->token, 'GET', $this->gatewayURL, $this->params );
		$url->set_parameter( 'oauth_callback', $this->callback_url );
		$url->set_parameter( 'pesapal_request_data', $order_xml );
		$url->sign_request( $this->signature_method, $this->consumer, $this->token );
		?>
		<div class="pesapress_container" style="position:relative;">
			<img class="pesapress_loading_preloader" src="<?php echo PESAPRESS_ASSETS_URL; ?>/img/spinner.gif" alt="loading" style="position:absolute;"/>
			<iframe class="pesapress_loading_frame" src="<?php echo $url; ?>" width="100%" height="700px"  scrolling="yes" frameBorder="0">
				<p><?php _e( 'Browser unable to load iFrame', 'pesapress' ); ?></p>
			</iframe>
		</div>
		<script>
			jQuery(document).ready(function () {
				jQuery('.pesapress_loading_frame').on('load', function () {
					jQuery('.pesapress_loading_preloader').hide();
				});
			});
		</script>
		<?php
	}

	/**
	 * Process Return
	 *
	 * @since 1.0.0
	 */
	public function process_return( $order_log ) {
		$reference   = $_REQUEST['pesapal_merchant_reference'];
		$tracking_id = $_REQUEST['pesapal_transaction_tracking_id'];
		if ( $order_log && ( $order_log->external_id === $tracking_id ) ) {
			$transaction_status = $this->get_transaction_details( $reference, $tracking_id );
			$status             = $transaction_status['status'];
			switch ( $status ) {
				case 'PENDING':
					$order_log->status = 'pending';
					break;
				case 'COMPLETED':
					$order_log->status = 'paid';
					break;
				case 'FAILED':
					$order_log->status = 'canceled';
					break;
				default:
					$order_log->status = 'canceled';
					break;
			}
			$order_log->save();
		}
	}

	/**
	 * Process IPN
	 *
	 * @since 1.0.0
	 */
	public function process_ipn() {
		$reference    = $_REQUEST['pesapal_merchant_reference'];
		$tracking_id  = $_REQUEST['pesapal_transaction_tracking_id'];
		$notification = $_REQUEST['pesapal_notification_type'];
		$order_log    = PP_Model_Log::get_by_external_id( $tracking_id );
		if ( $order_log ) {
			$transaction_status = $this->get_transaction_details( $reference, $tracking_id );
			$status             = $transaction_status['status'];
			switch ( $status ) {
				case 'PENDING':
					$order_log->status = 'pending';
					break;
				case 'COMPLETED':
					$order_log->status = 'paid';
					break;
				case 'FAILED':
					$order_log->status = 'canceled';
					break;
				default:
					$order_log->status = 'canceled';
					break;
			}
			$order_log->save();
			$this->order_log = $order_log;
		}
		exit( "pesapal_notification_type=$notification&pesapal_transaction_tracking_id=$tracking_id&pesapal_merchant_reference=$reference" );
	}

	/**
	 * Generate Payment XML
	 */
	private function generate_xml( $total, $reference, $name, $email, $phone = '' ) {
		$name_split = explode( ' ', $name );
		$first_name = $name;
		$last_name  = $name;
		if ( count( $name_split ) > 1 ) {
			$first_name = $name_split[0];
			$last_name  = $name_split[1];
		}
		$xml = '<?xml version="1.0" encoding="utf-8"?>
			<PesapalDirectOrderInfo xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
			Amount="' . $total . '"
			Description="Order from ' . bloginfo( 'name' ) . '."
			Type="MERCHANT"
			Reference="' . $reference . '"
			FirstName="' . $first_name . '"
			LastName="' . $last_name . '"
			Email="' . $email . '"
			PhoneNumber="' . $phone . '"
			Currency="' . $this->currency . '"
			xmlns="http://www.pesapal.com" />';

		return htmlentities( $xml );
	}

	/**
	 * Handle HTTP requests
	 *
	 * @param string $request_status
	 *
	 * @return array
	 **/
	private function do_http( $request_status ) {

		$response = wp_remote_get(
			$request_status,
			array(
				'sslverify' => false,
			)
		);

		$response = wp_remote_retrieve_body( $response );
		return $response;
	}


	/**
	 * Get Transaction Details
	 */
	private function get_transaction_details( $merchant_reference, $tracking_id ) {

		$request_status = PesaPalOAuthRequest::from_consumer_and_token(
			$this->consumer,
			$this->token,
			'GET',
			$this->querypaymentdetails,
			$this->params
		);

		$request_status->set_parameter( 'pesapal_merchant_reference', $merchant_reference );
		$request_status->set_parameter( 'pesapal_transaction_tracking_id', $tracking_id );
		$request_status->sign_request( $this->signature_method, $this->consumer, $this->token );

		$responseData = $this->do_http( $request_status );

		$pesapalResponse = explode( ',', $responseData );
		$response        = array(
			'tracking_id'        => $pesapalResponse[0],
			'payment_method'     => $pesapalResponse[1],
			'status'             => $pesapalResponse[2],
			'merchant_reference' => $pesapalResponse[3],
		);

		return $response;
	}

	private function status_request( $transaction_id, $merchant_ref ) {
		$request_status = PesaPalOAuthRequest::from_consumer_and_token(
			$this->consumer,
			$this->token,
			'GET',
			$this->gatewayURL,
			$this->params
		);
		$request_status->set_parameter( 'pesapal_merchant_reference', $merchant_ref );
		$request_status->set_parameter( 'pesapal_transaction_tracking_id', $transaction_id );
		$request_status->sign_request( $this->signature_method, $this->consumer, $this->token );

		return $this->check_transaction_status( $merchant_ref );
	}


	/**
	 * Check Transaction status
	 */
	private function check_transaction_status( $merchant_ref, $tracking_id = null ) {
		$query_url = ( $tracking_id ) ? $this->QueryPaymentStatus : $this->QueryPaymentStatusByMerchantRef;

		// get transaction status
		$request_status = PesaPalOAuthRequest::from_consumer_and_token(
			$this->consumer,
			$this->token,
			'GET',
			$query_url,
			$this->params
		);

		$request_status->set_parameter( 'pesapal_merchant_reference', $merchant_ref );

		if ( $tracking_id ) {
			$request_status->set_parameter( 'pesapal_transaction_tracking_id', $tracking_id );
		}

		$request_status->sign_request( $this->signature_method, $this->consumer, $this->token );

		return $this->do_http( $request_status );
	}
}
?>
