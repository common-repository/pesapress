<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Used for create purposes
 */
class PP_Views_Admin_Gateways_Create extends PP_Core_View {

	/**
	 * Builds template and return it as string.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function to_html() {
		$integrations = $this->data['gateways'];
		array_unshift( $integrations, 'Select Gateway' );
		ob_start();
		?>
		<div class="wrap pesapress-input">
			<div class="pesapress-message"></div>
			<form class="uk-form-horizontal create-gateway-form">
				<?php
					wp_nonce_field( 'pesapress_gateway_save' );
					$this->ui->render(
						'backend/hidden',
						array(
							'name'  => 'action',
							'value' => 'pesapress_save_gateway',
						)
					);
				?>
				<div class="uk-margin">
					<label class="uk-form-label">
						<?php _e( 'Gateway', 'pesapress' ); ?>
					</label>
					<div class="uk-form-controls">
						<?php
							$this->ui->render(
								'backend/dropdown',
								array(
									'name'       => 'gateway',
									'values'     => $integrations,
									'class'      => 'pesapress-load-setup-select',
									'attributes' => array(
										'data-nonce'  => wp_create_nonce( 'pesapress_load_setup_form' ),
										'data-action' => 'pesapress_load_setup_form',
									),
								)
							);
						?>
					</div>
					<span class="uk-text-meta"><?php _e( 'Select a payment gateway first to setup integrations', 'pesapress' ); ?></span>
				</div>
				<div class="pesapress-setup-form-details"></div>
			</form>
		</div>
		<?php
		$content = ob_get_clean();
		return apply_filters( 'pesapress_views_admin_gateways_create', $content );
	}
}
?>
