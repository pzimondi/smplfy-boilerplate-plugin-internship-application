<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Redirects administrators to /wp-admin/ on login.
 *
 * Non-admin roles (Manager, Support, Applicant, Intern, Subscriber)
 * pass through untouched so MemberPress's per-membership Login Redirect URL
 * setting remains in control of those flows.
 *
 * Hooked via WordpressAdapter — do not register hooks here.
 */
class LoginRedirect {

    /**
     * Filter callback for the `login_redirect` hook.
     *
     * @param string           $redirect_to           The redirect destination URL.
     * @param string           $requested_redirect_to The requested redirect destination URL.
     * @param \WP_User|\WP_Error $user                Logged in user object, or WP_Error on failure.
     *
     * @return string
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