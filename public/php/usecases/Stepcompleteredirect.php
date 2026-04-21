<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Appends a meta-refresh redirect to Gravity Flow's approval feedback message,
 * sending the user back to their role's inbox after a short delay.
 *
 * Uses gravityflow_feedback_approval — the filter that modifies the "Entry
 * approved" / "Entry rejected" feedback text shown after a user clicks
 * Approve or Reject on a workflow step. Fires only on approval step actions.
 *
 * Hooked via GravityFlowAdapter — do not register hooks here.
 */
class StepCompleteRedirect {

    private const REDIRECT_DELAY_SECONDS = 5;

    /**
     * WordPress role slug → inbox URL. First matching role in the user's
     * role array wins. Administrator is handled separately (goes to wp-admin).
     */
    private const ROLE_INBOX_MAP = [
        'manager'   => 'https://intern.simplifybiz.com/managers-dashboard/managers-inbox/',
        'support'   => 'https://intern.simplifybiz.com/support-inbox/',
        'applicant' => 'https://intern.simplifybiz.com/applicant-inbox/',
    ];

    /**
     * gravityflow_feedback_approval filter callback.
     *
     * @param string               $feedback   The feedback message text.
     * @param array                $entry      The entry array.
     * @param \Gravity_Flow_Assignee $assignee The assignee object.
     * @param string               $new_status The new approval status ('approved' / 'rejected').
     * @param array                $form       The form array.
     * @param \Gravity_Flow_Step   $step       The step object.
     * @return string
     */
    public function filter_feedback_approval( $feedback, $entry, $assignee, $new_status, $form, $step ): string {

        try {

            $user = wp_get_current_user();

            if ( ! $user || ! $user->ID ) {
                return $feedback;
            }

            $destination = $this->get_destination_for_user( $user );

            if ( empty( $destination ) ) {
                return $feedback;
            }

            return $this->inject_redirect( $feedback, $destination );

        } catch ( \Throwable $e ) {
            \SmplfyCore\SMPLFY_Log::error( 'StepCompleteRedirect error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() );
            return $feedback;
        }
    }

    /**
     * Administrators go to /wp-admin/. Everyone else gets the URL from
     * ROLE_INBOX_MAP based on the first matching role in their role array.
     */
    private function get_destination_for_user( \WP_User $user ): string {

        $roles = (array) $user->roles;

        if ( in_array( 'administrator', $roles, true ) ) {
            return admin_url();
        }

        foreach ( $roles as $role ) {
            if ( isset( self::ROLE_INBOX_MAP[ $role ] ) ) {
                return self::ROLE_INBOX_MAP[ $role ];
            }
        }

        return '';
    }

    /**
     * Appends a meta-refresh tag and a styled notice to the feedback message.
     */
    private function inject_redirect( string $feedback, string $destination ): string {

        $delay = (int) self::REDIRECT_DELAY_SECONDS;

        $refresh = '<meta http-equiv="refresh" content="' . $delay . ';url=' . esc_url( $destination ) . '">';

        $notice  = '<div style="margin-top: 16px; padding: 14px 18px; background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; color: #000000; line-height: 1.6; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">';
        $notice .= '<strong style="color: #000000;">Redirecting to your inbox...</strong> ';
        $notice .= 'You will be taken to your inbox in ' . $delay . ' seconds. ';
        $notice .= '<a style="color: #000000; font-weight: 600; text-decoration: underline;" href="' . esc_url( $destination ) . '">Click here</a> if you are not redirected automatically.';
        $notice .= '</div>';

        return $refresh . $feedback . $notice;
    }
}