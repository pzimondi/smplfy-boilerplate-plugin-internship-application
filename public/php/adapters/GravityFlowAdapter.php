<?php

namespace SMPLFY\boilerplate;

class GravityFlowAdapter {

    private WorkflowNotifications $workflowNotifications;

    public function __construct( WorkflowNotifications $workflowNotifications ) {
        $this->workflowNotifications = $workflowNotifications;

        $this->register_hooks();
    }

    private function register_hooks(): void {
        add_action(
            'gravityflow_step_complete',
            [ $this->workflowNotifications, 'handle_step_complete' ],
            10,
            4
        );

        // Link the entry to the new user after Gravity Flow finishes registration.
        // This makes the "User (Created by)" assignee option appear.
        add_action(
            'gravityflow_user_registration_complete',
            function( $user_id, $entry ) {
                if ( $user_id ) {
                    \GFAPI::update_entry_property( $entry['id'], 'created_by', $user_id );
                }
            },
            10,
            2
        );
    }
}
