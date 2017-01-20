<?php
$featured_page_count = intval(of_get_option('ttrust_featured_pages_count'));
$featured_pages_title = of_get_option('ttrust_featured_pages_title');
$featured_pages_links_enabled = of_get_option('ttrust_featured_pages_links_enabled');

if($featured_page_count > 0) : ?>
	<div class="home-section home-section--featured-pages">			
	<?php if($featured_pages_title): ?>
		<div class="home-section__head">		
			<h2 class="home-section__title"><?php echo $featured_pages_title; ?></h2>	
			<p class="home-section__description"><?php echo of_get_option('ttrust_featured_pages_description'); ?></p>	
		</div>
	<?php endif; ?>	
	<?php
		$featured_pages_query = ppc__get_featured_pages_query();
	?>
		<div class="featured-pages">
			<?php
			while ( $featured_pages_query->have_posts() ) {
				$featured_pages_query->the_post();			    
				ppc__show_the_featured_page();		
			}
			?>	
		</div>
	</div>
<?php endif; ?>