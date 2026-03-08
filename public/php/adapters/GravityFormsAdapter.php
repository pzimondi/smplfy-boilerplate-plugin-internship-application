<?php

namespace SMPLFY\boilerplate;

class GravityFormsAdapter {

    private InternshipApplicationUsecase $internshipApplicationUsecase;

    public function __construct( InternshipApplicationUsecase $internshipApplicationUsecase ) {
        $this->internshipApplicationUsecase = $internshipApplicationUsecase;

        $this->register_hooks();
        $this->register_filters();
    }

    public function register_hooks() {
        add_action(
            'gform_after_submission_' . FormIds::INTERNSHIP_APPLICATION_FORM_ID,
            function( $entry, $form ) {
                $this->internshipApplicationUsecase->handle_application_submission( $entry );
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

                $confirmation  = '<div style="';
                $confirmation .= 'font-family: sans-serif;';
                $confirmation .= 'max-width: 520px;';
                $confirmation .= 'margin: 40px auto;';
                $confirmation .= 'padding: 32px;';
                $confirmation .= 'background: #f9f9f9;';
                $confirmation .= 'border-radius: 10px;';
                $confirmation .= 'border-left: 5px solid #4CAF50;';
                $confirmation .= 'text-align: center;">';
                $confirmation .= '<h2 style="color: #4CAF50; margin-bottom: 12px;">Application Submitted!</h2>';
                $confirmation .= '<p style="color: #333; font-size: 16px;">Thank you <strong>' . esc_html( $first_name ) . '</strong>,</p>';
                $confirmation .= '<p style="color: #555; font-size: 15px;">Your application has been successfully submitted.<br>We will review it and be in touch with you shortly.</p>';
                $confirmation .= '<p style="color: #888; font-size: 13px; margin-top: 20px;">Redirecting you to your dashboard...</p>';
                $confirmation .= '<script>';
                $confirmation .= 'setTimeout(function(){';
                $confirmation .= 'window.location.href="https://intern.simplifybiz.com/interns-dashboard/";';
                $confirmation .= '}, 4000);';
                $confirmation .= '</script>';
                $confirmation .= '</div>';

                return $confirmation;
            },
            10,
            4
        );
    }
}