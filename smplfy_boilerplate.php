<?php

namespace SMPLFY\boilerplate;

function bootstrap_boilerplate_plugin() {
    require_boilerplate_dependencies();
    DependencyFactory::create_plugin_dependencies();
}

function require_boilerplate_dependencies() {

    $log = SMPLFY_NAME_PLUGIN_DIR . 'debug-error.txt';
    file_put_contents( $log, "Starting load\n" );

    require_file( 'includes/enqueue_scripts.php' );
    file_put_contents( $log, "enqueue ok\n", FILE_APPEND );

    require_file( 'admin/DependencyFactory.php' );
    file_put_contents( $log, "DependencyFactory ok\n", FILE_APPEND );

    require_directory( 'public/php/types' );
    file_put_contents( $log, "types ok\n", FILE_APPEND );

    require_directory( 'public/php/entities' );
    file_put_contents( $log, "entities ok\n", FILE_APPEND );

    require_directory( 'public/php/repositories' );
    file_put_contents( $log, "repositories ok\n", FILE_APPEND );

    require_directory( 'public/php/usecases' );
    file_put_contents( $log, "usecases ok\n", FILE_APPEND );

    require_directory( 'public/php/adapters' );
    file_put_contents( $log, "adapters ok\n", FILE_APPEND );

    file_put_contents( $log, "All loaded ok\n", FILE_APPEND );
}