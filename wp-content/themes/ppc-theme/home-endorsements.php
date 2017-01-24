<div class="home-section home-section--endorsers">
<?php
	ppc__home_section_head( 'endorsers', "Who's endorsing the Poor People's Campaign" );
?>
 	<div class="ppc-endorsements-wrap">
		<?php
		$args = array(
			"post_type" => "ppc-endorsement",
			"posts_per_page" => -1
		);
		$endorsements = new WP_Query( $args );
		if( $endorsements->have_posts() ) : while ( $endorsements->have_posts() ) :
			$endorsements->the_post();
			?>
			<div class="ppc-endorsement">
				<?php
				$endorsement_url = get_post_meta( $post->ID, 'ppc-endorsement-url', true );
				if ( ! empty( $endorsement_url ) ) :
				?>
					<a href="<?php echo esc_url( $endorsement_url ); ?>" class="ppc-endorsement-link">
				<?php
				endif;
				$atts = array( 'class' => 'ppc-endorsement-image' );
				if( has_post_thumbnail() ) {
					the_post_thumbnail( 'medium', $atts);
				} 
				?>
					<h3 class='ppc-endorsement-title'>
						<?php the_title() ?>
					</h3>
				<?php if ( ! empty( $endorsement_url ) ) : ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endwhile; endif; ?>
 	</div>
</div>