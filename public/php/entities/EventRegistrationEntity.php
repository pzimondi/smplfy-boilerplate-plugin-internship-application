<?php

namespace SMPLFY\boilerplate;

use SmplfyCore\SMPLFY_BaseEntity;

class EventRegistrationEntity extends SMPLFY_BaseEntity
{

    /**
     * @inheritDoc
     */
    protected function get_property_map(): array
    {
        return [
            // Name field
            'nameFirst'    => '1.3',
            'nameLast'     => '1.6',

            // Contact info
            'email'        => '3',
            'phone'        => '4',

            // Event selections
            'eventSelected' => '23',
            'addons'        => '15',    // Checkbox field

            // Event details
            'attendees'     => '21',
            'totalCost'     => '22',

            // Terms
            'termsAccepted' => '9',
        ];
    }
}