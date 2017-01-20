<?php

function enqueue_jquery_tabs() {
	if ( is_page_template( 'page-toc.php' ) ) {
		wp_enqueue_script( 'toc-js', get_stylesheet_directory_uri() . '/js/ppc-toc.js', 'jquery');
	}
}

add_action( 'wp_enqueue_scripts', 'enqueue_jquery_tabs');

add_theme_support( "aesop-component-styles", array( "image", "quote", "gallery", "content", "video", "audio" ) );

function ppc_set_style_version( $styles ) {
	$styles ->default_version = "20150605-2";
}
add_action( 'wp_default_styles', 'ppc_set_style_version');

function ppc__get_featured_pages_query() {
	$args = array(
		'ignore_sticky_posts' => 1,  
		'meta_key' => '_ttrust_page_featured',
		'meta_value' => true,  			
    	'posts_per_page' => $featured_page_count,
    	'post_type' => array( 'page' )
	);	
	return new WP_Query( $args );
}

function ppc__show_the_featured_page() {
?>
	<div class="featured-page">	
		<a href="<?php the_permalink() ?>" rel="bookmark" >
			<h3 class="featured-page__title"><?php the_title(); ?></h3>
		</a>
		<a class="featured-page__image-link" href="<?php the_permalink() ?>" rel="bookmark">
			<img class="featured-page__image" src="<?php echo esc_url( the_post_thumbnail_url( 'full' ) ); ?>">
		</a>			
		<div class="featured-page__description"><?php the_excerpt(); ?></div>
	</div>
<?php	
}

function ppc__show_the_post_link() {
?>
	<div class="recent-post">
		<a href="<?php the_permalink(); ?>" class="recent-post__link">
			<h3 class="recent-post__title"><?php the_title(); ?></h3>
			<img src="<?php echo esc_url( the_post_thumbnail_url( 'full') ); ?>" class="recent-post__image">
		</a>
		<div class="recent-post__excerpt"><?php the_excerpt(); ?><a href="<?php the_permalink(); ?>" class="recent-post__read-more">Read more</a></div>
	</div>
<?php
}