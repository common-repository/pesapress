<?php
if ( ! isset( $icon ) ) {
	$icon = 'cart';
}
if ( ! isset( $active ) ) {
	$active = false;
}
if ( ! isset( $header ) ) {
	$header = false;
}

if ( ! isset( $attributes ) ) {
	$attributes = array();
}
$data = array();
foreach ( $attributes as $k => $v ) {
	$data[] = "$k=$v";
}
$data = implode( ' ', $data );

if ( ! isset( $padding ) ) {
	$padding = true;
}

$active_class = $active ? 'uk-card-primary' : 'uk-card-default';
$title_class  = '';
if ( ! $padding ) {
	$active_class .= ' uk-padding-remove';
	$title_class   = 'uk-padding uk-padding-remove-bottom';
}

?>
<div>
	<div class="uk-card <?php echo $active_class; ?> uk-card-body uk-card-hover" <?php echo $data; ?> >
		<h3 class="uk-card-title <?php echo $title_class; ?>">
			<span uk-icon="icon: <?php echo $icon; ?>; ratio: 1.5"></span> <?php echo esc_attr( $title ); ?>
			<div class="uk-position-top-right uk-padding-small uk-padding-remove-top">
				<?php echo $header; ?>
			</div>
		</h3>
		<div class="uk-card-body">
			<?php echo $body; ?>
		</div>
		<?php
		if ( isset( $footer ) ) {
			?>
				<div class="uk-card-footer">
				<?php echo $footer; ?>
				</div>
				<?php
		}
		?>
		
	</div>
</div>
