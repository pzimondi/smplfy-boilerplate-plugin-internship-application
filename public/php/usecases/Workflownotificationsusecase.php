<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sends Google Chat notifications to the shared SimplifyBiz Google Space
 * when a workflow step becomes active that requires manager or support action.
 *
 * Uses the gravityflow_step_complete hook which fires when a step finishes,
 * meaning the NEXT step is now active and ready for action.
 *
 * The map below uses the name of the step that completes BEFORE the
 * manager/support step that needs the notification. For example, when
 * "New Internship Application" (the first webhook/notification step) completes,
 * "Approve Advance to Tasks" becomes active, so we notify the manager then.
 *
 * After deploying, untick "Send an email to the assignee" on each of these
 * steps in GravityFlow → Form 2 → Settings → Workflow:
 *   - Approve Advance to Tasks
 *   - Support Setup
 *   - Approve Tasks
 *   - Schedule Interview
 *   - Interview Review
 *   - Agreement Signed
 *   - Onboard Applicant
 *
 * IMPORTANT: Step names must match GravityFlow exactly, character for character.
 */
class WorkflowNotificationsUsecase {

    private string $webhook_url = 'https://chat.googleapis.com/v1/spaces/AAQAoIBJG0w/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=Qui-5Y4sTCw9r6ZL5RKEh73nzVrapEiTBF9scx487bA';

    /**
     * Key   = the exact GravityFlow step name that becomes active (the step needing action)
     * Value = notification config to send when that step becomes active
     *
     * gravityflow_step_complete fires AFTER a step completes and BEFORE the next
     * step processes. We use it to detect which step just became active by checking
     * the entry's current workflow_step meta immediately after completion.
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
        /**
         * gravityflow_step_complete fires after any step completes.
         * Parameters: $step_id, $entry_id, $form_id, $status
         * We read the entry's current workflow_step to find which step
         * is now active and send the appropriate notification.
         */
        add_action(
            'gravityflow_step_complete',
            [ $this, 'handle_step_complete' ],
            10,
            4
        );
    }

    /**
     * Fires after a GravityFlow step completes.
     * Reads the current active step on the entry and sends a Google Chat
     * notification if the newly active step is one we care about.
     *
     * @param int    $step_id  The step that just completed.
     * @param int    $entry_id The Gravity Forms entry ID.
     * @param int    $form_id  The Gravity Forms form ID.
     * @param string $status   The completion status (approved, rejected, complete etc).
     */
    public function handle_step_complete( int $step_id, int $entry_id, int $form_id, string $status ): void {

        // Only handle the main internship application form (Form 2)
        if ( $form_id !== FormIds::INTERNSHIP_APPLICATION_FORM_ID ) {
            return;
        }

        // Get the updated entry after the step completed
        $entry = GFAPI::get_entry( $entry_id );

        if ( is_wp_error( $entry ) ) {
            error_log( 'SMPLFY WorkflowNotifications: Could not retrieve entry ' . $entry_id );
            return;
        }

        // Get the current active step on this entry after completion
        $current_step = gravity_flow()->get_current_step( $form_id, $entry );

        if ( ! $current_step ) {
            // Workflow may have completed — no next step
            return;
        }

        $step_name = $current_step->get_name();

        // Check if this newly active step needs a Google Chat notification
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