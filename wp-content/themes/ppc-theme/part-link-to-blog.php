<?php
	$blog_page_id = get_option( 'page_for_posts');
	$blog_page_featured_image_src = get_the_post_thumbnail_url( $blog_page_id, 'full' );
?>

<div class="home-section home-section--blog">
	<div class="home-section__head">
		<a href="/blog" class="home-section--blog__link"><h2 class="home-section__title">Blog posts</h2></a>
	</div>
	<div class="recent-posts">
	<?php
	$recent_posts_query = new WP_Query( 'posts_per_page=3' );
	if ( $recent_posts_query->have_posts() ) {
		while ( $recent_posts_query->have_posts() ) {
			$recent_posts_query->the_post();
			ppc__show_the_post_link();
		}
	} ?>
	</div>
	<a href="/blog" class="frontpage-blog-link">
		<span class="frontpage-blog-link__text">Read more blog posts</span>
	</a>
</div>
