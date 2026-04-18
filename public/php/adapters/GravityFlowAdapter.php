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

        add_action(
            'gform_user_registered',
            function( $user_id, $feed, $entry, $user_pass ) {
                if ( $user_id && (int) $entry['form_id'] === FormIds::INTERNSHIP_APPLICATION_FORM_ID ) {
                    \GFAPI::update_entry_field( $entry['id'], '110', "user_id|{$user_id}" );
                }
            },
            10,
            4
        );
    }
}