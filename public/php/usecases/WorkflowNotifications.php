<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sends Google Chat notifications when workflow steps complete.
 *
 * Hooked via GravityFlowAdapter — do not register hooks here.
 *
 * Form 2 — Internship Application (all steps live here, including the
 * "Agreement Signing" WP E-Signature step which completes with status 'complete').
 */
class WorkflowNotifications {

    private string $webhook_managers = 'https://chat.googleapis.com/v1/spaces/AAQAoIBJG0w/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=Qui-5Y4sTCw9r6ZL5RKEh73nzVrapEiTBF9scx487bA';
    private string $webhook_support  = 'https://chat.googleapis.com/v1/spaces/AAQAoIBJG0w/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=Qui-5Y4sTCw9r6ZL5RKEh73nzVrapEiTBF9scx487bA';

    private InternshipApplicationRepository $internshipApplicationRepository;
    private ESignatureAgreementRepository   $eSignatureAgreementRepository;

    public function __construct(
        InternshipApplicationRepository $internshipApplicationRepository,
        ESignatureAgreementRepository   $eSignatureAgreementRepository
    ) {
        $this->internshipApplicationRepository = $internshipApplicationRepository;
        $this->eSignatureAgreementRepository   = $eSignatureAgreementRepository;
    }

    public function handle_step_complete( $step_id, $entry_id, $form_id, $status ): void {

        try {

            if ( ! function_exists( 'gravity_flow' ) ) {
                return;
            }

            if ( ! class_exists( 'GFAPI' ) ) {
                return;
            }

            if ( (int) $form_id !== FormIds::INTERNSHIP_APPLICATION_FORM_ID ) {
                return;
            }

            $raw_entry = \GFAPI::get_entry( (int) $entry_id );

            if ( is_wp_error( $raw_entry ) || empty( $raw_entry ) ) {
                return;
            }

            $step_name = '';
            $steps     = gravity_flow()->get_steps( (int) $form_id, $raw_entry );

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

            $entity = $this->internshipApplicationRepository->get_one(
                [ FormIds::INTERNSHIP_APPLICATION_EMAIL_FIELD_ID => rgar( $raw_entry, FormIds::INTERNSHIP_APPLICATION_EMAIL_FIELD_ID ) ]
            );

            if ( $entity ) {
                $full_name  = trim( $entity->nameFirst . ' ' . $entity->nameLast );
                $email      = $entity->email;
                $country    = $entity->country;
                $internship = $entity->internship;
            } else {
                $full_name  = trim( rgar( $raw_entry, '6.3' ) . ' ' . rgar( $raw_entry, '6.6' ) );
                $email      = (string) rgar( $raw_entry, '7' );
                $country    = (string) rgar( $raw_entry, '11.6' );
                $internship = (string) rgar( $raw_entry, '3' );
            }

            $interview_date = (string) rgar( $raw_entry, '99' );
            $interview_time = (string) rgar( $raw_entry, '100' );
            $interview_link = (string) rgar( $raw_entry, '101' );
            $entry_link     = 'https://intern.simplifybiz.com/managers-dashboard/managers-inbox/';

            $text    = '';
            $webhook = '';

            // Agreement Signing (WP E-Signature step, fires on 'complete' when intern signs)
            // Notifies support to onboard the applicant to ops.simplifybiz.com.
            if ( $step_name === 'Agreement Signing' && (string) $status === 'complete' ) {
                $webhook = $this->webhook_support;
                $text  = "*Action Required - Onboard New Intern*\n\n";
                $text .= "Hello Support Team,\n\n";
                $text .= "{$full_name} has signed their internship agreement and is ready to be onboarded to ops.simplifybiz.com.\n\n";
                $text .= "*Applicant Details:*\n";
                $text .= "Name: {$full_name}\n";
                $text .= "Email: {$email}\n";
                $text .= "Internship: {$internship}\n\n";
                $text .= "*ONBOARDING CHECKLIST*\n\n";
                $text .= "1. Create their ops.simplifybiz.com account (username + password, appropriate permissions)\n";
                $text .= "2. Assign them to a Project\n";
                $text .= "3. Assign their Project Role (Lead or Team Member)\n\n";
                $text .= "Once onboarding is complete, return to your support inbox and approve the next step to send the intern their welcome email:\n";
                $text .= "https://intern.simplifybiz.com/support-inbox/\n\n";
                $text .= "Regards,\nSimplifyBiz Workflow";
            }

            // The remaining branches below all require status === 'approved'
            // (standard Gravity Flow approval steps). Agreement Signing above is
            // the exception because it's a WP E-Signature step (status 'complete').
            if ( empty( $text ) && (string) $status === 'approved' ) {

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

                if ( $step_name === 'Onboard Applicant' ) {
                    $webhook = $this->webhook_managers;
                    $text  = "*New Intern Onboarded - {$full_name}*\n\n";
                    $text .= "Hello Managers,\n\n";
                    $text .= "A new intern has been successfully onboarded and is now active on ops.simplifybiz.com.\n\n";
                    $text .= "*Applicant Details:*\n";
                    $text .= "Name: {$full_name}\n";
                    $text .= "Email: {$email}\n";
                    $text .= "Internship: {$internship}\n\n";
                    $text .= "The intern has been set up with their ops.simplifybiz.com account and assigned to a project. They are now ready to begin their internship.\n\n";
                    $text .= "Regards,\nSimplifyBiz Workflow";
                }
            }

            if ( ! empty( $text ) && ! empty( $webhook ) ) {
                $this->send_to_google_chat( $text, $webhook );
            }

        } catch ( \Throwable $e ) {
            \SmplfyCore\SMPLFY_Log::error( 'WorkflowNotifications error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() );
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
            \SmplfyCore\SMPLFY_Log::error( 'WorkflowNotifications send error: ' . $e->getMessage() );
        }
    }
}