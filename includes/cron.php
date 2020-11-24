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
	nabeats_log( sprintf( __( 'Nabeats says "%d" %s.', 'notw' ), $minutes, $being_stupid ? 'stupidly' : 'seriously' ) );
	if ( $being_stupid ) {
		// Stop 40 seconds because of stupidity.
		sleep( 45 );
	}
} );


/**
 * Log unschedule.
 */
add_filter( 'pre_unschedule_event', function( $pre, $timestamp, $hook, $args ) {
	nabeats_log( [ 'WP-CRON', 'UNSCHEDULE', $hook, nabeast_is_cron() ] );
	return $pre;
}, 10, 4 );

/**
 * Log reschedule.
 */
add_filter( 'pre_reschedule_event', function( $pre, $event ) {
	nabeats_log( [ 'WP-CRON', 'RESCHEDULE', $event->hook, nabeast_is_cron() ] );
	return $pre;
}, 10, 2 );

/**
 * Log event schedule.
 */
add_filter( 'pre_schedule_event', function( $pre, $event ) {
	$event_name = $event->schedule ? 'SCHEDULE_RECURRING' : 'SCHEDULE_SINGLE';
	nabeats_log( [ 'WP-CRON', $event_name, $event->hook, nabeast_is_cron() ] );
	return $pre;
}, 10, 2 );

/**
 * Update option to check cron value changes.
 */
add_filter( 'pre_update_option_cron', function( $value, $old_value, $option ) {
	$action = 'unknown';
	foreach ( debug_backtrace() as $backtrace ) {
		if ( 'wp-cron.php' == basename( $backtrace['file'] ) ) {
			$action = 'wp_cron_php';
			break;
		} elseif ( preg_match( '/wp_insert_post/u', $backtrace['function'] ) ) {
			$action = 'wp_insert_post';
			break;
		}
	}
	$count = 0;
	foreach ( $value as $time_stamp => $jobs ) {
		if ( is_array( $jobs ) ) {
			$count += count( $jobs );
		}
	}
	nabeats_log( [ 'CRON-UPDATED', $action, $count ] );
	return $value;
}, 10, 3 );

/**
 * Check if now is cron.
 *
 * @return string
 */
function nabeast_is_cron() {
	return ( defined( 'DOING_CRON' ) && DOING_CRON ) ? 'DOING_CRON' : 'NO_CRON';
}

/**
 * Log message.
 *
 * @param string|string[] $message
 */
function nabeats_log( $message ) {
	if ( ! is_array( $message ) ) {
		$message = [ $message ];
	}
	array_unshift( $message, 'NOTW' );
	array_push( $message,"\n" );
	array_unshift( $message, sprintf( '[%s UTC]', date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp', true ), true ) ) );
	$message = implode( "\t", $message );
	error_log( $message, 3, WP_CONTENT_DIR . '/debug.log' );
}

/**
 * Log before execution.
 */
add_action( 'wp_cron_before_execution', function( $job ) {
	nabeats_log( [ 'WP-CRON', 'EXECUTION', $job['hook'] ] );
} );

add_action( 'wp_cron_after_execution', function ( $job ) {
	nabeats_log( [ 'WP-CRON', 'DONE', $job['hook'] ] );
} );
