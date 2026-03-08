<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class InternshipApplicationUsecase {

    private string $webhook_url = 'https://chat.googleapis.com/v1/spaces/AAQAoIBJG0w/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=Qui-5Y4sTCw9r6ZL5RKEh73nzVrapEiTBF9scx487bA';

    public function __construct() {
        // Register the cron hook once when the class is instantiated
        add_action(
            'smplfy_assign_applicant_membership',
            [ $this, 'process_membership_assignment' ],
            10,
            2
        );
    }

    public function handle_application_submission( array $entry ): void {

        $entity = new InternshipApplicationEntity( $entry );

        // Fire and forget — non-blocking
        $this->send_google_chat_notification( $entity );

        // Schedule membership assignment to run async via WP-Cron
        wp_schedule_single_event( time(), 'smplfy_assign_applicant_membership', [
            $entity->email,
            FormIds::APPLICANTS_MEMBERSHIP_ID,
        ] );
    }

    public function process_membership_assignment( string $email, int $membership_id ): void {

        $user = get_user_by( 'email', $email );

        if ( ! $user ) {
            error_log( 'SMPLFY: No WordPress user found for email: ' . $email );
            return;
        }

        MembershipTransactionUsecase::assign_membership_if_not_active( $user->ID, $membership_id );
    }

    private function send_google_chat_notification( InternshipApplicationEntity $entity ): void {

        $fullName = trim( $entity->nameFirst . ' ' . $entity->nameLast );

        $text  = "*New Internship Application Submitted*\n";
        $text .= "Name: {$fullName}\n";
        $text .= "Country: {$entity->country}\n";
        $text .= "Internship: {$entity->internship}\n";
        $text .= "Credits Completed: {$entity->creditsCompleted}\n";

        wp_remote_post( $this->webhook_url, [
            'body'     => wp_json_encode( [ 'text' => $text ] ),
            'headers'  => [ 'Content-Type' => 'application/json; charset=utf-8' ],
            'timeout'  => 0.01,
            'blocking' => false,
        ] );
    }
}