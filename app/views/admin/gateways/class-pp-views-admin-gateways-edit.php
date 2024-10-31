<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Used for edit purposes
 */
class PP_Views_Admin_Gateways_Edit extends PP_Core_View {

	/**
	 * Builds template and return it as string.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function to_html() {
		$gateway = $this->data['gateway'];
		$form    = $this->data['form'];
		ob_start();
		?>
		<div class="wrap pesapress-input">
			<div class="pesapress-message"></div>
			<form class="uk-form-horizontal create-gateway-form">
				<?php
					wp_nonce_field( 'pesapress_gateway_update' );
					$this->ui->render(
						'backend/hidden',
						array(
							'name'  => 'action',
							'value' => 'pesapress_update_gateway',
						)
					);
					$this->ui->render(
						'backend/hidden',
						array(
							'name'  => 'id',
							'value' => $gateway->setting_id,
						)
					);

					$view       = new $form();
					$view->data = array(
						'gateway' => $gateway,
					);

					$view->before_render();
					?>
				<div class="uk-margin">
					<label class="uk-form-label">
						<?php _e( 'Gateway', 'pesapress' ); ?>
					</label>
					<div class="uk-form-controls">
						<?php echo ucfirst( $gateway->setting_name ); ?>
					</div>
				</div>
				<?php
					$view->render();
				?>
			</form>
		</div>
		<?php
		$content = ob_get_clean();
		return apply_filters( 'pesapress_views_admin_gateways_edit', $content );
	}
}
?>
