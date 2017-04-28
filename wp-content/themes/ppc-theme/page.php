<?php get_header(); ?>		
<?php if(!is_front_page()):?>
	<div class="page-head" id="pageHead">
		<img class="page-head__image" src="<?php the_post_thumbnail_url(); ?>">
		<h1 class="page-head__title"><?php the_title(); ?></h1>
	</div>
<?php endif; ?>	
	<div id="middle" class="clearfix">
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
<?php get_footer(); ?>
