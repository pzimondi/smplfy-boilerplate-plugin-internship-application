<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GravityFlowAdapter {

    private TaskEvaluationCompleteUsecase $taskEvaluationCompleteUsecase;

    public function __construct(
        TaskEvaluationCompleteUsecase $taskEvaluationCompleteUsecase
    ) {
        $this->taskEvaluationCompleteUsecase = $taskEvaluationCompleteUsecase;

        $this->register_hooks();
    }

    private function register_hooks(): void {

        // Fires when any GravityFlow step is completed
        add_action(
            'gravityflow_step_complete',
            [ $this->taskEvaluationCompleteUsecase, 'handle_step_complete' ],
            10,
            1
        );
    }
}