<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Populates derived fields on form 2 (Internship Application) at submission.
 *
 * Uses a dual-hook strategy for maximum reliability:
 *
 *   1. gform_pre_submission_2 — injects values into $_POST so GF picks them up
 *      during its normal save pipeline. Works for most fields.
 *
 *   2. gform_after_submission_2 — reads the just-saved entry and uses GFAPI
 *      to write any missing values directly. Catches fields that pre_submission
 *      missed because of visibility / conditional logic / page membership issues.
 *
 * Direct copies:
 *   - Field 7  (Email)            → Fields 62, 105
 *   - Field 33 (Account Password) → Fields 63, 120
 *
 * Computed sblik URLs from first name (field 6.3), slugified:
 *   - Field 114 = https://{firstname}.sblik.com
 *   - Field 115 = https://{firstname}.sblik.com/login/
 *
 * Static URL:
 *   - Field 123 = https://ops.simplifybiz.com/login
 *
 * NOTE: backfill_entry has temporary diagnostic logging to debug field 120.
 * Remove the SMPLFY_Log::error() calls once the issue is resolved.
 */
class FieldCopier {

    public function register_hooks(): void {
        add_action( 'gform_pre_submission_2',   [ $this, 'inject_post_values' ] );
        add_action( 'gform_after_submission_2', [ $this, 'backfill_entry' ], 10, 2 );
    }

    /**
     * Pre-submission: inject values into $_POST.
     * Handles fields that are on the current page of the multi-page form.
     */
    public function inject_post_values( $form ): void {

        // Direct copies
        $email = rgpost( 'input_7' );
        if ( $email !== null && $email !== '' ) {
            $_POST['input_62']  = $email;
            $_POST['input_105'] = $email;
        }

        $password = rgpost( 'input_33' );
        if ( $password !== null && $password !== '' ) {
            $_POST['input_63']  = $password;
            $_POST['input_120'] = $password;
        }

        // Sblik URLs from first name
        $first_name = rgpost( 'input_6_3' );
        $slug       = $this->slugify( $first_name );
        if ( $slug !== '' ) {
            $_POST['input_114'] = "https://{$slug}.sblik.com";
            $_POST['input_115'] = "https://{$slug}.sblik.com/login/";
        }

        // Static ops URL
        $_POST['input_123'] = 'https://ops.simplifybiz.com/login';
    }

    /**
     * After submission: backfill anything that didn't stick from pre_submission.
     * Fires after GF has saved the entry; we read what's there and patch gaps.
     */
    public function backfill_entry( $entry, $form ): void {

        if ( ! class_exists( 'GFAPI' ) ) {
            return;
        }

        $entry_id = (int) rgar( $entry, 'id' );
        if ( $entry_id <= 0 ) {
            return;
        }

        $updates = [];

        $email = (string) rgar( $entry, '7' );
        if ( $email !== '' ) {
            if ( rgar( $entry, '62' )  === '' ) { $updates['62']  = $email; }
            if ( rgar( $entry, '105' ) === '' ) { $updates['105'] = $email; }
        }

        $password = (string) rgar( $entry, '33' );
        if ( $password !== '' ) {
            if ( rgar( $entry, '63' )  === '' ) { $updates['63']  = $password; }
            if ( rgar( $entry, '120' ) === '' ) { $updates['120'] = $password; }
        }

        $first_name = (string) rgar( $entry, '6.3' );
        $slug       = $this->slugify( $first_name );
        if ( $slug !== '' ) {
            if ( rgar( $entry, '114' ) === '' ) { $updates['114'] = "https://{$slug}.sblik.com"; }
            if ( rgar( $entry, '115' ) === '' ) { $updates['115'] = "https://{$slug}.sblik.com/login/"; }
        }

        if ( rgar( $entry, '123' ) === '' ) {
            $updates['123'] = 'https://ops.simplifybiz.com/login';
        }

        // DIAGNOSTIC: log the entry state for field 120 and password source
        \SmplfyCore\SMPLFY_Log::error( "FieldCopier entry snapshot: id={$entry_id} field_33='" . rgar( $entry, '33' ) . "' field_63='" . rgar( $entry, '63' ) . "' field_120='" . rgar( $entry, '120' ) . "'" );
        \SmplfyCore\SMPLFY_Log::error( 'FieldCopier updates queued: ' . print_r( $updates, true ) );

        if ( empty( $updates ) ) {
            return;
        }

        foreach ( $updates as $field_id => $value ) {
            $result = \GFAPI::update_entry_field( $entry_id, $field_id, $value );

            // DIAGNOSTIC: log each write result
            $result_str = is_wp_error( $result )
                ? 'WP_Error: ' . $result->get_error_message()
                : var_export( $result, true );
            \SmplfyCore\SMPLFY_Log::error( "FieldCopier update field={$field_id} result={$result_str}" );
        }
    }

    private function slugify( $value ): string {
        if ( $value === null || $value === '' ) {
            return '';
        }
        return strtolower( preg_replace( '/[^a-zA-Z0-9]/', '', (string) $value ) );
    }
}