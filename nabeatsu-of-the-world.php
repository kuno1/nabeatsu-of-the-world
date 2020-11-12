<?php
/**
Plugin Name: Nabeatsu of the World
Plugin URI: https://github.com/kuno1/nabeatsu-of-the-world
Description: A WordPress plugin for sending e-mail via SendGrid.
Author: Kunoichi Hosting
Version: 1.0.1
PHP Version: 5.6
Author URI: https://hametuha.co.jp/
License: GPL3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: notw
Domain Path: /languages
*/

defined( 'ABSPATH' ) or die();

// Load all hooks.
foreach ( scandir( __DIR__ . '/includes' ) as $file ) {
	if ( preg_match( '/^[^._].*\.php$/u', $file ) ) {
		require __DIR__ . '/includes/' . $file;
	}
}
