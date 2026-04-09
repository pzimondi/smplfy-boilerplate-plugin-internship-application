<?php
namespace SMPLFY\boilerplate;
return;

/**
 * Plugin Name: SMPLFY Boiler Plate Internship
 * Version: 1.0.0
 * Description: Plugin for Internship Application
 * Author: Pastor Munashe Zimondi
 * Author URI: https://simplifybiz.com/
 * Requires PHP: 7.4
 * Requires Plugins:  smplfy-core
 *
 * @package Bliksem
 * @author Pastor Munashe Zimondi
 * @since 0.0.1
 */

prevent_external_script_execution();

define( 'SITE_URL', get_site_url() );
define( 'SMPLFY_NAME_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SMPLFY_NAME_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once SMPLFY_NAME_PLUGIN_DIR . 'admin/utilities/smplfy_require_utilities.php';
require_once SMPLFY_NAME_PLUGIN_DIR . 'includes/smplfy_bootstrap.php';

add_action( 'plugins_loaded', 'SMPLFY\boilerplate\bootstrap_boilerplate_plugin' );

function prevent_external_script_execution(): void {
    if ( ! function_exists( 'get_option' ) ) {
        header( 'HTTP/1.0 403 Forbidden' );
        die;
    }
}