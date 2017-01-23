<?php
function ppc__main_navigation() {
	global $wp_query;
	$menu_items = wp_get_nav_menu_items( 'main-pages-menu' );
	$queried_object_id = $wp_query->queried_object_id;
	?>
	<ul class="navigation-list">
	<?php
	if ( $menu_items ) {
		ppc__logo_nav_item();
		?>
		<div class="navigation-list__not-home">
			<?php
			foreach( $menu_items as $item ) {
				ppc__main_navigation_item( $item );
			}
			?>
		</div> <!-- navigation-list__not-home -->
		<?php
	}
	?>
	</ul>
<?php
}
function ppc__main_navigation_item( $nav_item ) {
	global $wp_query;

	$label = $nav_item->title;
	$url = $nav_item->url;
	if (
		empty( $label ) ||
		empty( $url ) ||
		$nav_item->menu_item_parent != '0'
	)
		return;

	$item_is_current = false;
	if ( $nav_item->object_id == $wp_query->queried_object_id ) {
		$item_is_current = true;
	}
	$item_is_home = false;

	if ( $item_is_current ) {
		$url = "#main";
	}
	$item_classes = ppc__get_nav_item_classes( $item_is_home, $item_is_current );
	$item_classes_string = implode( ' ', $item_classes );
	?>
	<li class="<?php echo esc_attr( $item_classes_string ); ?>">
		<a href="<?php echo esc_url( $url ); ?>" class="navigation-list__item-link">
			<?php echo esc_html( $label ); ?>
		</a>
	</li>
	<?php
}

function ppc__get_nav_item_classes( $item_is_home, $item_is_current ) {
	$item_classes = array( 'navigation-list__item' );
	if ( $item_is_current ) {
		$item_classes[] = 'navigation-list__item--current';
	}
	if ( $item_is_home ) {
		$item_classes[] = 'navigation-list__item--home';
	}

	return $item_classes;
}

function ppc__logo_nav_item() {
	$item_is_home = true;
	$item_is_current = false;
	$url = '/';
	$label = 'Home';
	if ( is_front_page() ) {
		$item_is_current = true;
		$url = "#main";
	}
	$item_classes = ppc__get_nav_item_classes( $item_is_home, $item_is_current );
	$item_classes_string = implode( ' ', $item_classes );
?>
	<li class="<?php echo esc_attr( $item_classes_string ); ?>">
		<a href="<?php echo esc_url( $url ); ?>" class="navigation-list__item-link">
			<?php
			$ttrust_logo = of_get_option('logo');
			if ( !empty( $ttrust_logo ) ) : ?>
				<img src="<?php echo esc_url( $ttrust_logo ); ?>" alt="<?php esc_attr( $label ); ?>" class="navigation-list__home-logo">
			<?php
			else :
				echo esc_html( $label );
			endif;
			?>
		</a>
	</li>
<?php
}