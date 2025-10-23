( function () {
	'use strict';

	const docReady = ( callback ) => {
		if ( document.readyState === 'loading' ) {
			document.addEventListener( 'DOMContentLoaded', callback );
		} else {
			callback();
		}
	};

	docReady( () => {
		const navToggle = document.querySelector( '.nav-toggle' );
		const drawer = document.querySelector( '.site-drawer' );
		const drawerOverlay = document.querySelector( '.site-drawer__overlay' );
		const drawerClose = document.querySelector( '.site-drawer__close' );
		const drawerNav = document.querySelector( '.site-drawer__nav' );
		let lastDrawerTrigger = null;

		const drawerTriggerLinks = document.querySelectorAll( '.menu-item--drawer > a' );

		const setDrawerTriggerState = ( isOpen ) => {
			drawerTriggerLinks.forEach( ( trigger ) => {
				trigger.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
				trigger.setAttribute( 'aria-haspopup', 'true' );
			} );
		};

		const toggleDrawer = ( forceOpen = null ) => {
			if ( ! drawer ) {
				return;
			}
			const isCurrentlyOpen = drawer.classList.contains( 'is-open' );
			const shouldOpen = 'boolean' === typeof forceOpen ? forceOpen : ! isCurrentlyOpen;

			if ( shouldOpen === isCurrentlyOpen ) {
				return;
			}

			if ( shouldOpen ) {
				lastDrawerTrigger = document.activeElement instanceof HTMLElement ? document.activeElement : null;
				drawer.classList.add( 'is-open' );
				navToggle?.setAttribute( 'aria-expanded', 'true' );
				drawerOverlay?.removeAttribute( 'hidden' );
				drawer.removeAttribute( 'aria-hidden' );
				drawer.focus();
				document.documentElement.classList.add( 'has-drawer-open' );
				setDrawerTriggerState( true );
			} else {
				drawer.classList.remove( 'is-open' );
				navToggle?.setAttribute( 'aria-expanded', 'false' );
				drawerOverlay?.setAttribute( 'hidden', 'hidden' );
				drawer.setAttribute( 'aria-hidden', 'true' );
				if ( lastDrawerTrigger ) {
					lastDrawerTrigger.focus();
				} else {
					navToggle?.focus();
				}
				setDrawerTriggerState( false );
				document.documentElement.classList.remove( 'has-drawer-open' );
			}
		};

		if ( navToggle ) {
			navToggle.addEventListener( 'click', () => {
				toggleDrawer();
			} );
		}

		drawerTriggerLinks.forEach( ( drawerLink ) => {
			drawerLink.addEventListener( 'click', ( event ) => {
				if ( event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0 ) {
					return;
				}
				event.preventDefault();
				lastDrawerTrigger = drawerLink;
				toggleDrawer( true );
			} );
		} );

		setDrawerTriggerState( false );

		if ( drawerClose ) {
			drawerClose.addEventListener( 'click', () => {
				toggleDrawer( false );
			} );
		}

		if ( drawerOverlay ) {
			drawerOverlay.addEventListener( 'click', () => {
				toggleDrawer( false );
			} );
		}

		document.addEventListener( 'keydown', ( event ) => {
			if ( event.key === 'Escape' ) {
				toggleDrawer( false );
			}
		} );

		if ( drawer ) {
			drawer.setAttribute( 'tabindex', '-1' );
		}

		if ( drawerNav ) {
			drawerNav.addEventListener( 'click', ( event ) => {
				if ( event.target instanceof HTMLElement && event.target.closest( 'a' ) ) {
					toggleDrawer( false );
				}
			} );
		}

		const homeTabs = document.querySelectorAll( '.home-tab' );
		const homePanels = document.querySelectorAll( '.home-tab-panel' );

		if ( homeTabs.length && homePanels.length ) {
			const activateTab = ( targetSlug ) => {
				homeTabs.forEach( ( tab ) => {
					const isActive = tab.dataset.tab === targetSlug;
					tab.classList.toggle( 'is-active', isActive );
					tab.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
				} );

				homePanels.forEach( ( panel ) => {
					panel.classList.toggle( 'is-active', panel.dataset.tabPanel === targetSlug );
				} );
			};

			homeTabs.forEach( ( tab ) => {
				tab.addEventListener( 'click', () => {
					activateTab( tab.dataset.tab );
				} );
			} );
		}

		const cookieBanner = document.querySelector( '.cookie-banner' );
		if ( cookieBanner ) {
			const closeButton = cookieBanner.querySelector( 'button' );
			if ( closeButton ) {
				closeButton.addEventListener( 'click', () => {
					cookieBanner.remove();
				} );
			}
		}
	} );
} )();
