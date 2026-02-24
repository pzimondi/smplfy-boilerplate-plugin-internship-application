<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MembershipTransactionUsecase {

    /**
     * Creates a MemberPress transaction for a user on a given membership.
     * Skips if the user is already active on that membership.
     * Calls send_signup_notices() so MemberPress fires its internal events:
     *   - mepr-event-member-signup-completed
     *   - mepr-event-transaction-completed
     * These events are what MemberPress uses to apply login redirects,
     * send welcome emails, and mark the member as fully signed up.
     */
    public static function assign_membership_if_not_active( int $user_id, int $membership_id ): void {

        if ( ! class_exists( 'MeprTransaction' ) ) {
            error_log( 'SMPLFY: MemberPress MeprTransaction class not found.' );
            return;
        }

        $member = new \MeprUser( $user_id );

        if ( $member->is_active_on_membership( $membership_id ) ) {
            error_log( 'SMPLFY: User ' . $user_id . ' already active on membership ' . $membership_id . '. Skipping.' );
            return;
        }

        $transaction             = new \MeprTransaction();
        $transaction->user_id    = $user_id;
        $transaction->product_id = $membership_id;
        $transaction->amount     = 0.00;
        $transaction->total      = 0.00;
        $transaction->tax_amount = 0.00;
        $transaction->tax_rate   = 0.000;
        $transaction->status     = \MeprTransaction::$complete_str;
        $transaction->txn_type   = \MeprTransaction::$payment_str;
        $transaction->gateway    = 'manual';
        $transaction->created_at = gmdate( 'Y-m-d H:i:s' );
        $transaction->expires_at = \MeprUtils::ts_to_mysql_date( 0 ); // 0 = lifetime

        // Save transaction to database
        $transaction->store();

        // Fire MemberPress internal events: mepr-event-member-signup-completed
        // and mepr-event-transaction-completed. This is what MemberPress uses
        // to apply login redirects and send welcome emails.
        $transaction->send_signup_notices();

        error_log( 'SMPLFY: Transaction created and signup notices sent for user_id: ' . $user_id . ' on membership_id: ' . $membership_id );
    }
}