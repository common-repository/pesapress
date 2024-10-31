<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Used for view purposes
 */
class PP_Views_Admin_Logs_View extends PP_Core_View {

	/**
	 * Builds template and return it as string.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function to_html() {
		$log = $this->data['log'];
		ob_start();
		?>
		<div class="wrap pesapress-input pesapress-view-log">
			<div class="uk-form-horizontal">
				<div class="uk-margin">
					<label class="uk-form-label">
						<?php _e( 'Amount', 'pesapress' ); ?>
					</label>
					<div class="uk-form-controls">
						<?php echo $log->get_amount_formatted(); ?>
					</div>
				</div>
				<div class="uk-margin">
					<label class="uk-form-label">
						<?php _e( 'Gateway', 'pesapress' ); ?>
					</label>
					<div class="uk-form-controls">
						<?php echo $log->gateway_name(); ?>
					</div>
				</div>
				<div class="uk-margin">
					<label class="uk-form-label">
						<?php _e( 'Status', 'pesapress' ); ?>
					</label>
					<div class="uk-form-controls">
						<?php
							$this->ui->render(
								'backend/dropdown',
								array(
									'name'     => 'log_status',
									'selected' => $log->status,
									'values'   => PP_Model_Log::log_status(),
									'class'    => 'log_status',
								)
							);
						?>
						<button class="uk-button uk-button-secondary pesapress-manage-log" data-id="<?php echo $log->log_id; ?>" data-nonce="<?php echo wp_create_nonce( 'pesapress_manage_log_' . $log->log_id ); ?>"><?php _e( 'Update', 'pesapress' ); ?></button>
					</div>
				</div>
				<div class="uk-margin">
					<label class="uk-form-label">
						<?php _e( 'Details', 'pesapress' ); ?>
					</label>
					<div class="uk-form-controls">
						<ul class="uk-list">
							<li><span class="uk-text-bold"><?php _e( 'Firstname', 'pesapress' ); ?></span> : <?php echo $log->get_meta( 'firstname' ); ?></li>
							<li><span class="uk-text-bold"><?php _e( 'Lastname', 'pesapress' ); ?></span> : <?php echo $log->get_meta( 'lastname' ); ?></li>
							<li><span class="uk-text-bold"><?php _e( 'Email', 'pesapress' ); ?></span> : <?php echo $log->get_meta( 'email' ); ?></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php
		$content = ob_get_clean();
		return apply_filters( 'pesapress_views_admin_logs_view', $content );
	}
}

?>
