<?php

namespace SMPLFY\boilerplate;

use SmplfyCore\SMPLFY_BaseRepository;
use SmplfyCore\SMPLFY_GravityFormsApiWrapper;
use WP_Error;

/**
 * @method static EventRegistrationEntity|null get_one( $fieldId, $value )
 * @method static EventRegistrationEntity|null get_one_for_current_user()
 * @method static EventRegistrationEntity|null get_one_for_user( $userId )
 * @method static EventRegistrationEntity[] get_all( $fieldId = null, $value = null, string $direction = 'ASC' )
 * @method static int|WP_Error add( EventRegistrationEntity $entity )
 */
class EventRegistrationRepository extends SMPLFY_BaseRepository {

    public function __construct( SMPLFY_GravityFormsApiWrapper $gravityFormsApi ) {
        $this->entityType = EventRegistrationEntity::class;
        $this->formId     = FormIds::EVENT_REGISTRATION_FORM_ID;

        parent::__construct( $gravityFormsApi );
    }
}