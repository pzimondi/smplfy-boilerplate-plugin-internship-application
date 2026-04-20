<?php
/*
 *
 * * Enqueue scripts on Website
 *
 *  */

namespace SMPLFY\boilerplate;

function enqueue_boilerplate_frontend_scripts() {
    global $current_user;
    global $post;
    $current_user = wp_get_current_user();

    wp_enqueue_script('heartbeat');

    // Heartbeat example script (leave untouched)
    wp_register_script(
        'smplfy-demo-heartbeat-script',
        SMPLFY_NAME_PLUGIN_URL . 'public/js/wp-heartbeat-example.js',
        ['jquery', 'heartbeat'],
        null,
        true
    );

    wp_register_style(
        'smplfy-demo-frontend-styles',
        SMPLFY_NAME_PLUGIN_URL . 'public/css/frontend.css'
    );

    // Enqueue your public CSS
    wp_enqueue_style('smplfy-demo-frontend-styles');

    // Header scroll behavior — hide on scroll down, show on scroll up.
    // Also handles back-to-top button visibility.
    wp_enqueue_script(
        'smplfy-header-scroll',
        SMPLFY_NAME_PLUGIN_URL . 'public/js/header-scroll.js',
        [],
        '1.0.0',
        true
    );

    // Only load heartbeat script on page ID 999 (demo logic)
    if ( isset($post->ID) && $post->ID == 999 ) {
        wp_enqueue_script('smplfy-demo-heartbeat-script');

        wp_localize_script('smplfy-demo-heartbeat-script', 'heartbeat_object',
            [
                'user_id' => $current_user->ID,
                'page_id' => $post->ID
            ]
        );
    }
}

add_action('wp_enqueue_scripts', 'SMPLFY\boilerplate\enqueue_boilerplate_frontend_scripts');


/**
 * Hide Gravity Forms buttons that conditional logic has marked as hidden.
 * GF sets data-conditional-logic="hidden" + disabled + style="display:none" on the
 * input, but our .gform_button { display: inline-flex !important } CSS overrides
 * the inline display:none. This JS guard sets the display via inline !important
 * which beats every stylesheet, and also hides empty footer wrappers so they
 * don't render as "ghost" buttons. Re-runs on every GF conditional logic event.
 */
function hide_conditionally_hidden_gf_buttons() {
    ?>
    <script>
    (function () {
        function hideHidden() {
            document.querySelectorAll(
                'input[data-conditional-logic="hidden"], button[data-conditional-logic="hidden"]'
            ).forEach(function (el) {
                el.style.setProperty('display', 'none', 'important');
                var parent = el.closest('.gform-page-footer, .gform_page_footer, .gform_footer');
                if (parent) {
                    var hasVisible = parent.querySelector(
                        'input:not([data-conditional-logic="hidden"]):not([style*="display: none"]), ' +
                        'button:not([data-conditional-logic="hidden"]):not([style*="display: none"])'
                    );
                    if (!hasVisible) parent.style.setProperty('display', 'none', 'important');
                }
            });
        }
        if (document.readyState !== 'loading') hideHidden();
        else document.addEventListener('DOMContentLoaded', hideHidden);
        if (window.jQuery) {
            jQuery(document).on('gform_post_conditional_logic', hideHidden);
        }
        if (window.MutationObserver) {
            new MutationObserver(hideHidden).observe(document.body, {
                childList: true, subtree: true,
                attributes: true, attributeFilter: ['data-conditional-logic', 'disabled', 'style']
            });
        }
    })();
    </script>
    <?php
}

add_action('wp_footer', 'SMPLFY\boilerplate\hide_conditionally_hidden_gf_buttons', 100);
