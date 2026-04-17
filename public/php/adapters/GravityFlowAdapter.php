<?php

namespace SMPLFY\boilerplate;

class GravityFlowAdapter {

    private WorkflowNotifications $workflowNotifications;

    public function __construct( WorkflowNotifications $workflowNotifications ) {
        $this->workflowNotifications = $workflowNotifications;
        $this->register_hooks();
    }

    private function register_hooks(): void {
        add_action( 'gravityflow_step_complete', [ $this->workflowNotifications, 'handle_step_complete' ], 10, 4 );

        /**
         * Fired exactly when the user account is created.
         * This links the ID before the Support or Manager ever see the entry.
         */
        add_action( 'gform_user_registered', function( $user_id, $feed, $entry ) {
            // Target Form 2 only
            if ( $entry['form_id'] == 2 ) {
                // 1. Force the Assignee Field (110)
                \GFAPI::update_entry_field( $entry['id'], '110', "user_id|{$user_id}" );

                // 2. Link the entry 'owner' property
                \GFAPI::update_entry_property( $entry['id'], 'created_by', $user_id );
            }
        }, 10, 3 );
    }
}
