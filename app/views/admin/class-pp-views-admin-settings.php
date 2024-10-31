<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PP_Views_Admin_Settings extends PP_Core_View {


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
		<div class="wrap uk-padding-small">
			<h1><?php _e( 'Settings', 'pesapress' ); ?></h1>
			<div class="uk-padding-remove uk-container uk-background-default uk-height-viewport">
				<div class="uk-invisible pesapress-message uk-padding-remove uk-margin-remove" uk-alert></div>
				<ul class="uk-margin-small-top" uk-tab="animation: uk-animation-fade">
					<li class="uk-margin-remove-bottom"><a href="#"><?php _e( 'General', 'pesapress' ); ?></a></li>
					<li class="uk-margin-remove-bottom"><a href="#"><?php _e( 'Form Fields', 'pesapress' ); ?></a></li>
					<li class="uk-margin-remove-bottom"><a href="#"><?php _e( 'Integration', 'pesapress' ); ?></a></li>
				</ul>

				<ul class="uk-switcher uk-margin uk-padding-small">
					<li>
					<?php
						$view       = new PP_Views_Admin_Settings_General();
						$view->data = $this->data;
						$view->render();
					?>
					</li>
					<li>
					<?php
						$view       = new PP_Views_Admin_Settings_Fields();
						$view->data = $this->data;
						$view->render();
					?>
					</li>
					<li>
					<?php
						$view       = new PP_Views_Admin_Settings_Integration();
						$view->data = $this->data;
						$view->render();
					?>
					</li>
				</ul>
			</div>
		</div>
		<?php
		$content = ob_get_clean();
		return apply_filters( 'pesapress_view_admin_settings', $content );
	}
}
?>
