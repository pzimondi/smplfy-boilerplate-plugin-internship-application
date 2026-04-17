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
         * Use gform_user_registered instead.
         * This fires as soon as the account is created, ensuring the ID
         * exists before any other workflow steps (like Support) load.
         */
        add_action( 'gform_user_registered', function( $user_id, $feed, $entry ) {
            // Only target Form 2
            if ( $entry['form_id'] == 2 ) {
                // 1. Link the entry 'owner'
                \GFAPI::update_entry_property( $entry['id'], 'created_by', $user_id );

                // 2. Explicitly fill Field 110 with the correct format
                \GFAPI::update_entry_field( $entry['id'], '110', "user_id|{$user_id}" );
            }
        }, 10, 3 );
    }
}
