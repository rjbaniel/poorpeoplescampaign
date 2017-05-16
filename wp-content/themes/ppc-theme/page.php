<?php get_header(); ?>		
<?php if(!is_front_page()):?>
	<div class="page-head" id="pageHead">
		<?php if ( has_post_thumbnail( get_the_id() ) ) : ?>
			<img class="page-head__image" src="<?php the_post_thumbnail_url(); ?>">
			<h1 class="page-head__title"><?php the_title(); ?></h1>
		<?php else : ?>
			<h1 class="page-head__title page-head__title--no-image"><?php the_title(); ?></h1>
		<?php endif; ?>
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
