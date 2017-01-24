<?php
	$menu_items = wp_get_nav_menu_items( 'main-pages-menu' );
	$queried_object_id = $wp_query->queried_object_id;
?>
	<ul class="navigation-list">
<?php
	foreach( $menu_items as $item ) :
		$label = $item->title;
		$url = $item->url;
		
		if (
			empty( $label ) ||
			empty( $url ) ||
			$item->menu_item_parent != '0'
		)
			continue;

		$item_classes = array( 'navigation-list__item' );
		$item_is_current = false;
		if ( $item->object_id == $wp_query->queried_object_id ) {
			$item_is_current = true;
			$url = "#main";
			$item_classes[] = 'navigation-list__item--current';
		}

		$item_is_home = false;
		if ( $item->object_id == get_option( 'page_on_front' ) ) {
			$item_is_home = true;
			$item_classes[] = 'navigation-list__item--home';
		}
		$item_classes_string = implode( ' ', $item_classes );

?>

		<li class="<?php echo esc_attr( $item_classes_string ); ?>">
			<a href="<?php echo esc_url( $url ); ?>" class="navigation-list__item-link">
				<?php
				if ( $item_is_home && !empty( get_theme_mod( 'site-logo' ) ) ) : ?>
					<img src="<?php echo esc_url( get_theme_mod( 'site-logo' ) ); ?>" alt="<?php echo esc_attr( $label ); ?>" class="navigation-list__home-logo">
				<?php
				else :
					echo esc_html( $label );
				endif;
				?>
			</a>
		</li>

	<?php endforeach; ?>
	</ul>