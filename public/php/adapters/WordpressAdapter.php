<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WordpressAdapter {

    private UserCreatedUsecase         $userCreatedUsecase;
    private BackfillMembershipsUsecase $backfillMembershipsUsecase;
    private DeleteUserUsecase          $deleteUserUsecase;

    public function __construct(
        UserCreatedUsecase         $userCreatedUsecase,
        BackfillMembershipsUsecase $backfillMembershipsUsecase,
        DeleteUserUsecase          $deleteUserUsecase
    ) {
        $this->userCreatedUsecase         = $userCreatedUsecase;
        $this->backfillMembershipsUsecase = $backfillMembershipsUsecase;
        $this->deleteUserUsecase          = $deleteUserUsecase;

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

        // Fires before a user is deleted — removes their MemberPress transactions
        add_action(
            'delete_user',
            [ $this->deleteUserUsecase, 'handle_user_deleted' ],
            10,
            1
        );

        // Runs on every page load to catch users missing transactions
        add_action(
            'init',
            [ $this->backfillMembershipsUsecase, 'run' ]
        );
    }
}