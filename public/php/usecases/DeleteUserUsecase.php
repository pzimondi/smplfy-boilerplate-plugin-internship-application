<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DeleteUserUsecase {

    /**
     * Fired before a user is deleted from WordPress.
     * Runs at priority 1 to ensure it fires before MemberPress
     * nullifies the user_id on the transaction record.
     * Finds all transactions for the user and deletes them
     * so they don't remain in MemberPress showing as "Deleted".
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
            $txn = new \MeprTransaction( $transaction->id );
            $txn->destroy();
            error_log( 'SMPLFY: Deleted transaction ID: ' . $transaction->id . ' for user_id: ' . $user_id );
        }
    }
}