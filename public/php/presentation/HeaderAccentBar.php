<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class HeaderAccentBar {

    private static bool $injected = false;

    public function __construct() {
        $this->register_filters();
    }

    private function register_filters(): void {
        add_filter(
            'genesis_markup_site-header_close',
            [ $this, 'inject_accent_bar' ],
            10,
            1
        );
    }

    public function inject_accent_bar( string $close_html ): string {

        if ( self::$injected ) {
            return $close_html;
        }
        self::$injected = true;

        return '<div class="site-header-accent-bar" role="presentation" aria-hidden="true">'
            . '<div class="site-header-accent-bar__track"></div>'
            . '</div>'
            . $close_html;
    }
}