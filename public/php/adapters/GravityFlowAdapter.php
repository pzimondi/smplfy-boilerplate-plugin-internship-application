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
         * Safely links the newly registered user to the entry.
         * This handles both the 'created_by' property and updates
         * a specific Assignee field if needed.
         */
        add_action(
            'gravityflow_user_registration_complete',
            function( $user_id, $entry ) {
                if ( ! $user_id ) {
                    return;
                }

                // 1. Link entry 'owner' to the new user ID
                \GFAPI::update_entry_property( $entry['id'], 'created_by', $user_id );

                /**
                 * 2. OPTIONAL: Update a dedicated Assignee Field
                 * If you add an 'Assignee' field to your form, replace 'XX' with its ID.
                 * Gravity Flow uses the 'user_id|ID' format for these fields.
                 */
                // \GFAPI::update_entry_field( $entry['id'], 'XX', "user_id|{$user_id}" );
            },
            10,
            2
        );
    }
}
