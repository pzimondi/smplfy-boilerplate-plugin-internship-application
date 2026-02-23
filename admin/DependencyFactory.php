<?php
/**
 * A factory class responsible for creating and initializing all dependencies used in the plugin
 */

namespace SMPLFY\boilerplate;

class DependencyFactory {

    /**
     * Create and initialize all dependencies
     *
     * @return void
     */
    static function create_plugin_dependencies() {

        // Usecases
        $internshipApplicationUsecase = new InternshipApplicationUsecase();

        // Adapters
        new GravityFormsAdapter( $internshipApplicationUsecase );
    }
}