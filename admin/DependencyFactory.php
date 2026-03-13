<?php

namespace SMPLFY\boilerplate;

class DependencyFactory {

    private static bool $initialized = false;

    static function create_plugin_dependencies(): void {

        if ( self::$initialized ) {
            return;
        }
        self::$initialized = true;

        $internshipApplicationUsecase = new InternshipApplicationUsecase();
        $userCreatedUsecase           = new UserCreatedUsecase();
        $backfillMembershipsUsecase   = new BackfillMembershipsUsecase();
        $deleteUserUsecase            = new DeleteUserUsecase();

        new GravityFormsAdapter( $internshipApplicationUsecase );
        new WordpressAdapter( $userCreatedUsecase, $backfillMembershipsUsecase, $deleteUserUsecase );
        new WorkflowNotificationsUsecase();
    }
}