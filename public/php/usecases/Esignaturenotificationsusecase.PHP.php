<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sends a Google Chat notification when a WP E-Signature document is signed.
 *
 * Hook: appm_document_signed
 * Parameter: $document_id (int) — the ID of the document that was signed
 *
 * Wrapped in try/catch so no error here can affect the signing process.
 */
class ESignatureNotificationsUsecase {

    private string $webhook_url = 'https://chat.googleapis.com/v1/spaces/AAQAoIBJG0w/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=Qui-5Y4sTCw9r6ZL5RKEh73nzVrapEiTBF9scx487bA';

    public function __construct() {
        add_action(
            'appm_document_signed',
            [ $this, 'handle_document_signed' ],
            10,
            1
        );
    }

    public function handle_document_signed( $document_id ): void {

        try {

            // Get the document post
            $document = get_post( (int) $document_id );

            $document_title = $document ? $document->post_title : 'Internship Agreement';

            // Get the signers attached to this document
            // WP E-Signature stores signers as post meta
            $signer_email = get_post_meta( (int) $document_id, 'esig_signer_email', true );
            $signer_name  = get_post_meta( (int) $document_id, 'esig_signer_name', true );

            // Fall back to WordPress user lookup if meta not available
            if ( empty( $signer_name ) || empty( $signer_email ) ) {
                $current_user = wp_get_current_user();
                if ( $current_user && $current_user->ID ) {
                    $signer_name  = trim( $current_user->first_name . ' ' . $current_user->last_name );
                    $signer_email = $current_user->user_email;
                    // Fall back to display name if first/last not set
                    if ( empty( trim( $signer_name ) ) ) {
                        $signer_name = $current_user->display_name;
                    }
                }
            }

            $signer_name  = ! empty( $signer_name )  ? $signer_name  : 'Unknown';
            $signer_email = ! empty( $signer_email ) ? $signer_email : 'Unknown';

            $text  = "*Internship Agreement Signed*\n\n";
            $text .= "Hello Manager,\n\n";
            $text .= "{$signer_name} has signed their internship agreement.\n\n";
            $text .= "*Document:* {$document_title}\n";
            $text .= "*Signed by:* {$signer_name}\n";
            $text .= "*Email:* {$signer_email}\n\n";
            $text .= "Please log in to your manager inbox to confirm the signed agreement and advance the applicant to onboarding:\n";
            $text .= "https://intern.simplifybiz.com/managers-dashboard/managers-inbox/\n\n";
            $text .= "Regards,\nSimplifyBiz Team";

            $this->send_to_google_chat( $text );

        } catch ( \Throwable $e ) {
            error_log( 'SMPLFY ESignatureNotifications error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() );
        }
    }

    private function send_to_google_chat( string $text ): void {

        try {
            wp_remote_post( $this->webhook_url, [
                'body'     => wp_json_encode( [ 'text' => $text ] ),
                'headers'  => [ 'Content-Type' => 'application/json; charset=utf-8' ],
                'timeout'  => 0.01,
                'blocking' => false,
            ] );
        } catch ( \Throwable $e ) {
            error_log( 'SMPLFY ESignatureNotifications send error: ' . $e->getMessage() );
        }
    }
}