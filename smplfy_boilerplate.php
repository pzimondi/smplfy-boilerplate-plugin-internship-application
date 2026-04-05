<?php
/**
 * Plugin Name: SMPLFY Boiler Plate
 * Version: 1.0.0
 * Description: Starter plugin with custom plugin framework to get started
 * Author: Thomas Picolo-Donnelly
 * Author URI: https://simplifybiz.com/
 * Requires PHP: 7.4
 * Requires Plugins:  smplfy-core
 *
 * @package Bliksem
 * @author Thomas Picolo-Donnelly
 * @since 0.0.1
 */

namespace SMPLFY\boilerplate;

ini_set('display_errors', 1);
error_reporting(E_ALL);

prevent_external_script_execution();

define( 'SITE_URL', get_site_url() );
define( 'SMPLFY_NAME_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SMPLFY_NAME_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

//Load files and run function that initialise the whole plugin
require_once SMPLFY_NAME_PLUGIN_DIR . 'admin/utilities/smplfy_require_utilities.php';
require_once SMPLFY_NAME_PLUGIN_DIR . 'includes/smplfy_bootstrap.php';

bootstrap_boilerplate_plugin();

//require_once SMPLFY_NAME_PLUGIN_DIR . 'includes/smplfy_bootstrap.php';

function prevent_external_script_execution(): void {
	if ( ! function_exists( 'get_option' ) ) {
		header( 'HTTP/1.0 403 Forbidden' );
		die;
	}
}