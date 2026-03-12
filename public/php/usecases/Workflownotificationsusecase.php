<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sends Google Chat notifications when workflow approval steps complete.
 *
 * Hook: gravityflow_step_complete
 * Parameters: $step_id (int), $entry_id (int), $form_id (int), $status (string)
 *
 * The step name is read from the entry meta 'workflow_step_name_{step_id}'
 * which GravityFlow stores on the entry — no API calls that could break the workflow.
 *
 * Currently active: Approve Advance to Tasks only (for testing).
 * Once confirmed working, remaining steps will be uncommented.
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

    public function handle_step_complete( $step_id, $entry_id, $form_id, $status ): void {

        // Only handle Form 2 — the main internship application form
        if ( (int) $form_id !== FormIds::INTERNSHIP_APPLICATION_FORM_ID ) {
            return;
        }

        // Only fire when a step is approved
        if ( $status !== 'approved' ) {
            return;
        }

        // Get the entry
        $entry = GFAPI::get_entry( $entry_id );

        if ( is_wp_error( $entry ) ) {
            return;
        }

        // Read applicant details from the entry
        $first_name = rgar( $entry, '6.3' );
        $last_name  = rgar( $entry, '6.6' );
        $full_name  = trim( $first_name . ' ' . $last_name );
        $email      = rgar( $entry, '7' );
        $internship = rgar( $entry, '3' );

        // Get the step name safely from GravityFlow
        // gravity_flow()->get_steps() returns all steps for the form
        // We find the one matching $step_id to get its name
        $step_name = $this->get_step_name( (int) $step_id, (int) $form_id, $entry );

        if ( empty( $step_name ) ) {
            return;
        }

        // --- STEP 1: Approve Advance to Tasks ---
        // Manager approved → notify Support to set up sandbox
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

        // if ( $step_name === 'Support Setup' ) {
        //     $text  = "*Action Required - Approve Tasks*\n\n";
        //     $text .= "Support has completed the sandbox setup.\n\n";
        //     $text .= "Applicant: {$full_name}\n";
        //     $text .= "Email: {$email}\n";
        //     $text .= "Internship: {$internship}\n\n";
        //     $text .= "The applicant has been set up and can now begin Tasks 1 to 5.";
        //     $this->send_to_google_chat( $text );
        //     return;
        // }

        // if ( $step_name === 'Approve Tasks' ) {
        //     $text  = "*Tasks Approved - Schedule Interview*\n\n";
        //     $text .= "Applicant: {$full_name}\n";
        //     $text .= "Email: {$email}\n";
        //     $text .= "Internship: {$internship}\n\n";
        //     $text .= "The applicant's tasks have been approved. They will now schedule their interview.";
        //     $this->send_to_google_chat( $text );
        //     return;
        // }

        // if ( $step_name === 'Schedule Interview' ) {
        //     $text  = "*Action Required - Interview Scheduled*\n\n";
        //     $text .= "Applicant: {$full_name}\n";
        //     $text .= "Email: {$email}\n";
        //     $text .= "Internship: {$internship}\n\n";
        //     $text .= "An applicant has scheduled their interview. Please log in to your manager inbox.\n\n";
        //     $text .= "https://intern.simplifybiz.com/managers-dashboard/managers-inbox/";
        //     $this->send_to_google_chat( $text );
        //     return;
        // }

        // if ( $step_name === 'Interview Review' ) {
        //     $text  = "*Offer Sent - Awaiting Applicant Acceptance*\n\n";
        //     $text .= "Applicant: {$full_name}\n";
        //     $text .= "Email: {$email}\n";
        //     $text .= "Internship: {$internship}\n\n";
        //     $text .= "The interview review is complete and an internship offer has been sent to the applicant.";
        //     $this->send_to_google_chat( $text );
        //     return;
        // }

        // if ( $step_name === 'Agreement Signed' ) {
        //     $text  = "*Action Required - Onboard New Intern*\n\n";
        //     $text .= "Applicant: {$full_name}\n";
        //     $text .= "Email: {$email}\n";
        //     $text .= "Internship: {$internship}\n\n";
        //     $text .= "The internship agreement has been confirmed. Please log in to your support inbox to complete onboarding on ops.simplifybiz.com.\n\n";
        //     $text .= "https://intern.simplifybiz.com/support-inbox/";
        //     $this->send_to_google_chat( $text );
        //     return;
        // }

        // if ( $step_name === 'Onboard Applicant' ) {
        //     $text  = "*Onboarding Complete*\n\n";
        //     $text .= "Applicant: {$full_name}\n";
        //     $text .= "Email: {$email}\n";
        //     $text .= "Internship: {$internship}\n\n";
        //     $text .= "The intern has been successfully onboarded on ops.simplifybiz.com.";
        //     $this->send_to_google_chat( $text );
        //     return;
        // }
    }

    /**
     * Safely retrieves the step name for a given step ID.
     * Uses gravity_flow()->get_steps() which returns the configured
     * step objects for the form without triggering any workflow processing.
     */
    private function get_step_name( int $step_id, int $form_id, array $entry ): string {

        if ( ! function_exists( 'gravity_flow' ) ) {
            return '';
        }

        $steps = gravity_flow()->get_steps( $form_id, $entry );

        if ( empty( $steps ) ) {
            return '';
        }

        foreach ( $steps as $step ) {
            if ( (int) $step->get_id() === $step_id ) {
                return $step->get_name();
            }
        }

        return '';
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