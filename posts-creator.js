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

	var messages = [];
	var updateMessage = function( text ) {
		messages.push( text );
		if ( 6 < messages.length ) {
			messages.shift();
		}
		$( '.nabeats-pre' )
			.html( messages.join( '<br />' ) )
			.effect( 'highlight' );
	};

	var cronHit = 0;
	var cronMessage = function() {
		$( '.nabeatsu-pre-cron' )
			.html( '<span>' + cronHit + '<small>(each 5 sec)</small></span>'  )
			.effect( 'highlight' );
	}

	// Hit top page for cron runs.
	setInterval( function() {
		$.get( '/wp-cron.php' ).done( function () {
			cronHit++;
			cronMessage();
		} ).fail( function ( res ) {
			console.error( 'Error: %o', res );
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
			wp.apiFetch( {
				path: 'wp/v2/posts',
				method: 'post',
				data: {
					title: 'Post in ' + later.toLocaleString(),
					status: 'future',
					content: 'This post should be published at ' + later.toLocaleString(),
					date: ymd,
				}
			} ).then( function( response ) {
				updateMessage( '<span>New post scheduled @ ' + later.toLocaleString() + '</span>' );
				console.log( 'Posted: %o', response );
			} ).catch( function( response ) {
				console.error( 'Error: %o', response );
			} );
		}, 10000 );
	} );
} )( jQuery );
