( function () {
	'use strict';

	function getDefaults() {
		if ( typeof window.wcSettings !== 'undefined' && window.wcSettings.getSetting ) {
			var fromSettings = window.wcSettings.getSetting( 'jwcfeBlockFieldDefaults', {} );

			if ( fromSettings && Object.keys( fromSettings ).length ) {
				return fromSettings;
			}
		}

		if ( typeof window.jwcfeBlockFieldDefaults !== 'undefined' ) {
			return window.jwcfeBlockFieldDefaults || {};
		}

		return {};
	}

	function applyDefaultsToStore( defaults ) {
		if ( ! window.wp || ! window.wp.data ) {
			return false;
		}

		var select;
		var dispatch;

		try {
			select = window.wp.data.select( 'wc/store/checkout' );
			dispatch = window.wp.data.dispatch( 'wc/store/checkout' );
		} catch ( e ) {
			return false;
		}

		if ( ! select || ! dispatch || typeof dispatch.setAdditionalFields !== 'function' ) {
			return false;
		}

		var current = select.getAdditionalFields() || {};
		var merged = Object.assign( {}, current );
		var changed = false;

		Object.keys( defaults ).forEach( function ( key ) {
			var val = defaults[ key ];

			if ( val === undefined || val === null || val === '' ) {
				return;
			}

			if ( merged[ key ] === undefined || merged[ key ] === '' ) {
				merged[ key ] = val;
				changed = true;
			}
		} );

		if ( changed ) {
			dispatch.setAdditionalFields( merged );
		}

		return changed;
	}

	function applyDefaultsToDom( defaults ) {
		var addressTypes = [ 'contact', 'order', 'billing', 'shipping' ];

		Object.keys( defaults ).forEach( function ( key ) {
			var val = defaults[ key ];

			if ( val === undefined || val === null || val === '' ) {
				return;
			}

			addressTypes.forEach( function ( addressType ) {
				var selector =
					'input[name="' +
					addressType +
					'_' +
					key +
					'"], textarea[name="' +
					addressType +
					'_' +
					key +
					'"], select[name="' +
					addressType +
					'_' +
					key +
					'"]';

				document.querySelectorAll( selector ).forEach( function ( el ) {
					if ( el.tagName === 'SELECT' ) {
						if ( ! el.value ) {
							el.value = val;
							el.dispatchEvent( new Event( 'change', { bubbles: true } ) );
						}
						return;
					}

					if ( ! el.value ) {
						el.value = val;
						el.dispatchEvent( new Event( 'input', { bubbles: true } ) );
					}
				} );
			} );
		} );
	}

	function applyDefaults() {
		var defaults = getDefaults();

		if ( ! Object.keys( defaults ).length ) {
			return;
		}

		applyDefaultsToStore( defaults );
		applyDefaultsToDom( defaults );
	}

	function init() {
		applyDefaults();

		if ( window.wp && window.wp.data && typeof window.wp.data.subscribe === 'function' ) {
			var attempts = 0;
			var unsubscribe = window.wp.data.subscribe( function () {
				attempts += 1;

				if ( applyDefaultsToStore( getDefaults() ) || attempts > 50 ) {
					if ( typeof unsubscribe === 'function' ) {
						unsubscribe();
					}
				}
			} );
		}

		if ( typeof MutationObserver !== 'undefined' ) {
			var observer = new MutationObserver( function () {
				applyDefaultsToDom( getDefaults() );
			} );

			observer.observe( document.body, { childList: true, subtree: true } );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
