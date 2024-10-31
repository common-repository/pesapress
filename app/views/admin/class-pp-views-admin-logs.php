<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Views_Admin_Logs extends PP_Core_View {

	protected function header() {
		?>
		<div class="uk-section-small header uk-padding-remove">
			<h1>
				<?php _e( 'Transaction Logs', 'pesapress' ); ?>
			</h1>
		</div>
		<div class="uk-margin-small-top uk-margin-small-bottom">
			<div uk-grid>
				<div class="uk-width-1-3@s">
					<form class="uk-grid-small bulk-logs-form" name="bulk-logs-form" uk-grid>
						<?php
							wp_nonce_field( 'pesapress_bulk_logs' );
							$this->ui->render(
								'backend/hidden',
								array(
									'name'  => 'action',
									'value' => 'pesapress_bulk_logs',
								)
							);
							$this->ui->render(
								'backend/hidden',
								array(
									'name'  => 'ids',
									'value' => '',
								)
							);
						?>
						<div class="uk-width-1-2@s">
							<?php
								$this->ui->render(
									'backend/dropdown',
									array(
										'name'   => 'bulk_action',
										'values' => array(
											''       => __( 'Bulk Actions', 'pesapress' ),
											'delete' => __( 'Delete', 'pesapress' ),
										),
									)
								);
							?>
						</div>
						<div class="uk-width-1-2@s">
							<button class="uk-button uk-button-secondary"><?php _e( 'Apply', 'pesapress' ); ?></button>
						</div>
					</form>
				</div>
				<div class="uk-width-1-5@s uk-padding-remove-left">
					<form class="export-logs-form" action="<?php wp_nonce_url( admin_url( 'admin.php?action=pesapress_bulk_logs' ), 'pesapress_export_logs' ); ?>" method="POST">
						<?php
							$this->ui->render(
								'backend/hidden',
								array(
									'name'  => 'ids',
									'value' => '',
								)
							);
						?>
						<button class="uk-button uk-button-secondary"><?php _e( 'Export', 'pesapress' ); ?></button>
					</form>
				</div>
				<div class="uk-width-expand@s uk-padding-remove-left">
					<form class="uk-grid-small filter-logs-form" name="filter-logs-form" uk-grid>
						<?php
							wp_nonce_field( 'pesapress_filter_logs' );
							$this->ui->render(
								'backend/hidden',
								array(
									'name'  => 'action',
									'value' => 'pesapress_filter_logs',
								)
							);
						?>
						<div class="uk-width-1-2@s">
							<?php
								$this->ui->render(
									'backend/dropdown',
									array(
										'name'   => 'bulk_action',
										'values' => $this->data['gateways'],
									)
								);
							?>
						</div>
						<div class="uk-width-1-2@s">
							<button class="uk-button uk-button-secondary"><?php _e( 'Filter by Gateway', 'pesapress' ); ?></button>
						</div>
					</form>
				</div>
			</div>
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
	protected function to_html() {
		ob_start();
		?>
		<div class="wrap uk-padding-small uk-height-viewport">
			<?php $this->header(); ?>
			<div class="uk-padding-remove uk-container uk-background-default">
				<?php
				$view       = new PP_Views_Admin_Logs_List();
				$view->data = $this->data;
				$view->render();
				?>
			</div>
		</div>
		<?php
		$this->ui->render(
			'backend/modal',
			array(
				'id'      => 'object-details',
				'title'   => __( 'Loading Data', 'pesapress' ),
				'content' => sprintf( __( 'Loading content %s', 'pesapress' ), '<div class="uk-position-center"><div uk-spinner></div></div>' ),
			)
		);
		$content = ob_get_clean();
		return apply_filters( 'pesapress_view_admin_gateway_list', $content );
	}
}
?>
