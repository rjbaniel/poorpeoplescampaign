<?php

function register_ppc_endorsements() {
	$labels = array(
		"name" => "Endorsements",
		"singular_name" => "Endorsement",
		"add_new_item" => "Add New Endorsement",
		"edit_item" => "Edit Endorsement",
		"new_item" => "New Endorsement",
		"view_item" => "View Endorsement",
		"search_items" => "Search Endorsement",
		"not_found" => "No endorsements found",
		"not_found_in_trash" => "No endorsements found in trash",
	);

	$args = array(
		"labels" => $labels,
		"description" => "Endorsements for the Poor People's Campaign",
		"public" => true,
		"menu_position" => 15,
		"supports" => array( 'title', 'thumbnail', 'excerpt'),
		"register_meta_box_cb" => 'ppc__add_endorsement_metabox'
	);
	register_post_type( "ppc-endorsement", $args );
}
add_action( "init", "register_ppc_endorsements", 15 );

function ppc__add_endorsement_metabox() {
	add_meta_box( 'ppc-endorsement', 'Endorsement Info', 'ppc__display_endorsement_metabox', 'ppc-endorsement' );
}

function ppc__display_endorsement_metabox( $post ) {
	$saved_value = get_post_meta( $post->ID, 'ppc-endorsement-url', true );
	if ( ! $saved_value )
		$saved_value = '';
?>
	<label for="ppc-endorsement-url">Endorser website or other link:</label>
	<input
		type="text"
		class="ppc-endorsement__meta widefat"
		id="ppc-endorsement-url"
		name="ppc-endorsement-url"
		value="<?php echo esc_attr( $saved_value ); ?>"
	>
<?php
	wp_nonce_field( 'save_ppc_endorsement', 'ppc-endorsement-nonce' );
}

function ppc__save_endorsement_meta( $post_id ) {
	$post = get_post( $post_id );
	if ( $post->post_type != 'ppc-endorsement' )
		return;

	if ( ! wp_verify_nonce( $_POST['ppc-endorsement-nonce'], 'save_ppc_endorsement' ) )
		wp_die( "Sorry, we were unable to verify your request. Please try again" );

	if ( isset( $_POST['ppc-endorsement-url'] ) ) {
		update_post_meta( $post->ID, 'ppc-endorsement-url', $_POST['ppc-endorsement-url'] );
	}
}
add_action( 'save_post', 'ppc__save_endorsement_meta' );
?>