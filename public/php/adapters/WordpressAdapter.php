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

        add_action(
            'user_register',
            [ $this->userCreatedUsecase, 'handle_user_created' ],
            10,
            1
        );

        add_action(
            'profile_update',
            [ $this->userCreatedUsecase, 'handle_user_created' ],
            10,
            1
        );

        add_action(
            'edit_user_profile_update',
            [ $this->userCreatedUsecase, 'handle_user_created' ],
            10,
            1
        );

        add_action(
            'delete_user',
            [ $this->deleteUserUsecase, 'handle_user_deleted' ],
            1,
            1
        );

        // Skip backfill during AJAX to prevent send_signup_notices()
        // from interfering with the form submission confirmation
        add_action(
            'init',
            function() {
                if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                    return;
                }
                $this->backfillMembershipsUsecase->run();
            }
        );
    }
}