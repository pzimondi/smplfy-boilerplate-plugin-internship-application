<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BackfillMemberships {

    private array $role_to_membership = [
        'Manager' => FormIds::MANAGERS_MEMBERSHIP_ID,
        'Support' => FormIds::SUPPORT_MEMBERSHIP_ID,
    ];

    public function run(): void {

        foreach ( $this->role_to_membership as $role => $membership_id ) {

            $users = get_users( [ 'role' => $role ] );

            foreach ( $users as $user ) {
                MembershipTransaction::assign_membership_if_not_active(
                    $user->ID,
                    $membership_id
                );
            }
        }
    }
}