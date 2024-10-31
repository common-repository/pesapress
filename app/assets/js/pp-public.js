/*! PesaPress Free - v2.3
 * https://alloy.co.ke/projects/pesapress
 * Copyright (c) 2021; * Licensed GPLv2+ */
/*global $:false */
/*global window:false */
/*global document:false */
/*global ppfront:false */

jQuery(function($) {

	$('body').on('submit', '.pesapress-payment-form', function (e) {
		var $this = $(this),
			$label = $this.find('.pesapress-submit-response');
		if ($this.hasClass('pesapress_ajax')) {
			$label.html('');
			$label.html('<label class="forminator-label--info"><span>' + window.ppfront.processing + '</span></label>');
			e.preventDefault();
			$this.find('button').attr('disabled', true);
			$.post(
				ppfront.ajaxurl,
				$this.serialize()
			).done( function( data ) {
				$this.find('button').removeAttr('disabled');
				$label.html('');
				var $label_class = data.success ? 'success' : 'error';
				if (typeof data.message !== "undefined") {
					$label.html('<div class="pesapress-label--' + $label_class + '"><span>' + data.message + '</span></div>');

				} else {
					if (typeof data.data !== "undefined") {
						$label_class = data.data.success ? 'success' : 'error';
						$label.html('<div class="pesapress-label--' + $label_class + '"><span>' + data.data.message + '</span></div>');
					}
				}

				if (data.success === true) {
					if (typeof data.data.url !== "undefined") {
						window.location.href = data.data.url;
					}
				}
			}).fail(function(xhr, status, error) {
				$this.find('button').removeAttr('disabled');
				$label.html('');
				$label.html('<label class="pesapress-label--notice"><span>' + window.ppfront.error + '</span></label>');
			});
			return false;
		}
		return true;
	});
});