/**
 * Description
 */

/*global hoge: true*/


( function ( $ ) {
	'use strict';

	var sprintf = wp.i18n.sprintf;

	var zeroPadding = function( number ) {
		return ( '00' + number ).slice( -2 );
	};

	var updateMessage = function( text ) {
		$( '.nabeats-pre' ).html( text );
	};

	// Hit top page for cron runs.
	setInterval( function() {
		$.get( '/wp-cron.php' ).done( function () {
			console.log( 'hit cron' );
		} ).fail( function () {

		} );
	}, 5000 );

	// If button is clicked, Try to post minutely.
	// 6 posts per minutes.
	$( '.nabeatsu-button' ).click( function( e ) {
		e.preventDefault();
		var $button = $( this );
		var $container = $( '.nabeatsu' );
		if ( $button.hasClass( 'disabled' ) ) {
			return;
		}
		$button.attr( 'disabled', true ).addClass( 'disabled' );
		setInterval( function() {
			var later = new Date();
			later.setMinutes( later.getMinutes() + 5 );
			var ymd = sprintf(
				'%d-%s-%s %s:%s:%s',
				later.getFullYear(),
				zeroPadding( later.getMonth() + 1 ),
				zeroPadding( later.getDate() ),
				zeroPadding( later.getHours() ),
				zeroPadding( later.getMinutes() ),
				zeroPadding( later.getSeconds() )
			);
			console.log( ymd );
			wp.apiFetch( {
				path: 'wp/v2/posts',
				method: 'post',
				data: {
					title: 'Post in ' + later.toLocaleString(),
					status: 'future',
					content: 'This post should be published at ' + later.toLocaleString(),
					date: ymd,
				}
			} ).then( function() {
				console.log( 'New post scheduled @ ' + later.toLocaleString() );
			} ).catch( function( response ) {
				console.log( response );
			} );
		}, 10000 );
	} );
} )( jQuery );
