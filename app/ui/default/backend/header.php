<div class="uk-section-small header uk-padding-remove">
	<?php if ( isset( $page_title ) ) : ?>
		<h1>
			<?php echo esc_attr( $page_title ); ?>
			<?php if ( isset( $action ) ) : ?>
				<?php echo $action; ?>
			<?php endif; ?>
		</h1>
		<!-- <div class="uk-invisible pesapress-message uk-padding-remove uk-margin-remove" uk-alert></div> -->
	<?php endif; ?>
</div>
