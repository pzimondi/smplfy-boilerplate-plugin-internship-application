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