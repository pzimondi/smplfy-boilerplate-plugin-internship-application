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

        // This is the CRITICAL hook that fills the field before Step #91 is reached
        add_action(
            'gravityflow_post_user_registration',
            function( $user_id, $entry ) {
                if ( $user_id ) {
                    // Update field 110 so the workflow has an assignee to land on
                    \GFAPI::update_entry_field( $entry['id'], '110', "user_id|{$user_id}" );

                    // Update entry owner for the inbox security filter
                    \GFAPI::update_entry_property( $entry['id'], 'created_by', $user_id );
                }
            },
            10,
            2
        );
    }
}
