<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DeleteUserUsecase {

    /**
     * Fired before a user is deleted from WordPress.
     * Finds all MemberPress transactions for the user and deletes them
     * so they don't remain in MemberPress showing as "Deleted" user.
     */
    public function handle_user_deleted( int $user_id ): void {

        if ( ! class_exists( 'MeprTransaction' ) ) {
            error_log( 'SMPLFY: MemberPress not available.' );
            return;
        }

        $transactions = \MeprTransaction::get_all_by_user_id( $user_id );

        if ( empty( $transactions ) ) {
            error_log( 'SMPLFY: No transactions found for user_id: ' . $user_id );
            return;
        }

        foreach ( $transactions as $transaction ) {
            $transaction->destroy();
            error_log( 'SMPLFY: Deleted transaction ID: ' . $transaction->id . ' for user_id: ' . $user_id );
        }
    }
}