<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Views_Admin_Settings_Integration extends PP_Core_View {

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
		<article class="uk-article">
			<h1 class="uk-article-title"><?php _e( 'Payment Form Shortcode', 'pesapress' ); ?></h1>
			<p class="uk-text-lead uk-text-muted">[pesapress_form]</p>
			<p><?php _e( 'Use this shortcode to display the payment form on the page. Attributes include ', 'pesapress' ); ?>:</p>
			<ul>
				<li><span class="uk-text-bold">button_name</span> - <span class="uk-text-muted"><?php _e( 'the name of the button. Defaults to "Complete Payment"', 'pesapress' ); ?></span></li>
				<li><span class="uk-text-bold">total_label</span> - <span class="uk-text-muted"><?php _e( 'the text used as total. Defaults to "Total"', 'pesapress' ); ?></span></li>
				<li><span class="uk-text-bold">amount</span> - <span class="uk-text-muted"><?php _e( 'the total amount to be paid. Defaults to "1.0"', 'pesapress' ); ?></span></li>
				<li><span class="uk-text-bold">gateway_id</span> - <span class="uk-text-muted"><?php _e( 'the payment gateway to use. Defaults to "0"', 'pesapress' ); ?></span></li>
				<li><span class="uk-text-bold">form_class</span> - <span class="uk-text-muted"><?php _e( 'additional classes to be used in the form. Defaults to "payment-form"', 'pesapress' ); ?></span></li>
				<li><span class="uk-text-bold">ajax_form</span> - <span class="uk-text-muted"><?php _e( 'submit the form using ajax or full page post. Defaults to "true"', 'pesapress' ); ?></span></li>
				<li><span class="uk-text-bold">show_amount</span> - <span class="uk-text-muted"><?php _e( 'shot the amount on the payment form. This allows a user to input any value to pay "false"', 'pesapress' ); ?></span></li>
			</ul>
			<p><?php _e( 'Example usage ', 'pesapress' ); ?> :</p>
			[pesapress_form button_name="Pay Me" amount="100" gateway_id="1"]
		</article>
		<?php
		do_action( 'pesapress_integration_instructions' );
		$content = ob_get_clean();
		return apply_filters( 'pesapress_view_admin_integration_settings', $content );
	}
}

?>
