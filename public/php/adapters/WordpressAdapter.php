<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WordpressAdapter {

    private UserCreatedUsecase            $userCreatedUsecase;
    private BackfillMembershipsUsecase    $backfillMembershipsUsecase;
    private DeleteUserUsecase             $deleteUserUsecase;
    private TaskSubmissionAssigneeUsecase $taskSubmissionAssigneeUsecase;

    public function __construct(
        UserCreatedUsecase            $userCreatedUsecase,
        BackfillMembershipsUsecase    $backfillMembershipsUsecase,
        DeleteUserUsecase             $deleteUserUsecase,
        TaskSubmissionAssigneeUsecase $taskSubmissionAssigneeUsecase
    ) {
        $this->userCreatedUsecase            = $userCreatedUsecase;
        $this->backfillMembershipsUsecase    = $backfillMembershipsUsecase;
        $this->deleteUserUsecase             = $deleteUserUsecase;
        $this->taskSubmissionAssigneeUsecase = $taskSubmissionAssigneeUsecase;

        $this->register_hooks();
    }

    private function register_hooks(): void {

        // Fires when a new user is created in WP Admin
        add_action(
            'user_register',
            [ $this->userCreatedUsecase, 'handle_user_created' ],
            10,
            1
        );

        // Fires when an existing user is updated — catches role changes
        add_action(
            'profile_update',
            [ $this->userCreatedUsecase, 'handle_user_created' ],
            10,
            1
        );

        // Fires when an admin saves changes to another user's profile
        add_action(
            'edit_user_profile_update',
            [ $this->userCreatedUsecase, 'handle_user_created' ],
            10,
            1
        );

        // Priority 1 ensures our code runs BEFORE MemberPress nullifies the user_id
        // on the transaction — allowing us to find and delete transactions first
        add_action(
            'delete_user',
            [ $this->deleteUserUsecase, 'handle_user_deleted' ],
            1,
            1
        );

        // Runs on every page load to catch users missing transactions
        add_action(
            'init',
            [ $this->backfillMembershipsUsecase, 'run' ]
        );

        // Filter — dynamically assigns Submit Task Evidence step
        // to the specific applicant who submitted the form
        add_filter(
            'gravityflow_assignees',
            [ $this->taskSubmissionAssigneeUsecase, 'filter_assignees' ],
            10,
            2
        );
    }
}