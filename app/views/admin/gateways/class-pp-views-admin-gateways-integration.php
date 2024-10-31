<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Views_Admin_Gateways_Integration extends PP_Core_View {

	/**
	 * Gateway settings
	 * HTML representation of gateway settings
	 *
	 * @since 1.0.0
	 */
	protected function settings() {

	}

	/**
	 * Submit button
	 *
	 * @since 1.0.0
	 */
	protected function submit_button() {
		?>
		<div>
			<button class="uk-button uk-button-primary"><?php _e( 'Save', 'pesapress' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Update button
	 *
	 * @since 1.0.0
	 */
	protected function update_button() {
		?>
		<div>
			<button class="uk-button uk-button-primary"><?php _e( 'Update', 'pesapress' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Called before render
	 *
	 * @since  1.0.0
	 */
	public function before_render() {

	}

	/**
	 * Set the top instructions
	 *
	 * @since  1.0.0
	 */
	protected function top_instructions() {

	}

	/**
	 * Builds template and return it as string.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function to_html() {
		$gateway = isset( $this->data['gateway'] ) ? $this->data['gateway'] : false;
		ob_start();
		$this->top_instructions();
		?>
		<div class="uk-margin">
			<label class="uk-form-label">
				<?php _e( 'Nickname', 'pesapress' ); ?>
			</label>
			<div class="uk-form-controls">
				<input class="uk-input" type="text" name="data[nickname]" value="
				<?php
				if ( $gateway ) {
					if ( isset( $gateway->setting_details['nickname'] ) ) {
						echo $gateway->setting_details['nickname']; }
				}
				?>
				"/>
			</div>
			<span class="uk-text-meta"><?php _e( 'This is a nickname to allow you to identify your setting', 'pesapress' ); ?></span>
		</div>
		<div class="uk-margin">
			<label class="uk-form-label">
				<?php _e( 'Mode', 'pesapress' ); ?>
			</label>
			<div class="uk-form-controls">
				<?php
					$this->ui->render(
						'backend/dropdown',
						array(
							'name'     => 'mode',
							'selected' => ( $gateway ) ? $gateway->active_mode : '',
							'values'   => array(
								'sandbox' => __( 'Sandbox', 'pesapress' ),
								'live'    => __( 'Live', 'pesapress' ),
							),
						)
					);
				?>
			</div>
			<span class="uk-text-meta"><?php _e( 'Gateway Mode', 'pesapress' ); ?></span>
		</div>
		<?php
		$this->settings();
		if ( $gateway ) {
			$this->update_button();
		} else {
			$this->submit_button();
		}

		$content = ob_get_clean();
		return apply_filters( 'pesapress_views_admin_gateways_integration', $content );
	}
}

?>
