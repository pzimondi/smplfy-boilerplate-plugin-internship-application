<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sends Google Chat notifications when workflow steps are actioned.
 *
 * Uses gravityflow_post_complete_step which fires AFTER GravityFlow
 * has fully finished processing the step including advancing to the
 * next step. This avoids any interference with the workflow process.
 *
 * Currently active: Approve Advance to Tasks only (for testing).
 * Once confirmed working, remaining steps will be uncommented.
 */
class WorkflowNotificationsUsecase {

    private string $webhook_url = 'https://chat.googleapis.com/v1/spaces/AAQAoIBJG0w/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=Qui-5Y4sTCw9r6ZL5RKEh73nzVrapEiTBF9scx487bA';

    public function __construct() {
        // gravityflow_post_complete_step fires AFTER the step is fully
        // processed and the workflow has already advanced to the next step.
        // This means it cannot interfere with or block the workflow.
        add_action(
            'gravityflow_post_complete_step',
            [ $this, 'handle_post_complete_step' ],
            10,
            4
        );
    }

    public function handle_post_complete_step( $step, $entry, $form, $status ): void {

        // Only handle Form 2 — the main internship application form
        if ( (int) rgar( $form, 'id' ) !== FormIds::INTERNSHIP_APPLICATION_FORM_ID ) {
            return;
        }

        // Only fire on approved status
        if ( $status !== 'approved' ) {
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
        if ( $step_name === 'Approve Advance to Tasks' ) {

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

        // if ( $step_name === 'Support Setup' ) { ... }
        // if ( $step_name === 'Approve Tasks' ) { ... }
        // if ( $step_name === 'Schedule Interview' ) { ... }
        // if ( $step_name === 'Interview Review' ) { ... }
        // if ( $step_name === 'Agreement Signed' ) { ... }
        // if ( $step_name === 'Onboard Applicant' ) { ... }
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