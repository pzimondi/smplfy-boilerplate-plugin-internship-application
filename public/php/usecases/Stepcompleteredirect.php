<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Injects a meta-refresh redirect into Gravity Flow's approval/rejection
 * confirmation page, sending the user back to their membership's
 * configured Login Redirect URL after 5 seconds.
 *
 * Mirrors the design of LoginRedirect: administrators go to /wp-admin/,
 * all other roles defer to MemberPress's per-membership Login Redirect
 * URL so there is a single source of truth for role-based destinations.
 *
 * Hooked via GravityFlowAdapter — do not register hooks here.
 */
class StepCompleteRedirect {

    private const REDIRECT_DELAY_SECONDS = 5;

    /**
     * gravityflow_approval_confirmation filter — fires when Gravity Flow
     * renders the confirmation message after a user approves/rejects a step.
     *
     * @param string $confirmation
     * @param array  $form
     * @param array  $entry
     * @param object $step
     * @return string
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
     * Administrators go to /wp-admin/ (matches LoginRedirect).
     * Everyone else defers to MemberPress's per-membership Login Redirect URL.
     *
     * @param \WP_User $user
     * @return string
     */
    private function get_destination_for_user( \WP_User $user ): string {

        if ( in_array( 'administrator', (array) $user->roles, true ) ) {
            return admin_url();
        }

        return $this->get_memberpress_login_redirect( $user );
    }

    /**
     * Pulls the Login Redirect URL from the user's active MemberPress
     * membership. Returns empty string if MemberPress is unavailable,
     * the user has no active memberships, or no redirect is configured.
     *
     * @param \WP_User $user
     * @return string
     */
    private function get_memberpress_login_redirect( \WP_User $user ): string {

        if ( ! class_exists( '\MeprUser' ) ) {
            return '';
        }

        try {

            $mepr_user = new \MeprUser( $user->ID );

            $active_memberships = $mepr_user->active_product_subscriptions( 'ids' );

            if ( empty( $active_memberships ) ) {
                return '';
            }

            foreach ( (array) $active_memberships as $product_id ) {

                if ( ! class_exists( '\MeprProduct' ) ) {
                    continue;
                }

                $product = new \MeprProduct( (int) $product_id );

                if ( empty( $product->ID ) ) {
                    continue;
                }

                $redirect = isset( $product->login_redirect_url ) ? (string) $product->login_redirect_url : '';

                if ( ! empty( $redirect ) ) {
                    return $redirect;
                }
            }

        } catch ( \Throwable $e ) {
            \SmplfyCore\SMPLFY_Log::error( 'StepCompleteRedirect MemberPress lookup failed: ' . $e->getMessage() );
            return '';
        }

        return '';
    }

    /**
     * Wraps the confirmation message with a meta-refresh tag and a
     * styled notice explaining the redirect.
     *
     * @param string $confirmation
     * @param string $destination
     * @return string
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