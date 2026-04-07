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

        // Usecases
        $internshipApplicationUsecase = new InternshipApplicationUsecase();
        $userCreatedUsecase           = new UserCreatedUsecase();
        $backfillMembershipsUsecase   = new BackfillMembershipsUsecase();
        $deleteUserUsecase            = new DeleteUserUsecase();
        $workflowNotificationsUsecase = new WorkflowNotificationsUsecase( $internshipApplicationRepository );
        $eSignatureNotificationsUsecase = new ESignatureNotificationsUsecase();

        // Adapters
        new GravityFormsAdapter( $internshipApplicationUsecase );
        new WordpressAdapter( $userCreatedUsecase, $backfillMembershipsUsecase, $deleteUserUsecase );
        new GravityFlowAdapter( $workflowNotificationsUsecase );
        new ESignatureAdapter( $eSignatureNotificationsUsecase );
    }
}