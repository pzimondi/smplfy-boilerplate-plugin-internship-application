<?php

namespace SMPLFY\boilerplate;

class DependencyFactory {

    private static bool $initialized = false;

    static function create_plugin_dependencies(): void {

        if ( self::$initialized ) {
            return;
        }
        self::$initialized = true;

        $gravityFormsApi = new \SmplfyCore\SMPLFY_GravityFormsApiWrapper();

        // Repositories
        $internshipApplicationRepository = new InternshipApplicationRepository( $gravityFormsApi );
        $eSignatureAgreementRepository   = new ESignatureAgreementRepository( $gravityFormsApi );

        // Usecases
        $internshipApplication   = new InternshipApplication();
        $userCreated             = new UserCreated();
        $backfillMemberships     = new BackfillMemberships();
        $deleteUser              = new DeleteUser();
        $loginRedirect           = new LoginRedirect();
        $workflowNotifications   = new WorkflowNotifications( $internshipApplicationRepository, $eSignatureAgreementRepository );
        $eSignatureNotifications = new ESignatureNotifications();
        $stepCompleteRedirect    = new StepCompleteRedirect();
        $fieldCopier             = new FieldCopier();
        $fieldCopier->register_hooks();

        // Adapters
        new GravityFormsAdapter( $internshipApplication );
        new WordpressAdapter( $userCreated, $backfillMemberships, $deleteUser, $loginRedirect );
        new GravityFlowAdapter( $workflowNotifications, $stepCompleteRedirect );
        new ESignatureAdapter( $eSignatureNotifications );

        // Presentation
        new FooterAccentBar();
        new HeaderAccentBar();


    }
}