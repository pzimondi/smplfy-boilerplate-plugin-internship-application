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
         * Fired after the User Registration step.
         * Links the Applicant ID to Field 110 so their inbox works later.
         */
        add_action(
            'gravityflow_post_user_registration',
            function( $user_id, $entry ) {
                // Only run for Form 2 to avoid conflicts elsewhere
                if ( $user_id && $entry['form_id'] == 2 ) {

                    // 1. Set the Assignee Field (ID 110) for the Applicant's Inbox
                    \GFAPI::update_entry_field( $entry['id'], '110', "user_id|{$user_id}" );

                    // 2. Claim entry ownership
                    \GFAPI::update_entry_property( $entry['id'], 'created_by', $user_id );
                }
            },
            10,
            2
        );
    }
}
