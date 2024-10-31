<?php
if ( ! isset( $class ) ) {
	$class = '';
} else {
	$class = 'class="' . $class . '"';
}
if ( ! isset( $href ) ) {
	$href = '';
} else {
	$href = 'href="' . $href . '"';
}
if ( ! isset( $target ) ) {
	$target = '';
} else {
	$target = 'target="' . $target . '"';
}
if ( ! isset( $rel ) ) {
	$rel = '';
} else {
	$rel = 'rel="' . $rel . '"';
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
<a <?php echo $class; ?> <?php echo $href; ?> <?php echo $target; ?> <?php echo $rel; ?> <?php echo $data; ?> title="<?php echo $title; ?>"><?php echo $title; ?></a>
