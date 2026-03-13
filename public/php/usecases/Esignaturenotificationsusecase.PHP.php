<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sends a Google Chat notification when a WP E-Signature document is signed.
 *
 * Uses the confirmed esig_signature_saved hook found in the plugin's actions.php.
 * $args contains 'invitation' (object with document_id) and 'signature_id'.
 */
class ESignatureNotificationsUsecase {

    private string $webhook_url = 'https://chat.googleapis.com/v1/spaces/AAQAoIBJG0w/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=Qui-5Y4sTCw9r6ZL5RKEh73nzVrapEiTBF9scx487bA';

    public function __construct() {
        // Confirmed hook from the plugin's own actions.php file.
        // Fires after a signer's signature is saved.
        // Priority 10 (after the plugin's own -100 priority handler).
        add_action(
            'esig_signature_saved',
            [ $this, 'handle_signature_saved' ],
            10,
            1
        );
    }

    /**
     * Handler for esig_signature_saved hook.
     *
     * @param array $args {
     *     @type object $invitation    Invitation object — contains document_id, invite_id, etc.
     *     @type int    $signature_id  ID of the saved signature row.
     *     @type int    $user_id       WordPress user ID of the signer (may be present).
     * }
     */
    public function handle_signature_saved( $args ): void {

        try {

            // Resolve signer identity — try args first, fall back to current user.
            $signer_name  = '';
            $signer_email = '';

            // Some versions pass user_id directly in $args.
            $user_id = isset( $args['user_id'] ) ? (int) $args['user_id'] : 0;

            // The invitation object carries the signer's wp user id on some builds.
            if ( ! $user_id && isset( $args['invitation'] ) && ! empty( $args['invitation']->invite_id ) ) {
                // invitation->invite_id is the WP user id of the invited signer.
                $user_id = (int) $args['invitation']->invite_id;
            }

            if ( $user_id ) {
                $user = get_userdata( $user_id );
                if ( $user ) {
                    $signer_name  = trim( $user->first_name . ' ' . $user->last_name );
                    $signer_email = $user->user_email;
                    if ( empty( $signer_name ) ) {
                        $signer_name = $user->display_name;
                    }
                }
            }

            // Last resort: whoever is currently logged in.
            if ( empty( $signer_name ) ) {
                $current = wp_get_current_user();
                if ( $current && $current->ID ) {
                    $signer_name  = trim( $current->first_name . ' ' . $current->last_name );
                    $signer_email = $current->user_email;
                    if ( empty( $signer_name ) ) {
                        $signer_name = $current->display_name;
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

    private function send_notification( string $signer_name, string $signer_email ): void {

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