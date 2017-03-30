<?php get_header(); ?>
<?php $blog_full_width = of_get_option('ttrust_post_full_width'); ?>
<?php $bw = ($blog_full_width) ? "full" : "twoThirds"; ?>
	<div id="middle" class="clearfix">			 
	<div id="content" class="<?php echo $bw; ?>">
		<?php while (have_posts()) : the_post(); ?>    
		<div <?php post_class(); ?>>													
			<h1><?php the_title(); ?></h1>
			<?php get_template_part( 'part-post-thumb'); ?>
			<?php
				$tc_meta = ppc__get_tc_meta( $post->ID );
			?>
			<div class="tc__details">
				<p class="tc__detail"><strong>When:</strong> <?php echo esc_html( $tc_meta['when'] ); ?></p>
				<p class="tc__detail"><strong>Where:</strong> <?php echo esc_html( $tc_meta['where'] ); ?></p>
			</div>
			<?php the_content(); ?>
			
			<?php wp_link_pages( array( 'before' => '<div class="pagination clearfix">Pages: ', 'after' => '</div>' ) ); ?>
																										
		</div>				
		<?php comments_template('', true); ?>
			
		<?php endwhile; ?>					    	
	</div>		
	<?php if($bw == "twoThirds") get_sidebar(); ?>				
	</div>
<?php get_footer(); ?>
