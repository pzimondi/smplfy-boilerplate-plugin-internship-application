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
         * Use gform_after_submission to bridge the gap between MemberPress/Registration
         * and the Workflow. This ensures Field 110 is filled before Support or Applicant see it.
         */
        add_action( 'gform_after_submission_2', function( $entry, $form ) {
            // 1. Get the newly created User ID from the User Registration feed
            $user_id = gform_get_meta( $entry['id'], 'workflow_user_registration_user_id' );

            // If meta isn't ready, try to find the user by the email field (Field ID 3 - adjust if different)
            if ( ! $user_id ) {
                $user = get_user_by( 'email', $entry['3'] );
                $user_id = $user ? $user->ID : false;
            }

            if ( $user_id ) {
                // 2. Populate Field 110 (Assignee) immediately
                \GFAPI::update_entry_field( $entry['id'], '110', "user_id|{$user_id}" );

                // 3. Link entry ownership so the inbox filter recognizes the user
                \GFAPI::update_entry_property( $entry['id'], 'created_by', $user_id );
            }
        }, 20, 2 ); // Priority 20 to ensure it runs AFTER User Registration/MemberPress feeds
    }
}
