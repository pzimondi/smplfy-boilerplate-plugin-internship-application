/**
 * Header scroll behavior — hide on scroll down, show on scroll up.
 *
 * - At top of page: header always visible
 * - Scrolling down past threshold: header hides
 * - Scrolling up: header shows
 *
 * Also handles back-to-top button visibility (consolidates scroll logic
 * into one listener for performance).
 */
( function () {
    'use strict';

    var lastScrollY   = window.scrollY || window.pageYOffset;
    var ticking       = false;
    var body          = document.body;

    var TOP_THRESHOLD = 80;   // px from top before hide logic kicks in
    var SCROLL_DELTA  = 6;    // ignore sub-pixel jitter

    function onScroll() {
        var currentScrollY = window.scrollY || window.pageYOffset;
        var delta          = currentScrollY - lastScrollY;
        var scrollBtn      = document.getElementById( 'scroll-to-top' );

        // Back-to-top button — show after ~80% of one viewport scrolled
        if ( scrollBtn ) {
            if ( currentScrollY > window.innerHeight * 0.8 ) {
                scrollBtn.classList.add( 'visible' );
            } else {
                scrollBtn.classList.remove( 'visible' );
            }
        }

        // Ignore tiny scroll jitters
        if ( Math.abs( delta ) < SCROLL_DELTA ) {
            ticking = false;
            return;
        }

        if ( currentScrollY < TOP_THRESHOLD ) {
            // Near top — always show
            body.classList.remove( 'header-hidden' );
        } else if ( delta > 0 ) {
            // Scrolling down
            body.classList.add( 'header-hidden' );
        } else {
            // Scrolling up
            body.classList.remove( 'header-hidden' );
        }

        lastScrollY = currentScrollY;
        ticking     = false;
    }

    window.addEventListener( 'scroll', function () {
        if ( ! ticking ) {
            window.requestAnimationFrame( onScroll );
            ticking = true;
        }
    }, { passive: true } );
} )();