<?php

namespace SMPLFY\boilerplate;

use SmplfyCore\SMPLFY_BaseEntity;

class ExampleEntity extends SMPLFY_BaseEntity {

    public function __construct( $formEntry = [] ) {
        parent::__construct( $formEntry );
        $this->formId = FormIds::CONTACT_FORM_ID;
    }

    protected function get_property_map(): array {
        return [

            // Name (ID 1 — but this is a "Name" field, so use 1.3 and 1.6)
            'nameFirst' => '1.3',
            'nameLast'  => '1.6',

            // Email (ID 2)
            'email' => '2',

            // Comments/Questions (ID 3)
            'message' => '3',

            // Address (ID 4)
            'address_street'   => '4.1',
            'address_city'     => '4.3',
            'address_country'  => '4.6',

            // Phone (ID 5)
            'phone' => '5',

            // Preferred contact method (ID 11)
            'preferred_contact_method' => '11',

            // Best time to call (ID 12)
            'best_time_to_call' => '12',
        ];
    }
}



