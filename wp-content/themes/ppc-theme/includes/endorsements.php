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
	);
	register_post_type( "ppc-endorsement", $args );
}
add_action( "init", "register_ppc_endorsements" );

?>