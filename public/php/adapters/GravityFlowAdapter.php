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

        /**
         * Use 'post_user_registration' to ensure the ID is saved
         * BEFORE the workflow evaluates the next step (#91).
         */
        add_action(
            'gravityflow_post_user_registration',
            function( $user_id, $entry ) {
                if ( $user_id ) {
                    // 1. Set the Assignee Field (ID 110) in the required format
                    \GFAPI::update_entry_field( $entry['id'], '110', "user_id|{$user_id}" );

                    // 2. Link entry ownership for inbox filtering
                    \GFAPI::update_entry_property( $entry['id'], 'created_by', $user_id );
                }
            },
            10,
            2
        );
    }
}
