<?php

namespace SMPLFY\boilerplate;

use SmplfyCore\SMPLFY_BaseRepository;
use SmplfyCore\SMPLFY_GravityFormsApiWrapper;
use WP_Error;

/**
 * @method static ESignatureAgreementEntity|null get_one( $fieldId, $value )
 * @method static ESignatureAgreementEntity|null get_one_for_current_user()
 * @method static ESignatureAgreementEntity|null get_one_for_user( $userId )
 * @method static ESignatureAgreementEntity[] get_all( $fieldId = null, $value = null, string $direction = 'ASC' )
 * @method static int|WP_Error add( ESignatureAgreementEntity $entity )
 */
class ESignatureAgreementRepository extends SMPLFY_BaseRepository {

    public function __construct( SMPLFY_GravityFormsApiWrapper $gravityFormsApi ) {
        $this->entityType = ESignatureAgreementEntity::class;
        $this->formId     = FormIds::ESIGNATURE_AGREEMENT_FORM_ID;

        parent::__construct( $gravityFormsApi );
    }
}