<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class UserCreatedUsecase {

    private array $role_to_membership = [
        'manager' => FormIds::MANAGERS_MEMBERSHIP_ID,
    ];

    public function handle_user_created( int $user_id ): void {

        $user = get_userdata( $user_id );

        if ( ! $user ) {
            error_log( 'SMPLFY: Could not load user data for user_id: ' . $user_id );
            return;
        }

        foreach ( $user->roles as $role ) {

            if ( isset( $this->role_to_membership[ $role ] ) ) {

                $membership_id = $this->role_to_membership[ $role ];

                MembershipTransactionUsecase::assign_membership_if_not_active( $user_id, $membership_id );

                return;
            }
        }

        error_log( 'SMPLFY: No membership mapping found for user_id: ' . $user_id . ' with roles: ' . implode( ', ', $user->roles ) );
    }
}