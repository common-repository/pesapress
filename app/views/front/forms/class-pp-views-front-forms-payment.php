<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Views_Front_Forms_Payment extends PP_Core_View {

	/**
	 * Builds template and return it as string.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	protected function to_html() {
		$button_name       = $this->data['button_name'];
		$total_field_label = $this->data['total_label'];
		$amount            = $this->data['amount'];
		$gateway_id        = $this->data['gateway_id'];
		$form_class        = $this->data['form_class'];
		$ajax_form         = $this->data['ajax_form'];
		$pesapress_forms   = PP_Model_Forms::instance();
		$fields            = $pesapress_forms->get_settings();
		$total_fields      = count( $fields ) + 1;
		$last_tab_index    = $total_fields + 1;
		$page_id           = $this->get_post_id();
		$return_url        = get_permalink( $page_id );

		if ( filter_var( $ajax_form, FILTER_VALIDATE_BOOLEAN ) ) {
			$form_class .= ' pesapress_ajax';
		}
		$form_class .= ' pesapress-payment-form pesapress-form';
		$form_class  = apply_filters( 'pesapress_payment_form_class', $form_class, $this );

		ob_start();
		?>
		<form action="" method="post" class="<?php echo esc_attr( $form_class ); ?>">
			<div class="pesapress-submit-response">
				<?php do_action( 'pesapress_payment_form_submit_response', $this ); ?>
			</div>
			<?php do_action( 'pesapress_payment_form_before', $this ); ?>
			<?php wp_nonce_field( 'pesapress_payment_form', 'pesapress_nonce' ); ?>

			<input type="hidden" name="action" value="pesapress_process_payment_form"/>
			<input type="hidden" name="amount" value="<?php echo $amount; ?>"/>
			<input type="hidden" name="page_id" value="<?php echo $page_id; ?>"/>
			<input type="hidden" name="return_url" value="<?php echo $return_url; ?>"/>
			<input type="hidden" name="gateway_id" value="<?php echo $gateway_id; ?>"/>
			<input type="text" style="display:none !important; visibility:hidden !important;" name="input_<?php echo $total_fields; ?>" value="">

			<?php echo $pesapress_forms->render( $total_field_label, $amount ); ?>

			<?php do_action( 'pesapress_payment_form_after', $this ); ?>

			<p>
				<input tabindex="<?php echo $last_tab_index; ?>" value="<?php echo $button_name; ?>" type="submit" />
			</p>
		</form>
		<?php
		$content = ob_get_clean();
		return apply_filters( 'pesapress_view_front_forms_payment', $content );
	}


	/**
	 * Return post ID
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_post_id() {
		return get_post() ? get_the_ID() : 0;
	}
}
?>
