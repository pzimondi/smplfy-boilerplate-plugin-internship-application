<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WorkflowInbox {

    public function filter_inbox_entries( array $args ): array {

        if ( ! is_user_logged_in() ) {
            return $args;
        }

        $current_user  = wp_get_current_user();
        $current_email = $current_user->user_email;

        $args['field_filters'][] = [
            'key'      => FormIds::INTERNSHIP_APPLICATION_EMAIL_FIELD_ID,
            'value'    => $current_email,
            'operator' => 'is',
        ];

        return $args;
    }
}