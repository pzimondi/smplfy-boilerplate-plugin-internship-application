<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WordpressAdapter {

    private UserCreated         $userCreated;
    private BackfillMemberships $backfillMemberships;
    private DeleteUser          $deleteUser;
    private LoginRedirect       $loginRedirect;

    public function __construct(
        UserCreated         $userCreated,
        BackfillMemberships $backfillMemberships,
        DeleteUser          $deleteUser,
        LoginRedirect       $loginRedirect
    ) {
        $this->userCreated         = $userCreated;
        $this->backfillMemberships = $backfillMemberships;
        $this->deleteUser          = $deleteUser;
        $this->loginRedirect       = $loginRedirect;

        $this->register_hooks();
        $this->register_filters();
    }

    private function register_hooks(): void {

        add_action(
            'user_register',
            [ $this->userCreated, 'handle_user_created' ],
            10,
            1
        );

        add_action(
            'profile_update',
            [ $this->userCreated, 'handle_user_created' ],
            10,
            1
        );

        add_action(
            'edit_user_profile_update',
            [ $this->userCreated, 'handle_user_created' ],
            10,
            1
        );

        add_action(
            'delete_user',
            [ $this->deleteUser, 'handle_user_deleted' ],
            1,
            1
        );

        add_action(
            'init',
            function() {
                if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                    return;
                }
                $this->backfillMemberships->run();
            }
        );
    }

    private function register_filters(): void {

        add_filter(
            'login_redirect',
            [ $this->loginRedirect, 'handle_login_redirect' ],
            99,
            3
        );
    }
}