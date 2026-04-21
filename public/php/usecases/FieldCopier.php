<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Copies values between fields on form 2 (Internship Application) at submission.
 *
 * Replaces Copy Cat for these mappings:
 *   - Field 7  → Field 62, Field 105
 *   - Field 33 → Field 63, Field 120
 *
 * Runs on gform_pre_submission_2: after validation, before save, so the
 * values land in the entry as if the user typed them. Unlike Copy Cat (JS-only),
 * this fires on every submission path including admin edits and workflow re-saves.
 */
class FieldCopier {

    public function register_hooks(): void {
        add_action( 'gform_pre_submission_2', [ $this, 'copy_fields' ] );
    }

    public function copy_fields( $form ): void {

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
}
