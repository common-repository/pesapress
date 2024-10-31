<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Transaction Model
 * Handles all transactions
 *
 * @since 1.0.0
 */
class PP_Model_Transaction {

	/**
	 * Save transaction
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function save_tranaction() {
		$response = array(
			'message' => __( 'Invalid action. Please check the form and try again', 'pesapress' ),
			'success' => false,
		);

		$gateway_id = isset( $_POST['gateway_id'] ) ? sanitize_text_field( $_POST['gateway_id'] ) : false;
		if ( $gateway_id ) {
			$gateway    = new PP_Model_Gateway( $gateway_id );
			$can_submit = false;
			if ( is_numeric( $gateway->setting_id ) && $gateway->setting_id > 0 ) {
				$pesapress_forms = PP_Model_Forms::instance();
				$fields          = $pesapress_forms->get_settings();

				//If a form id is passed, we add a filter
				if ( isset( $_POST['form_id'] ) ) {
					$form_id	= sanitize_text_field( $_POST['form_id'] );
					$fields		= apply_filters( 'pesapress_form_fields', $fields, $form_id );
				}
				
				$total_fields    = count( $fields ) + 1;

				if ( isset( $_POST[ "input_$total_fields" ] ) && empty( $_POST[ "input_$total_fields" ] ) ) {
					$can_submit = true;
				} else {
					// Bot response. Dont do anything
					$response = array(
						'message' => __( 'Transaction processed successfully', 'pesapress' ),
						'success' => true,
					);
				}

				$errors       = array();
				$data_to_save = array();
				if ( $can_submit ) {
					$amount     = floatval( sanitize_text_field( $_POST['amount'] ) );
					$page_id    = sanitize_text_field( $_POST['page_id'] );
					$return_url = sanitize_text_field( $_POST['return_url'] );

					if ( ! empty( $return_url ) && ! filter_var( $return_url, FILTER_VALIDATE_URL ) ) {
						$return_url = get_site_url(); // If the url is invalid, et it to sit url
					} else {
						$return_url = esc_url( $return_url );
					}

					/**
					 * Filter the return url
					 * 
					 * @since 2.2.9.1
					 * 
					 * @return string
					 */
					$return_url = apply_filters( 'pesapress_transaction_return_url', $return_url, $gateway, $fields, $page_id );

					if ( ! is_numeric( $page_id ) && ! empty( $page_id ) ) {
						$page_id = 'N/A'; // Set N/A as page id
					}

					// Loop fields
					foreach ( $fields as $checkout_row ) {
						$visible   = isset( $checkout_row['visible'] ) ? $checkout_row['visible'] : false;
						$mandatory = isset( $checkout_row['mandatory'] ) ? $checkout_row['mandatory'] : '';
						$uname     = isset( $checkout_row['uname'] ) ? $checkout_row['uname'] : '';
						$initial   = isset( $checkout_row['initial'] ) ? $checkout_row['initial'] : '';
						$name      = isset( $checkout_row['name'] ) ? $checkout_row['name'] : '';
						if ( $visible && $visible === 'checked' ) {
							if ( $uname != 'pesapress_amount' ) {
								if ( ( $mandatory === 'checked' ) ) {
									if ( ! isset( $_POST[ $uname ] ) || empty( $_POST[ $uname ] ) ) {
										$errors[ $uname ] = sprintf( __( '%s is a required field', 'pesapress' ), $name );
									} else {
										$save_name = $uname;
										if ( $uname === 'pesapress_firstname' ) {
											$save_name   = 'firstname';
											$field_value = sanitize_text_field( $_POST[ $uname ] );
											$field_value = esc_html( $_POST[ $uname ] );
										} elseif ( $uname === 'pesapress_lastname' ) {
											$save_name   = 'lastname';
											$field_value = sanitize_text_field( $_POST[ $uname ] );
											$field_value = esc_html( $_POST[ $uname ] );
										} elseif ( $uname === 'pesapress_email' ) {
											$save_name   = 'email';
											$field_value = sanitize_email( $_POST[ $uname ] );
											if ( ! is_email( $field_value ) ) {
												$errors[ $uname ] = sprintf( __( '%s is an invalid email', 'pesapress' ), $name );
											}
										} elseif ( $uname === 'pesapress_phone' ) {
											$save_name   = 'phone';
											$field_value = sanitize_text_field( $_POST[ $uname ] );
											$field_value = esc_html( $_POST[ $uname ] );
										} else {
											$field_value = sanitize_text_field( $_POST[ $uname ] );
											$field_value = esc_html( $_POST[ $uname ] );
										}

										$data_to_save[] = array(
											'name'  => $save_name,
											'value' => $field_value,
										);
									}
								} else {
									$field_value    = sanitize_text_field( $_POST[ $uname ] );
									$field_value    = esc_html( $_POST[ $uname ] );
									$data_to_save[] = array(
										'name'  => $uname,
										'value' => $field_value,
									);
								}
							}
						}
					}

					/**
					 * Action called before save
					 */
					do_action( 'pesapress_transaction_before_save', $errors, $gateway_id, $data_to_save );

					if ( empty( $errors ) ) {
						if ( ! empty( $data_to_save ) ) {
							$data_to_save[]            = array(
								'name'  => 'page_id',
								'value' => $page_id,
							);
							if ( isset( $_POST['form_id'] ) ) {
								$data_to_save[]            = array(
									'name'  => 'form_id',
									'value' => sanitize_text_field( $_POST['form_id'] ),
								);
							}

							$payment_log               = new PP_Model_Log();
							$payment_log->amount       = $amount;
							$payment_log->gateway_id   = $gateway_id;
							$payment_log->gateway_name = $gateway->setting_name;
							$payment_log->status       = ( $amount == 0 ) ? 'paid' : 'pending';
							$payment_log->save();
							$payment_log->set_fields( $data_to_save );
							$url      = add_query_arg( 'pp-pay', $payment_log->log_id, $return_url );

							/**
							 * Action called when payment log is saved
							 * 
							 * @since 2.2.9.1
							 */
							do_action( 'pesapress_payment_log_saved', $payment_log );

							$response = array(
								'message' => __( 'Transaction saved successfully', 'pesapress' ),
								'success' => true,
								'log_id'  => $payment_log->log_id,
								'url'     => $url,
							);
						} else {
							$response = array(
								'message' => __( 'No data to save for this transaction', 'pesapress' ),
								'success' => false,
							);
						}
					} else {
						$response = array(
							'message' => sprintf( __( 'Please correct the following errors %1$s %2$s', 'pesapress' ), '<br/>', implode( '<br/>', $errors ) ),
							'success' => false,
							'errors'  => $errors,
						);
					}
				}
			} else {
				$response = array(
					'message' => __( 'Invalid action. Selected gateway does not exist', 'pesapress' ),
					'success' => false,
				);
			}
			do_action( 'pesapress_transaction_after_save', $gateway_id );
		}
		return $response;
	}

	/**
	 * Save External transaction
	 *
	 * @param int    $gateway_id
	 * @param string $external_id
	 * @param double $amount
	 * @param string $return_url
	 * @param array  $data
	 *
	 * @since 2.0.0
	 */
	public static function external_transaction( $gateway_id, $external_id, $amount, $return_url, $data ) {
		$gateway = new PP_Model_Gateway( $gateway_id );
		if ( is_numeric( $gateway->setting_id ) && $gateway->setting_id > 0 ) {
			$data[] = array(
				'name'  => 'return_url',
				'value' => $return_url,
			);

			$payment_log               = new PP_Model_Log();
			$payment_log->amount       = $amount;
			$payment_log->gateway_id   = $gateway_id;
			$payment_log->external_id  = $external_id;
			$payment_log->gateway_name = $gateway->setting_name;
			$payment_log->status       = ( $amount == 0 ) ? 'paid' : 'pending';
			$payment_log->save();
			$payment_log->set_fields( $data );

			switch ( $gateway->setting_name ) {
				case 'pesapal':
					$integration = new PP_Integration_Pesapal( $gateway );
					$integration->process_purchase( $payment_log );
					break;

				default:
					do_action( 'pesapress_payment_payment_form', $payment_log );
					break;
			}
		} else {
			return __( 'Invalid gateway. Please check your settingss', 'pesapress' );
		}
	}
}

