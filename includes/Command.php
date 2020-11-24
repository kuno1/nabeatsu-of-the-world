<?php

namespace Kunoichi\Nabeats;


// If not CLI, do nothing.
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * CLI utility for Nabeats of the World.
 *
 * @package notw
 */
class Command extends \WP_CLI_Command {

	/**
	 * Remove all posts.
	 */
	public function clean() {
		\WP_CLI::line( __( 'Getting posts and deleting...', 'notw' ) );
		$query = new \WP_Query( [
			'post_type'      => 'post',
			'post_status'    => 'any',
			'posts_per_page' => -1,
		] );
		if ( ! $query->have_posts() ) {
			\WP_CLI::error( __( 'No post found.', 'notw' ) );
		}
		$found   = $query->found_posts;
		$deleted = 0;
		foreach ( $query->posts as $post ) {
			if ( wp_delete_post( $post->ID, true ) ) {
				$deleted++;
				echo '.';
			} else {
				echo 'x';
			}
		}
		\WP_CLI::line( '' );
		\WP_CLI::success( sprintf( __( '%d / %d deleted.', 'notw' ), $deleted, $found ) );
	}

	/**
	 * Get missed schedules
	 *
	 */
	public function stats() {
		$query = new \WP_Query( [
			'post_type'      => 'post',
			'posts_per_page' => 1,
			'post_status'    => 'future',
			'date_query' => [
				[
					'before' => date_i18n( 'Y-m-d H:i:s' ),
				],
			],
		] );
		$found = $query->found_posts;
		$message = sprintf( __( '%d posts are missed schedule.' ), number_format( $found ) );
		if ( $found ) {
			\WP_CLI::error( $message );
		} else {
			\WP_CLI::success( $message );
		}
	}

	/**
	 * Replace wp-cron.php
	 */
	public function replace() {
		$new = plugin_dir_path( __DIR__ ) . 'wp-cron-loader.php';
		$old = ABSPATH . 'wp-cron.php';
		if ( md5_file( $new ) === md5_file( $old ) ) {
			\WP_CLI::success( __( 'wp-cron.php is already replaced.', 'notw' ) );
			exit;
		}
		if ( file_put_contents( $old, file_get_contents( $new ) ) ) {
			\WP_CLI::success( __( 'wp-cron.php is replaced with customized one.', 'notw' ) );
		} else {
			\WP_CLI::error( __( 'Failed to replace.', 'notw' ) );
		}
	}

	/**
	 * Restore wp-cron.php to trunk.
	 */
	public function restore() {
		$new = plugin_dir_path( __DIR__ ) . 'wp-cron.original.php';
		$old = ABSPATH . 'wp-cron.php';
		if ( md5_file( $new ) === md5_file( $old ) ) {
			\WP_CLI::success( __( 'wp-cron.php is already original.', 'notw' ) );
			exit;
		}
		if ( file_put_contents( $old, file_get_contents( $new ) ) ) {
			\WP_CLI::success( __( 'wp-cron.php is restored.', 'notw' ) );
		} else {
			\WP_CLI::error( __( 'Failed to restore original file.', 'notw' ) );
		}
	}

	/**
	 * Display current status.
	 */
	public function current() {
		$current = md5_file( ABSPATH . 'wp-cron.php' );
		$table = new \cli\Table();
		$table->setHeaders( [ 'Name', 'Path', 'MD5', 'Current' ] );
		$valid = false;
		foreach ( [
			[ 'Customized', 'wp-cron-loader.php' ],
			[ 'Original', 'wp-cron.original.php' ],
		] as list( $name, $path ) ) {
			$md5        = md5_file( plugin_dir_path( __DIR__ ) . $path );
			$is_current = $current === $md5;
			$table->addRow( [
				$name,
				$path,
				$md5,
				$is_current ? '✔︎' : ' ',
			] );
			if ( $is_current ) {
				$valid = true;
			}
		}
		$table->display();
		if ( ! $valid ) {
			\WP_CLI::error( __( 'Current wp-cron.php is different from local file.', 'notw' ) );
		}
	}
}

\WP_CLI::add_command( 'nabeatsu', \Kunoichi\Nabeats\Command::class );
