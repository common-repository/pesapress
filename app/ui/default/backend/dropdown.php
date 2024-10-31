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
?>

<select name="<?php echo $name; ?>" class="uk-select <?php echo $class; ?>" id="form-horizontal-select" <?php echo $data; ?>>
	<?php
	if ( isset( $values ) ) {
		foreach ( $values as $key => $value ) {
			?>
				<option value="<?php echo $key; ?>" <?php selected( $selected, $key ); ?>><?php echo $value; ?></option>
										  <?php
		}
	}
	?>
</select>
	
