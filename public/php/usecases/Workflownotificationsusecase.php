<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sends Google Chat notifications when workflow approval steps complete.
 *
 * The entire handler is wrapped in a try/catch so that any error
 * inside this class cannot crash the page or affect the workflow.
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

        try {

            // Only handle Form 2
            if ( (int) $form_id !== FormIds::INTERNSHIP_APPLICATION_FORM_ID ) {
                return;
            }

            // Only fire when approved
            if ( (string) $status !== 'approved' ) {
                return;
            }

            // Bail if GravityFlow is not available
            if ( ! function_exists( 'gravity_flow' ) ) {
                return;
            }

            // Bail if GFAPI is not available
            if ( ! class_exists( 'GFAPI' ) ) {
                return;
            }

            $entry = \GFAPI::get_entry( (int) $entry_id );

            if ( is_wp_error( $entry ) || empty( $entry ) ) {
                return;
            }

            // Get the step name by looping through configured steps
            $step_name = '';
            $steps     = gravity_flow()->get_steps( (int) $form_id, $entry );

            if ( ! empty( $steps ) ) {
                foreach ( $steps as $step ) {
                    if ( (int) $step->get_id() === (int) $step_id ) {
                        $step_name = (string) $step->get_name();
                        break;
                    }
                }
            }

            if ( empty( $step_name ) ) {
                return;
            }

            $first_name = (string) rgar( $entry, '6.3' );
            $last_name  = (string) rgar( $entry, '6.6' );
            $full_name  = trim( $first_name . ' ' . $last_name );
            $email      = (string) rgar( $entry, '7' );
            $internship = (string) rgar( $entry, '3' );

            $text = '';

            // --- STEP 1: Approve Advance to Tasks ---
            if ( $step_name === 'Approve Advance to Tasks' ) {
                $text  = "*Action Required - Support Setup*\n\n";
                $text .= "An internship application has been approved.\n\n";
                $text .= "Applicant: {$full_name}\n";
                $text .= "Email: {$email}\n";
                $text .= "Internship: {$internship}\n\n";
                $text .= "Please log in to your support inbox to set up the sandbox environment for this applicant before they begin Tasks 1 to 5.\n\n";
                $text .= "https://intern.simplifybiz.com/support-inbox/";
            }

            // --- REMAINING STEPS (uncomment once Step 1 confirmed working) ---

            // if ( $step_name === 'Support Setup' ) {
            //     $text  = "*Action Required - Approve Tasks*\n\n";
            //     $text .= "Support has completed the sandbox setup.\n\n";
            //     $text .= "Applicant: {$full_name}\n";
            //     $text .= "Email: {$email}\n";
            //     $text .= "Internship: {$internship}\n\n";
            //     $text .= "The applicant has been set up and can now begin Tasks 1 to 5.";
            // }

            // if ( $step_name === 'Approve Tasks' ) {
            //     $text  = "*Tasks Approved - Schedule Interview*\n\n";
            //     $text .= "Applicant: {$full_name}\n";
            //     $text .= "Email: {$email}\n";
            //     $text .= "Internship: {$internship}\n\n";
            //     $text .= "The applicant's tasks have been approved. They will now schedule their interview.";
            // }

            // if ( $step_name === 'Schedule Interview' ) {
            //     $text  = "*Action Required - Interview Scheduled*\n\n";
            //     $text .= "Applicant: {$full_name}\n";
            //     $text .= "Email: {$email}\n";
            //     $text .= "Internship: {$internship}\n\n";
            //     $text .= "An applicant has scheduled their interview. Please log in to your manager inbox.\n\n";
            //     $text .= "https://intern.simplifybiz.com/managers-dashboard/managers-inbox/";
            // }

            // if ( $step_name === 'Interview Review' ) {
            //     $text  = "*Offer Sent - Awaiting Applicant Acceptance*\n\n";
            //     $text .= "Applicant: {$full_name}\n";
            //     $text .= "Email: {$email}\n";
            //     $text .= "Internship: {$internship}\n\n";
            //     $text .= "The interview review is complete and an internship offer has been sent to the applicant.";
            // }

            // if ( $step_name === 'Agreement Signed' ) {
            //     $text  = "*Action Required - Onboard New Intern*\n\n";
            //     $text .= "Applicant: {$full_name}\n";
            //     $text .= "Email: {$email}\n";
            //     $text .= "Internship: {$internship}\n\n";
            //     $text .= "The internship agreement has been confirmed. Please log in to your support inbox to complete onboarding.\n\n";
            //     $text .= "https://intern.simplifybiz.com/support-inbox/";
            // }

            // if ( $step_name === 'Onboard Applicant' ) {
            //     $text  = "*Onboarding Complete*\n\n";
            //     $text .= "Applicant: {$full_name}\n";
            //     $text .= "Email: {$email}\n";
            //     $text .= "Internship: {$internship}\n\n";
            //     $text .= "The intern has been successfully onboarded on ops.simplifybiz.com.";
            // }

            if ( ! empty( $text ) ) {
                $this->send_to_google_chat( $text );
            }

        } catch ( \Throwable $e ) {
            error_log( 'SMPLFY WorkflowNotifications error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() );
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
            error_log( 'SMPLFY WorkflowNotifications send error: ' . $e->getMessage() );
        }
    }
}