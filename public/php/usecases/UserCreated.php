<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class UserCreated {

    private array $role_to_membership = [
        'Manager' => FormIds::MANAGERS_MEMBERSHIP_ID,
        'Support' => FormIds::SUPPORT_MEMBERSHIP_ID,
    ];

    public function handle_user_created( int $user_id ): void {

        $user = get_userdata( $user_id );

        if ( ! $user ) {
            SMPLFY_Log::error( 'Could not load user data for user_id: ' . $user_id );
            return;
        }

        foreach ( $user->roles as $role ) {

            if ( isset( $this->role_to_membership[ $role ] ) ) {

                $membership_id = $this->role_to_membership[ $role ];

                MembershipTransaction::assign_membership_if_not_active( $user_id, $membership_id );

                return;
            }
        }

        SMPLFY_Log::error( 'No membership mapping found for user_id: ' . $user_id . ' with roles: ' . implode( ', ', $user->roles ) );
    }
}