<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Views_Admin_Dash extends PP_Core_View {

	protected function header() {
		// Show banner when there is nothing and a graph when there are some transactions
		$header = array(
			'page_title' => apply_filters( 'pesapress_dashboard_page_title', __( 'PesaPress', 'pesapress' ) ),
		);
		return $this->ui->render( 'backend/header', $header );
	}

	/**
	 * Builds template and return it as string.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function to_html() {
		$logs  = $this->data['list'];
		$stats = $this->data['stats'];
		ob_start();
		?>
		<div class="wrap uk-padding-small">
			<div class="uk-section-small uk-section-default header">
				<div class="uk-container uk-container-large">
					<?php $this->header(); ?>
				</div>
			</div>
			<div class="uk-margin-top">
				<div class="uk-child-width-expand@s" uk-grid>
					<div>
						<div class="uk-card uk-card-default uk-card-body">
							<p class="uk-text-lead uk-text-center"><?php _e( 'Recent Transactions', 'pesapress' ); ?></p>
							<?php
							if ( count( $logs ) > 0 ) :
								?>
								<table class="uk-table uk-table-responsive uk-table-divider">
									<thead>
										<tr>
											<th><?php _e( 'ID', 'pesapress' ); ?></th>
											<th><?php _e( 'Status', 'pesapress' ); ?></th>
											<th><?php _e( 'Amount', 'pesapress' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ( $logs as $log ) : ?>
											<tr>
												<td><?php echo $log->log_id; ?></td>
												<td><?php echo $log->status; ?></td>
												<td><?php echo $log->get_amount_formatted(); ?></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
								<?php
							else :
								_e( 'No recent transactions found', 'pesapress' );
							endif;
							?>
							
						</div>
					</div>
					<div class="uk-width-1-3@m">
						<div class="uk-card uk-card-default uk-card-body">
							<p class="uk-text-lead uk-text-center">Statistics</p>
							<ul class="uk-list">
								<li>
									<div uk-grid>
										<div class="uk-text-bold uk-width-3-4">Total Revenue</div>
										<div class="uk-width-1-4 uk-padding-remove-left"><?php echo $stats->get_total_revenue(); ?></div>
									</div>
								</li>
								<li>
									<div uk-grid>
										<div class="uk-text-bold uk-width-3-4">Total Monthly Revenue</div>
										<div class="uk-width-1-4 uk-padding-remove-left"><?php echo $stats->get_months_revenue(); ?></div>
									</div>
								</li>
								<li>
									<div uk-grid>
										<div class="uk-text-bold uk-width-3-4">Total Weekly Revenue</div>
										<div class="uk-width-1-4 uk-padding-remove-left"><?php echo $stats->get_weeks_revenue(); ?></div>
									</div>
								</li>
								<li>
									<div uk-grid>
										<div class="uk-text-bold uk-width-3-4">Last Transaction</div>
										<div class="uk-width-1-4 uk-padding-remove-left"><?php echo $stats->get_last_transaction(); ?></div>
									</div>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		$content = ob_get_clean();
		return apply_filters( 'pesapress_view_admin_dash', $content );
	}
}
?>
