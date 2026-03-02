<?php

namespace SMPLFY\boilerplate;

class DependencyFactory {

    static function create_plugin_dependencies(): void {

        $internshipApplicationUsecase  = new InternshipApplicationUsecase();
        $userCreatedUsecase            = new UserCreatedUsecase();
        $backfillMembershipsUsecase    = new BackfillMembershipsUsecase();
        $deleteUserUsecase             = new DeleteUserUsecase();
        $taskSubmissionAssigneeUsecase = new TaskSubmissionAssigneeUsecase();

        new GravityFormsAdapter( $internshipApplicationUsecase );
        new WordpressAdapter(
            $userCreatedUsecase,
            $backfillMembershipsUsecase,
            $deleteUserUsecase,
            $taskSubmissionAssigneeUsecase
        );
    }
}