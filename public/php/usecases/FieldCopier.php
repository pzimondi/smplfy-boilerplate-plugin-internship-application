<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Populates fields on form 2 (Internship Application) at submission time.
 *
 * Two responsibilities:
 *
 * 1. Direct copies (replaces Copy Cat JS):
 *    - Field 7  → Field 62, Field 105
 *    - Field 33 → Field 63, Field 120
 *
 * 2. Computed sblik URLs built from first name (field 6.3):
 *    - Field 114 = https://{firstname}.sblik.com
 *    - Field 115 = https://{firstname}.sblik.com/login/
 *    First name is slugified: lowercased and stripped of non-alphanumerics
 *    so it produces a valid subdomain.
 *
 * Runs on gform_pre_submission_2: after validation, before save, so the
 * values land in the entry as if the user typed them. Unlike Copy Cat (JS-only)
 * and GF default-value merge tags (which aren't evaluated for URL fields),
 * this fires on every submission path including admin edits and workflow re-saves.
 */
class FieldCopier {

    public function register_hooks(): void {
        add_action( 'gform_pre_submission_2', [ $this, 'populate_fields' ] );
    }

    public function populate_fields( $form ): void {
        $this->copy_fields();
        $this->build_sblik_urls();
    }

    private function copy_fields(): void {

        $map = [
            '7'  => [ '62', '105' ],
            '33' => [ '63', '120' ],
        ];

        foreach ( $map as $source_id => $target_ids ) {

            $source_value = rgpost( 'input_' . $source_id );

            if ( $source_value === null || $source_value === '' ) {
                continue;
            }

            foreach ( $target_ids as $target_id ) {
                $_POST[ 'input_' . $target_id ] = $source_value;
            }
        }
    }

    private function build_sblik_urls(): void {

        $first_name = rgpost( 'input_6_3' );

        if ( $first_name === null || $first_name === '' ) {
            return;
        }

        $slug = strtolower( preg_replace( '/[^a-zA-Z0-9]/', '', $first_name ) );

        if ( $slug === '' ) {
            return;
        }

        $_POST['input_114'] = "https://{$slug}.sblik.com";
        $_POST['input_115'] = "https://{$slug}.sblik.com/login/";
    }
}