<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Populates derived fields on form 2 (Internship Application) at submission.
 *
 * ARCHITECTURE NOTES:
 *
 * Form 2 is a single-submission multi-page form. All fields save together at
 * final submit. However, GF's conditional-logic engine STRIPS values from
 * children of hidden sections on save — even values injected via $_POST.
 *
 * Field 103 (Intern Onboarding section) shows only when Application Status =
 * Onboarding, which is false on fresh submission. So its children 105, 120,
 * 123 get their values stripped by GF despite successful POST injection and
 * despite successful GFAPI::update_entry_field calls (GF re-applies CL on
 * individual field updates).
 *
 * Field 59 (Applicant Onboarding) has no conditional logic, so its children
 * 62, 63, 114, 115 save normally.
 *
 * Field 33 (password input) is stripped from the saved entry by GF's password-
 * field handling — regardless of conditional logic.
 *
 * SOLUTION:
 *
 *   1. gform_pre_submission_2 — captures source values (email, password, first
 *      name) into class state, since some get stripped before after_submission.
 *
 *   2. gform_after_submission_2 — uses GFAPI::update_entry() with the FULL
 *      entry array rather than update_entry_field(). The full-array updater
 *      does NOT re-run conditional logic per field, so values in hidden-section
 *      children survive.
 *
 * Mappings:
 *   - Field 7  (Email)            → Fields 62, 105
 *   - Field 33 (Account Password) → Fields 63, 120
 *   - Field 6.3 (First Name)      → Fields 114, 115 (slugified subdomain)
 *   - Static                      → Field 123 (https://ops.simplifybiz.com/login)
 */
class FieldCopier {

    private array $captured = [];

    public function register_hooks(): void {
        add_action( 'gform_pre_submission_2',   [ $this, 'capture_sources' ] );
        add_action( 'gform_after_submission_2', [ $this, 'populate_entry' ], 10, 2 );
    }

    /**
     * Capture source values from POST before GF strips them on save.
     */
    public function capture_sources( $form ): void {

        $email      = rgpost( 'input_7' );
        $password   = rgpost( 'input_33' );
        $first_name = rgpost( 'input_6_3' );

        if ( $email !== null && $email !== '' ) {
            $this->captured['email'] = (string) $email;
        }
        if ( $password !== null && $password !== '' ) {
            $this->captured['password'] = (string) $password;
        }
        if ( $first_name !== null && $first_name !== '' ) {
            $this->captured['first_name'] = (string) $first_name;
        }
    }

    /**
     * After entry is saved, build the target values and update the full entry
     * in one shot. GFAPI::update_entry bypasses per-field conditional-logic
     * stripping, so hidden-section children retain their values.
     */
    public function populate_entry( $entry, $form ): void {

        if ( ! class_exists( 'GFAPI' ) ) {
            return;
        }

        $entry_id = (int) rgar( $entry, 'id' );
        if ( $entry_id <= 0 ) {
            return;
        }

        // Resolve source values: prefer entry (already saved), fall back to captured.
        $email = (string) rgar( $entry, '7' );
        if ( $email === '' ) {
            $email = $this->captured['email'] ?? '';
        }

        $password = (string) rgar( $entry, '33' );
        if ( $password === '' ) {
            $password = $this->captured['password'] ?? '';
        }

        $first_name = (string) rgar( $entry, '6.3' );
        if ( $first_name === '' ) {
            $first_name = $this->captured['first_name'] ?? '';
        }

        // Build target assignments on the entry array.
        $dirty = false;

        if ( $email !== '' ) {
            if ( rgar( $entry, '62' )  === '' ) { $entry['62']  = $email; $dirty = true; }
            if ( rgar( $entry, '105' ) === '' ) { $entry['105'] = $email; $dirty = true; }
        }

        if ( $password !== '' ) {
            if ( rgar( $entry, '63' )  === '' ) { $entry['63']  = $password; $dirty = true; }
            if ( rgar( $entry, '120' ) === '' ) { $entry['120'] = $password; $dirty = true; }
        }

        $slug = $this->slugify( $first_name );
        if ( $slug !== '' ) {
            if ( rgar( $entry, '114' ) === '' ) { $entry['114'] = "https://{$slug}.sblik.com"; $dirty = true; }
            if ( rgar( $entry, '115' ) === '' ) { $entry['115'] = "https://{$slug}.sblik.com/login/"; $dirty = true; }
        }

        if ( rgar( $entry, '123' ) === '' ) {
            $entry['123'] = 'https://ops.simplifybiz.com/login';
            $dirty = true;
        }

        if ( ! $dirty ) {
            return;
        }

        // Full-entry update — does not re-run conditional-logic stripping.
        \GFAPI::update_entry( $entry );
    }

    private function slugify( $value ): string {
        if ( $value === null || $value === '' ) {
            return '';
        }
        return strtolower( preg_replace( '/[^a-zA-Z0-9]/', '', (string) $value ) );
    }
}