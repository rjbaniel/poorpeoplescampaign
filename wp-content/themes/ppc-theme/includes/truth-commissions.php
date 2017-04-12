<?php
function register_truth_commission_cpt() {
	$labels = array(
		"name" => "Truth Commissions",
		"singular_name" => "Truth Commission",
		"add_new_item" => "Add Truth Commission",
		"edit_item" => "Edit Truth Commission",
		"new_item" => "New Truth Commission",
		"view_item" => "View Truth Commission",
		"search_items" => "Search Truth Commissions",
		"not_found" => "No truth commissions found",
		"not_found_in_trash" => "No truth commissions found in trash",
	);

	$args = array(
		"labels" => $labels,
		"description" => "Truth Commission events",
		"public" => true,
		"menu_position" => 15,
		"supports" => array( 'title', 'editor', 'thumbnail', 'excerpt'),
		"register_meta_box_cb" => 'ppc__add_tc_metabox'
	);
	register_post_type( "truth-commission", $args );
}
add_action( 'init', 'register_truth_commission_cpt', 15 );

function ppc__add_tc_metabox() {
	add_meta_box( 'ppc_tc', 'Truth Commission Info', 'ppc__tc_metabox', 'truth-commission', 'side' );
}

function ppc__tc_metabox( $post ) {
	$tc_meta = ppc__get_tc_meta( $post );
	if ( ! $tc_meta ) {
		$tc_meta = array(
			'when' => false,
			'where' => false,
			'past'	=> false,
			'hide' => false,
		);
	}
	$hide_checked = $tc_meta['hide'] ? 'checked' : '';
	$past_checked = $tc_meta['past'] ? 'checked' : '';

	?>
	<label for="tc_when">When: </label>
	<input
		type="text"
		class="widefat"
		name="tc_when"
		id="tc_when"
		value="<?php echo esc_attr( $tc_meta['when'] ); ?>"
	>
	<label for="tc_where">Where: </label>
	<input
		type="text"
		class="widefat"
		name="tc_where"
		id="tc_where"
		value="<?php echo esc_attr( $tc_meta['where'] ); ?>"
	>
	<input
		type="checkbox"
		name="tc_hide"
		id="tc_hide"
		<?php echo esc_attr( $hide_checked ); ?>
	>
	<label for="tc_hide">Hide this event?</label><br>
	<input
		type="checkbox"
		name="tc_past"
		id="tc_past"
		<?php echo esc_attr( $past_checked ); ?>
	>
	<label for="tc_past">Is this event past?</label>
	<?php
	wp_nonce_field( 'save_truth_commission', 'truth-commission-nonce' );

}

function ppc__save_tc_meta( $post_id ) {
	$post = get_post( $post_id );
	if ( $post->post_type != 'truth-commission' )
		return;

	if ( isset( $_POST['truth-commission-nonce'] ) && ! wp_verify_nonce( $_POST['truth-commission-nonce'], 'save_truth_commission' ) )
		wp_die( "Sorry, we were unable to verify your request. Please try again" );

	$meta_text_fields = array( 'tc_when', 'tc_where' );
	foreach( $meta_text_fields as $field ) {
		if ( isset( $_POST[$field] ) ) {
			update_post_meta( $post_id, $field, $_POST[$field] );
		}
	}

	$meta_checkboxes = array( 'tc_hide', 'tc_past' );
	foreach( $meta_checkboxes as $field ) {
		update_post_meta( $post_id, $field, isset( $_POST[$field] ) );
	}
}
add_action( 'save_post', 'ppc__save_tc_meta' );

function ppc__tc_previews() {
	$tcs_query = new WP_Query( array(
		'post_type' => 'truth-commission',
		'posts_per_page' => -1,
		'meta_key'	=> 'tc_past',
		'orderby' => 'meta_value_binary',

	) );
	if ( $tcs_query->have_posts() ) :
		ob_start();
		
		// If we have "past events", start with that header
		$has_past_events = get_post_meta( $tcs_query->posts[0]->ID, 'tc_past', true );
		if ( $has_past_events ) :
			?>
			<h2 class="tc-previews__title">Past Truth Commission Events</h2>
			<?php
			$in_upcoming_events = false;
		else :
			?>
			<h2 class="tc-previews__title">Upcoming Truth Commission Events</h2>
			<?php
		endif;

		while( $tcs_query->have_posts() ) :
			$tcs_query->the_post();
			global $post;
			// If we started with past events, we need to output the "Upcoming Events" header before the first
			// event that isn't past.
			if ( $has_past_events && !$in_upcoming_events && !get_post_meta( $post->ID, 'tc_past', true ) ) :
				?>
				<h2 class="tc-previews__title">Upcoming Truth Commission Events</h2>
				<?php
				$in_upcoming_events = true;
			endif;
			ppc__tc_preview( $post->ID );
		endwhile;
		wp_reset_postdata();
		?>
		<?php
		return ob_get_clean();
	endif;
}

function ppc__tc_preview( $tc_id ) {
	$tc_meta = ppc__get_tc_meta( $tc_id );
	if ( $tc_meta['hide'] )
		return;
	?>
	<article class="tc">
		<h3 class="tc__title"><?php the_title(); ?></h3>
		<p class="tc__detail tc__detail--when"><strong>When: </strong><?php echo esc_html( $tc_meta['when'] ); ?></p>
		<p class="tc__detail tc__detail--where"><strong>Where: </strong><?php echo esc_html( $tc_meta['where'] ); ?></p>
		<?php
			if ( has_post_thumbnail() ) :
				?>
				<div class="tc__image-container">
					<a href="<?php the_permalink() ?>"><img class="tc__image" src="<?php echo esc_url( the_post_thumbnail_url( 'large' ) ); ?>"></a>
				</div>
				<?php
			endif;
		?>
		<div class="tc__detail tc__detail--excerpt-wrapper">
		<?php the_excerpt(); ?>
		</div>
		<a href="<?php the_permalink(); ?>">Learn more</a>
	</article>
	<?php
}

function ppc__get_tc_meta( $tc ) {
	if ( ! $tc instanceof WP_Post ) {
		if ( is_int( $tc ) ) {
			$tc = get_post( $tc_id );
			if ( ! $tc ) {
				return false;
			}
		} else {
			return false;
		}
	}
	$tc_id = $tc->ID;

	return array(
		'when' => get_post_meta( $tc_id, 'tc_when', true ),
		'where' => get_post_meta( $tc_id, 'tc_where', true ),
		'hide' => get_post_meta( $tc_id, 'tc_hide', true ),
		'past' => get_post_meta( $tc_id, 'tc_past', true )
	);
}

function register_tcs_shortcode() {
	add_shortcode( 'truth-commissions', 'ppc__tc_previews' );
}
add_action( 'init', 'register_tcs_shortcode' , 15 );

?>