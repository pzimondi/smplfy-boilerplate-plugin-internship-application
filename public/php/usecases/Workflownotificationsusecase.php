<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sends Google Chat notifications to the shared SimplifyBiz Google Space
 * when a workflow step becomes active that requires manager or support action.
 *
 * Replaces all assignee email notifications for manager and support steps.
 * After deploying this file, untick "Send an email to the assignee" on each
 * of the following steps in GravityFlow → Form 2 → Settings → Workflow:
 *
 *   - Approve Advance to Tasks
 *   - Support Setup
 *   - Approve Tasks
 *   - Schedule Interview
 *   - Interview Review
 *   - Agreement Signed
 *   - Onboard Applicant
 *
 * IMPORTANT: Step names in $step_notifications must match GravityFlow
 * step names exactly, character for character including capitalisation.
 */
class WorkflowNotificationsUsecase {

    private string $webhook_url = 'https://chat.googleapis.com/v1/spaces/AAQAoIBJG0w/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=Qui-5Y4sTCw9r6ZL5RKEh73nzVrapEiTBF9scx487bA';

    /**
     * Each key is the exact GravityFlow step name.
     *
     * 'role'   — WordPress role assigned to this step (informational only)
     * 'title'  — bold heading displayed in Google Chat
     * 'action' — instruction text sent with the notification
     * 'inbox'  — direct URL to the relevant inbox
     */
    private array $step_notifications = [

        'Approve Advance to Tasks' => [
            'role'   => 'Manager',
            'title'  => '*Action Required - Review Internship Application*',
            'action' => 'Please log in to your manager inbox to review and approve or reject the application.',
            'inbox'  => 'https://intern.simplifybiz.com/managers-dashboard/managers-inbox/',
        ],

        'Support Setup' => [
            'role'   => 'Support',
            'title'  => '*Action Required - Support Setup*',
            'action' => 'Please log in to your support inbox to complete the applicant setup before they begin their internship tasks.',
            'inbox'  => 'https://intern.simplifybiz.com/support-inbox/',
        ],

        'Approve Tasks' => [
            'role'   => 'Manager',
            'title'  => '*Action Required - Review and Approve Task Submission*',
            'action' => 'Please log in to your manager inbox to review and approve or reject the task submission.',
            'inbox'  => 'https://intern.simplifybiz.com/managers-dashboard/managers-inbox/',
        ],

        'Schedule Interview' => [
            'role'   => 'Manager',
            'title'  => '*Action Required - Interview Scheduled*',
            'action' => 'An applicant has scheduled their interview. Please log in to your manager inbox to review the interview details.',
            'inbox'  => 'https://intern.simplifybiz.com/managers-dashboard/managers-inbox/',
        ],

        'Interview Review' => [
            'role'   => 'Manager',
            'title'  => '*Action Required - Complete Interview Review and Make Offer Decision*',
            'action' => 'Please log in to your manager inbox to complete the interview review and approve or reject the applicant.',
            'inbox'  => 'https://intern.simplifybiz.com/managers-dashboard/managers-inbox/',
        ],

        'Agreement Signed' => [
            'role'   => 'Manager',
            'title'  => '*Action Required - Confirm Internship Agreement Signed*',
            'action' => 'When you receive a notification from WP E-Signature that the applicant has signed their agreement, please log in to your manager inbox to confirm and advance to onboarding.',
            'inbox'  => 'https://intern.simplifybiz.com/managers-dashboard/managers-inbox/',
        ],

        'Onboard Applicant' => [
            'role'   => 'Support',
            'title'  => '*Action Required - Onboard New Intern*',
            'action' => 'Please log in to your support inbox to complete the onboarding setup on ops.simplifybiz.com.',
            'inbox'  => 'https://intern.simplifybiz.com/support-inbox/',
        ],

    ];

    public function __construct() {
        // gravityflow_step_active fires when a step becomes the current active step
        add_action(
            'gravityflow_step_active',
            [ $this, 'handle_step_active' ],
            10,
            3
        );
    }

    /**
     * Fires when any GravityFlow step becomes active.
     * Checks if the step is on Form 2 and matches one we care about,
     * then sends the appropriate Google Chat notification.
     *
     * @param int $step_id  The GravityFlow step ID.
     * @param int $entry_id The Gravity Forms entry ID.
     * @param int $form_id  The Gravity Forms form ID.
     */
    public function handle_step_active( int $step_id, int $entry_id, int $form_id ): void {

        // Only handle the main internship application form (Form 2)
        if ( $form_id !== FormIds::INTERNSHIP_APPLICATION_FORM_ID ) {
            return;
        }

        $entry = GFAPI::get_entry( $entry_id );

        if ( is_wp_error( $entry ) ) {
            error_log( 'SMPLFY WorkflowNotifications: Could not retrieve entry ' . $entry_id );
            return;
        }

        $step = gravity_flow()->get_step( $step_id, $entry );

        if ( ! $step ) {
            error_log( 'SMPLFY WorkflowNotifications: Could not retrieve step ' . $step_id );
            return;
        }

        $step_name = $step->get_name();

        // If this step is not in our map, do nothing
        if ( ! isset( $this->step_notifications[ $step_name ] ) ) {
            return;
        }

        $config = $this->step_notifications[ $step_name ];

        $first_name = rgar( $entry, '6.3' );
        $last_name  = rgar( $entry, '6.6' );
        $full_name  = trim( $first_name . ' ' . $last_name );
        $email      = rgar( $entry, '7' );
        $internship = rgar( $entry, '3' );

        $text  = $config['title'] . "\n\n";
        $text .= "Applicant: {$full_name}\n";
        $text .= "Email: {$email}\n";
        $text .= "Internship: {$internship}\n\n";
        $text .= $config['action'] . "\n\n";
        $text .= $config['inbox'];

        $this->send_to_google_chat( $text );
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
