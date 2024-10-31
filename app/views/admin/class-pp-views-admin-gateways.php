<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Views_Admin_Gateways extends PP_Core_View {

	protected function header() {
		?>
		<div class="uk-section-small header uk-padding-remove">
			<h1>
				<?php _e( 'Gateways', 'pesapress' ); ?>
				<?php
					$this->ui->render(
						'backend/link',
						array(
							'title'      => __( 'Add Gateway', 'pesapress' ),
							'class'      => 'uk-button uk-button-primary uk-button-small pesapress-modal',
							'attributes' => array(
								'data-nonce'  => wp_create_nonce( 'pesapress_add_gateway' ),
								'data-action' => 'pesapress_add_gateway',
							),
						)
					);
				?>
			</h1>
		</div>
		<div class="uk-margin-small-top uk-margin-small-bottom">
			<form class="uk-grid-small bulk-action-form" name="bulk-action-form" uk-grid>
				<?php
				wp_nonce_field( 'pesapress_bulk_action' );
				$this->ui->render(
					'backend/hidden',
					array(
						'name'  => 'action',
						'value' => 'pesapress_bulk_gateway',
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
				<div class="uk-width-1-4@s">
					<?php
						$this->ui->render(
							'backend/dropdown',
							array(
								'name'   => 'bulk_action',
								'values' => array(
									''        => __( 'Bulk Actions', 'pesapress' ),
									'enable'  => __( 'Enable', 'pesapress' ),
									'disable' => __( 'Disable', 'pesapress' ),
									'delete'  => __( 'Delete', 'pesapress' ),
								),
							)
						);
					?>
				</div>
				<div class="uk-width-1-4@s">
					<button class="uk-button uk-button-secondary"><?php _e( 'Apply', 'pesapress' ); ?></button>
				</div>
			</form>
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
				$view       = new PP_Views_Admin_Gateways_List();
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
