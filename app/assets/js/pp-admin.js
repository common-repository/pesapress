/*! PesaPress Free - v2.3
 * https://alloy.co.ke/projects/pesapress
 * Copyright (c) 2021; * Licensed GPLv2+ */
/*global UIkit:false */
/*global $:false */
/*global window:false */
/*global document:false */
/*global pesapress:false */

window.pesapress = window.pesapress || {};

pesapress.helper = {

	/**
	 * Notification
	 */
	notification : {
		show : function(message, type){
			jQuery('.pesapress-message').html('<a class="uk-alert-close" uk-close></a>'+message).addClass('uk-alert-' + type).removeClass('uk-invisible');
		},
		
		reset : function(){
			jQuery('.pesapress-message').html('').removeClass('uk-alert-success').removeClass('uk-alert-warning').removeClass('uk-alert-danger').addClass('uk-invisible');
		}
	},
	
	/**
	 * Progress Loader
	 */
	loader : function(container_class,elem){
		jQuery("<div class='"+container_class+"'><img src='"+pesapress.assets.spinner+"' class='pesapress-spinner-center'/></div>").css({
			position: "absolute",
			width: "100%",
			height: "100%",
			top: 0,
			left: 0,
			background: "#ecebea",
			textAlign : 'center'
		}).appendTo(elem.css("position", "relative"));
	},

	/**
	 * UI slider
	 */
	slider : function(elem) {
		var target = jQuery(elem).attr('data-id'),
			max = jQuery(elem).attr('data-max'),
			field = jQuery('input[name='+target+']');
		jQuery( elem ).slider({
			min: 0,
			max: max,
			value: field.val(),
			slide: function( event, ui ) {
				jQuery( "." + target ).html( ui.value );
				field.val( ui.value );
			}
		});
	}
};
/*global UIkit:false */
/*global $:false */
/*global window:false */
/*global document:false */
/*global pesapress:false */

jQuery(function($) {

	$('body').on('click', '.pesapress-modal', function(e){
		var $elem = $(this),
			$nonce = $elem.attr('data-nonce'),
			$action = $elem.attr('data-action'),
			$label = $elem.attr('title'),
			$modal = $('.object-details'),
			$modal_title = $('.object-details .uk-modal-title'),
			$modal_content = $('.object-details .uk-modal-body');
			pesapress.helper.notification.reset();
		$elem.html('<img src="'+pesapress.assets.spinner+'"/>');
		$.post(
			window.ajaxurl,
			{ '_wpnonce' : $nonce, 'action' : $action }
		).done( function( response ) {
			$elem.html($label);
			if ( response.success === true ) {
				$modal_title.html(response.data.title);
				$modal_content.html(response.data.html);
				UIkit.modal($modal).show();
			} else {
				pesapress.helper.notification.show(response.data, 'warning');
			}
		}).fail(function(xhr, status, error) {
			$elem.html($label);
			pesapress.helper.notification.show(pesapress.loading.error, 'danger');
		});
	});

	$('body').on('click', '.pesapress-edit-modal', function(e){
		var $elem = $(this),
			$nonce = $elem.attr('data-nonce'),
			$action = $elem.attr('data-action'),
			$label = $elem.attr('title'),
			$id = $elem.attr('data-id'),
			$modal = $('.object-details'),
			$modal_title = $('.object-details .uk-modal-title'),
			$modal_content = $('.object-details .uk-modal-body'),
			$loader_class = 'pesapress-setup-loader',
			$form = $('.pesapress-gateway-list');
		pesapress.helper.loader($loader_class,$form);
		$.post(
			window.ajaxurl,
			{ '_wpnonce' : $nonce, 'action' : $action, 'id' : $id }
		).done( function( response ) {
			$('.'+$loader_class).remove();
			if ( response.success === true ) {
				$modal_title.html(response.data.title);
				$modal_content.html(response.data.html);
				UIkit.modal($modal).show();
			} else {
				pesapress.helper.notification.show(response.data, 'warning');
			}
		}).fail(function(xhr, status, error) {
			$('.'+$loader_class).remove();
			pesapress.helper.notification.show(pesapress.loading.error, 'danger');
		});
	});

	$('body').on('click', '.pesapress-view-modal', function(e){
		var $elem = $(this),
			$nonce = $elem.attr('data-nonce'),
			$action = $elem.attr('data-action'),
			$label = $elem.attr('title'),
			$id = $elem.attr('data-id'),
			$modal = $('.object-details'),
			$modal_title = $('.object-details .uk-modal-title'),
			$modal_content = $('.object-details .uk-modal-body'),
			$loader_class = 'pesapress-setup-loader',
			$form = $('.pesapress-logs-list');
		pesapress.helper.loader($loader_class,$form);
		$.post(
			window.ajaxurl,
			{ '_wpnonce' : $nonce, 'action' : $action, 'id' : $id }
		).done( function( response ) {
			$('.'+$loader_class).remove();
			if ( response.success === true ) {
				$modal_title.html(response.data.title);
				$modal_content.html(response.data.html);
				UIkit.modal($modal).show();
			} else {
				pesapress.helper.notification.show(response.data, 'warning');
			}
		}).fail(function(xhr, status, error) {
			$('.'+$loader_class).remove();
			pesapress.helper.notification.show(pesapress.loading.error, 'danger');
		});
	});
});
/*global UIkit:false */
/*global $:false */
/*global window:false */
/*global document:false */
/*global pesapress:false */

jQuery(function($) {

	$('body').on('change', '.pesapress-load-setup-select', function(e){
		var $elem = $(this),
			$val = $elem.val(),
			$nonce = $elem.attr('data-nonce'),
			$action = $elem.attr('data-action'),
			$target = $('.pesapress-setup-form-details'),
			$loader_class = 'pesapress-setup-loader',
			$form = $('.create-gateway-form');
		if ( $val !== '0' ) {
			pesapress.helper.loader($loader_class,$form);
			$.post(
				window.ajaxurl,
				{ '_wpnonce' : $nonce, 'action' : $action, 'gateway' : $val }
			).done( function( response ) {
				$('.'+$loader_class).remove();
				if ( response.success === true ) {
					$target.html(response.data);
				} else {
					$target.html(pesapress.loading.error);
				}
			}).fail(function(xhr, status, error) {
				$('.'+$loader_class).remove();
				$target.html(pesapress.loading.error);
			});
		} else {
			$target.html('');
		}
	});

	$('body').on('submit', '.create-gateway-form', function(e){
		var $form = $(this),
			$button = $form.find('button'),
			$loader_class = 'pesapress-setup-loader';
		pesapress.helper.loader($loader_class,$form);
		$button.attr('disabled', 'disabled');
		$.post(
			window.ajaxurl,
			$form.serialize()
		).done( function( response ) {
			$button.removeAttr('disabled');
			$('.'+$loader_class).remove();
			if ( response.success === true ) {
				pesapress.helper.notification.show(response.data.message, 'success');
				window.setTimeout(function () {
					window.location.href = response.data.url;
				}, 1000);
			} else {
				pesapress.helper.notification.show(response.data, 'warning');
			}
		}).fail(function(xhr, status, error) {
			$('.'+$loader_class).remove();
			$button.removeAttr('disabled');
			pesapress.helper.notification.show(pesapress.error, 'danger');
		});
		return false;
	});


	$('body').on('click', '.clone-gateway', function(e){
		var $elem = $(this),
			$label = $elem.attr('title'),
			$nonce = $elem.attr('data-nonce'),
			$action = $elem.attr('data-action'),
			$id = $elem.attr('data-id'),
			$loader_class = 'pesapress-setup-loader',
			$form = $('.pesapress-gateway-list');
		pesapress.helper.loader($loader_class,$form);
		$.post(
			window.ajaxurl,
			{ '_wpnonce' : $nonce, 'action' : $action, 'id' : $id }
		).done( function( response ) {
			$('.'+$loader_class).remove();
			if ( response.success === true ) {
				window.setTimeout(function () {
					window.location.href = response.data.url;
				}, 100);
			}
		}).fail(function(xhr, status, error) {
			$('.'+$loader_class).remove();
		});
	});

	$('body').on('click', '.delete-gateway', function(e){
		var $elem = $(this),
			$label = $elem.attr('title'),
			$nonce = $elem.attr('data-nonce'),
			$action = $elem.attr('data-action'),
			$id = $elem.attr('data-id'),
			$loader_class = 'pesapress-setup-loader',
			$form = $('.pesapress-gateway-list');
		pesapress.helper.loader($loader_class,$form);
		$.post(
			window.ajaxurl,
			{ '_wpnonce' : $nonce, 'action' : $action, 'id' : $id }
		).done( function( response ) {
			$('.'+$loader_class).remove();
			if ( response.success === true ) {
				window.setTimeout(function () {
					window.location.href = response.data.url;
				}, 100);
			}
		}).fail(function(xhr, status, error) {
			$('.'+$loader_class).remove();
		});
	});

	$('body').on('click', '.pesapress-select-all', function(e){
		$( '.pesapress-gateway-list tbody input[type="checkbox"]' ).prop('checked', this.checked);
		var ids = $( ".pesapress-gateway-list tbody input:checked" ).map( function() { if ( parseFloat( this.value ) ) { return this.value; } } ).get().join( ',' );
		
		$( 'form[name="bulk-action-form"] input[name="ids"]' ).val( ids );
	});

	$('body').on('click', '.pesapress-single-check', function(e){
		var ids = $( ".pesapress-gateway-list tbody input:checked" ).map( function() { if ( parseFloat( this.value ) ) { return this.value; } } ).get().join( ',' );
		
		$( 'form[name="bulk-action-form"] input[name="ids"]' ).val( ids );
	});
 
	$('body').on('submit', '.bulk-action-form', function(e){
		var $form = $(this),
			$button = $form.find('button'),
			$loader_class = 'pesapress-setup-loader';
		pesapress.helper.loader($loader_class,$('.pesapress-gateway-list'));
		$button.attr('disabled', 'disabled');
		$.post(
			window.ajaxurl,
			$form.serialize()
		).done( function( response ) {
			$button.removeAttr('disabled');
			$('.'+$loader_class).remove();
			if ( response.success === true ) {
				window.location.href = response.data.url;
			} else {
				pesapress.helper.notification.show(response.data, 'warning');
			}
		}).fail(function(xhr, status, error) {
			$('.'+$loader_class).remove();
			$button.removeAttr('disabled');
			pesapress.helper.notification.show(pesapress.error, 'danger');
		});
		return false;
	});
});
/*global UIkit:false */
/*global $:false */
/*global window:false */
/*global document:false */
/*global pesapress:false */

jQuery(function($) {
	$('body').on('click', '.pesapress-log-select-all', function(e){
		$( '.pesapress-logs-list tbody input[type="checkbox"]' ).prop('checked', this.checked);
		var ids = $( ".pesapress-logs-list tbody input:checked" ).map( function() { if ( parseFloat( this.value ) ) { return this.value; } } ).get().join( ',' );
		
		$( 'form[name="bulk-logs-form"] input[name="ids"]' ).val( ids );
		$( 'form[name="export-logs-form"] input[name="ids"]' ).val( ids );
	});

	$('body').on('click', '.pesapress-log-single-check', function(e){
		var ids = $( ".pesapress-logs-list tbody input:checked" ).map( function() { if ( parseFloat( this.value ) ) { return this.value; } } ).get().join( ',' );
		
		$( 'form[name="bulk-logs-form"] input[name="ids"]' ).val( ids );
		$( 'form[name="export-logs-form"] input[name="ids"]' ).val( ids );
	});

	$('body').on('click', '.delete-log', function(e){
		var $elem = $(this),
			$label = $elem.attr('title'),
			$nonce = $elem.attr('data-nonce'),
			$action = $elem.attr('data-action'),
			$id = $elem.attr('data-id'),
			$loader_class = 'pesapress-setup-loader',
			$form = $('.pesapress-logs-list');
		pesapress.helper.loader($loader_class,$form);
		$.post(
			window.ajaxurl,
			{ '_wpnonce' : $nonce, 'action' : $action, 'id' : $id }
		).done( function( response ) {
			$('.'+$loader_class).remove();
			if ( response.success === true ) {
				window.setTimeout(function () {
					window.location.href = response.data.url;
				}, 100);
			}
		}).fail(function(xhr, status, error) {
			$('.'+$loader_class).remove();
		});
	});

	$('body').on('submit', '.bulk-logs-form, .filter-logs-form', function(e){
		var $form = $(this),
			$button = $form.find('button'),
			$loader_class = 'pesapress-setup-loader';
		pesapress.helper.loader($loader_class,$('.pesapress-logs-list'));
		$button.attr('disabled', 'disabled');
		$.post(
			window.ajaxurl,
			$form.serialize()
		).done( function( response ) {
			$button.removeAttr('disabled');
			$('.'+$loader_class).remove();
			if ( response.success === true ) {
				window.location.href = response.data.url;
			} else {
				pesapress.helper.notification.show(response.data, 'warning');
			}
		}).fail(function(xhr, status, error) {
			$('.'+$loader_class).remove();
			$button.removeAttr('disabled');
			pesapress.helper.notification.show(pesapress.error, 'danger');
		});
		return false;
	});


	$('body').on('click', '.pesapress-manage-log', function(e){
		var $button = $(this),
			$id = $button.attr('data-id'),
			$nonce = $button.attr('data-nonce'),
			$status = $('.log_status').val(),
			$loader_class = 'pesapress-setup-loader';
		pesapress.helper.loader($loader_class,$('.pesapress-view-log'));
		$button.attr('disabled', 'disabled');
		$.post(
			window.ajaxurl,
			{ '_wpnonce' : $nonce, 'action' : 'pesapress_update_log', 'id' : $id, 'status' : $status }
		).done( function( response ) {
			$button.removeAttr('disabled');
			$('.'+$loader_class).remove();
			if ( response.success === true ) {
				$('td#'+$id).html($status);
			} else {
				pesapress.helper.notification.show(response.data, 'warning');
			}
		}).fail(function(xhr, status, error) {
			$('.'+$loader_class).remove();
			$button.removeAttr('disabled');
			pesapress.helper.notification.show(pesapress.error, 'danger');
		});
	});
});
/*global UIkit:false */
/*global $:false */
/*global window:false */
/*global document:false */
/*global pesapress:false */

jQuery(function($) {

	$('body').on('submit', '.pesapress-general-setting-form', function(e){
		var $form = $(this),
			$button = $form.find('button'),
			$loader_class = 'settings-form-loader';
		pesapress.helper.loader($loader_class,$form);
		$button.attr('disabled', 'disabled');
		pesapress.helper.notification.reset();
		$.post(
			window.ajaxurl,
			$form.serialize()
		).done( function( response ) {
			$button.removeAttr('disabled');
			$('.'+$loader_class).remove();
			if ( response.success === true ) {
				pesapress.helper.notification.show(response.data, 'success');
			} else {
				pesapress.helper.notification.show(response.data, 'warning');
			}
		}).fail(function(xhr, status, error) {
			$button.removeAttr('disabled');
			$('.'+$loader_class).remove();
			pesapress.helper.notification.show(pesapress.error, 'danger');
		});
		return false;
	});


	$('body').on('submit', '.pesapress-field-setting-form', function(e){
		var $form = $(this),
			$button = $form.find('button'),
			$loader_class = 'settings-form-loader';
		pesapress.helper.loader($loader_class,$form);
		$button.attr('disabled', 'disabled');
		pesapress.helper.notification.reset();
		$.post(
			window.ajaxurl,
			$form.serialize()
		).done( function( response ) {
			$button.removeAttr('disabled');
			$('.'+$loader_class).remove();
			if ( response.success === true ) {
				pesapress.helper.notification.show(response.data, 'success');
			} else {
				pesapress.helper.notification.show(response.data, 'warning');
			}
		}).fail(function(xhr, status, error) {
			$button.removeAttr('disabled');
			$('.'+$loader_class).remove();
			pesapress.helper.notification.show(pesapress.error, 'danger');
		});
		return false;
	});
});