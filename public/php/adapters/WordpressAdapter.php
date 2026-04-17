<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WordpressAdapter {

    private UserCreated         $userCreated;
    private BackfillMemberships $backfillMemberships;
    private DeleteUser          $deleteUser;

    public function __construct(
        UserCreated         $userCreated,
        BackfillMemberships $backfillMemberships,
        DeleteUser          $deleteUser
    ) {
        $this->userCreated         = $userCreated;
        $this->backfillMemberships = $backfillMemberships;
        $this->deleteUser          = $deleteUser;

        $this->register_hooks();
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

        add_action(
            'gform_user_registered',
            function( $user_id, $feed, $entry, $user_pass ) {
                GFAPI::update_entry_property( $entry['id'], 'created_by', $user_id );
            },
            10,
            4
        );
    }
}