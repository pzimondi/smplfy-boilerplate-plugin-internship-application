<?php

namespace SMPLFY\boilerplate;

class DependencyFactory {

    static function create_plugin_dependencies(): void {

        $internshipApplicationUsecase  = new InternshipApplicationUsecase();
        $userCreatedUsecase            = new UserCreatedUsecase();
        $backfillMembershipsUsecase    = new BackfillMembershipsUsecase();
        $deleteUserUsecase             = new DeleteUserUsecase();
        $taskEvaluationCompleteUsecase = new TaskEvaluationCompleteUsecase();

        new GravityFormsAdapter( $internshipApplicationUsecase );
        new WordpressAdapter(
            $userCreatedUsecase,
            $backfillMembershipsUsecase,
            $deleteUserUsecase
        );
        new GravityFlowAdapter( $taskEvaluationCompleteUsecase );
    }
}