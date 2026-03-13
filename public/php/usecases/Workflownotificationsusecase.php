<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sends Google Chat notifications when workflow steps complete.
 *
 * Form 2 — Internship Application (all Approval steps, status = 'approved')
 * Form 4 — WP E-Signature Agreement (completes when signed, status = 'complete')
 *
 * Active steps:
 *   Form 2:
 *     1. Approve Advance to Tasks           (approved) → Support Team
 *     2. Next Step - Submit Tasks           (approved) → Manager
 *     3. Schedule Interview                 (approved) → Manager
 *     4. Review and Accept Internship Offer (approved) → Manager
 *     5. Agreement Signed                   (approved) → Support Team
 *   Form 4:
 *     6. Any step complete                  (complete) → Manager (document signed)
 *
 */
class WorkflowNotificationsUsecase {

    private string $webhook_managers = 'https://chat.googleapis.com/v1/spaces/AAQAoIBJG0w/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=Qui-5Y4sTCw9r6ZL5RKEh73nzVrapEiTBF9scx487bA';

    private string $webhook_support = 'https://chat.googleapis.com/v1/spaces/AAQAoIBJG0w/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=Qui-5Y4sTCw9r6ZL5RKEh73nzVrapEiTBF9scx487bA';

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

            if ( ! function_exists( 'gravity_flow' ) ) {
                return;
            }

            if ( ! class_exists( 'GFAPI' ) ) {
                return;
            }

            // ---------------------------------------------------------------
            // FORM 4 — WP E-Signature Agreement
            // Fires when the document is signed (status = complete)
            // ---------------------------------------------------------------
            if ( (int) $form_id === 4 && (string) $status === 'complete' ) {

                $entry = \GFAPI::get_entry( (int) $entry_id );

                if ( is_wp_error( $entry ) || empty( $entry ) ) {
                    return;
                }

                // Get signer name from entry field 4 (name field on Form 4)
                $signer_name  = (string) rgar( $entry, '4' );
                $signer_email = (string) rgar( $entry, '7' );

                // Fall back to WordPress user who submitted if fields are empty
                if ( empty( $signer_name ) ) {
                    $user = get_userdata( (int) rgar( $entry, 'created_by' ) );
                    if ( $user ) {
                        $signer_name  = trim( $user->first_name . ' ' . $user->last_name );
                        $signer_email = $user->user_email;
                        if ( empty( trim( $signer_name ) ) ) {
                            $signer_name = $user->display_name;
                        }
                    }
                }

                $signer_name  = ! empty( $signer_name )  ? $signer_name  : 'Unknown';
                $signer_email = ! empty( $signer_email ) ? $signer_email : 'Unknown';

                $text  = "*Internship Agreement Signed*\n\n";
                $text .= "Hello Manager,\n\n";
                $text .= "{$signer_name} has signed their internship agreement.\n\n";
                $text .= "*Signed by:* {$signer_name}\n";
                $text .= "*Email:* {$signer_email}\n\n";
                $text .= "Please log in to your manager inbox to confirm the signed agreement and advance the applicant to onboarding:\n";
                $text .= "https://intern.simplifybiz.com/managers-dashboard/managers-inbox/\n\n";
                $text .= "Regards,\nSimplifyBiz Team";

                $this->send_to_google_chat( $text, $this->webhook_managers );
                return;
            }

            // ---------------------------------------------------------------
            // FORM 2 — Internship Application
            // All steps are Approval type — only fire on approved
            // ---------------------------------------------------------------
            if ( (int) $form_id !== FormIds::INTERNSHIP_APPLICATION_FORM_ID ) {
                return;
            }

            if ( (string) $status !== 'approved' ) {
                return;
            }

            $entry = \GFAPI::get_entry( (int) $entry_id );

            if ( is_wp_error( $entry ) || empty( $entry ) ) {
                return;
            }

            // Get the step name
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

            // Applicant details
            $first_name     = (string) rgar( $entry, '6.3' );
            $last_name      = (string) rgar( $entry, '6.6' );
            $full_name      = trim( $first_name . ' ' . $last_name );
            $email          = (string) rgar( $entry, '7' );
            $country        = (string) rgar( $entry, '11.6' );
            $internship     = (string) rgar( $entry, '3' );
            $interview_date = (string) rgar( $entry, '99' );
            $interview_time = (string) rgar( $entry, '100' );
            $interview_link = (string) rgar( $entry, '101' );

            $entry_link = 'https://intern.simplifybiz.com/managers-dashboard/managers-inbox/';

            $text    = '';
            $webhook = '';

            // STEP 1: Approve Advance to Tasks → Support Team
            if ( $step_name === 'Approve Advance to Tasks' ) {
                $webhook = $this->webhook_support;
                $text  = "*Setup Required - {$full_name}*\n\n";
                $text .= "Hello Support Team,\n\n";
                $text .= "An applicant has been approved to start their application tasks and requires setup before they can begin.\n\n";
                $text .= "*Applicant Details:*\n";
                $text .= "Name: {$full_name}\n";
                $text .= "Email: {$email}\n";
                $text .= "Country: {$country}\n";
                $text .= "Internship: {$internship}\n\n";
                $text .= "Please log in to your inbox to view the full setup instructions and complete the required setup:\n";
                $text .= "https://intern.simplifybiz.com/support-inbox/\n\n";
                $text .= "Regards,\nSimplifyBiz Team";
            }

            // STEP 2: Next Step - Submit Tasks → Manager
            if ( $step_name === 'Next Step - Submit Tasks' ) {
                $webhook = $this->webhook_managers;
                $text  = "*Action Required - Review and Approve Task Submission*\n\n";
                $text .= "Hello Manager,\n\n";
                $text .= "An applicant has completed Tasks 1 to 5 and their submission is ready for your review and approval.\n\n";
                $text .= "Applicant: {$full_name}\n";
                $text .= "Email: {$email}\n";
                $text .= "Internship: {$internship}\n\n";
                $text .= "Please log in to your manager's inbox to review and approve or reject the submission:\n";
                $text .= "https://intern.simplifybiz.com/managers-dashboard/managers-inbox/\n\n";
                $text .= "Regards,\nAndre\nSimplifyBiz LLC";
            }

            // STEP 3: Schedule Interview → Manager
            if ( $step_name === 'Schedule Interview' ) {
                $webhook = $this->webhook_managers;
                $text  = "*Interview Scheduled - {$full_name}*\n\n";
                $text .= "Hello Manager,\n\n";
                $text .= "{$full_name} has scheduled their interview for the {$internship} internship position.\n\n";
                $text .= "*Interview Details:*\n";
                $text .= "Date: {$interview_date}\n";
                $text .= "Time: {$interview_time}\n";
                $text .= "Link: {$interview_link}\n\n";
                $text .= "Please log in to your inbox to complete the interview review step:\n";
                $text .= "https://intern.simplifybiz.com/managers-dashboard/managers-inbox/\n\n";
                $text .= "Regards,\nSimplifyBiz Team";
            }

            // STEP 4: Review and Accept Internship Offer → Manager
            if ( $step_name === 'Review and Accept Internship Offer' ) {
                $webhook = $this->webhook_managers;
                $text  = "*Action Required - Confirm Internship Agreement Signed*\n\n";
                $text .= "Hello Manager,\n\n";
                $text .= "When you receive a notification from WP E-Signature that the following applicant has signed their internship agreement, please come back and confirm by clicking Approve to advance the applicant to the onboarding stage.\n\n";
                $text .= "Applicant: {$full_name}\n";
                $text .= "Email: {$email}\n";
                $text .= "Internship: {$internship}\n\n";
                $text .= "Please click the link below to come back and confirm once you have received the signed agreement notification:\n";
                $text .= "{$entry_link}\n\n";
                $text .= "Click *Approve* to confirm the agreement has been signed and advance to onboarding.\n";
                $text .= "Click *Reject* only if the signed agreement is invalid or incomplete.\n\n";
                $text .= "Regards,\nAndre\nSimplifyBiz LLC";
            }

            // STEP 5: Agreement Signed → Support Team
            if ( $step_name === 'Agreement signed' ) {
                $webhook = $this->webhook_support;
                $text  = "*Action Required - Onboard New Intern*\n\n";
                $text .= "Hello Support Team,\n\n";
                $text .= "A new intern has signed their internship agreement and is ready to be onboarded on ops.simplifybiz.com.\n\n";
                $text .= "Applicant: {$full_name}\n";
                $text .= "Email: {$email}\n";
                $text .= "Internship: {$internship}\n\n";
                $text .= "Please log in to your support inbox to complete the onboarding steps:\n";
                $text .= "https://intern.simplifybiz.com/support-inbox/\n\n";
                $text .= "Regards,\nSimplifyBiz Team";
            }

            // ---------------------------------------------------------------
            // PENDING STEPS — no message provided yet
            // ---------------------------------------------------------------

            // if ( $step_name === 'Support Setup' ) {
            //     $text = '';
            // }

            // if ( $step_name === 'Approve Tasks' ) {
            //     $text = '';
            // }

            // if ( $step_name === 'Interview Review' ) {
            //     $text = '';
            // }

            // if ( $step_name === 'Onboard Applicant' ) {
            //     $text = '';
            // }

            if ( ! empty( $text ) && ! empty( $webhook ) ) {
                $this->send_to_google_chat( $text, $webhook );
            }

        } catch ( \Throwable $e ) {
            error_log( 'SMPLFY WorkflowNotifications error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() );
        }
    }

    private function send_to_google_chat( string $text, string $webhook ): void {

        try {
            wp_remote_post( $webhook, [
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