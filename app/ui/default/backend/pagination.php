<?php
$pagenum     = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
$per_page    = isset( $per_page ) ? $per_page : 0;
$total       = isset( $total ) ? $total : 0;
$page_number = max( 1, $pagenum );
if ( $total > $per_page ) {
	$removable_query_args = wp_removable_query_args();

	$current_url   = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url   = remove_query_arg( $removable_query_args, $current_url );
	$current       = $page_number + 1;
	$total_pages   = ceil( $total / $per_page );
	$disable_first = $disable_last = $disable_prev = $disable_next = false;
	$mid_size      = 2;
	$end_size      = 1;

	if ( $page_number == 1 ) {
		$disable_first = true;
		$disable_prev  = true;
	}

	if ( $page_number == 2 ) {
		$disable_first = true;
	}

	if ( $page_number == $total_pages ) {
		$disable_last = true;
		$disable_next = true;
	}

	if ( $page_number == $total_pages - 1 ) {
		$disable_last = true;
	}
	?>
	<ul class="uk-pagination" uk-margin>

		<?php
		if ( ! $disable_first ) :

			$prev_url  = esc_url( add_query_arg( 'paged', min( $total_pages, $page_number - 1 ), $current_url ) );
			$first_url = esc_url( add_query_arg( 'paged', min( 1, $total_pages ), $current_url ) );
			?>

			<li>
				<a href="<?php echo $first_url; ?>">
					<span uk-pagination-previous></span><span uk-pagination-previous></span> <?php _e( 'Previous page', 'pesapress' ); ?>
				</a>
			</li>
			<li>
				<a href="<?php echo $prev_url; ?>">
					<span uk-pagination-previous></span> <?php _e( 'Previous page', 'pesapress' ); ?>
				</a>
			</li>
			<?php
		endif;

		$dots = false;
		for ( $i = 1; $i <= $total_pages; $i ++ ) :
			$class = ( $page_number == $i ) ? 'uk-active' : '';
			$url   = esc_url( add_query_arg( 'paged', ( $i ), $current_url ) );
			if ( ( $i <= $end_size || ( $current && $i >= $current - $mid_size && $i <= $current + $mid_size ) || $i > $total_pages - $end_size ) ) {
				?>
				<li class="<?php echo $class; ?>"><a href="<?php echo $url; ?>"><?php echo( $i ); ?></a></li>
				<?php
				$dots = true;
			} elseif ( $dots ) {
				?>
				<li class="uk-disabled"><span><?php _e( '&hellip;' ); ?></span></li>
				<?php
				$dots = false;
			}

			?>

		<?php endfor; ?>

		<?php
		if ( ! $disable_last ) :

			$next_url = esc_url( add_query_arg( 'paged', min( $total_pages, $page_number + 1 ), $current_url ) );
			$last_url = esc_url( add_query_arg( 'paged', max( $total_pages, $page_number - 1 ), $current_url ) );
			?>

			<li>
				<a href="<?php echo $next_url; ?>">
					<span uk-pagination-next></span><?php _e( 'Next page', 'pesapress' ); ?>
				</a>
			</li>
			<li>
				<a href="<?php echo $last_url; ?>">
					<span uk-pagination-next></span><span uk-pagination-next></span><?php _e( 'Next page', 'pesapress' ); ?>
				</a>
			</li>
		<?php endif; ?>
	</ul>
	<?php
} ?>
