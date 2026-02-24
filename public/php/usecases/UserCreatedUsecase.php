<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class UserCreatedUsecase {

    /**
     * Maps WordPress roles to MemberPress membership IDs.
     * When a user is created manually in WP Admin with one of these roles,
     * they will automatically get the corresponding membership transaction,
     * which enables the MemberPress login redirect for their role.
     * Add new roles here as the system grows.
     */
    private array $role_to_membership = [
        'Manager' => FormIds::MANAGERS_MEMBERSHIP_ID,
    ];

    /**
     * Fired by WordpressAdapter via the user_register hook.
     * Checks the new user's role and assigns the matching membership.
     */
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

                // Only assign the first matching role's membership
                return;
            }
        }

        error_log( 'SMPLFY: No membership mapping found for user_id: ' . $user_id . ' with roles: ' . implode( ', ', $user->roles ) );
    }
}