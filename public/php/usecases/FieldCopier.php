<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Populates derived fields on form 2 (Internship Application) at save time.
 *
 * Uses gform_entry_pre_save because it fires AFTER form processing is complete
 * and writes directly to the entry array going into the database. This sidesteps
 * POST/visibility/conditional-logic timing issues that plague pre_submission for
 * Hidden and Administrative fields on later pages of multi-page forms.
 *
 * Direct copies (values derived from user input):
 *   - Field 7  (Email)           → Fields 62, 105       (Sandbox + ops usernames)
 *   - Field 33 (Account Password) → Fields 63, 120      (Sandbox + ops passwords)
 *
 * Computed sblik URLs built from first name (field 6.3), lowercased and stripped
 * of non-alphanumerics to make a valid subdomain:
 *   - Field 114 = https://{firstname}.sblik.com         (Assigned Subdomain)
 *   - Field 115 = https://{firstname}.sblik.com/login/  (Sandbox Login URL)
 *
 * Static URL (never changes):
 *   - Field 123 = https://ops.simplifybiz.com/login     (ops.simplifybiz.com login URL)
 */
class FieldCopier {

    public function register_hooks(): void {
        add_filter( 'gform_entry_pre_save', [ $this, 'populate_entry' ], 10, 2 );
    }

    public function populate_entry( $entry, $form ) {

        if ( (int) rgar( $form, 'id' ) !== (int) FormIds::INTERNSHIP_APPLICATION_FORM_ID ) {
            return $entry;
        }

        // Direct copies: email → sandbox/ops username, password → sandbox/ops password
        $email    = (string) rgar( $entry, '7' );
        $password = (string) rgar( $entry, '33' );

        if ( $email !== '' ) {
            $entry['62']  = $email;
            $entry['105'] = $email;
        }

        if ( $password !== '' ) {
            $entry['63']  = $password;
            $entry['120'] = $password;
        }

        // Computed sblik URLs from first name subfield (6.3)
        $first_name = (string) rgar( $entry, '6.3' );
        $slug       = strtolower( preg_replace( '/[^a-zA-Z0-9]/', '', $first_name ) );

        if ( $slug !== '' ) {
            $entry['114'] = "https://{$slug}.sblik.com";
            $entry['115'] = "https://{$slug}.sblik.com/login/";
        }

        // Static ops login URL
        $entry['123'] = 'https://ops.simplifybiz.com/login';

        return $entry;
    }
}