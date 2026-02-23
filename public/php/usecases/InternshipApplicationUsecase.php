<?php

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class InternshipApplicationUsecase {

    private string $webhook_url = 'https://chat.googleapis.com/v1/spaces/AAQAoIBJG0w/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=Qui-5Y4sTCw9r6ZL5RKEh73nzVrapEiTBF9scx487bA';

    public function handle_application_submission( array $entry ): void {

        $entity = new InternshipApplicationEntity( $entry );

        $fullName = trim( $entity->nameFirst . ' ' . $entity->nameLast );

        $text  = "*New Internship Application Submitted*\n";
        $text .= "Name: {$fullName}\n";
        $text .= "Country: {$entity->country}\n";
        $text .= "Internship: {$entity->internship}\n";
        $text .= "Credits Completed: {$entity->creditsCompleted}\n";

        $args = [
            'body'    => wp_json_encode( [ 'text' => $text ] ),
            'headers' => [ 'Content-Type' => 'application/json; charset=utf-8' ],
            'timeout' => 15,
        ];

        $response = wp_remote_post( $this->webhook_url, $args );

        if ( is_wp_error( $response ) ) {
            error_log( 'Google Chat error (Internship Application): ' . $response->get_error_message() );
        } else {
            error_log( 'Google Chat notification sent successfully for internship application.' );
        }
    }
}