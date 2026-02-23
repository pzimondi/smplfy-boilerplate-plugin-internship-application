<?php
/**
 * Task 1 + Task 3 Usecase Logic + Task 5 Logging
 */

namespace SMPLFY\boilerplate;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ExampleUsecase {

    private ?ExampleRepository $exampleRepository;

    public function __construct( ExampleRepository $exampleRepository = null ) {
        $this->exampleRepository = $exampleRepository;
    }

    /**
     * Task 1 – Contact form submission
     */
    public function example_function( array $entry ): void {

        /* 🔹 TASK 5 LOGGING */
        error_log( 'Task 5: Contact form submitted' );
        error_log( print_r( $entry, true ) );

        $entity = new ExampleEntity( $entry );

        $data = [
            'name_first' => $entity->nameFirst,
            'name_last'  => $entity->nameLast,
            'full_name'  => trim( $entity->nameFirst . ' ' . $entity->nameLast ),
            'email'      => $entity->email,
            'message'    => $entity->message,
            'address_street'  => $entity->address_street,
            'address_city'    => $entity->address_city,
            'address_country' => $entity->address_country,
            'phone' => $entity->phone,
            'preferred_contact_method' => $entity->preferred_contact_method,
            'best_time_to_call'        => $entity->best_time_to_call,
        ];

        wp_remote_post(
            'https://webhook.site/5824f131-1149-49d0-99ab-ba154d3a27f6',
            [
                'method' => 'POST',
                'body'   => $data,
            ]
        );
    }

    /**
     * Task 3 – Event Registration Submission
     */
    public function handle_event_registration( array $entry ): void {

        /* 🔹 TASK 5 LOGGING */
        error_log( 'Task 5: Event registration submitted' );
        error_log( print_r( $entry, true ) );

        $entity = new EventRegistrationEntity( $entry );

        $data = [
            'full_name'  => trim( $entity->nameFirst . ' ' . $entity->nameLast ),
            'email'      => $entity->email,
            'phone'      => $entity->phone,
            'event'      => $entity->eventSelected,
            'attendees'  => $entry['21'] ?? '',
            'addons'     => $entry['15'] ?? '',
            'total_cost' => $entity->totalCost,
        ];

        wp_remote_post(
            'https://webhook.site/YOUR-UNIQUE-URL',
            [
                'method' => 'POST',
                'body'   => $data,
            ]
        );
    }

}
