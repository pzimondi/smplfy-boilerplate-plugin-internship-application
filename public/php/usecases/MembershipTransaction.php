<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MembershipTransaction {

    public static function assign_membership_if_not_active( int $user_id, int $membership_id ): void {

        if ( ! class_exists( 'MeprTransaction' ) ) {
            \SmplfyCore\SMPLFY_Log::error( 'MemberPress MeprTransaction class not found.' );
            return;
        }

        $member = new \MeprUser( $user_id );

        if ( $member->is_active_on_membership( $membership_id ) ) {
            \SmplfyCore\SMPLFY_Log::error( 'User ' . $user_id . ' already active on membership ' . $membership_id . '. Skipping.' );
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
        $transaction->expires_at = '0000-00-00 00:00:00';

        $transaction->store();
        $transaction->send_signup_notices();

        \SmplfyCore\SMPLFY_Log::error( 'Transaction created for user_id: ' . $user_id . ' on membership_id: ' . $membership_id );
    }
}