<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class InternshipApplicationUsecase {

    private string $webhook_url = 'https://chat.googleapis.com/v1/spaces/AAQAoIBJG0w/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=Qui-5Y4sTCw9r6ZL5RKEh73nzVrapEiTBF9scx487bA';

    public function handle_application_submission( array $entry ): void {

        $entity = new InternshipApplicationEntity( $entry );

        // Always send Google Chat notification first
        $this->send_google_chat_notification( $entity );

        // Find the WordPress user created by Gravity Forms User Registration
        $user = get_user_by( 'email', $entity->email );

        if ( ! $user ) {
            error_log( 'SMPLFY: No WordPress user found for email: ' . $entity->email );
            return;
        }

        // Assign the Applicants membership transaction
        MembershipTransactionUsecase::assign_membership_if_not_active(
            $user->ID,
            FormIds::APPLICANTS_MEMBERSHIP_ID
        );
    }

    private function send_google_chat_notification( InternshipApplicationEntity $entity ): void {

        $fullName = trim( $entity->nameFirst . ' ' . $entity->nameLast );

        $text  = "*New Internship Application Submitted*\n";
        $text .= "Name: {$fullName}\n";
        $text .= "Country: {$entity->country}\n";
        $text .= "Internship: {$entity->internship}\n";
        $text .= "Credits Completed: {$entity->creditsCompleted}\n";

        $response = wp_remote_post( $this->webhook_url, [
            'body'    => wp_json_encode( [ 'text' => $text ] ),
            'headers' => [ 'Content-Type' => 'application/json; charset=utf-8' ],
            'timeout' => 0.01,
            'blocking' => false,
        ]);

        if ( is_wp_error( $response ) ) {
            error_log( 'SMPLFY: Google Chat error: ' . $response->get_error_message() );
        }
    }
}