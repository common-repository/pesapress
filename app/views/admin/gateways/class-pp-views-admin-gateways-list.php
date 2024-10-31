<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Views_Admin_Gateways_List extends PP_Core_View {

	protected function table_columns() {
		?>
		<tr>
			<th class="uk-table-shrink"><input class="uk-checkbox pesapress-select-all" type="checkbox"></th>
			<th class="uk-table-expand"><?php _e( 'ID', 'pesapress' ); ?></th>
			<th class="uk-table-expand"><?php _e( 'Nickname', 'pesapress' ); ?></th>
			<th class="uk-table-expand"><?php _e( 'Gateway', 'pesapress' ); ?></th>
			<th class="uk-width-small"><?php _e( 'Mode', 'pesapress' ); ?></th>
			<th class="uk-width-small"><?php _e( 'Status', 'pesapress' ); ?></th>
			<th class="uk-table-small uk-text-nowrap"><?php _e( 'Action', 'pesapress' ); ?></th>
		</tr>
		<?php
	}

	/**
	 * Builds template and return it as string.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function to_html() {
		$total    = $this->data['total'];
		$gateways = $this->data['list'];
		$per_page = $this->data['per_page'];
		ob_start();

		$this->ui->render(
			'backend/pagination',
			array(
				'per_page' => $per_page,
				'total'    => $total,
			)
		);
		if ( $total > 0 ) :
			?>
		<table class="uk-table uk-table-hover uk-table-middle uk-table-divider pesapress-table pesapress-gateway-list">
			<thead>
				<?php $this->table_columns(); ?>
			</thead>
			<tfoot>
				<?php $this->table_columns(); ?>
			</tfoot>
			<tbody>
				<?php
				foreach ( $gateways as $gateway ) {
					?>
					<tr>
						<td><input class="uk-checkbox pesapress-single-check" type="checkbox" value="<?php echo $gateway->setting_id; ?>" /></td>
						<td class="uk-text-truncate"><?php echo $gateway->setting_id; ?></td>
						<td class="uk-text-truncate"><?php echo $gateway->get_nickname(); ?></td>
						<td class="uk-text-truncate"><?php echo $gateway->setting_name; ?></td>
						<td class="uk-text-truncate"><?php echo $gateway->active_mode; ?></td>
						<td class="uk-text-truncate"><?php echo ( $gateway->is_enabled ) ? __( 'Enabled', 'pesapress' ) : __( 'Disabled', 'pesapress' ); ?></td>
						<td class="uk-text-nowrap pesapress-action">
							<div class="uk-inline">
								<button class="uk-button uk-button-default" type="button"><span uk-icon="more"></span></button>
								<div uk-dropdown="boundary: .pesapress-action">
									<ul class="uk-nav uk-dropdown-nav">
										<li>
											<?php
												$this->ui->render(
													'backend/link',
													array(
														'title'         => __( 'Edit', 'pesapress' ),
														'class'         => 'pesapress-edit-modal',
														'attributes'    => array(
															'data-nonce'    => wp_create_nonce( 'pesapress_edit_gateway' ),
															'data-action'   => 'pesapress_edit_gateway',
															'data-id'       => $gateway->setting_id,
														),
													)
												);
											?>
										</li>
										<li>
										<?php
											$this->ui->render(
												'backend/link',
												array(
													'title' => __( 'Copy', 'pesapress' ),
													'class' => 'clone-gateway',
													'attributes' => array(
														'data-nonce'    => wp_create_nonce( 'pesapress_gateway_clone' ),
														'data-action'   => 'pesapress_clone_gateway',
														'data-id'       => $gateway->setting_id,
													),
												)
											);
										?>
										</li>
										<li>
										<?php
											$this->ui->render(
												'backend/link',
												array(
													'title' => __( 'Delete', 'pesapress' ),
													'class' => 'delete-gateway',
													'attributes' => array(
														'data-nonce'    => wp_create_nonce( 'pesapress_gateway_delete' ),
														'data-action'   => 'pesapress_delete_gateway',
														'data-id'       => $gateway->setting_id,
													),
												)
											);
										?>
										</li>
									</ul>
								</div>
							</div>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
			<?php
		else :
			?>
			<h4 class="uk-padding uk-heading-line uk-text-center"><?php _e( 'No gateways saved', 'pesapress' ); ?></h4><?php
		endif;
		$this->ui->render(
			'backend/pagination',
			array(
				'per_page' => $per_page,
				'total'    => $total,
			)
		);
		$content = ob_get_clean();
		return apply_filters( 'pesapress_view_admin_settings_gateways', $content );
	}
}
?>
