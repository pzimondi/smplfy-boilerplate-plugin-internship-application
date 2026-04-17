<?php

namespace SMPLFY\boilerplate;

class GravityFlowAdapter {

    private WorkflowNotifications $workflowNotifications;

    public function __construct( WorkflowNotifications $workflowNotifications ) {
        $this->workflowNotifications = $workflowNotifications;
        $this->register_hooks();
    }

    private function register_hooks(): void {

        // Your existing notification hook
        add_action(
            'gravityflow_step_complete',
            [ $this->workflowNotifications, 'handle_step_complete' ],
            10,
            4
        );

        /**
         * RECOMMENDED FIX: Link applicant only when the Registration step finishes.
         * This prevents infinite loading for other roles (Support/Manager).
         */
        add_action( 'gravityflow_step_complete', function( $step_id, $entry_id, $form_id, $status ) {
            $step = \Gravity_Flow_API::get_step( $step_id );

            // Only run if the completed step was a 'user_registration' type
            if ( $step && $step->get_type() == 'user_registration' && $status == 'complete' ) {

                $entry = \GFAPI::get_entry( $entry_id );
                // The registration step stores the new User ID in the entry meta
                $new_user_id = gform_get_meta( $entry_id, 'workflow_user_registration_user_id' );

                if ( $new_user_id ) {
                    // 1. Link entry ownership property
                    \GFAPI::update_entry_property( $entry_id, 'created_by', $new_user_id );

                    // 2. Populate Field 110 for the Applicant Inbox
                    \GFAPI::update_entry_field( $entry_id, '110', "user_id|{$new_user_id}" );
                }
            }
        }, 10, 4 );
    }
}
