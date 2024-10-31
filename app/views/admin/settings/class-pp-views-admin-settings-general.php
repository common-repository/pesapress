<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Views_Admin_Settings_General extends PP_Core_View {


	/**
	 * Builds template and return it as string.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function to_html() {
		$currency_helper = PP_Core_Currency::instance();
		$currencies      = $currency_helper->get_currencies();
		$settings        = $this->data['settings'];
		ob_start();
		?>
		<form name="pesapress-general-setting" class="uk-form-vertical pesapress-general-setting-form" method="POST">
			<?php
				wp_nonce_field( 'pesapress_settings_save' );
				$this->ui->render(
					'backend/hidden',
					array(
						'name'  => 'action',
						'value' => 'pesapress_save_settings',
					)
				);
			?>
			<div class="uk-margin">
				<label class="uk-form-label" for="form-vertical-text">
					<?php _e( 'Thank You Page', 'pesapress' ); ?>
				</label>
				<div class="uk-form-controls">
					<?php
					$this->ui->render(
						'backend/page',
						array(
							'name'     => 'success_page',
							'selected' => $settings->get_checkout_setting( 'success_page' ),
						)
					);
					?>
				</div>
				<span class="uk-text-meta"><?php _e( 'The success page. This is shown after the transaction is complete with the status', 'pesapress' ); ?></span>
			</div>
			<div class="uk-margin">
				<label class="uk-form-label" for="form-horizontal-text">
					<?php _e( 'Checkout Currency', 'pesapress' ); ?>
				</label>
				<div class="uk-form-controls">
					<?php
					$currency = $settings->get_currency_setting( 'currency', 'USD' );
					$this->ui->render(
						'backend/currency',
						array(
							'name'     => 'currency',
							'selected' => $currency,
							'values'   => $currencies,
						)
					);
					?>
				</div>
				<span class="uk-text-meta"><?php _e( 'Checkout currency used in the gateways', 'pesapress' ); ?></span>
			</div>
			<div class="uk-margin">
				<label class="uk-form-label" for="form-horizontal-text">
					<?php _e( 'Currency position', 'pesapress' ); ?>
				</label>
				<div class="uk-form-controls">
					<?php
						$position = $settings->get_currency_setting( 'currency_position', 1 );
					?>
					<div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
						<label><input class="uk-radio" type="radio" name="currency_position" value="1" <?php checked( $position, 1 ); ?> ><?php echo $currency_helper->format_currency( $currency ); ?>100</label>
						<label><input class="uk-radio" type="radio" name="currency_position" value="2" <?php checked( $position, 2 ); ?> ><?php echo $currency_helper->format_currency( $currency ); ?> 100</label>
						<label><input class="uk-radio" type="radio" name="currency_position" value="3" <?php checked( $position, 3 ); ?> >100<?php echo $currency_helper->format_currency( $currency ); ?></label>
						<label><input class="uk-radio" type="radio" name="currency_position" value="4" <?php checked( $position, 4 ); ?> >100 <?php echo $currency_helper->format_currency( $currency ); ?></label>
					</div>
				</div>
				<span class="uk-text-meta"><?php _e( 'The currency position', 'pesapress' ); ?></span>
			</div>
			<div class="uk-margin">
				<label class="uk-form-label" for="form-horizontal-text">
					<?php _e( 'Show decimal', 'pesapress' ); ?>
				</label>
				<div class="uk-form-controls">
					<?php
						$decimal = $settings->get_currency_setting( 'currency_decimal', 1 );
					?>
					<div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
						<label><input class="uk-radio" type="radio" name="currency_decimal" value="0" <?php checked( $decimal, 0 ); ?> ><?php _e( 'No', 'pesapress' ); ?></label>
						<label><input class="uk-radio" type="radio" name="currency_decimal" value="1" <?php checked( $decimal, 1 ); ?> ><?php _e( 'Yes', 'pesapress' ); ?></label>
					</div>
				</div>
				<span class="uk-text-meta"><?php _e( 'Show or hide a decimal in the price shown', 'soko' ); ?></span>
			</div>
			<div class="uk-margin uk-margin-remove-bottom">
				<button type="submit" class="uk-button uk-button-primary">
					<?php _e( 'Update Settings', 'pesapress' ); ?>
				</button>
			</div>
		</form>
		<?php
		$content = ob_get_clean();
		return apply_filters( 'pesapress_view_admin_general_settings', $content );
	}
}

?>
