<?php
	$blog_page_id = get_option( 'page_for_posts');
	$blog_page_featured_image_id = get_post_thumbnail_id( $blog_page_id );
	$blog_page_featured_image_src = wp_get_attachment_image_src( $blog_page_featured_image_id, 'full' )[0];
?>

<div class="blog-link">
	<div class="blog-link-text">
		<a href="/blog" class="blog-link-link">
			<div class="blog-link-title"><?php echo get_the_title( $blog_page_id ); ?></div>
			<p class="blog-link-description">Read stories from the effort to build a new Poor People's Campaign for today.</p>
		</a>

		<div class="blog-link-posts-list-container">
		<?php
		$query = new WP_Query( 'posts_per_page=3' );
		if ( $query->have_posts() ) {
			echo '<ul class="blog-link-posts-list">';
			while ( $query->have_posts() ) {
				$query->the_post();
				$permalink = get_permalink();
				echo '<li><a href="' . $permalink . '" class="blog-link-post-link">';
				echo get_the_title();
				echo '</a></li>';
			}
			echo '</ul>';
		}
		?>
		</div>
	</div>
	<a href="/blog">
		<img src="<?php echo $blog_page_featured_image_src; ?>" class="blog-link-image">
	</a>
</div>
