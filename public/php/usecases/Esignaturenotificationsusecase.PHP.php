<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sends Google Chat notifications when workflow steps complete.
 *
 * All approval steps fire on status 'approved'.
 * The WP E-Signature step fires on status 'complete' since it has no
 * approve/reject — it completes automatically when the document is signed.
 *
 * Active steps:
 *   1. Approve Advance to Tasks           (approved) → Support Team
 *   2. Next Step - Submit Tasks           (approved) → Manager
 *   3. Schedule Interview                 (approved) → Manager
 *   4. Review and Accept Internship Offer (approved) → Manager
 *   5. Agreement Signed                   (approved) → Support Team
 *   6. WP E-Signature step                (complete) → Manager (document signed)
 *
 * Pending steps (no message provided yet):
 *   - Support Setup     → Support
 *   - Approve Tasks     → Manager
 *   - Interview Review  → Manager
 *   - Onboard Applicant → Support
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

            if ( (int) $form_id !== FormIds::INTERNSHIP_APPLICATION_FORM_ID ) {
                return;
            }

            if ( ! function_exists( 'gravity_flow' ) ) {
                return;
            }

            if ( ! class_exists( 'GFAPI' ) ) {
                return;
            }

            $entry = \GFAPI::get_entry( (int) $entry_id );

            if ( is_wp_error( $entry ) || empty( $entry ) ) {
                return;
            }

            // Get the step name and type
            $step_name = '';
            $step_type = '';
            $steps     = gravity_flow()->get_steps( (int) $form_id, $entry );

            if ( ! empty( $steps ) ) {
                foreach ( $steps as $step ) {
                    if ( (int) $step->get_id() === (int) $step_id ) {
                        $step_name = (string) $step->get_name();
                        $step_type = (string) $step->get_type();
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

            $entry_link = get_site_url() . '/managers-dashboard/managers-inbox/?page=gravityflow-inbox&view=entry&id=' . $form_id . '&lid=' . $entry_id;

            $text = '';

            // ---------------------------------------------------------------
            // WP E-SIGNATURE STEP — fires when document is signed (complete)
            // Notifies Manager to confirm signed agreement in their inbox
            // ---------------------------------------------------------------
            if ( $step_type === 'wp-esignature' && (string) $status === 'complete' ) {
                $text  = "*Internship Agreement Signed*\n\n";
                $text .= "Hello Manager,\n\n";
                $text .= "{$full_name} has signed their internship agreement.\n\n";
                $text .= "*Applicant Details:*\n";
                $text .= "Name: {$full_name}\n";
                $text .= "Email: {$email}\n";
                $text .= "Internship: {$internship}\n\n";
                $text .= "Please log in to your manager inbox to confirm the signed agreement and advance the applicant to onboarding:\n";
                $text .= "https://intern.simplifybiz.com/managers-dashboard/managers-inbox/\n\n";
                $text .= "Regards,\nSimplifyBiz Team";
            }

            // ---------------------------------------------------------------
            // STEP 1: Approve Advance to Tasks → Support Team
            // ---------------------------------------------------------------
            if ( $step_name === 'Approve Advance to Tasks' && (string) $status === 'approved' ) {
                $text  = "*Setup Required - {$full_name}*\n\n";
                $text .= "Hello Support Team,\n\n";
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

            // ---------------------------------------------------------------
            // STEP 2: Next Step - Submit Tasks → Manager
            // ---------------------------------------------------------------
            if ( $step_name === 'Next Step - Submit Tasks' && (string) $status === 'approved' ) {
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

            // ---------------------------------------------------------------
            // STEP 3: Schedule Interview → Manager
            // ---------------------------------------------------------------
            if ( $step_name === 'Schedule Interview' && (string) $status === 'approved' ) {
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

            // ---------------------------------------------------------------
            // STEP 4: Review and Accept Internship Offer → Manager
            // ---------------------------------------------------------------
            if ( $step_name === 'Review and Accept Internship Offer' && (string) $status === 'approved' ) {
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

            // ---------------------------------------------------------------
            // STEP 5: Agreement Signed → Support Team
            // ---------------------------------------------------------------
            if ( $step_name === 'Agreement signed' && (string) $status === 'approved' ) {
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

            // if ( $step_name === 'Support Setup' && (string) $status === 'approved' ) {
            //     $text = '';
            // }

            // if ( $step_name === 'Approve Tasks' && (string) $status === 'approved' ) {
            //     $text = '';
            // }

            // if ( $step_name === 'Interview Review' && (string) $status === 'approved' ) {
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