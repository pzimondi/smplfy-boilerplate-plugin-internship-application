<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sends Google Chat notifications when workflow steps complete.
 *
 * The entire handler is wrapped in a try/catch so that any error
 * inside this class cannot crash the page or affect the workflow.
 *
 * Status values by step type:
 *   Approval step   → 'approved' or 'rejected'
 *   Form Submission → 'complete'
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
            $country    = (string) rgar( $entry, '11.6' );
            $internship = (string) rgar( $entry, '3' );

            $text = '';

            // --- STEP 1: Approve Advance to Tasks ---
            // Approval step — fires on 'approved'
            // Manager approved → notify Support to set up sandbox
            if ( $step_name === 'Approve Advance to Tasks' && (string) $status === 'approved' ) {
                $text  = "Hello Support Team,\n\n";
                $text .= "An applicant has been approved and requires setup before they can begin their internship tasks.\n\n";
                $text .= "*Applicant Details:*\n";
                $text .= "Name: {$full_name}\n";
                $text .= "Email: {$email}\n";
                $text .= "Country: {$country}\n";
                $text .= "Internship: {$internship}\n\n";
                $text .= "Please log in to your inbox to view the full setup instructions and complete the required setup:\n";
                $text .= "https://intern.simplifybiz.com/support-inbox/\n\n";
                $text .= "Regards,\nSimplifyBiz Team";
            }

            // --- STEP 2: Next Step - Submit Tasks ---
            // Form Submission step — fires on 'complete'
            // Applicant submitted tasks → notify Manager to review
            if ( $step_name === 'Next Step - Submit Tasks' && (string) $status === 'complete' ) {
                $text  = "Hello,\n\n";
                $text .= "An applicant has completed Tasks 1 to 5 and their submission is ready for your review and approval.\n\n";
                $text .= "Applicant: {$full_name}\n";
                $text .= "Email: {$email}\n";
                $text .= "Internship: {$internship}\n\n";
                $text .= "Please log in to your manager's inbox to review and approve or reject the submission:\n";
                $text .= "https://intern.simplifybiz.com/managers-dashboard/managers-inbox/\n\n";
                $text .= "Regards,\nAndre\nSimplifyBiz LLC";
            }

            // --- REMAINING STEPS (provide message text to activate) ---

            // if ( $step_name === 'Support Setup' && (string) $status === 'approved' ) {
            //     $text = '';
            // }

            // if ( $step_name === 'Approve Tasks' && (string) $status === 'approved' ) {
            //     $text = '';
            // }

            // if ( $step_name === 'Schedule Interview' && (string) $status === 'complete' ) {
            //     $text = '';
            // }

            // if ( $step_name === 'Interview Review' && (string) $status === 'approved' ) {
            //     $text = '';
            // }

            // if ( $step_name === 'Agreement Signed' && (string) $status === 'approved' ) {
            //     $text = '';
            // }

            // if ( $step_name === 'Onboard Applicant' && (string) $status === 'approved' ) {
            //     $text = '';
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