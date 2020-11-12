<?php
/**
 * UI builder.
 *
 * @package notw
 */


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
<p>
Cron Hit: <code class="nabeatsu-pre-cron">0</code>
</p>
<pre class="nabeats-pre">Here comes response messages.</pre>
</div>
HTML;
	}
	return $content;
}, 9999 );

/**
 * Add scripts.
 */
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'nabeatsu-of-the-world', plugins_url( 'posts-creator.js', __DIR__ ), [ 'jquery-effects-highlight', 'wp-api-fetch', 'wp-i18n' ], '1.0.0', true );
	wp_enqueue_style( 'nabeatsu-of-the-world', plugins_url( 'posts-creator.css', __DIR__ ), [], '1.0.0' );
} );
