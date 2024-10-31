<?php
if ( ! isset( $selected ) ) {
	$selected = '';
}
if ( ! isset( $class ) ) {
	$class = '';
}
if ( ! isset( $attributes ) ) {
	$attributes = array();
}
$data = array();
foreach ( $attributes as $k => $v ) {
	$data[] = "$k=$v";
}
$data = implode( ' ', $data );

$pages = get_pages();
?>

<select name="<?php echo $name; ?>" class="uk-select <?php echo $class; ?>" id="form-horizontal-select" <?php echo $data; ?>>
	<option value=""><?php _e( 'Select One', 'soko' ); ?></option>
	<?php
	foreach ( $pages as $value ) {
		?>
			<option value="<?php echo $value->ID; ?>" <?php selected( $selected, $value->ID ); ?>><?php echo $value->post_title; ?></option>
									  <?php
	}
	?>
</select>
	
