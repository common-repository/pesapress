<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Views_Admin_Settings_Fields extends PP_Core_View {

	/**
	 * Builds template and return it as string.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function to_html() {
		$pesapress_forms = PP_Model_Forms::instance();
		$fields          = $pesapress_forms->get_settings();
		ob_start();
		?>
		<form name="pesapress-field-setting" class="uk-form-vertical pesapress-field-setting-form" method="POST">
			<?php
				wp_nonce_field( 'pesapress_fields_save' );
				$this->ui->render(
					'backend/hidden',
					array(
						'name'  => 'action',
						'value' => 'pesapress_save_fields',
					)
				);
			?>
			<p class="submit">
				<a class="uk-button uk-button-secondary uk-margin-right" onclick="add_checkout_element()"><?php _e( 'Add Field', 'pesapress' ); ?></a><input class='uk-button uk-button-primary' type='submit' value='<?php _e( 'Save Fields', 'pesapress' ); ?>'/><br/>
			</p>
			<table width="100%" border="0" class="widefat">
				<thead>
					<tr>
						<th width="1%" align="left" scope="col"></th>
						<th width="20%" align="left" scope="col"><?php _e( 'Name', 'pesapress' ); ?></th>
						<th width="10%" align="left" scope="col"><?php _e( 'Type', 'pesapress' ); ?></th>
						<th width="10%" align="left" scope="col"><?php _e( 'Unique Name', 'pesapress' ); ?></th>
						<th width="39%" align="left" scope="col"><?php _e( 'Place Holder', 'pesapress' ); ?></th>
						<th width="10%" align="left" scope="col"><?php _e( 'Mandatory', 'pesapress' ); ?></th>
						<th width="10%" align="left" scope="col"><?php _e( 'Visible', 'pesapress' ); ?></th>
						<th width="1%" align="left" scope="col"></th>
					</tr>
				</thead>

				<tfoot>
					<tr>
						<th align="left" scope="col"></th>
						<th align="left" scope="col"><?php _e( 'Name', 'pesapress' ); ?></th>
						<th align="left" scope="col"><?php _e( 'Type', 'pesapress' ); ?></th>
						<th align="left" scope="col"><?php _e( 'Unique Name', 'pesapress' ); ?></th>
						<th align="left" scope="col"><?php _e( 'Place Holder', 'pesapress' ); ?></th>
						<th align="left" scope="col"><?php _e( 'Mandatory', 'pesapress' ); ?></th>
						<th align="left" scope="col"><?php _e( 'Visible', 'pesapress' ); ?></th>
						<th align="left" scope="col"></th>
					</tr>
				</tfoot>
				<tbody class='sort-checkout ui-sortable'>
					<?php
						$count = 0;
					foreach ( $fields as $checkout_row ) {

						$mandatory = isset( $checkout_row['mandatory'] ) ? $checkout_row['mandatory'] : '';
						$visible   = isset( $checkout_row['visible'] ) ? $checkout_row['visible'] : '';
						$uname     = isset( $checkout_row['uname'] ) ? $checkout_row['uname'] : '';
						$initial   = isset( $checkout_row['initial'] ) ? $checkout_row['initial'] : '';
						$name      = isset( $checkout_row['name'] ) ? $checkout_row['name'] : '';
						$type      = isset( $checkout_row['type'] ) ? $checkout_row['type'] : '';
						?>
							<tr id="<?php echo $name; ?>">
								<td><span style="cursor:move" class="dashicons dashicons-sort"></span></td>
							<?php if ( ! isset( $checkout_row['delete'] ) ) { ?>
								<td><input type="text" name="pp[<?php echo $count; ?>][name]" value="<?php echo $name; ?>"/></td>
								<td>
									<?php
										$this->ui->render(
											'backend/dropdown',
											array(
												'name'     => 'pp[' . $count . '][type]',
												'values'   => $pesapress_forms->form_elements(),
												'selected' => $type,
											)
										);
									?>
								</td>
								<td><input type="text" name="pp[<?php echo $count; ?>][uname]" value="<?php echo $uname; ?>" /></td>
								<td><input type="text" name="pp[<?php echo $count; ?>][initial]" value="<?php echo $initial; ?>" style="width:100%"/></td>
								<?php } else { ?>
								<td><input type="hidden" name="pp[<?php echo $count; ?>][name]" value="<?php echo $name; ?>" /><?php _e( $name ); ?></td>
								<td><input type="hidden" name="pp[<?php echo $count; ?>][type]" value="<?php echo $type; ?>" /><?php echo ucfirst( $type ); ?></td>
								<td><input type="hidden" name="pp[<?php echo $count; ?>][uname]" value="<?php echo $uname; ?>" /><?php _e( $uname ); ?></td>
								<td><input type="hidden" name="pp[<?php echo $count; ?>][initial]" value="<?php echo $initial; ?>" /><?php _e( $initial ); ?></td>
								<?php } ?>
								<?php if ( ! isset( $checkout_row['delete'] ) ) { ?>
								<td><input type="checkbox" value="checked" name="pp[<?php echo $count; ?>][mandatory]" <?php checked( $mandatory, 'checked' ); ?> /></td>
								<td><input type="checkbox" value="checked" name="pp[<?php echo $count; ?>][visible]"  <?php checked( $visible, 'checked' ); ?> /></td>
								<?php } else { ?>
								<td><?php _e( 'Mandatory', 'pesapress' ); ?><input type="hidden" value="checked" name="pp[<?php echo $count; ?>][mandatory]" <?php checked( $mandatory, 'checked' ); ?> /></td>
								<td><?php _e( 'Visible', 'pesapress' ); ?><input type="hidden" value="checked" name="pp[<?php echo $count; ?>][visible]" <?php checked( $visible, 'checked' ); ?> /></td>
								<?php } ?>
								<?php if ( ! isset( $checkout_row['delete'] ) ) { ?>
									<td><span style="cursor:pointer" class="dashicons dashicons-no-alt" onclick="delete_checkout_element(this)"></span></td>
								<?php } else { ?>
									<td><input type="hidden" name="pp[<?php echo $count; ?>][delete]" value="false" /></td>
								<?php } ?>
							</tr>
							<?php
							$count++;
					}
					?>

				</tbody>
			</table>
			<p class="submit">
				<a class="uk-button uk-button-secondary uk-margin-right" onclick="add_checkout_element()"><?php _e( 'Add Field', 'pesapress' ); ?></a><input class='uk-button uk-button-primary' type='submit' value='<?php _e( 'Save Fields', 'pesapress' ); ?>'/><br/>
			</p>
		</form>
		<input type="hidden" id="pp_item_count" value="<?php echo $count; ?>" />
		<div style="display:none" class="pp_append_row">
			<script type="pp_checkout_row">
				<tr class="ui-sortable-handle">
					<td><span style="cursor:move" class="dashicons dashicons-sort"></span></td>
					<td><input type="text" name="pp[CURRENTCOUNT][name]" value=""/></td>
					<td>
						<?php
							$this->ui->render(
								'backend/dropdown',
								array(
									'name'   => 'pp[CURRENTCOUNT][type]',
									'values' => $pesapress_forms->form_elements(),
								)
							);
						?>
					</td>
					<td><input type="text" name="pp[CURRENTCOUNT][uname]" value="" /></td>
					<td><input type="text" name="pp[CURRENTCOUNT][initial]" value="" style="width:100%"/></td>
					<td><input type="checkbox" value="checked" name="pp[CURRENTCOUNT][manadatory]" /></td>
					<td><input type="checkbox" value="checked" name="pp[CURRENTCOUNT][visible]" /></td>
					<td><span style="cursor:pointer" class="dashicons dashicons-no-alt" onclick="delete_checkout_element(this)"></span></td>
				</tr>
			</script>
		</div>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				var idsInOrder = [];
				$("tbody.sort-checkout").sortable({
					update: function( event, ui ) {
						idsInOrder = [];
						$('tbody.sort-checkout tr').each(function() {
							idsInOrder.push($(this).attr('id'));
						});
						$('#sort_order').val(idsInOrder);
					}
				});
			});
			function add_checkout_element(){
				var cc_current = jQuery('#pp_item_count').val();
				var newRow = jQuery('div.pp_append_row script[type="pp_checkout_row"]').clone();
				newRow.attr('id',cc_current);
				newRow = newRow.html().replace(/CURRENTCOUNT/g,cc_current);
				jQuery('tbody.sort-checkout').append(newRow);
				cc_current++;
				jQuery('#pp_item_count').val(cc_current);
			}

			function delete_checkout_element(elem){
				jQuery(elem).parent().parent().remove();
				
			}
		</script>
		<?php
		$content = ob_get_clean();
		return apply_filters( 'pesapress_view_admin_settings_fields', $content );
	}
}

?>
