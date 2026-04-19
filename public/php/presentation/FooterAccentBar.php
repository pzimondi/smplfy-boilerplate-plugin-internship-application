<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FooterAccentBar {

    private static bool $injected = false;

    public function __construct() {
        $this->register_filters();
    }

    private function register_filters(): void {

        add_filter(
            'genesis_markup_site-footer_open',
            [ $this, 'inject_accent_bar' ],
            10,
            1
        );
    }

    public function inject_accent_bar( string $open_html ): string {

        if ( self::$injected ) {
            return $open_html;
        }
        self::$injected = true;

        return $open_html
            . '<div class="site-footer-accent-bar" role="presentation" aria-hidden="true">'
            . '<div class="site-footer-accent-bar__track"></div>'
            . '</div>';
    }
}