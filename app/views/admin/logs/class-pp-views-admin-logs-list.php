<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Views_Admin_Logs_List extends PP_Core_View {

	protected function table_columns() {
		?>
		<tr>
			<th class="uk-table-shrink"><input class="uk-checkbox pesapress-log-select-all" type="checkbox"></th>
			<th class="uk-table-expand"><?php _e( 'ID', 'pesapress' ); ?></th>
			<th class="uk-table-expand"><?php _e( 'Status', 'pesapress' ); ?></th>
			<th class="uk-width-small"><?php _e( 'Amount', 'pesapress' ); ?></th>
			<th class="uk-width-small"><?php _e( 'Gateway', 'pesapress' ); ?></th>
			<th class="uk-width-small"><?php _e( 'Date Created', 'pesapress' ); ?></th>
			<th class="uk-width-small"><?php _e( 'Date Updated', 'pesapress' ); ?></th>
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
		$logs     = $this->data['list'];
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
			<table class="uk-table uk-table-hover uk-table-middle uk-table-divider pesapress-table pesapress-logs-list">
				<thead>
					<?php $this->table_columns(); ?>
				</thead>
				<tfoot>
					<?php $this->table_columns(); ?>
				</tfoot>
				<tbody>
					<?php
					foreach ( $logs as $log ) {
						?>
						<tr>
							<td><input class="uk-checkbox pesapress-log-single-check" type="checkbox" value="<?php echo $log->log_id; ?>" /></td>
							<td class="uk-text-truncate"><?php echo $log->log_id; ?></td>
							<td class="uk-text-truncate" id="<?php echo $log->log_id; ?>"><?php echo $log->status; ?></td>
							<td class="uk-text-truncate"><?php echo $log->get_amount_formatted(); ?></td>
							<td class="uk-text-truncate"><?php echo $log->gateway_name(); ?></td>
							<td class="uk-text-truncate"><?php echo $log->date_created; ?></td>
							<td class="uk-text-truncate"><?php echo $log->date_updated; ?></td>
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
															'title'         => __( 'View', 'pesapress' ),
															'class'         => 'pesapress-view-modal',
															'attributes'    => array(
																'data-nonce'    => wp_create_nonce( 'pesapress_view_log' ),
																'data-action'   => 'pesapress_view_log',
																'data-id'       => $log->log_id,
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
															'title'         => __( 'Delete', 'pesapress' ),
															'class'         => 'delete-log',
															'attributes'    => array(
																'data-nonce'    => wp_create_nonce( 'pesapress_log_delete' ),
																'data-action'   => 'pesapress_delete_log',
																'data-id'       => $log->log_id,
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
			<h2 class="uk-heading-line uk-text-center"><?php _e( 'No logs found', 'pesapress' ); ?></h2><?php
		endif;

		$this->ui->render(
			'backend/pagination',
			array(
				'per_page' => $per_page,
				'total'    => $total,
			)
		);
		$content = ob_get_clean();
		return apply_filters( 'pesapress_view_admin_settings_logs', $content );
	}
}

?>
