<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Views_Admin_Gateways_Integrations_Pesapal extends PP_Views_Admin_Gateways_Integration {


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
					__( 'PesaPal requires Full names and email/phone number. To handle IPN return requests, please copy and set the following url in your %1$sPesaPal Account Settings%2$s %3$s', 'pesapress ' ),
					'<a href="https://www.pesapal.com/merchantdashboard" target="_blank">',
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
				<?php _e( 'Customer Key', 'pesapress' ); ?>
			</label>
			<div class="uk-form-controls">
				<input class="uk-input" type="text" name="data[consumer_key]" value="<?php echo ( $gateway ) ? $gateway->setting_details['consumer_key'] : ''; ?>"/>
			</div>
			<span class="uk-text-meta"><?php _e( 'Your PesaPal Consumer Key', 'pesapress' ); ?></span>
		</div>
		<div class="uk-margin">
			<label class="uk-form-label">
				<?php _e( 'Customer Secret', 'pesapress' ); ?>
			</label>
			<div class="uk-form-controls">
				<input class="uk-input" type="text" name="data[consumer_secret]" value="<?php echo ( $gateway ) ? $gateway->setting_details['consumer_secret'] : ''; ?>"/>
			</div>
			<span class="uk-text-meta"><?php _e( 'Your PesaPal Consumer Secret', 'pesapress' ); ?></span>
		</div>
		<?php
	}
}

?>
