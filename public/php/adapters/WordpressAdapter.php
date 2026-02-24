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

        // Fires when a user is created manually in WP Admin
        add_action(
            'user_register',
            [ $this->userCreatedUsecase, 'handle_user_created' ],
            10,
            1
        );

        // Runs once to backfill existing users missing transactions
        add_action(
            'init',
            [ $this->backfillMembershipsUsecase, 'run' ]
        );
    }
}