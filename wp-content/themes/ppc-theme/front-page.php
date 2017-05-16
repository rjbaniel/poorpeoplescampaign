<?php
/*
	Template Name: Home
*/
?>
<?php get_header(); ?>
<div class="home-content">
	<div class="home-hero" style="background-image: url(<?php 
the_post_thumbnail_url( 'full' ); ?>);">
		<h1 class="home-hero__title">
			The New Poor People's Campaign
		</h1>		
		<img class="home-hero__image" src="<?php 
the_post_thumbnail_url( 'full' ); ?>">
		<p class="home-hero__text">Fifty years ago, Rev. Dr. King called for a Poor People's Campaign. Poor people from all across the country, of all races, started coming together to wage a campaign against racism, war, and poverty. Today, organizations of the poor are taking up that call again, building a New Poor People's Campaign for today.</p>
	</div>
<?php
	get_template_part( 'home', 'featured' );
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
