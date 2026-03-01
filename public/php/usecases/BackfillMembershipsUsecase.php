<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BackfillMembershipsUsecase {

    /**
     * Same role → membership map as UserCreatedUsecase.
     * Runs on every page load but is safe because
     * assign_membership_if_not_active skips users
     * who already have an active transaction.
     */
    private array $role_to_membership = [
        'Manager' => FormIds::MANAGERS_MEMBERSHIP_ID,
        'Support' => FormIds::SUPPORT_MEMBERSHIP_ID,
    ];

    public function run(): void {

        foreach ( $this->role_to_membership as $role => $membership_id ) {

            $users = get_users( [ 'role' => $role ] );

            foreach ( $users as $user ) {
                MembershipTransactionUsecase::assign_membership_if_not_active(
                    $user->ID,
                    $membership_id
                );
            }
        }
    }
}