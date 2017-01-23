<?php
/*
Template Name: Archives
*/
?>

<?php get_header(); ?>
		<div id="pageHead">
			<div class="inside">
			<h1><?php the_title(); ?></h1>
			<?php $page_description = get_post_meta($post->ID, "_ttrust_page_description", true); ?>
			<?php if ($page_description) : ?>
				<p><?php echo $page_description; ?></p>
			<?php endif; ?>
			</div>
		</div>
		<div id="middle" class="clearfix">
		<div id="content" class="twoThirds clearfix">
			<h2><?php _e('Archives by Month:', 'themetrust'); ?></h2>
			<ul>
				<?php wp_get_archives('type=monthly'); ?>
			</ul>
			<h2><?php _e('Archives by Subject:', 'themetrust'); ?></h2>
			<ul>
				<?php wp_list_categories(); ?>
			</ul>
		</div>
		<?php get_sidebar(); ?>
		</div>
<?php get_footer(); ?>
