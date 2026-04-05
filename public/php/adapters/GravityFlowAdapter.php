<?php

namespace SMPLFY\boilerplate;

class GravityFlowAdapter {

    private WorkflowNotifications $WorkflowNotifications;

    public function __construct( WorkflowNotifications $WorkflowNotifications ) {
        $this->WorkflowNotifications = $WorkflowNotifications;

        $this->register_hooks();
    }

    private function register_hooks(): void {
        add_action(
            'gravityflow_step_complete',
            [ $this->WorkflowNotifications, 'handle_step_complete' ],
            10,
            4
        );
    }
}