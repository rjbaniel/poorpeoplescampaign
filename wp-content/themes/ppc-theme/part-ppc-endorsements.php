<div class="home-section home-section--endorsements">
 	<div class="home-section__head">
 		<h3 class="home-section__title">Who is endorsing the call for a Poor People's Campaign?</h3>
 	</div>
 	<div class="ppc-endorsements-wrap">
 		<?php
 			$args = array( "post_type" => "ppc-endorsement", "posts_per_page" => -1 );
 			$endorsements = new WP_Query( $args );
 			if( $endorsements->have_posts() ) {
 				while ( $endorsements->have_posts() ) {
 					$endorsements->the_post();
 					?>
 						<div class="ppc-endorsement">
 							<?php
 								$atts = array( 'class' => 'ppc-endorsement-image' );
 								if( has_post_thumbnail() ) {
 									the_post_thumbnail( 'medium', $atts);
 								} 
 							?>
 							<h3 class='ppc-endorsement-title'><?php the_title() ?></h3>
 						</div>
 					<?php
 				}
 			}
 		?>
 	</div>
</div>
