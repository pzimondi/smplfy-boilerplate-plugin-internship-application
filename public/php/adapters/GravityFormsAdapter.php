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
            [ $this->internshipApplicationUsecase, 'handle_application_submission' ],
            10,
            2
        );
    }

    public function register_filters() {
        // Leave empty unless you need Gravity Forms filters
    }
}