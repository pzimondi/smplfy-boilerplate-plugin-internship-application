<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sends a Google Chat notification when a WP E-Signature document is signed.
 *
 * Registers two hooks simultaneously — whichever one fires on this
 * version of WP E-Signature will trigger the notification.
 * A flag prevents duplicate notifications if both hooks fire.
 */
class ESignatureNotificationsUsecase {

    private string $webhook_url = 'https://chat.googleapis.com/v1/spaces/AAQAoIBJG0w/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=Qui-5Y4sTCw9r6ZL5RKEh73nzVrapEiTBF9scx487bA';

    private bool $notification_sent = false;

    public function __construct() {
        // Hook 1: esig_document_signed( $args, $document_id )
        // $args contains signer_name and signer_email
        add_action(
            'esig_document_signed',
            [ $this, 'handle_esig_document_signed' ],
            10,
            2
        );

        // Hook 2: wp_esignature_after_document_signed( $document_id, $invite_hash, $signature_id )
        add_action(
            'wp_esignature_after_document_signed',
            [ $this, 'handle_wp_esignature_after_signed' ],
            10,
            3
        );
    }

    /**
     * Handler for esig_document_signed hook.
     * $args array contains signer_name and signer_email.
     */
    public function handle_esig_document_signed( $args, $document_id ): void {

        if ( $this->notification_sent ) {
            return;
        }

        try {

            $signer_name  = ! empty( $args['signer_name'] )  ? (string) $args['signer_name']  : '';
            $signer_email = ! empty( $args['signer_email'] ) ? (string) $args['signer_email'] : '';

            // Fall back to current user if args are empty
            if ( empty( $signer_name ) ) {
                $user = wp_get_current_user();
                if ( $user && $user->ID ) {
                    $signer_name  = trim( $user->first_name . ' ' . $user->last_name );
                    $signer_email = $user->user_email;
                    if ( empty( $signer_name ) ) {
                        $signer_name = $user->display_name;
                    }
                }
            }

            $signer_name  = ! empty( $signer_name )  ? $signer_name  : 'Unknown';
            $signer_email = ! empty( $signer_email ) ? $signer_email : 'Unknown';

            $this->send_notification( $signer_name, $signer_email );

        } catch ( \Throwable $e ) {
            error_log( 'SMPLFY ESignatureNotifications error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() );
        }
    }

    /**
     * Handler for wp_esignature_after_document_signed hook.
     * Gets signer details from current user.
     */
    public function handle_wp_esignature_after_signed( $document_id, $invite_hash, $signature_id ): void {

        if ( $this->notification_sent ) {
            return;
        }

        try {

            $signer_name  = '';
            $signer_email = '';

            $user = wp_get_current_user();
            if ( $user && $user->ID ) {
                $signer_name  = trim( $user->first_name . ' ' . $user->last_name );
                $signer_email = $user->user_email;
                if ( empty( $signer_name ) ) {
                    $signer_name = $user->display_name;
                }
            }

            $signer_name  = ! empty( $signer_name )  ? $signer_name  : 'Unknown';
            $signer_email = ! empty( $signer_email ) ? $signer_email : 'Unknown';

            $this->send_notification( $signer_name, $signer_email );

        } catch ( \Throwable $e ) {
            error_log( 'SMPLFY ESignatureNotifications error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() );
        }
    }

    private function send_notification( string $signer_name, string $signer_email ): void {

        $this->notification_sent = true;

        $text  = "*Internship Agreement Signed*\n\n";
        $text .= "Hello Manager,\n\n";
        $text .= "{$signer_name} has signed their internship agreement.\n\n";
        $text .= "*Signed by:* {$signer_name}\n";
        $text .= "*Email:* {$signer_email}\n\n";
        $text .= "Please log in to your manager inbox to confirm the signed agreement and advance the applicant to onboarding:\n";
        $text .= "https://intern.simplifybiz.com/managers-dashboard/managers-inbox/\n\n";
        $text .= "Regards,\nSimplifyBiz Team";

        wp_remote_post( $this->webhook_url, [
            'body'     => wp_json_encode( [ 'text' => $text ] ),
            'headers'  => [ 'Content-Type' => 'application/json; charset=utf-8' ],
            'timeout'  => 0.01,
            'blocking' => false,
        ] );
    }
}