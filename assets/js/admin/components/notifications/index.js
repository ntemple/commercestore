/* global cs_vars */

document.addEventListener( 'alpine:init', () => {
	Alpine.store( 'csNotifications', {
		isPanelOpen: false,
		notificationsLoaded: false,
		numberActiveNotifications: 0,
		activeNotifications: [],
		inactiveNotifications: [],

		init: function() {
			const csNotifications = this;

			/*
			 * The bubble starts out hidden until AlpineJS is initialized. Once it is, we remove
			 * the hidden class. This prevents a flash of the bubble's visibility in the event that there
			 * are no notifications.
			 */
			const notificationCountBubble = document.querySelector( '#cs-notification-button .cs-number' );
			if ( notificationCountBubble ) {
				notificationCountBubble.classList.remove( 'cs-hidden' );
			}

			document.addEventListener( 'keydown', function( e ) {
				if ( e.key === 'Escape' ) {
					csNotifications.closePanel();
				}
			} );
		},

		openPanel: function() {
			const panelHeader = document.getElementById( 'cs-notifications-header' );

			if ( this.notificationsLoaded ) {
				this.isPanelOpen = true;
				if ( panelHeader ) {
					setTimeout( function() {
						panelHeader.focus();
					} );
				}

				return;
			}

			this.isPanelOpen = true;

			this.apiRequest( '/notifications', 'GET' )
				.then( data => {
					this.activeNotifications = data.active;
					this.inactiveNotifications = data.dismissed;
					this.notificationsLoaded = true;

					if ( panelHeader ) {
						panelHeader.focus();
					}
				} )
				.catch( error => {
					console.log( 'Notification error', error );
				} );
		},

		closePanel: function() {
			if ( ! this.isPanelOpen ) {
				return;
			}

			this.isPanelOpen = false;

			const notificationButton = document.getElementById( 'cs-notification-button' );
			if ( notificationButton ) {
				notificationButton.focus();
			}
		},

		apiRequest: function( endpoint, method ) {
			return fetch( cs_vars.restBase + endpoint, {
				method: method,
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': cs_vars.restNonce
				}
			} ).then( response => {
				if ( ! response.ok ) {
					return Promise.reject( response );
				}

				/*
				 * Returning response.text() instead of response.json() because dismissing
				 * a notification doesn't return a JSON response, so response.json() will break.
				 */
				return response.text();
				//return response.json();
			} ).then( data => {
				return data ? JSON.parse( data ) : null;
			} );
		} ,

		dismiss: function( event, index ) {
			if ( 'undefined' === typeof this.activeNotifications[ index ] ) {
				return;
			}

			event.target.disabled = true;

			const notification = this.activeNotifications[ index ];

			this.apiRequest( '/notifications/' + notification.id, 'DELETE' )
				.then( response => {
					this.activeNotifications.splice( index, 1 );
					this.numberActiveNotifications = this.activeNotifications.length;
				} )
				.catch( error => {
					console.log( 'Dismiss error', error );
				} );
		}
	} );
} );
