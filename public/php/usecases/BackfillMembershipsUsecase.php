<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BackfillMembershipsUsecase {

    /**
     * Same role → membership map as UserCreatedUsecase.
     * Kept in sync so existing users get the same treatment as new ones.
     */
    private array $role_to_membership = [
        'applicant' => FormIds::APPLICANTS_MEMBERSHIP_ID,
        'intern'    => FormIds::INTERNS_MEMBERSHIP_ID,
        'manager'   => FormIds::MANAGERS_MEMBERSHIP_ID,
    ];

    /**
     * Fired once on plugin load via the init hook.
     * Loops through all existing users with mapped roles and creates
     * any missing MemberPress transactions so they get proper login redirects.
     * Uses a wp_options flag so it only ever runs once.
     * To re-run: delete 'smplfy_membership_backfill_done' from wp_options.
     */
    public function run(): void {

        if ( get_option( 'smplfy_membership_backfill_done' ) ) {
            return;
        }

        error_log( 'SMPLFY: Starting membership backfill for existing users...' );

        $processed = 0;

        foreach ( $this->role_to_membership as $role => $membership_id ) {

            $users = get_users( [ 'role' => $role ] );

            foreach ( $users as $user ) {
                MembershipTransactionUsecase::assign_membership_if_not_active(
                    $user->ID,
                    $membership_id
                );
                $processed++;
            }
        }

        update_option( 'smplfy_membership_backfill_done', true );

        error_log( 'SMPLFY: Backfill complete. Processed ' . $processed . ' users.' );
    }
}