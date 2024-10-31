<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Views_Admin_Gateways_Integrations_Mpesa extends PP_Views_Admin_Gateways_Integration {


	/**
	 * Set the top instructions
	 *
	 * @since  1.0.0
	 */
	public function before_render() {
		$gateway = isset( $this->data['gateway'] ) ? $this->data['gateway'] : false;
		?>
		<div class="uk-alert-primary uk-padding-small">
			<p>
				<?php
				echo sprintf(
					__( 'Mpesa requires the phone number field. To get started, please visit %1$shere%2$s and create your account. To go live, please visit %3$sthis url%4$s . Add the following URL as the callback handler %5$s', 'pesapress ' ),
					'<a href="https://developer.safaricom.co.ke/login-register" target="_blank">',
					'</a>',
					'<a href="https://developer.safaricom.co.ke/production_profile/form_production_profile" target="_blank">',
					'</a>',
					'<input class="uk-input uk-muted" type="text" value="' . $gateway->get_ipn_url() . '" readOnly="readOnly">'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Builds template and return it as string.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function settings() {
		$gateway = isset( $this->data['gateway'] ) ? $this->data['gateway'] : false;
		?>
		
		<div class="uk-margin">
			<label class="uk-form-label">
				<?php _e( 'Merchant Name', 'pesapress' ); ?>
			</label>
			<div class="uk-form-controls">
				<input class="uk-input" type="text" name="data[merchant_name]" value="<?php echo ( $gateway ) ? $gateway->setting_details['merchant_name'] : ''; ?>"/>
			</div>
			<span class="uk-text-meta"><?php _e( 'Your Mpesa Company name', 'pesapress' ); ?></span>
		</div>
		<div class="uk-margin">
			<label class="uk-form-label">
				<?php _e( 'Credentials Endpoint', 'pesapress' ); ?>
			</label>
			<div class="uk-form-controls">
				<input class="uk-input" type="text" name="data[credentials_endpoint]" value="<?php echo ( $gateway ) ? $gateway->setting_details['credentials_endpoint'] : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'; ?>"/>
			</div>
			<span class="uk-text-meta"><?php _e( 'Credentials Endpoint', 'pesapress' ); ?></span>
		</div>
		<div class="uk-margin">
			<label class="uk-form-label">
				<?php _e( 'Payments Endpoint', 'pesapress' ); ?>
			</label>
			<div class="uk-form-controls">
				<input class="uk-input" type="text" name="data[payments_endpoint]" value="<?php echo ( $gateway ) ? $gateway->setting_details['payments_endpoint'] : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest'; ?>"/>
			</div>
			<span class="uk-text-meta"><?php _e( 'Payments Endpoint', 'pesapress' ); ?></span>
		</div>
		<div class="uk-margin">
			<label class="uk-form-label">
				<?php _e( 'PassKey', 'pesapress' ); ?>
			</label>
			<div class="uk-form-controls">
				<input class="uk-input" type="text" name="data[passkey]" value="<?php echo ( $gateway ) ? $gateway->setting_details['passkey'] : ''; ?>"/>
			</div>
			<span class="uk-text-meta"><?php _e( 'Your Mpesa PassKey', 'pesapress' ); ?></span>
		</div>
		<div class="uk-margin">
			<label class="uk-form-label">
				<?php _e( 'Consumer Key', 'pesapress' ); ?>
			</label>
			<div class="uk-form-controls">
				<input class="uk-input" type="text" name="data[consumer_key]" value="<?php echo ( $gateway ) ? $gateway->setting_details['consumer_key'] : ''; ?>"/>
			</div>
			<span class="uk-text-meta"><?php _e( 'Your Mpesa Consumer Key', 'pesapress' ); ?></span>
		</div>
		<div class="uk-margin">
			<label class="uk-form-label">
				<?php _e( 'Consumer Secret', 'pesapress' ); ?>
			</label>
			<div class="uk-form-controls">
				<input class="uk-input" type="text" name="data[consumer_secret]" value="<?php echo ( $gateway ) ? $gateway->setting_details['consumer_secret'] : ''; ?>"/>
			</div>
			<span class="uk-text-meta"><?php _e( 'Your Mpesa Consumer Secret', 'pesapress' ); ?></span>
		</div>
		<div class="uk-margin">
			<label class="uk-form-label">
				<?php _e( 'Shortcode', 'pesapress' ); ?>
			</label>
			<div class="uk-form-controls">
				<input class="uk-input" type="text" name="data[shortcode]" value="<?php echo ( $gateway ) ? $gateway->setting_details['shortcode'] : ''; ?>"/>
			</div>
			<span class="uk-text-meta"><?php _e( 'Your Mpesa Shortcode', 'pesapress' ); ?></span>
		</div>
		<?php
	}
}

?>
