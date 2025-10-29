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

		const agendaModal = document.getElementById( 'agendaModal' );
		const agendaOpeners = document.querySelectorAll( '[data-open-agenda]' );
		const agendaClosers = document.querySelectorAll( '[data-close-agenda]' );
		let agendaLastFocus = null;

		const toggleAgenda = ( shouldOpen ) => {
			if ( ! agendaModal ) {
				return;
			}

			const isHidden = agendaModal.getAttribute( 'aria-hidden' ) === 'true' || agendaModal.hasAttribute( 'hidden' );
			const willOpen = 'boolean' === typeof shouldOpen ? shouldOpen : isHidden;

			if ( willOpen === ! isHidden ) {
				return;
			}

			if ( willOpen ) {
				agendaLastFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;
				agendaModal.removeAttribute( 'hidden' );
				agendaModal.setAttribute( 'aria-hidden', 'false' );
				const firstFocusable = agendaModal.querySelector( 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])' );
				if ( firstFocusable instanceof HTMLElement ) {
					firstFocusable.focus();
				}
				document.documentElement.classList.add( 'has-modal-open' );
				document.body.classList.add( 'has-modal-open' );
			} else {
				agendaModal.setAttribute( 'aria-hidden', 'true' );
				agendaModal.setAttribute( 'hidden', 'hidden' );
				if ( agendaLastFocus ) {
					agendaLastFocus.focus();
				}
				document.documentElement.classList.remove( 'has-modal-open' );
				document.body.classList.remove( 'has-modal-open' );
			}
		};

		agendaOpeners.forEach( ( trigger ) => {
			trigger.addEventListener( 'click', () => {
				toggleAgenda( true );
			} );
		} );

		agendaClosers.forEach( ( trigger ) => {
			trigger.addEventListener( 'click', () => {
				toggleAgenda( false );
			} );
		} );

		document.addEventListener( 'keydown', ( event ) => {
			if ( event.key === 'Escape' ) {
				toggleAgenda( false );
			}
		} );

		if ( agendaModal ) {
			agendaModal.addEventListener( 'click', ( event ) => {
				if ( event.target instanceof HTMLElement && event.target.dataset.closeAgenda !== undefined ) {
					toggleAgenda( false );
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
			const acceptButton = cookieBanner.querySelector( '.cookie-accept' );
			const declineButton = cookieBanner.querySelector( '.cookie-decline' );

			const dismissBanner = () => {
				cookieBanner.remove();
			};

			if ( acceptButton ) {
				acceptButton.addEventListener( 'click', dismissBanner );
			}

			if ( declineButton ) {
				declineButton.addEventListener( 'click', dismissBanner );
			}
		}
	} );
} )();
