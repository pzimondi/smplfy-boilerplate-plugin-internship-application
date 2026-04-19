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

        // Adapters
        new GravityFormsAdapter( $internshipApplication );
        new WordpressAdapter( $userCreated, $backfillMemberships, $deleteUser, $loginRedirect );
        new GravityFlowAdapter( $workflowNotifications );
        new ESignatureAdapter( $eSignatureNotifications );

        // Presentation
        new FooterAccentBar();
    }
}