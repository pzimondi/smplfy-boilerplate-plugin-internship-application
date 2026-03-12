<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sends Google Chat notifications when workflow steps are actioned.
 *
 * Currently active: Approve Advance to Tasks only (for testing).
 * Once confirmed working, remaining steps will be uncommented.
 *
 * Flow:
 * Manager approves "Approve Advance to Tasks"
 *   → Google Chat notifies Support to set up sandbox for the approved applicant
 *   → Workflow advances to Support Setup step
 */
class WorkflowNotificationsUsecase {

    private string $webhook_url = 'https://chat.googleapis.com/v1/spaces/AAQAoIBJG0w/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=Qui-5Y4sTCw9r6ZL5RKEh73nzVrapEiTBF9scx487bA';

    public function __construct() {
        add_action(
            'gravityflow_step_complete',
            [ $this, 'handle_step_complete' ],
            10,
            4
        );
    }

    public function handle_step_complete( int $step_id, int $entry_id, int $form_id, string $status ): void {

        // Only handle Form 2 — the main internship application form
        if ( $form_id !== FormIds::INTERNSHIP_APPLICATION_FORM_ID ) {
            return;
        }

        $entry = GFAPI::get_entry( $entry_id );

        if ( is_wp_error( $entry ) ) {
            error_log( 'SMPLFY WorkflowNotifications: could not retrieve entry ' . $entry_id );
            return;
        }

        $step = gravity_flow()->get_step( $step_id, $entry );

        if ( ! $step ) {
            error_log( 'SMPLFY WorkflowNotifications: could not retrieve step ' . $step_id );
            return;
        }

        $step_name  = $step->get_name();
        $first_name = rgar( $entry, '6.3' );
        $last_name  = rgar( $entry, '6.6' );
        $full_name  = trim( $first_name . ' ' . $last_name );
        $email      = rgar( $entry, '7' );
        $internship = rgar( $entry, '3' );

        // --- STEP 1: Approve Advance to Tasks ---
        // Manager approved the application → notify Support to set up sandbox
        if ( $step_name === 'Approve Advance to Tasks' && $status === 'approved' ) {

            $text  = "*Action Required - Support Setup*\n\n";
            $text .= "An internship application has been approved.\n\n";
            $text .= "Applicant: {$full_name}\n";
            $text .= "Email: {$email}\n";
            $text .= "Internship: {$internship}\n\n";
            $text .= "Please log in to your support inbox to set up the sandbox environment for this applicant before they begin Tasks 1 to 5.\n\n";
            $text .= "https://intern.simplifybiz.com/support-inbox/";

            $this->send_to_google_chat( $text );
            return;
        }

        // --- REMAINING STEPS (uncomment once Step 1 is confirmed working) ---

        // if ( $step_name === 'Support Setup' && $status === 'approved' ) { ... }
        // if ( $step_name === 'Approve Tasks' && $status === 'approved' ) { ... }
        // if ( $step_name === 'Schedule Interview' && $status === 'approved' ) { ... }
        // if ( $step_name === 'Interview Review' && $status === 'approved' ) { ... }
        // if ( $step_name === 'Agreement Signed' && $status === 'approved' ) { ... }
        // if ( $step_name === 'Onboard Applicant' && $status === 'approved' ) { ... }
    }

    private function send_to_google_chat( string $text ): void {

        wp_remote_post( $this->webhook_url, [
            'body'     => wp_json_encode( [ 'text' => $text ] ),
            'headers'  => [ 'Content-Type' => 'application/json; charset=utf-8' ],
            'timeout'  => 0.01,
            'blocking' => false,
        ] );
    }
}