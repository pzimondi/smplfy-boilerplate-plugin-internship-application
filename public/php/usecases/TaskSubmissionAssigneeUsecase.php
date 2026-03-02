<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TaskSubmissionAssigneeUsecase {

    /**
     * Dynamically assigns the Submit Task Evidence step
     * to the specific user who submitted the form,
     * based on their email address in field 4.
     * This ensures only that specific applicant sees
     * the step in their inbox, not all applicants.
     */
    public function filter_assignees( array $assignees, $step ): array {

        if ( $step->get_form_id() != FormIds::TASK_SUBMISSION_FORM_ID ) {
            return $assignees;
        }

        if ( $step->get_name() != 'Submit Task Evidence' ) {
            return $assignees;
        }

        $entry = $step->get_entry();
        $email = rgar( $entry, FormIds::TASK_SUBMISSION_EMAIL_FIELD_ID );

        if ( ! $email ) {
            error_log( 'SMPLFY: No email found in Task Submission entry.' );
            return $assignees;
        }

        $user = get_user_by( 'email', $email );

        if ( ! $user ) {
            error_log( 'SMPLFY: No WordPress user found for email: ' . $email );
            return $assignees;
        }

        return [ new \Gravity_Flow_Assignee( 'user_id|' . $user->ID, $step ) ];
    }
}