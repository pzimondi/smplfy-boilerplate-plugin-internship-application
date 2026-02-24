<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WordpressAdapter {

    private UserCreatedUsecase         $userCreatedUsecase;
    private BackfillMembershipsUsecase $backfillMembershipsUsecase;

    public function __construct(
        UserCreatedUsecase         $userCreatedUsecase,
        BackfillMembershipsUsecase $backfillMembershipsUsecase
    ) {
        $this->userCreatedUsecase         = $userCreatedUsecase;
        $this->backfillMembershipsUsecase = $backfillMembershipsUsecase;

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

        // Fires when a user is saved via WP Admin user edit screen
        add_action(
            'edit_user_profile_update',
            [ $this->userCreatedUsecase, 'handle_user_created' ],
            10,
            1
        );

        // Runs on every page load to catch any users missing transactions
        add_action(
            'init',
            [ $this->backfillMembershipsUsecase, 'run' ]
        );
    }
}