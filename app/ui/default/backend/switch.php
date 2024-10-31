<?php
if ( ! isset( $class ) ) {
	$class = '';
} else {
	$class = 'class="' . $class . '"';
}
if ( ! isset( $label_class ) ) {
	$label_class = '';
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
<section class="slider-checkbox">
	<input type="checkbox" <?php echo $class; ?> <?php echo $data; ?> name="<?php echo $name; ?>" value="<?php echo $value; ?>" <?php checked( $option, $value ); ?>/>
	<label class="label <?php echo $label_class; ?>" for="<?php echo $name; ?>"><?php echo $title; ?></label>
</section>
