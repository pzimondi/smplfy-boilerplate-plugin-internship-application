<?php
/**
 *  Loads specified files and all files in specified directories initialises dependencies
 */

namespace SMPLFY\boilerplate;

function bootstrap_boilerplate_plugin() {
	require_boilerplate_dependencies();

	DependencyFactory::create_plugin_dependencies();
}

/**
 * When adding a new directory to the custom plugin, remember to require it here
 *
 * @return void
 */
function require_boilerplate_dependencies() {

	require_file( 'includes/enqueue_scripts.php' );
	require_file( 'admin/DependencyFactory.php' );

	require_directory( 'public/php/types' );
	require_directory( 'public/php/entities' );
	require_directory( 'public/php/repositories' );
	require_directory( 'public/php/usecases' );
	require_directory( 'public/php/adapters' );
    require_directory( 'public/php/presentation' );

}

