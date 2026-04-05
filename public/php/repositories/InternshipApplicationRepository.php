<?php

namespace SMPLFY\boilerplate;

use SmplfyCore\SMPLFY_BaseRepository;
use SmplfyCore\SMPLFY_GravityFormsApiWrapper;
use WP_Error;

/**
 * @method static InternshipApplicationEntity|null get_one( $fieldId, $value )
 * @method static InternshipApplicationEntity|null get_one_for_current_user()
 * @method static InternshipApplicationEntity|null get_one_for_user( $userId )
 * @method static InternshipApplicationEntity[] get_all( $fieldId = null, $value = null, string $direction = 'ASC' )
 * @method static int|WP_Error add( InternshipApplicationEntity $entity )
 */
class InternshipApplicationRepository extends SMPLFY_BaseRepository {

    public function __construct( SMPLFY_GravityFormsApiWrapper $gravityFormsApi ) {
        $this->entityType = InternshipApplicationEntity::class;
        $this->formId     = FormIds::INTERNSHIP_APPLICATION_FORM_ID;

        parent::__construct( $gravityFormsApi );
    }
}