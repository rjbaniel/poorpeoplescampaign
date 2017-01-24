<?php
/*
	Template Name: Home
*/
?>
<?php get_header(); ?>
	
<div class="home-content">
<?php
	get_template_part( 'home', 'video' );
	ppc__home_section(
		'featured-pages',
		'Learn more about the Poor People\'s Campaign', 
		ppc__get_featured_pages_query()
	);
	ppc__home_section(
		'recent-posts',
		'Recent Updates',
		new WP_Query( 'posts_per_page=3' )
	);
	get_template_part( 'home', 'endorsements' );
?>
</div>

<?php get_footer(); ?>	