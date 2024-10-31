<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Views_Admin_Gateways_Integrations_Ppsingle extends PP_Views_Admin_Gateways_Integration {


	/**
	 * Set the top instructions
	 *
	 * @since  1.0.0
	 */
	public function before_render() {
		?>
		<div class="uk-margin">
			<p class="uk-text-meta">
				<?php _e( 'This the a basic PayPal gateway that allows users to easily make a single payment.', 'pesapress' ); ?>
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
		$gateway    = isset( $this->data['gateway'] ) ? $this->data['gateway'] : false;
		$currencies = PP_Core_Currency::instance()->get_paypal_currencies();
		array_unshift( $currencies, __( 'Select Currency', 'pesapress' ) );
		?>
		
		<div class="uk-margin">
			<label class="uk-form-label">
				<?php _e( 'PayPal Email', 'pesapress' ); ?>
			</label>
			<div class="uk-form-controls">
				<input class="uk-input" type="text" name="data[paypal_email]" value="<?php echo ( $gateway ) ? $gateway->setting_details['paypal_email'] : ''; ?>"/>
			</div>
			<span class="uk-text-meta"><?php _e( 'Your PayPal Email', 'pesapress' ); ?></span>
		</div>
		<div class="uk-margin">
			<label class="uk-form-label">
				<?php _e( 'PayPal Currency', 'pesapress' ); ?>
			</label>
			<div class="uk-form-controls">
				<?php
					$this->ui->render(
						'backend/dropdown',
						array(
							'name'     => 'data[paypal_currency]',
							'selected' => ( $gateway ) ? $gateway->setting_details['paypal_currency'] : '',
							'values'   => $currencies,
						)
					);
				?>
			</div>
			<span class="uk-text-meta"><?php _e( 'Your PayPal Currency. If the default site currency is different, we will convert the amount to the selected currency', 'pesapress' ); ?></span>
		</div>
		<?php
	}
}

?>
