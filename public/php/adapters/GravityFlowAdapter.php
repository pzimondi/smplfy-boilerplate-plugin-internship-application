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
            'gravityflow_post_user_registration',
            function( $user_id, $entry ) {
                if ( $user_id ) {
                    \GFAPI::update_entry_field( $entry['id'], '110', "user_id|{$user_id}" );
                }
            },
            10,
            2
        );
    }
}