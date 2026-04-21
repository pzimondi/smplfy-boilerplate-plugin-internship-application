<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Injects a meta-refresh redirect into Gravity Flow's approval/rejection
 * confirmation page, sending the user back to their role's inbox after
 * a short delay.
 *
 * Uses a hardcoded WordPress role slug → URL map rather than MemberPress's
 * per-membership Login Redirect URL. The role-based approach works reliably
 * mid-request (during an approval POST) whereas MeprUser::active_product_subscriptions
 * can return stale or empty state in that context.
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
     * gravityflow_approval_confirmation filter — fires when Gravity Flow
     * renders the confirmation message after a user approves/rejects a step.
     */
    public function filter_approval_confirmation( $confirmation, $form, $entry, $step ): string {

        try {

            $user = wp_get_current_user();

            if ( ! $user || ! $user->ID ) {
                return $confirmation;
            }

            $destination = $this->get_destination_for_user( $user );

            if ( empty( $destination ) ) {
                return $confirmation;
            }

            return $this->inject_redirect( $confirmation, $destination );

        } catch ( \Throwable $e ) {
            \SmplfyCore\SMPLFY_Log::error( 'StepCompleteRedirect error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() );
            return $confirmation;
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
     * Wraps the confirmation message with a meta-refresh tag and a styled
     * notice explaining the redirect.
     */
    private function inject_redirect( string $confirmation, string $destination ): string {

        $delay = (int) self::REDIRECT_DELAY_SECONDS;

        $refresh = '<meta http-equiv="refresh" content="' . $delay . ';url=' . esc_url( $destination ) . '">';

        $notice  = '<div style="margin-top: 24px; padding: 16px 20px; background-color: #eff4fb; border: 1px solid #c7d8ef; border-left: 4px solid #1D47A1; border-radius: 10px; font-size: 14px; color: #1e293b; line-height: 1.6; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">';
        $notice .= '<strong style="color: #1d47a1;">Redirecting...</strong> ';
        $notice .= 'You will be taken to your inbox in ' . $delay . ' seconds. ';
        $notice .= '<a style="color: #1d47a1; font-weight: 600; text-decoration: underline;" href="' . esc_url( $destination ) . '">Click here</a> if you are not redirected automatically.';
        $notice .= '</div>';

        return $refresh . $confirmation . $notice;
    }
}