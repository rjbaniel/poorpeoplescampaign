<?php
/*
Plugin Name: Dan's Simple Google Calendar Shortcode
Description: Shortcode that displays events from a public google calendar
Author: Daniel Jones
Version: 0.2
License: GPL3
*/

/*
		Simple Google calendar widget for Wordpress
		Copyright (C) 2012 Nico Boehr

		This program is free software: you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation, either version 3 of the License, or
		(at your option) any later version.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program.	If not, see <http://www.gnu.org/licenses/>.
*/
function gcal_show_events( $atts, $output = false ) {
	load_plugin_textdomain('simple_gcal', false, basename( dirname( __FILE__ ) ) . '/languages' );
	gcal_add_styles();
	$atts = shortcode_atts(
		array(
			'title' => 'Upcoming events',
			'cal_id' => '',
			'count' => 10
		), $atts, 'simple_gcal'
	);
	$title = $atts['title'];

	ob_start();
	?>
	<h2 class="gcal-events-title" id="calendar"><?php echo esc_html( $title ); ?></h4>
	
	<?php
	$data = get_events_data( $atts['cal_id'], $atts['count'] );
	// There was an error getting the data
	if ( $data === false ) : ?>
		<p>
			<?php _e( "Sorry, we weren't able to display our events - please check back later or email info@kairoscenter.org", 'simple_gcal' ); ?>
		</p>
	<?php
	// There were no events returned
	elseif ( count( $data ) < 1 ) : ?>
		<p class="simple-gcal-no-events">
			<?php _e( "We don't have any upcoming events right now - please check back later or email info@kairoscenter.org", 'simple_gcal' ); ?>
		</p>
	<?php else : ?>
		<ul class="gcal-event-list">
		<?php
		foreach($data as $item) :
			$startTime = '';
			$endTime = '';
			if ( isset( $item->start ) ) {
				if ( $item->dateType === "datetime" ) {
					$startTime = $item->start->format( 'l, F j, g:ia' );
					if ( $item->end->format( 'l, F j' ) === $item->start->format( 'l, F j' ) ) {
						$endTime = $item->end->format( 'g:ia' );
					} else {
						$endTime = $item->end->format( 'l, F j, g:ia' );
					}
					$timeZone = $item->start->format( 'T' );
				} elseif ( $item->dateType === "date" ) {
					$startTime = $item->start->format( 'l, F j' );
					if ( isset( $item->end ) ) {
						$endTime = $item->end->format( 'l, F j' );
					}
				}
			}

			 ?>	
			<li class='gcal-event'>
				<h3 class='gcal-event-title'> <?php echo esc_html($item->title); ?></h4>
				<?php
				if ( $startTime ) {
					$timeHTML = '<p class="gcal-event-time"><strong>When: </strong>';
					if ( $item->dateType == "datetime" ) {
						$timeHTML .= $startTime . " - " . $endTime . " (" . $timeZone . ")";
					} else {
						$timeHTML .= $startTime;
						if ( isset( $endTime ) && ! empty( $endTime ) ) {
							$timeHTML .= " - " . $endTime;
						} else {
							$timeHTML .= " (all-day event)";
						}
					}
					$timeHTML .= '</p>';
					echo $timeHTML;
				}
				if ( isset( $item->where ) ) : ?>
					<p class="gcal-event-location">
						<strong>Where:</strong> <?php echo esc_html( $item->where ) ?>
					</p>
				<?php endif;
				if ( isset( $item->content ) ) : ?>
					<p class='gcal-event-description'><?php echo esc_html( $item->content ); ?></p>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<?php
	if ( $output ) {
		echo ob_get_clean();
	} else {
		return ob_get_clean();
	}
}
add_action( 'init', 'simple_gcal_register_shortcode' );
function simple_gcal_register_shortcode() {
	add_shortcode( 'simple_gcal', 'gcal_show_events' );
}
function get_calendar_url( $cal_id, $count ) {
	// Assume the server is using GMT, because the API is broken: won't accept positive offsets
	$time =  date( "Y-m-d\TH:i:s", time() ) . 'Z';
	return 'https://www.googleapis.com/calendar/v3/calendars/' . $cal_id . '/events?key=AIzaSyA37U1v0bGm8PrWUkpPLs-UyyCNB7DcLxQ&maxResults=' . $count . '&orderBy=startTime&singleEvents=true&timeMin=' . $time;
}
	
function get_events_data( $cal_id, $count ) {
	$transientId = 'gcal_transient';
	//if( false === ( $data = get_transient( $transientId ) ) ) {
		$data = fetch_events( $cal_id, $count );
		set_transient( $transientId, $data, 60 * 60 * 6 );
	//}
	return $data;
}

function fetch_events( $cal_id, $count ) {
	$api_request_url = get_calendar_url( $cal_id, $count );
	$raw_api_response = wp_remote_get( $api_request_url );
	if( is_wp_error( $raw_api_response ) || !is_array( $raw_api_response ) ) {
		return false;
	}
	// Decode the JSON response
	$json = json_decode( $raw_api_response['body'], true );
	if( ! $json || ! isset( $json['items'] ) ) {
		return false;
	}
	$timeZone = $json['timeZone'];
	$timeZoneObject = new DateTimeZone( $timeZone );
	$events = $json['items'];
	$events_to_return = array();

	// Build return array of event objects
	foreach( $events as $event ) {
		$event_to_return = new StdClass;
		// Get the title
		$event_to_return->title = $event['summary'];
		// Get the description, if there is one
		if ( isset( $event['description'] ) ) {
			$event_to_return->content = $event['description'];
		}
		// Get either the start date and time, or just the date if only that is provided
		if ( isset( $event['start']['dateTime'] ) ) {
			$event_to_return->start = DateTime::createFromFormat( "Y-m-d\TH:i:sP", $event['start']['dateTime'] );
			$event_to_return->start->setTimezone( $timeZoneObject );
			$event_to_return->dateType = "datetime";

			if ( isset( $event['end']['dateTime'] ) ) {
				$event_to_return->end = DateTime::createFromFormat( "Y-m-d\TH:i:sP", $event['end']['dateTime'] );
				$event_to_return->end->setTimezone( $timeZoneObject );
			}
		} elseif ( isset( $event['start']['date'] ) ) {
			$event_to_return->start = DateTime::createFromFormat( "Y-m-d", $event['start']['date'] );
			$event_to_return->dateType = "date";
			if ( isset( $event['end']['date'] ) ) {
				$event_to_return->end = DateTime::createFromFormat( "Y-m-d", $event['end']['date'] );
			}
		// We don't have a date, so we can't use this event entry: skip it
		} else {
			continue;
		}

		// Get the location, if it's provided
		if ( isset( $event['location'] ) ) {
			$event_to_return->where = $event['location'];
		}

		$events_to_return[] = $event_to_return;
	}
	return $events_to_return;
}

function gcal_add_styles() {
	wp_enqueue_style( 'gcal-styles', plugins_url( 'gcal-styles.css', __FILE__ ) );
	wp_enqueue_script( 'gcal-js', plugins_url( 'gcal-js.js', __FILE__), 'jquery', '.0.1', true );
}

