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

        $args['created_by'] = get_current_user_id();

        return $args;
    }

    public function filter_assignee_notification( bool $is_assignee, $assignee, $step ): bool {

        if ( ! $step ) {
            return $is_assignee;
        }

        $entry      = $step->get_entry();
        $created_by = (int) rgar( $entry, 'created_by' );
        $user_id    = (int) $assignee->get_user_id();

        if ( ! $created_by || ! $user_id ) {
            return $is_assignee;
        }

        return $created_by === $user_id;
    }
}