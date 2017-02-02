<?php

function ppc__home_section( $name, $title, $query ) {
?>
<div class="home-section home-section--<?php echo $name; ?>">
	<?php ppc__home_section_head( $name, $title ); ?>
	<div class="home-section__collection home-section__collection--<?php echo $name; ?>">
		<?php ppc__home_section_collection( $query ); ?>
	</div>
</div>
<?php
}

function ppc__home_section_head( $name, $title ) {
?>
<div class="home-section__head">
		<div class="home-section__title-wrapper">
		<?php if ( $name == 'recent-posts' ) : ?>
			<a href="/updates" 
class="home-section--recent-posts__link">
		<?php endif; ?>
				<h2 class="home-section__title"><?php echo $title ?></h2>
		<?php if ( $name == 'recent-posts' ) : ?>
			</a>
		<?php endif; ?>
		</div> <!-- title-wrapper -->
	</div> <!-- section__head -->
<?php
}

function ppc__get_featured_pages_query() {
	$args = array(
		'ignore_sticky_posts' => 1,  
		'meta_key' => '_ttrust_page_featured',
		'meta_value' => true,  			
    	'posts_per_page' => 3,
    	'post_type' => array( 'page' )
	);	
	return new WP_Query( $args );
}

function ppc__home_section_collection( $query ) {
if ( $query->have_posts() ) : while ( $query->have_posts() ) :
	$query->the_post();
?>
	<div class="home-section__item">
		<a href="<?php the_permalink(); ?>" rel="bookmark" class="home-section__item-title-link">
			<h3 class="home-section__item-title">
				<?php the_title(); ?>
			</h3>
		</a>
		<a
			class="home-section__item-image-link"
			href="<?php the_permalink(); ?>"
			rel="bookmark"
		>
			<img
				class="home-section__item-image"
				src="<?php echo esc_url( 
the_post_thumbnail_url( 'large' ) ); ?>"
			>
		</a>
		<div class="home-section__item-description">
			<?php the_excerpt(); ?>
		</div>
	</div> <!-- home section item -->
<?php
endwhile; endif;
}

function ppc__add_read_more_to_excerpt( $excerpt ) {
	if ( is_front_page() ) {
		$excerpt = $excerpt . ' <a href="' . get_the_permalink() .'" class="home-section__item-description-link">Read more.</a>';
	}
	return $excerpt;
}
add_filter( 'get_the_excerpt', 'ppc__add_read_more_to_excerpt', 15 );
