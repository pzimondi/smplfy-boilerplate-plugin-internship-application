<?php

namespace SMPLFY\boilerplate;

class GravityFlowAdapter {

    private WorkflowNotificationsUsecase $workflowNotificationsUsecase;

    public function __construct( WorkflowNotificationsUsecase $workflowNotificationsUsecase ) {
        $this->workflowNotificationsUsecase = $workflowNotificationsUsecase;

        $this->register_hooks();
    }

    private function register_hooks(): void {
        add_action(
            'gravityflow_step_complete',
            [ $this->workflowNotificationsUsecase, 'handle_step_complete' ],
            10,
            4
        );
    }
}
