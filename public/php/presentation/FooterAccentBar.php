<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Injects the footer accent bar markup as the first child of
 * <footer class="site-footer">, outside the .wrap container,
 * so it can span full viewport width.
 *
 * Per-page coloring is handled entirely by CSS via body classes.
 * This class only places the markup.
 */
class FooterAccentBar {

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
        return $open_html
            . '<div class="site-footer-accent-bar" role="presentation" aria-hidden="true">'
            . '<div class="site-footer-accent-bar__track"></div>'
            . '</div>';
    }
}