<?php
/**
 * Cron hooks.
 *
 * @package notw
 */



define( 'NABEATSU_EVENT_NAME', 'nabeatsu_cron_event' );

/**
 * Add schedule.
 *
 * @param array $schedules
 * @return array
 */
add_filter( 'cron_schedules', function( $schedules ) {
	$schedules['minutely'] = [
		'interval' => 60,
		'display'  => __( 'Once Minutely', 'notw' ),
	];
	return $schedules;
}, 1 );

/**
 * Register cron minutely.
 */
add_action( 'init', function() {
	if ( ! wp_next_scheduled( NABEATSU_EVENT_NAME ) ) {
		wp_schedule_event( current_time( 'timestamp', true ), 'minutely', NABEATSU_EVENT_NAME );
	}
} );


/**
 * Cron event which occurs minutely.
 */
add_action( NABEATSU_EVENT_NAME, function() {
	$minutes = (int) date_i18n( 'i' );
	if ( 0 === $minutes ) {
		// Is zero.
		$being_stupid = false;
	} elseif( 0 === $minutes % 3 ) {
		// Is a multiple of 3.
		$being_stupid = true;
	} elseif ( preg_match( '/3/', (string) $minutes ) ) {
		// Including "3"
		$being_stupid = true;
	} else {
		// Serious.
		$being_stupid = false;
	}
	trigger_error( sprintf( 'Nabeats says "%d" %s.', $minutes, $being_stupid ? 'stupidly' : 'seriously' ), E_USER_NOTICE );
	if ( $being_stupid ) {
		// Stop 40 seconds because of stupidity.
		sleep( 45 );
	}
} );


