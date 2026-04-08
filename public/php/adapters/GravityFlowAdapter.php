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
    }
}