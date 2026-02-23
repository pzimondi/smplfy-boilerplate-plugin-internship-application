<?php

namespace SMPLFY\boilerplate;

use SmplfyCore\SMPLFY_BaseEntity;

class InternshipApplicationEntity extends SMPLFY_BaseEntity {

    protected function get_property_map(): array {
        return [
            'nameFirst'       => '6.3',
            'nameLast'        => '6.6',
            'country'         => '11.6',
            'internship'      => '3',
            'creditsCompleted' => '10',
        ];
    }
}