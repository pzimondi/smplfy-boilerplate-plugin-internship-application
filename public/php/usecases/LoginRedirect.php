<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Forces administrators to /wp-admin/ on login.
 *
 * MemberPress hooks into the `wp_login` action and does
 * wp_redirect() + exit to its account page, which runs BEFORE
 * the `login_redirect` filter chain. To win, we hook `wp_login`
 * ourselves at an earlier priority and short-circuit for admins.
 *
 * Non-admin roles are untouched — MemberPress's per-membership
 * Login Redirect URL behavior remains fully in control of those flows.
 *
 * Hooked via WordpressAdapter — do not register hooks here.
 */
class LoginRedirect {

    /**
     * wp_login action — fires right after authentication succeeds
     * and auth cookies are set. Redirecting here is safe.
     */
    public function handle_wp_login( string $user_login, \WP_User $user ): void {

        if ( in_array( 'administrator', (array) $user->roles, true ) ) {
            wp_safe_redirect( admin_url() );
            exit;
        }
    }

    /**
     * login_redirect filter — secondary safety net in case the wp_login
     * action path is bypassed (e.g. programmatic login, some SSO flows).
     */
    public function handle_login_redirect( $redirect_to, $requested_redirect_to, $user ) {

        if ( ! $user instanceof \WP_User ) {
            return $redirect_to;
        }

        if ( in_array( 'administrator', (array) $user->roles, true ) ) {
            return admin_url();
        }

        return $redirect_to;
    }
}