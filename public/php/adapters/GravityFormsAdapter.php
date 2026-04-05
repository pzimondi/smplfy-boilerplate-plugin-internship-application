<?php

namespace SMPLFY\boilerplate;

class GravityFormsAdapter {

    private InternshipApplication $internshipApplication;

    public function __construct( InternshipApplication $internshipApplication ) {
        $this->internshipApplication = $internshipApplication;

        $this->register_hooks();
        $this->register_filters();
    }

    public function register_hooks() {
        add_action(
            'gform_after_submission_' . FormIds::INTERNSHIP_APPLICATION_FORM_ID,
            function( $entry, $form ) {
                $this->internshipApplication->handle_application_submission( $entry );
            },
            10,
            2
        );
    }

    public function register_filters() {

        add_filter(
            'gform_confirmation_' . FormIds::INTERNSHIP_APPLICATION_FORM_ID,
            function( $confirmation, $form, $entry, $ajax ) {

                $first_name = rgar( $entry, '6.3' );

                return '<div style="'
                    . 'font-family: sans-serif;'
                    . 'max-width: 520px;'
                    . 'margin: 40px auto;'
                    . 'padding: 32px;'
                    . 'background: #f9f9f9;'
                    . 'border-radius: 10px;'
                    . 'border-left: 5px solid #4CAF50;'
                    . 'text-align: center;">'
                    . '<h2 style="color: #4CAF50; margin-bottom: 12px;">Application Submitted!</h2>'
                    . '<p style="color: #333; font-size: 16px;">Thank you <strong>' . esc_html( $first_name ) . '</strong>,</p>'
                    . '<p style="color: #555; font-size: 15px;">Your application has been successfully submitted.<br>We will review it and be in touch with you shortly.</p>'
                    . '<p style="color: #888; font-size: 13px; margin-top: 20px;">Redirecting you to your dashboard...</p>'
                    . '<script>'
                    . 'setTimeout(function(){'
                    . 'window.location.href="https://intern.simplifybiz.com/applicant-dashboard/";'
                    . '}, 10000);'
                    . '</script>'
                    . '</div>';
            },
            999,
            4
        );
    }
}