<?php
/**
Plugin Name: Nabeatsu of the World
Plugin URI: https://github.com/kuno1/nabeatsu-of-the-world
Description: A WordPress plugin for sending e-mail via SendGrid.
Author: Kunoichi Hosting
Version: 1.0.0
PHP Version: 5.6
Author URI: https://hametuha.co.jp/
License: GPL3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: notw
Domain Path: /languages
*/

defined( 'ABSPATH' ) or die();

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

/**
 * Create page and force it.
 */
add_action( 'init', function() {
	$query = new WP_Query( [
		'post_type'   => 'page',
		'post_status' => 'publish',
		'meta_query' => [
			[
				'key'   => '_nabeasu_page',
				'value' => 1,
			],
		],
		'posts_per_page' => 1,
	] );
	// Ensure page exists.
	if ( $query->have_posts() ) {
		$post_id = $query->posts[0]->ID;
	} else {
		$post_id = wp_insert_post( [
			'post_title'   => 'Nabeats of the World',
			'post_content' => '<p>Count up numbers from 1, but being stupid if the number is a multiple of 3 or contains "3"</p>',
			'post_type'    => 'page',
			'post_status'  => 'publish',
		] );
		if ( $post_id ) {
			update_post_meta( $post_id, '_nabeasu_page', 1 );
		}
	}
	// Force front page.
	if ( $post_id && ( $post_id != get_option( 'page_on_front' ) ) ) {
		update_option( 'page_on_front', $post_id );
	}
	// Show on front is always page.
	if ( 'front' !== get_option( 'show_on_front' ) ) {
		update_option( 'show_on_front', 'page' );
	}
} );

/**
 * Append button and screen.
 */
add_filter( 'the_content', function( $content ) {
	if ( get_post_meta( get_the_ID(), '_nabeasu_page', true ) ) {
		$content .= <<<HTML

<div class="nabeatsu">
<p>
<button class="nabeatsu-button button">Press Button</button>
</p>
<pre class="nabeats-pre"></pre>
</div>
HTML;
	}
	return $content;
}, 9999 );

/**
 * Add scripts.
 */
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'nabeatsu-of-the-world', plugins_url( 'posts-creator.js', __FILE__ ), [ 'jquery', 'wp-api-fetch', 'wp-i18n' ], '1.0.0', true );
	wp_enqueue_style( 'nabeatsu-of-the-world', plugins_url( 'posts-creator.css', __FILE__ ), [], '1.0.0' );
} );
