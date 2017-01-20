<?php /*
Template Name: Home
*/ ?>
<?php get_header(); ?>
	
<div class="home-content">	
	<div class="home-section home-section--featured-video">	
		<div class="featured-video-wrapper">
			<div class="fluid-width-video-wrapper" style="padding-top: 56.25%">
				<?php echo do_shortcode( '[lux_vimeo clip_id=199846068 portrait=0]' ); ?>
			</div>		
		</div>
	</div>
	<?php get_template_part( 'part-featured-pages'); //featured pages section ?>
	<?php get_template_part( 'part-link-to-blog'); //link to blog page ?>
	<?php get_template_part( 'part-ppc-endorsements'); //endorsements section ?>
</div>

<?php get_footer(); ?>	
