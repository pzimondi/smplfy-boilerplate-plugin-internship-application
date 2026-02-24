<?php

namespace SMPLFY\boilerplate;

class DependencyFactory {

    static function create_plugin_dependencies(): void {

        $internshipApplicationUsecase = new InternshipApplicationUsecase();
        $userCreatedUsecase           = new UserCreatedUsecase();
        $backfillMembershipsUsecase   = new BackfillMembershipsUsecase();

        new GravityFormsAdapter( $internshipApplicationUsecase );
        new WordpressAdapter( $userCreatedUsecase, $backfillMembershipsUsecase );
    }
}