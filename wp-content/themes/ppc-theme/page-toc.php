<?php
/*
Template Name: jQuery tabs
*/
?>
<?php get_header(); ?>	
		
		<?php if(!is_front_page()):?>
		<div id="pageHead">
			<div class="inside">
			<h1><?php the_title(); ?></h1>
			<?php $page_description = get_post_meta($post->ID, "_ttrust_page_description", true); ?>
			<?php if ($page_description) : ?>
				<p><?php echo $page_description; ?></p>
			<?php endif; ?>	
			</div>			
		</div>
		<?php endif; ?>			
		
		<div id="middle" class="clearfix">
		<div id="inner">			 
		<div id="content" class="twoThirds clearfix">
			<?php while (have_posts()) : the_post(); ?>			    
			    <div <?php post_class('clearfix'); ?>>						
					<?php the_content(); ?>				
				</div>				
				<?php comments_template('', true); ?>			
			<?php endwhile; ?>					    	
		</div>		
		<?php get_sidebar(); ?>
		</div>
		</div>
<?php get_footer(); ?>
