<?php

namespace SMPLFY\boilerplate;

use SmplfyCore\SMPLFY_BaseEntity;

class ESignatureAgreementEntity extends SMPLFY_BaseEntity {

    protected function get_property_map(): array {
        return [
            'signerName'  => '4',
            'signerEmail' => '7',
        ];
    }
}