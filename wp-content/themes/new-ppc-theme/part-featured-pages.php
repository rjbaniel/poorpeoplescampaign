<div class="home-section home-section--featured-pages">
	<div class="home-section__head">		
		<h2 class="home-section__title"><?php _e( 'Learn about the Poor People\'s Campaign', 'ppc-theme' ); ?></h2>	
	</div>
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